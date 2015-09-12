<?php

class Oxygen_EventListener_AttachStateListener
{
    /**
     * @var Oxygen_Drupal_StateInterface
     */
    private $state;

    /**
     * @var Oxygen_Drupal_Context
     */
    private $context;

    /**
     * @var DatabaseConnection
     */
    private $connection;

    /**
     * @var Oxygen_System_Environment
     */
    private $environment;

    /**
     * @param Oxygen_Drupal_StateInterface $state
     * @param Oxygen_Drupal_Context        $context
     * @param DatabaseConnection           $connection
     * @param Oxygen_System_Environment    $environment
     */
    public function __construct(Oxygen_Drupal_StateInterface $state, Oxygen_Drupal_Context $context, DatabaseConnection $connection, Oxygen_System_Environment $environment)
    {
        $this->state       = $state;
        $this->context     = $context;
        $this->connection  = $connection;
        $this->environment = $environment;
    }

    public function onMasterResponse(Oxygen_Event_MasterResponseEvent $event)
    {
        $response = $event->getResponse();

        if (!$response instanceof Oxygen_Http_JsonResponse) {
            return;
        }

        $content = $response->getContent();

        $content['stateResult'] = $this->getState($event->getRequestData()->stateParameters, $event->getRequest());

        $response->setContent($content);
    }

    private function getState($params, Oxygen_Http_Request $request)
    {
        $state = array();

        $this->populateSiteState($state, $request);
        $this->populateServerState($state, $request);
        $this->populateExtensionsState($params, $state);
        $this->populateUpdatesState($state);

        return $state;
    }

    private function populateSiteState(&$state, Oxygen_Http_Request $request)
    {
        // See how $site_key gets generated in _update_process_fetch_task() for statistical purposes.
        $state['siteKey']            = strtr(base64_encode(hash_hmac('sha256', (string)$this->context->getGlobal('base_url'), (string)$this->state->get('drupal_private_key'), true)), array('+' => '-', '/' => '_', '=' => ''));
        $state['cronKey']            = (string)$this->state->get('cron_key');
        $state['cronLastRunAt']      = (int)$this->state->get('cron_last');
        $state['siteMail']           = (string)$this->state->get('site_mail');
        $state['siteName']           = (string)$this->state->get('site_name');
        $state['siteRoot']           = isset($request->server['SCRIPT_FILENAME']) ? Oxygen_Util::normalizePath(dirname($request->server['SCRIPT_FILENAME'])) : '';
        $state['drupalRoot']         = Oxygen_Util::normalizePath($this->context->getConstant('DRUPAL_ROOT'));
        $state['drupalVersion']      = $this->context->getConstant('VERSION');
        $state['drupalMajorVersion'] = (int)basename($this->context->getConstant('DRUPAL_CORE_COMPATIBILITY'), '.x');
        $state['timezone']           = (string)$this->state->get('date_default_timezone');
    }

    private function populateServerState(&$state, Oxygen_Http_Request $request)
    {
        $state['phpVersion']            = PHP_VERSION;
        $state['phpVersionId']          = PHP_VERSION_ID;
        $state['databaseDriver']        = $this->connection->databaseType();
        $state['databaseDriverVersion'] = $this->connection->version();
        $state['databaseTablePrefix']   = $this->connection->tablePrefix();
        $state['memoryLimit']           = $this->environment->getMemoryLimit();
        $state['processArchitecture']   = strlen(decbin(~0)); // Results in 32 or 62.
        $state['internalIp']            = isset($request->server['SERVER_ADDR']) ? $request->server['SERVER_ADDR'] : null;
        $state['uname']                 = php_uname();
        $state['hostname']              = php_uname('n');
        $state['os']                    = PHP_OS;
        $state['windows']               = DIRECTORY_SEPARATOR === '\\';
    }

    private function populateExtensionsState($params, &$state)
    {
        $systemChecksum = $this->getExtensionChecksum();

        if ($systemChecksum === null || $systemChecksum !== $params['extensionsChecksum']) {
            // The checksum could not be calculated, or it differs.
            $state += array(
                'extensionsChecksum' => $systemChecksum,
                'extensionsCacheHit' => false,
                'extensions'         => $this->getExtensions(),
            );
        } else {
            $state += array(
                'extensionsChecksum' => $systemChecksum,
                'extensionsCacheHit' => true,
                'extensions'         => array(),
            );
        }
    }

    private function populateUpdatesState(&$state)
    {
        $state['updatesLastCheckAt'] = (int)$this->state->get('update_last_check');
        $state['updates']            = $this->getUpdates();
    }

    /**
     * Returns a checksum that indicates whether the 'system' table was modified. It is not required to be 100%
     * accurate, its goal is to not return the whole 'system' table in the response if the content did not change.
     *
     * @return string|null 32-character checksum value or NULL if the checksum is not supported.
     */
    private function getExtensionChecksum()
    {
        // There are multiple ways to check when the 'system' table was updated.
        if (_cache_get_object('cache_bootstrap') instanceof DrupalDatabaseCache) {
            // Every time a "system" change is detected by Drupal, system_list_reset() gets called, which clears the
            // 'system_list' cache entry from the 'cache_bootstrap' cache bin. Right here we will check when the cache
            // entry was last recreated and use that as the checksum.
            $cacheRecreatedAt = $this->connection->query('SELECT created FROM {cache_bootstrap} WHERE cid = :cid', array(
                ':cid' => 'system_list',
            ))->fetchField();

            if (ctype_digit((string)$cacheRecreatedAt)) {
                // Only rely on this check if we actually get a valid numeric timestamp.
                return md5($cacheRecreatedAt);
            }
        }

        if ($this->connection->databaseType() === 'mysql') {
            // https://dev.mysql.com/doc/refman/5.0/en/checksum-table.html
            $checksum = $this->connection->query('CHECKSUM TABLE {system}')->fetchField(1);
            // The columns returned are 'Table' (eg. schema.system) and 'Checksum' (numeric value, eg. 290814144), so
            // fetch the second value.

            if (ctype_digit((string)$checksum)) {
                return md5($checksum);
            }
        }

        return null;
    }

    /**
     * @return array
     */
    private function getExtensions()
    {
        // Select all columns; 'filename' is the primary key.
        $query = "SELECT filename, name, type, owner, status, bootstrap, info FROM {system} WHERE type IN ('module', 'theme') ORDER BY filename ASC";
        $rows  = $this->connection->query($query)->fetchAllAssoc('filename');

        $extensions = array();

        foreach ($rows as $row) {
            $info = unserialize($row->info);

            if ($info['hidden']) {
                continue;
            }
            // See default values in _system_rebuild_module_data().
            $extensions[$row->name] = [
                'filename'     => $row->filename,
                'type'         => $row->type,
                'slug'         => $row->name,
                'parent'       => strlen($row->owner) ? $row->owner : null,
                'enabled'      => (bool)$row->status,
                'name'         => $info['name'],
                'description'  => $info['description'],
                'package'      => $info['package'],
                'version'      => $info['version'],
                'required'     => empty($info['required']) ? false : true,
                'dependencies' => empty($info['dependencies']) ? array() : $info['dependencies'],
                'project'      => $info['project'],
                // screenshot, version, php
            ];
        }

        return $extensions;
    }

    private static $statusMap = array(
        // UPDATE_NOT_SECURE
        1  => 'not_secure',
        // UPDATE_REVOKED
        2  => 'revoked',
        // UPDATE_NOT_SUPPORTED
        3  => 'not_supported',
        // UPDATE_NOT_CURRENT
        4  => 'not_current',
        // UPDATE_CURRENT
        5  => 'current',
        // UPDATE_NOT_CHECKED
        -1 => 'not_checked',
        // UPDATE_UNKNOWN
        -2 => 'unknown',
        // UPDATE_NOT_FETCHED
        -3 => 'not_fetched',
        // UPDATE_FETCH_PENDING
        -4 => 'fetch_pending',
    );

    /**
     * @return array
     *
     * @see _update_process_info_list()
     */
    public function getUpdates()
    {
        $available = update_get_available(true);

        if (!$available) {
            return array();
        }
        module_load_include('inc', 'update', 'update.compare');

        $updates = array();

        $data = update_calculate_project_data($available);
        foreach ($data as $slug => $update) {
            // Disabled modules have a type of 'module-disabled'.
            $type = explode('-', $update['project_type'], 2);
            $type = $type[0];
            if ($update['recommended'] === $update['existing_version']) {
                continue;
            }
            $updates[$slug] = array(
                'slug'                    => $slug,
                'name'                    => $update['title'],
                'type'                    => $type,
                'project'                 => $update['info']['project'],
                'package'                 => $update['info']['package'],
                'existingVersion'         => $update['info']['version'],
                'recommendedVersion'      => $update['recommended'],
                'recommendedDownloadLink' => $update['releases'][$update['recommended']]['download_link'],
                'status'                  => self::$statusMap[$update['status']],
                'includes'                => empty($update['includes']) ? array() : array_keys($update['includes']),
                'enabled'                 => (bool)$update['status'],
                'baseThemes'              => array_keys($update['base_themes']),
                'subThemes'               => array_keys($update['sub_themes']),
            );
        }

        return $updates;
    }
}

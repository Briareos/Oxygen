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
        $this->populateSystemState($params, $state);

        return $state;
    }

    private function populateSiteState(&$state, Oxygen_Http_Request $request)
    {
        // See how $site_key gets generated in _update_process_fetch_task() for statistical purposes.
        $state['siteKey']           = strtr(base64_encode(hash_hmac('sha256', (string)$this->context->getGlobal('base_url'), (string)$this->state->get('drupal_private_key'), true)), array('+' => '-', '/' => '_', '=' => ''));
        $state['cronKey']           = $this->state->get('cron_key');
        $state['cronLastRunAt']     = $this->state->get('cron_last');
        $state['siteMail']          = $this->state->get('site_mail');
        $state['siteName']          = $this->state->get('site_name');
        $state['siteRoot']          = isset($request->server['SCRIPT_FILENAME']) ? Oxygen_Util::normalizePath(dirname($request->server['SCRIPT_FILENAME'])) : null;
        $state['drupalRoot']        = Oxygen_Util::normalizePath($this->context->getConstant('DRUPAL_ROOT'));
        $state['drupalVersion']     = $this->context->getConstant('VERSION');
        $state['updateLastCheckAt'] = $this->state->get('update_last_check');
        $state['timezone']          = $this->state->get('date_default_timezone');
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

    private function populateSystemState($params, &$state)
    {
        $systemChecksum = $this->getSystemChecksum();

        if ($systemChecksum === null || $systemChecksum !== $params['systemChecksum']) {
            // The checksum could not be calculated, or it differs.
            $state += array(
                'systemChecksum' => $systemChecksum,
                'systemCacheHit' => false,
                'systemData'     => $this->getSystemData(),
            );
        } else {
            $state += array(
                'systemChecksum' => $systemChecksum,
                'systemCacheHit' => true,
                'systemData'     => array(),
            );
        }
    }

    /**
     * Returns a checksum that indicates whether the 'system' table was modified. It is not required to be 100%
     * accurate, its goal is to not return the whole 'system' table in the response if the content did not change.
     *
     * @return string|null 32-character checksum value or NULL if the checksum is not supported.
     */
    private function getSystemChecksum()
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
     * @return array The whole 'system' table.
     */
    private function getSystemData()
    {
        // Select all columns; 'filename' is the primary key.
        $query = 'SELECT filename, name, type, owner, status, bootstrap, schema_version, weight, info FROM {system} ORDER BY filename ASC';
        $rows  = $this->connection->query($query)->fetchAllAssoc('filename');

        foreach ($rows as $row) {
            $row->owner          = strlen($row->owner) ? $row->owner : null;
            $row->status         = (bool)$row->status;
            $row->bootstrap      = (bool)$row->bootstrap;
            $row->schema_version = (int)$row->schema_version;
            $row->weight         = (int)$row->weight;
            $row->info           = unserialize($row->info);
        }

        // We must index by something in fetchAllAssoc, but return a 0-indexed array.
        return array_values($rows);
    }
}

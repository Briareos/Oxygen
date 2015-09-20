<?php

class Oxygen_Drupal_ExtensionManager
{
    /**
     * @see module_enable
     *
     * @param string[] $modules
     * @param bool     $enableDependencies
     */
    public function enableModules($modules, $enableDependencies = false)
    {
        module_enable($modules, $enableDependencies);
    }

    /**
     * This is removed in Drupal 8, and module deactivation is highly discouraged.
     *
     * @link https://www.drupal.org/node/2225029
     *
     * @see  module_disable
     *
     * @param string[] $modules
     * @param bool     $disableDependents
     */
    public function disableModules(array $modules, $disableDependents = false)
    {
        module_disable($modules, $disableDependents);
    }

    /**
     * This function will not perform an uninstallation
     *
     * @param array      $modules
     * @param bool|false $uninstallDependents
     *
     * @return bool Returns false if $uninstallDependents is true and a module in $modules list has dependents which are
     *              not already uninstalled and also not included in $modules list.
     */
    public function uninstallModule(array $modules, $uninstallDependents = true)
    {
        include_once DRUPAL_ROOT.'/includes/install.inc';

        return drupal_uninstall_modules($modules, $uninstallDependents);
    }

    /**
     * @param string $url
     *
     * @throws Oxygen_Exception
     *
     * @return array Context result generated by Drupal.
     *
     * @see update_manager_install_form_submit()
     */
    public function downloadExtensionFromUrl($url)
    {
        module_load_include('inc', 'update', 'update.manager');
        $localCache = update_manager_file_get($url);

        if (!$localCache) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_UNABLE_TO_RETRIEVE_DRUPAL_PROJECT);
        }

        $directory = _update_manager_extract_directory();

        try {
            /** @var ArchiverInterface $archive */
            $archive = update_manager_archive_extract($localCache, $directory);
        } catch (Exception $e) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_EXTRACT_FAILED, null, $e);
        }

        $files = $archive->listContents();
        if (!$files) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_ARCHIVE_CONTAINS_NO_FILES);
        }

        // Unfortunately, we can only use the directory name to determine the project
        // name. Some archivers list the first file as the directory (i.e., MODULE/)
        // and others list an actual file (i.e., MODULE/README.TXT).
        $project = strtok($files[0], '/\\');

        $archiveErrors = update_manager_archive_verify($project, $localCache, $directory);
        if (!empty($archiveErrors)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_ARCHIVE_VERIFY_ERROR, array(
                'errors' => $archiveErrors,
            ));
        }

        // Make sure the Updater registry is loaded.
        drupal_get_updaters();

        $projectLocation = $directory.'/'.$project;
        try {
            /** @var DrupalUpdaterInterface $updater */
            $updater = Updater::factory($projectLocation);
        } catch (Exception $e) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_CAN_NOT_FIND_APPROPRIATE_UPDATER, null, $e);
        }

        try {
            $projectTitle = Updater::getProjectTitle($projectLocation);
        } catch (Exception $e) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_UNABLE_TO_PARSE_PROJECT_INFO, null, $e);
        }

        if (!$projectTitle) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_UNABLE_TO_DETERMINE_PROJECT_NAME);
        }

        if ($updater->isInstalled()) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_PROJECT_ALREADY_INSTALLED);
        }

        $projectRealLocation = drupal_realpath($projectLocation);

        // If the owner of the directory we extracted is the same as the
        // owner of our configuration directory (e.g. sites/default) where we're
        // trying to install the code, there's no need to prompt for FTP/SSH
        // credentials. Instead, we instantiate a FileTransferLocal and invoke
        // update_authorize_run_install() directly.
        if (fileowner($projectRealLocation) !== fileowner(DRUPAL_ROOT.'/'.conf_path())) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_FILE_SYSTEM_NOT_WRITABLE, array(
                'projectOwner' => fileowner($projectRealLocation),
                'siteOwner'    => fileowner(conf_path()),
            ));
        }
        module_load_include('inc', 'update', 'update.authorize');
        // @TODO: Implement other file transfer types.
        $fileTransfer = new FileTransferLocal(DRUPAL_ROOT);
        $context      = array();
        update_authorize_batch_copy_project($project, get_class($updater), $projectRealLocation, $fileTransfer, $context);

        // Error example:
        // [
        //   'results' => [
        //     'log'  => [
        //       'views' => [
        //         [
        //           'message' => 'Error installing / updating',
        //           'success' => false,
        //         ],
        //         [
        //           'message' => 'File Transfer failed, reason: /var/www/s1/sites/all/modules is outside of the /var/www/s2',
        //           'success' => false,
        //         ],
        //         '#abort' => true,
        //       ],
        //     ],
        //     'tasks' => [],
        //   ],
        // ];

        // Success example:
        // [
        //   'results' => [
        //     'log'  => [
        //       'views' => [
        //         [
        //           'message' => 'Installed <em class="placeholder">views</em> successfully',
        //           'success' => true,
        //         ],
        //       ],
        //     ],
        //     'tasks' => [
        //       '<a href="./admin/modules/install">Install another module</a>',
        //       '<a href="./admin/modules">Enable newly added modules</a>',
        //       '<a href="./admin">Administration pages</a>',
        //     ],
        //   ],
        //   'finished' => 1,
        // ];

        return $context;
    }

    /**
     * @return array Context result generated by Drupal.
     *
     * @see update_manager_update_form_submit()
     */
    public function downloadExtensionUpdateFromUrl($extension, $url)
    {
        module_load_include('inc', 'update', 'update.manager');
        // The process won't start until we set this variable.
        $context['sandbox']['started'] = true;
        update_manager_batch_project_get($extension, $url, $context);

        return $context;
    }

    /**
     * @param string $extension
     *
     * @return array Context result generated by Drupal.
     *
     * @throws Oxygen_Exception
     *
     * @see update_authorize_run_update()
     */
    public function updateExtension($extension)
    {
        module_load_include('inc', 'update', 'update.manager');
        drupal_get_updaters();

        $directory = _update_manager_extract_directory();

        $projectLocation     = $directory.'/'.$extension;
        $updater             = Updater::factory($projectLocation);
        $projectRealLocation = drupal_realpath($projectLocation);

        // If the owner of the directory we extracted is the same as the
        // owner of our configuration directory (e.g. sites/default) where we're
        // trying to install the code, there's no need to prompt for FTP/SSH
        // credentials. Instead, we instantiate a FileTransferLocal and invoke
        // update_authorize_run_install() directly.
        if (fileowner($projectRealLocation) !== fileowner(DRUPAL_ROOT.'/'.conf_path())) {
            throw new Oxygen_Exception(Oxygen_Exception::PROJECT_MANAGER_FILE_SYSTEM_NOT_WRITABLE, array(
                'projectOwner' => fileowner($projectRealLocation),
                'siteOwner'    => fileowner(conf_path()),
            ));
        }
        module_load_include('inc', 'update', 'update.authorize');
        // @TODO: Implement other file transfer types.
        $fileTransfer = new FileTransferLocal(DRUPAL_ROOT);

        update_authorize_batch_copy_project($extension, get_class($updater), $projectRealLocation, $fileTransfer, $context);

        // Reset the cache for this extension only, so we may immediately update the dashboard.
        // Hacky way to do it, but most efficient.
        module_load_include('inc', 'update', 'update.fetch');
        module_load_include('inc', 'update', 'update.compare');
        _update_cache_clear('update_project_projects');
        _update_cache_clear('update_project_data');
        $projects = update_get_projects();
        _update_process_fetch_task($projects[$extension]);

        return $context;
    }
}
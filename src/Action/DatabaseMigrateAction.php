<?php

class Oxygen_Action_DatabaseMigrateAction
{
    /**
     * @return array
     *
     * @see update_batch()
     */
    public function listMigrations()
    {
        // Resolve any update dependencies to determine the actual updates that will
        // be run and the order they will be run in.
        require_once DRUPAL_ROOT.'/includes/update.inc';
        $startingUpdates = $this->listUpdates();
        $updates         = update_resolve_dependencies($startingUpdates);

        // Store the dependencies for each update function in an array which the
        // batch API can pass in to the batch operation each time it is called. (We
        // do not store the entire update dependency array here because it is
        // potentially very large.)
        $dependencyMap = array();
        foreach ($updates as $function => $update) {
            $dependencyMap[$function] = !empty($update['reverse_paths']) ? array_keys($update['reverse_paths']) : array();
        }

        $migrations = array();
        foreach ($updates as $update) {
            if ($update['allowed']) {
                // Set the installed version of each module so updates will start at the
                // correct place. (The updates are already sorted, so we can simply base
                // this on the first one we come across in the above foreach loop.)
                if (isset($startingUpdates[$update['module']])) {
                    drupal_set_installed_schema_version($update['module'], $update['number'] - 1);
                    unset($startingUpdates[$update['module']]);
                }
                // Add this update function to the batch.
                $function     = $update['module'].'_update_'.$update['number'];
                $migrations[] = array(
                    'module'        => $update['module'],
                    'number'        => $update['number'],
                    'dependencyMap' => $dependencyMap[$function],
                );
            }
        }

        return $migrations;
    }

    /**
     * @param string $module
     * @param int    $number
     * @param array  $dependencyMap
     *
     * @return array
     */
    public function runMigration($module, $number, array $dependencyMap)
    {
        require_once DRUPAL_ROOT.'/includes/update.inc';
        require_once DRUPAL_ROOT.'/includes/install.inc';
        module_load_include('install', 'oxygen');
        $context['sandbox']['#finished'] = true;
        update_do_one($module, $number, $dependencyMap, $context);

        return array('context' => $context);
    }

    /**
     * @return array
     *
     * @see update_script_selection_form()
     */
    private function listUpdates()
    {
        // [
        //   'oxygen' => [
        //   'pending' => [
        //     7001 => 'Update description.',
        //   ],
        //   'start' => 7001,
        // ],
        $list = array();
        require_once DRUPAL_ROOT.'/includes/install.inc';
        drupal_load_updates();
        foreach (update_get_update_list() as $extension => $info) {
            if (!isset($info['start'])) {
                // @todo: The update is incompatible, show a warning?
                continue;
            }
            $list[$extension] = $info['start'];
        }

        $updates = array();
        foreach (update_resolve_dependencies($list) as $update) {
            if (!$update['allowed']) {
                if ($update['missing_dependencies']) {
                    // Some module dependency is missing, so it's not safe to update.
                    continue;
                } else {
                    // There was a PHP syntax error in the module.
                    continue;
                }
            }
            $updates = array($update['module'] => $update['number']) + $updates;
        }

        return $updates;
    }
}

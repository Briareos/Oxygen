<?php

class Oxygen_Drupal_ModuleManager
{
    /**
     * @see module_disable
     *
     * @param array $modules
     * @param bool  $autoDisableDependantModules
     */
    public function disableModules(array $modules, $disableDependents = false)
    {
        module_disable($modules, $disableDependents);
    }
}

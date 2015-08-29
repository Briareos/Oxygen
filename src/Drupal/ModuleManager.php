<?php

class Oxygen_Drupal_ModuleManager
{
    /**
     * @see module_disable
     *
     * @param string[] $modules
     * @param bool     $disableDependents
     */
    public function disableModules(array $modules, $disableDependents = false)
    {
        module_disable($modules, $disableDependents);
    }

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
}

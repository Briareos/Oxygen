<?php

class Oxygen_Action_ModuleEnableAction implements Oxygen_Container_ServiceLocatorAware
{
    /**
     * @var Oxygen_Drupal_ModuleManager
     */
    private $moduleManager;

    /**
     * @param $moduleManager
     */
    public function __construct(Oxygen_Drupal_ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromContainer(Oxygen_Container_Interface $container)
    {
        return new self($container->getModuleManager());
    }

    public function execute(array $modules, $enableDependencies = false)
    {
        $this->moduleManager->enableModules($modules, $enableDependencies);

        return array();
    }
}

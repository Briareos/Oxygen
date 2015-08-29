<?php

class Oxygen_Action_ModuleDisableAction implements Oxygen_Container_ServiceLocatorAware
{
    /**
     * @var Oxygen_Drupal_ProjectManager
     */
    private $projectManager;

    /**
     * @param $projectManager
     */
    public function __construct(Oxygen_Drupal_ProjectManager $projectManager)
    {
        $this->projectManager = $projectManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromContainer(Oxygen_Container_Interface $container)
    {
        return new self($container->getProjectManager());
    }

    public function execute(array $modules, $disableDependents = false)
    {
        $this->projectManager->disableModules($modules, $disableDependents);

        return array();
    }
}

<?php

class Oxygen_Action_ProjectInstallFromUrlAction implements Oxygen_Container_ServiceLocatorAware
{
    /**
     * @var Oxygen_Drupal_ProjectManager
     */
    private $projectManager;

    /**
     * @param Oxygen_Drupal_ProjectManager $projectManager
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

    public function execute($url)
    {
        $context = $this->projectManager->installProjectFromUrl($url);

        return array(
            'context' => $context,
        );
    }
}

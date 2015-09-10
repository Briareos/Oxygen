<?php

class Oxygen_Action_ExtensionDownloadUpdateFromUrlAction implements Oxygen_Container_ServiceLocatorAware
{
    /**
     * @var Oxygen_Drupal_ExtensionManager
     */
    private $projectManager;

    /**
     * @param Oxygen_Drupal_ExtensionManager $projectManager
     */
    public function __construct(Oxygen_Drupal_ExtensionManager $projectManager)
    {
        $this->projectManager = $projectManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromContainer(Oxygen_Container_Interface $container)
    {
        return new self($container->getExtensionManager());
    }

    public function execute($extension, $url)
    {
        $context = $this->projectManager->downloadExtensionUpdateFromUrl($extension, $url);

        return array(
            'context' => $context,
        );
    }
}

<?php

class Oxygen_Action_SiteLogoutAction implements Oxygen_Container_ServiceLocatorAware
{
    /**
     * @var Oxygen_Drupal_SessionManager
     */
    private $sessionManager;

    /**
     * @param Oxygen_Drupal_SessionManager $sessionManager
     */
    public function __construct(Oxygen_Drupal_SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }


    public static function createFromContainer(Oxygen_Container_Interface $container)
    {
        return new self($container->getSessionManager());
    }

    public function execute($userUid)
    {
        $destroyed = $this->sessionManager->destroySessions($userUid);

        return array(
            'destroyedSessions' => $destroyed,
        );
    }
}

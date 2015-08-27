<?php

class Oxygen_Container_Production extends Oxygen_Container_Abstract
{
    /**
     * {@inheritdoc}
     */
    protected function createRsaVerifier()
    {
        if ($this->getParameter('disable_openssl') || !extension_loaded('openssl')) {
            return new Oxygen_Security_Rsa_PhpRsaVerifier();
        }

        return new Oxygen_Security_Rsa_OpensslRsaVerifier();
    }

    /**
     * {@inheritdoc}
     */
    protected function createConnection()
    {
        return Database::getConnection();
    }

    /**
     * {@inheritdoc}
     */
    protected function createActionRegistry()
    {
        $registry = new Oxygen_Action_Registry();

        $registry->setDefinition('site.ping', new Oxygen_Action_Definition('Oxygen_Action_SitePingAction', 'execute'));
        $registry->setDefinition('module.deactivate', new Oxygen_Action_Definition('Oxygen_Action_ModuleDeactivateAction', 'execute', array(
            'hook_name' => 'init',
        )));

        return $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function createDispatcher()
    {
        // Take special care about these services since we're avoiding auto-loading for micro-optimization reasons.
        // There ARE other ways to do it, but none of them are too pretty.

        $dispatcher = new Oxygen_EventDispatcher_EventDispatcher();

        $errorListener = new Oxygen_Container_LazyService(
            'Oxygen_EventListener_ErrorListener',
            $this->getParameter('fatal_error_reserved_memory_size')
        );

        $dispatcher->addListener(Oxygen_Event_Events::MASTER_REQUEST, array($errorListener, 'onMasterRequest'), 11);

        $protocolListener = new Oxygen_Container_LazyService(
            'Oxygen_EventListener_ProtocolListener',
            $this->getParameter('module_version'),
            $this->getParameter('base_url')
        );
        $dispatcher->addListener(Oxygen_Event_Events::MASTER_REQUEST, array($protocolListener, 'onMasterRequest'), 10);

        $handshakeListener = new Oxygen_Container_LazyService(
            'Oxygen_EventListener_HandshakeListener',
            array($this, 'getState'),
            array($this, 'getRsaVerifier'),
            array($this, 'getNonceManager'),
            $this->getParameter('base_url'),
            $this->getParameter('module_path')
        );
        $dispatcher->addListener(Oxygen_Event_Events::MASTER_REQUEST, array($handshakeListener, 'onMasterRequest'), 9);

        $actionDataListener = new Oxygen_Container_LazyService(
            'Oxygen_eventListener_SetResponseListener'
        );
        $dispatcher->addListener(Oxygen_Event_Events::ACTION_DATA, array($actionDataListener, 'onActionData'), -10);

        $dispatcher->addListener(Oxygen_Event_Events::EXCEPTION, array($errorListener, 'onException'), 0);

        return $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function createNonceManager()
    {
        return new Oxygen_Security_Nonce_NonceManager($this->getConnection());
    }

    /**
     * {@inheritdoc}
     */
    protected function createState()
    {
        return new Oxygen_Drupal_State();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModuleManager()
    {
        return new Oxygen_Drupal_ModuleManager();
    }

    /**
     * {@inheritdoc}
     */
    protected function createActionKernel()
    {
        return new Oxygen_ActionKernel($this->getActionRegistry(), $this->getDispatcher(), $this);
    }
}

<?php

class Oxygen_Container_Production extends Oxygen_Container_Abstract
{
    /**
     * @inheritdoc
     */
    protected function createRsaVerifier()
    {
        if ($this->getParameter('disable_openssl') || !extension_loaded('openssl')) {
            return new Oxygen_Security_Rsa_PhpRsaVerifier();
        }

        return new Oxygen_Security_Rsa_OpensslRsaVerifier();
    }

    /**
     * @inheritdoc
     */
    protected function createConnection()
    {
        return Database::getConnection();
    }

    /**
     * @inheritdoc
     */
    protected function createActionRegistry()
    {
        $registry = new Oxygen_Action_Registry();

        $registry->setDefinition('ping', new Oxygen_Action_Definition('Oxygen_Action_PingAction','execute'));

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

        $handshakeListener = new Oxygen_Container_LazyService(
            'Oxygen_EventListener_HandshakeListener',
            array($this, 'getState'),
            array($this, 'getRsaVerifier'),
            array($this, 'getNonceManager')
        );
        $dispatcher->addListener(Oxygen_Event_Events::MASTER_REQUEST, array($handshakeListener, 'onMasterRequest'));

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
}

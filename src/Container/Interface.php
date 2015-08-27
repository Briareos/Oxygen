<?php

interface Oxygen_Container_Interface
{
    /**
     * @param string $name
     *
     * @return bool|int|string|float|null
     */
    public function getParameter($name);

    /**
     * @return Oxygen_Security_Rsa_RsaVerifierInterface
     */
    public function getRsaVerifier();

    /**
     * @return DatabaseConnection
     */
    public function getConnection();

    /**
     * @return Oxygen_Action_Registry
     */
    public function getActionRegistry();

    /**
     * @return Oxygen_EventDispatcher_EventDispatcherInterface
     */
    public function getDispatcher();

    /**
     * @return Oxygen_Security_Nonce_NonceManagerInterface
     */
    public function getNonceManager();

    /**
     * @return Oxygen_Drupal_StateInterface
     */
    public function getState();

    /**
     * @return Oxygen_Drupal_ModuleManager
     */
    public function getModuleManager();

    /**
     * @return Oxygen_ActionKernel
     */
    public function getActionKernel();

    /**
     * @return Oxygen_Drupal_UserManager
     */
    public function getUserManager();

    /**
     * @return Oxygen_Drupal_SessionManager
     */
    public function getSessionManager();

    /**
     * @return Oxygen_Drupal_Context
     */
    public function getContext();
}

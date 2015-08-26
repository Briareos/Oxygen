<?php

abstract class Oxygen_Container_Abstract implements Oxygen_Container_Interface
{
    /**
     * @var object[] An array of services already instantiated by the container.
     */
    private $registry = array();

    /**
     * @var array
     */
    protected $parameters = array();

    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters + array(
                'module_version'  => '0.0',
                'module_path'     => dirname(dirname(dirname(__FILE__))),
                'base_url'        => 'http://.',
                'disable_openssl' => false,
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        if (!array_key_exists($name, $this->parameters)) {
            throw new InvalidArgumentException(sprintf('The parameter named "%s" does not exist.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getRsaVerifier()
    {
        if (!isset($this->registry[__METHOD__])) {
            $this->registry[__METHOD__] = $this->createRsaVerifier();
        }

        return $this->registry[__METHOD__];
    }

    /**
     * @return Oxygen_Security_Rsa_RsaVerifierInterface
     */
    abstract protected function createRsaVerifier();

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        if (!isset($this->registry[__METHOD__])) {
            $this->registry[__METHOD__] = $this->createConnection();
        }

        return $this->registry[__METHOD__];
    }

    /**
     * @return PDO
     */
    abstract protected function createConnection();

    /**
     * {@inheritdoc}
     */
    public function getActionRegistry()
    {
        if (!isset($this->registry[__METHOD__])) {
            $this->registry[__METHOD__] = $this->createActionRegistry();
        }

        return $this->registry[__METHOD__];
    }

    /**
     * @return Oxygen_Action_Registry
     */
    abstract protected function createActionRegistry();

    /**
     * {@inheritdoc}
     */
    public function getDispatcher()
    {
        if (!isset($this->registry[__METHOD__])) {
            $this->registry[__METHOD__] = $this->createDispatcher();
        }

        return $this->registry[__METHOD__];
    }

    /**
     * @return Oxygen_EventDispatcher_EventDispatcherInterface
     */
    abstract public function createDispatcher();

    /**
     * {@inheritdoc}
     */
    public function getNonceManager()
    {
        if (!isset($this->registry[__METHOD__])) {
            $this->registry[__METHOD__] = $this->createNonceManager();
        }

        return $this->registry[__METHOD__];
    }

    /**
     * @return Oxygen_Security_Nonce_NonceManagerInterface
     */
    abstract protected function createNonceManager();

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        if (!isset($this->registry[__METHOD__])) {
            $this->registry[__METHOD__] = $this->createState();
        }

        return $this->registry[__METHOD__];
    }

    /**
     * @return Oxygen_Drupal_StateInterface
     */
    abstract protected function createState();
}

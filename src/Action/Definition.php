<?php

class Oxygen_Action_Definition
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $class   Action class. It may implement Oxygen_Container_ServiceLocatorAware interface.
     * @param string $method  Action method. It will be parsed using reflection and injected with attributes
     *                        with the same name from the request object.
     * @param array  $options Execution options.
     *
     * @see Oxygen_Container_ServiceLocatorAware
     */
    public function __construct($class, $method, array $options = array())
    {
        // The reason we're not checking if it's a valid "callable" here is
        // because public non-static methods are not really callable.
        // Also, there's no need to autoload the class right now.
        $this->class   = $class;
        $this->method  = $method;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}

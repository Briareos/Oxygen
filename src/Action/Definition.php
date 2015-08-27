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

    private static $defaultOptions = array(
        'hook_name' => null,
    );

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
        $this->validateOptions($options);
        $options += self::$defaultOptions;

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

    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new InvalidArgumentException(sprintf('Option "%s" is not recognized', $name));
        }

        return $this->options[$name];
    }

    private function validateOptions(array $options)
    {
        foreach ($options as $optionName => $optionDefault) {
            if (!array_key_exists($optionName, self::$defaultOptions)) {
                throw new InvalidArgumentException(sprintf('Option "%s" is not registered, valid options are: "%s".', $optionName, implode('", "', array_keys(self::$defaultOptions))));
            }
        }
    }
}

<?php

/**
 * PHP 5.2 friendly way of loading listeners only when they are required.
 */
class Oxygen_Container_LazyService
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var array
     */
    private $constructorArgs = array();

    /**
     * @var object Internal instance of $this->class, so multiple calls to the listener do not create more instances.
     */
    private $instance;

    /**
     * @param string $class
     * @param mixed  ...$constructorArgs A list of arguments to pass to the class constructor. Each argument can be a
     *                                   callable instance of Oxygen_Container_Interface or anything else. If it's a
     *                                   callable like [ Oxygen_Container_Interface, 'containerMethod' ], it will be
     *                                   resolved. Anything else will be directly passed to the constructor.
     */
    public function __construct($class)
    {
        $this->class = $class;

        $this->constructorArgs = func_get_args();
        array_shift($this->constructorArgs);
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if ($this->instance === null) {
            $reflectionClass = new ReflectionClass($this->class);
            $this->instance  = $reflectionClass->newInstanceArgs($this->__resolveConstructorArgs($this->constructorArgs));
        }

        $callable = array($this->instance, $method);

        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf('%s::%s is not callable.', $this->class, $method));
        }

        return call_user_func_array($callable, $args);
    }

    private function __resolveConstructorArgs(array $args)
    {
        foreach ($args as &$arg) {
            if (!is_array($arg)) {
                continue;
            }
            if (count($arg) !== 2) {
                continue;
            }
            if (!isset($arg[0]) || (!$arg[0] instanceof Oxygen_Container_Interface)) {
                continue;
            }
            if (!is_callable($arg)) {
                continue;
            }
            $arg = call_user_func($arg);
        }

        return $args;
    }
}

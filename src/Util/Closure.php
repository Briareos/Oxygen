<?php

class Oxygen_Util_Closure
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var array
     */
    private $args;

    /**
     * @param callable $callback Hook callback; function to execute.
     * @param mixed    ...$args  Arguments that will be passed to $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
        $this->args     = func_get_args();
        array_shift($this->args);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return call_user_func_array($this->callback, $this->args);
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return array($this, 'execute');
    }
}

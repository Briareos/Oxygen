<?php

/**
 * A closure that should be executed on a hook.
 */
class Oxygen_Util_HookedClosure
{
    /**
     * @var string
     */
    private $hookName;

    /**
     * @var callable
     */
    private $closure;

    /**
     * @var bool
     */
    private $executed = false;

    /**
     * @param string   $hookName
     * @param callable $closure
     */
    public function __construct($hookName, callable $closure)
    {
        $this->hookName = $hookName;
        $this->closure  = $closure;
    }

    /**
     * @return string
     */
    public function getHookName()
    {
        return $this->hookName;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        if ($this->executed) {
            throw new LogicException('The hooked action should never get executed twice.');
        }

        $this->executed = true;
        return call_user_func($this->closure);
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return array($this, 'execute');
    }
}

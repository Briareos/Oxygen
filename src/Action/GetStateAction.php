<?php

class Oxygen_Action_GetStateAction implements Oxygen_Container_ServiceLocatorAware
{
    /**
     * @param Oxygen_Container_Interface $container
     *
     * @return $this
     */
    public static function createFromContainer(Oxygen_Container_Interface $container)
    {
        return new self();
    }

    public function __construct()
    {

    }
}

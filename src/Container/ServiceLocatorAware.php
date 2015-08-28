<?php

interface Oxygen_Container_ServiceLocatorAware
{
    /**
     * @param Oxygen_Container_Interface $container
     *
     * @return $this
     */
    public static function createFromContainer(Oxygen_Container_Interface $container);
}

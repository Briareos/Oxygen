<?php

class Oxygen_Action_Registry
{
    /**
     * @var Oxygen_Action_Definition[]
     */
    private $registry = array();

    /**
     * @param string                   $name
     * @param Oxygen_Action_Definition $definition
     */
    public function setDefinition($name, Oxygen_Action_Definition $definition)
    {
        $this->registry[$name] = $definition;
    }

    /**
     * @param string $name
     *
     * @return Oxygen_Action_Definition
     *
     * @throws Oxygen_Exception If the definition can not be found.
     */
    public function getDefinition($name)
    {
        if (!isset($this->registry[$name])) {
            throw new Oxygen_Exception(Oxygen_Exception::ACTION_NOT_FOUND, null, array('action' => $name));
        }

        return $this->registry[$name];
    }
}

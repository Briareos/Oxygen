<?php

class Oxygen_EventListener_ActionListener
{
    /**
     * @var Oxygen_Action_Registry
     */
    private $actionRegistry;

    /**
     * @param Oxygen_Action_Registry $actionRegistry
     */
    public function __construct(Oxygen_Action_Registry $actionRegistry)
    {
        $this->actionRegistry = $actionRegistry;
    }

    public function onMasterRequest(Oxygen_Event_MasterRequestEvent $event)
    {
        $data = $event->getData();

        if (!isset($data['actionName']) || !isset($data['actionParameters'])) {
            return;
        }

        $actionDefinition = $this->actionRegistry->getDefinition($data['actionName']);
        $reflectionMethod = new ReflectionMethod($actionDefinition->getClass(), $actionDefinition->getMethod());
        $parameters       = $reflectionMethod->getParameters();
        $arguments        = array();

        foreach ($parameters as $parameter) {
            if (isset($data['actionParameters'][$parameter->getName()])) {
                $arguments[] = $data['actionParameters'][$parameter->getName()];
            } else {
                if (!$parameter->isOptional()) {
                    throw new Oxygen_Exception(Oxygen_Exception::ACTION_ARGUMENT_EMPTY);
                }
                $arguments[] = $parameter->getDefaultValue();
            }
        }

        $result = call_user_func_array(array($actionDefinition->getClass(), $actionDefinition->getMethod()), $arguments);
        $event->setResponse(new Oxygen_Http_JsonResponse(array('actionResult' => $result)));
    }
}

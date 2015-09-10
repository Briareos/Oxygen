<?php

class Oxygen_ActionKernel
{
    /**
     * @var Oxygen_Action_Registry
     */
    private $actionRegistry;

    /**
     * @var Oxygen_EventDispatcher_EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var Oxygen_Container_Interface
     */
    private $container;

    /**
     * @param Oxygen_Action_Registry                          $actionRegistry
     * @param Oxygen_EventDispatcher_EventDispatcherInterface $dispatcher
     * @param Oxygen_Container_Interface                      $container Used for injection for ServiceLocatorAware actions.
     */
    public function __construct(Oxygen_Action_Registry $actionRegistry, Oxygen_EventDispatcher_EventDispatcherInterface $dispatcher, Oxygen_Container_Interface $container)
    {
        $this->actionRegistry = $actionRegistry;
        $this->dispatcher     = $dispatcher;
        $this->container      = $container;
    }

    /**
     * @param Oxygen_Http_Request     $request
     * @param Oxygen_Util_RequestData $requestData
     *
     * @return Oxygen_Util_HookedClosure|Oxygen_Http_Response
     * @throws Oxygen_Exception
     */
    public function handle(Oxygen_Http_Request $request, $requestData)
    {
        $actionDefinition = $this->actionRegistry->getDefinition($requestData->actionName);
        $hookName         = $actionDefinition->getOption('hook_name');

        if ($hookName === null) {
            return $this->handleRaw($request, $requestData, $actionDefinition->getClass(), $actionDefinition->getMethod(), $requestData->actionParameters);
        }

        $actionClosure = new Oxygen_Util_Closure(array($this, 'handleRaw'), $request, $requestData, $actionDefinition->getClass(), $actionDefinition->getMethod(), $requestData->actionParameters);

        return new Oxygen_Util_HookedClosure($hookName, $actionClosure->getCallable());
    }

    /**
     * @internal
     *
     * @param Oxygen_Http_Request     $request
     * @param Oxygen_Util_RequestData $requestData
     * @param string                  $className
     * @param string                  $method
     * @param array                   $actionParameters
     *
     * @return Oxygen_Http_Response
     * @throws Oxygen_Exception
     */
    public function handleRaw($request, $requestData, $className, $method, array $actionParameters)
    {
        $reflectionMethod = new ReflectionMethod($className, $method);
        $parameters       = $reflectionMethod->getParameters();
        $arguments        = array();

        foreach ($parameters as $parameter) {
            if (isset($actionParameters[$parameter->getName()])) {
                $arguments[] = $actionParameters[$parameter->getName()];
            } else {
                if (!$parameter->isOptional()) {
                    throw new Oxygen_Exception(Oxygen_Exception::ACTION_ARGUMENT_NOT_PROVIDED);
                }
                $arguments[] = $parameter->getDefaultValue();
            }
        }

        if (is_subclass_of($className, 'Oxygen_Container_ServiceLocatorAware')) {
            $instance = call_user_func(array($className, 'createFromContainer'), $this->container);
        } else {
            $instance = new $className();
        }

        $result = call_user_func_array(array($instance, $method), $arguments);

        if (is_array($result)) {
            $result = $this->convertResultToResponse($request, $requestData, $result);
        } elseif (!$result instanceof Oxygen_Http_Response) {
            throw new LogicException(sprintf('An action should return array or an instance of Oxygen_Http_Response; %s gotten.', gettype($result)));
        }

        return $result;
    }

    /**
     * @param Oxygen_Http_Request     $request
     * @param Oxygen_Util_RequestData $requestData
     * @param array                   $result
     *
     * @return Oxygen_Http_Response
     */
    private function convertResultToResponse($request, $requestData, array $result)
    {
        $actionDataEvent = new Oxygen_Event_ActionDataEvent($request, $requestData, $result);
        $this->dispatcher->dispatch(Oxygen_Event_Events::ACTION_DATA, $actionDataEvent);

        if (!$actionDataEvent->hasResponse()) {
            throw new LogicException('Action data should get converted to a response object.');
        }

        return $actionDataEvent->getResponse();
    }
}


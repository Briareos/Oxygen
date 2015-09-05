<?php

class Oxygen_Kernel
{
    /**
     * @var Oxygen_Container_Interface
     */
    private $container;

    /**
     * @param Oxygen_Container_Interface $container
     */
    public function __construct(Oxygen_Container_Interface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Oxygen_Http_Request $request
     *
     * @return null|Oxygen_Http_Response|Oxygen_Util_HookedClosure
     */
    public function handle(Oxygen_Http_Request $request)
    {
        $dispatcher = $this->container->getDispatcher();

        if (!$request->accepts('application/oxygen')) {
            // Public request.
            $publicRequestEvent = new Oxygen_Event_PublicRequestEvent($request);
            $dispatcher->dispatch(Oxygen_Event_Events::PUBLIC_REQUEST, $publicRequestEvent);
            if ($publicRequestEvent->hasDeferredResponse()) {
                return $publicRequestEvent->getDeferredResponse();
            }
            return $publicRequestEvent->getResponse();
        }

        try {
            // Master request.
            $requestData = json_decode($request->getContent(), true);
            if (!is_array($requestData)) {
                throw new RuntimeException('The request does not contain valid JSON.');
            }
            /** @var Oxygen_Util_RequestData $requestData */
            $requestData        = (object)$requestData;
            $masterRequestEvent = new Oxygen_Event_MasterRequestEvent($request, $requestData);
            $dispatcher->dispatch(Oxygen_Event_Events::MASTER_REQUEST, $masterRequestEvent);

            $result = $this->container->getActionKernel()->handle($request, $requestData);

            if ($result instanceof Oxygen_Http_Response) {
                return $this->notifyResponse($request, $requestData, $result);
            } else {
                $callback = new Oxygen_Util_Closure(array($this, 'wrapResponse'), $request, $requestData, $result->getCallable());
                return new Oxygen_Util_HookedClosure($result->getHookName(), $callback->getCallable());
            }
        } catch (Exception $e) {
            return $this->handleException($request, isset($requestData) ? $requestData : null, $e);
        }
    }

    /**
     * @param Oxygen_Http_Request     $request
     * @param Oxygen_Util_RequestData $requestData
     * @param callable                $responseCallback
     *
     * @return Oxygen_Http_Response
     */
    public function wrapResponse(Oxygen_Http_Request $request, $requestData, $responseCallback)
    {
        try {
            $delayedActionEvent = new Oxygen_Event_DelayedActionEvent($request, $requestData);
            $this->container->getDispatcher()->dispatch(Oxygen_Event_Events::DELAYED_ACTION, $delayedActionEvent);
            $response = $responseCallback();
            return $this->notifyResponse($request, $requestData, $response);
        } catch (Exception $e) {
            return $this->handleException($request, $requestData, $e);
        }
    }

    /**
     * @param Oxygen_Http_Request     $request
     * @param Oxygen_Util_RequestData $requestData
     * @param Oxygen_Http_Response    $response
     *
     * @return Oxygen_Http_Response
     */
    public function notifyResponse(Oxygen_Http_Request $request, $requestData, Oxygen_Http_Response $response)
    {
        try {
            $responseEvent = new Oxygen_Event_MasterResponseEvent($request, $requestData, $response);
            $this->container->getDispatcher()->dispatch(Oxygen_Event_Events::MASTER_RESPONSE, $responseEvent);
            return $responseEvent->getResponse();
        } catch (Exception $e) {
            return $this->handleException($request, $requestData, $e);
        }
    }

    /**
     * @param Oxygen_Http_Request          $request
     * @param Oxygen_Util_RequestData|null $requestData
     * @param                              $exception
     *
     * @return null|Oxygen_Http_Response
     */
    private function handleException(Oxygen_Http_Request $request, $requestData, $exception)
    {
        $exceptionEvent = new Oxygen_Event_ExceptionEvent($request, $requestData, $exception);
        $this->container->getDispatcher()->dispatch(Oxygen_Event_Events::EXCEPTION, $exceptionEvent);

        if (!$exceptionEvent->hasResponse()) {
            throw new RuntimeException('The response was not set after an exception.');
        }
        return $exceptionEvent->getResponse();
    }
}

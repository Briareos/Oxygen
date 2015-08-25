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
     * @param callable            $callback
     */
    public function handle(Oxygen_Http_Request $request, $callback = null)
    {
        if ($callback === null) {
            $callback = array($this, 'sendResponse');
        }

        $dispatcher = $this->container->getDispatcher();
        try {
            if (!$request->accepts('application/oxygen')) {
                // Public request.
                $publicRequestEvent = new Oxygen_Event_PublicRequestEvent($request);
                $dispatcher->dispatch(Oxygen_Event_Events::PUBLIC_REQUEST, $publicRequestEvent);
                if ($publicRequestEvent->hasResponse()) {
                    call_user_func($callback, $publicRequestEvent->getResponse());
                }
                return;
            } else {
                // Master request.
                $data = json_decode($request->getContent(), true);
                if (!is_array($data)) {
                    throw new RuntimeException('The request does not contain valid JSON.');
                }
                $masterRequestEvent = new Oxygen_Event_MasterRequestEvent($request, $data);
                $dispatcher->dispatch(Oxygen_Event_Events::MASTER_REQUEST, $masterRequestEvent);
                if ($masterRequestEvent->hasResponse()) {
                    throw new RuntimeException('The response was not set after an action call.');
                }
                call_user_func($callback, $masterRequestEvent->getResponse());
            }
        } catch (Exception $e) {
            $exceptionEvent = new Oxygen_Event_ExceptionEvent($request, $e);
            $dispatcher->dispatch(Oxygen_Event_Events::EXCEPTION, $exceptionEvent);

            if (!$exceptionEvent->hasResponse()) {
                throw new RuntimeException('The response was not set after an exception.');
            }
            call_user_func($callback, $exceptionEvent->getResponse());
        }
    }

    public function sendResponse(Oxygen_Http_Response $response)
    {
        $responseEvent = new Oxygen_Event_MasterResponseEvent($response);
        $this->container->getDispatcher()->dispatch(Oxygen_Event_Events::MASTER_RESPONSE, $responseEvent);
        $responseEvent->getResponse()->send();
        exit;
    }
}

<?php

class Oxygen_Event_ExceptionEvent extends Oxygen_EventDispatcher_Event
{
    /**
     * @var Oxygen_Http_Request
     */
    private $request;

    /**
     * @var Exception
     */
    private $exception;

    /**
     * @var Oxygen_Http_Response
     */
    private $response;

    /**
     * @param Oxygen_Http_Request $request
     * @param Exception           $exception
     */
    public function __construct(Oxygen_Http_Request $request, Exception $exception)
    {
        $this->request   = $request;
        $this->exception = $exception;
    }

    /**
     * @return Oxygen_Http_Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return Oxygen_Http_Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Oxygen_Http_Response $response
     */
    public function setResponse(Oxygen_Http_Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return $this->response !== null;
    }
}

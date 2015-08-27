<?php

class Oxygen_Event_ExceptionEvent extends Oxygen_EventDispatcher_Event
{
    /**
     * @var Oxygen_Http_Request
     */
    private $request;

    /**
     * @var Oxygen_Util_RequestData|null
     */
    private $requestData;

    /**
     * @var Exception
     */
    private $exception;

    /**
     * @var Oxygen_Http_Response
     */
    private $response;

    /**
     * @param Oxygen_Http_Request          $request
     * @param Oxygen_Util_RequestData|null $requestData
     * @param Exception                    $exception
     */
    public function __construct(Oxygen_Http_Request $request, $requestData = null, Exception $exception)
    {
        $this->request     = $request;
        $this->requestData = $requestData;
        $this->exception   = $exception;
    }

    /**
     * @return Oxygen_Http_Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Oxygen_Util_RequestData|null
     */
    public function getRequestData()
    {
        return $this->requestData;
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

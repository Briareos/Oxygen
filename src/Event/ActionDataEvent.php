<?php

class Oxygen_Event_ActionDataEvent extends Oxygen_EventDispatcher_Event
{
    /**
     * @var Oxygen_Http_Request
     */
    private $request;
    /**
     * @var Oxygen_Util_RequestData
     */
    private $requestData;
    /**
     * @var array
     */
    private $result;
    /**
     * @var Oxygen_Http_Response|null
     */
    private $response;

    /**
     * @param Oxygen_Http_Request     $request
     * @param Oxygen_Util_RequestData $requestData
     * @param array                   $result
     */
    public function __construct(Oxygen_Http_Request $request, $requestData, array $result)
    {
        $this->request = $request;
        $this->requestData = $requestData;
        $this->result = $result;
    }

    /**
     * @return Oxygen_Http_Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Oxygen_Util_RequestData
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Oxygen_Http_Response
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

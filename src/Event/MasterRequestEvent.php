<?php

class Oxygen_Event_MasterRequestEvent extends Oxygen_EventDispatcher_Event
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
     * @var Oxygen_Http_Response|null
     */
    private $response;

    /**
     * @param Oxygen_Http_Request     $request
     * @param Oxygen_Util_RequestData $data
     */
    public function __construct(Oxygen_Http_Request $request, $data)
    {
        $this->request     = $request;
        $this->requestData = $data;
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
     * @return Oxygen_Http_Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Oxygen_Http_Response|null $response
     */
    public function setResponse($response = null)
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

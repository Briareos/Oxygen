<?php

class Oxygen_Event_MasterResponseEvent extends Oxygen_EventDispatcher_Event
{
    /**
     * @var Oxygen_Http_Request
     */
    private $request;
    /**
     * @var Oxygen_Http_Response
     */
    private $response;

    /**
     * @param Oxygen_Http_Request  $request
     * @param Oxygen_Http_Response $response
     */
    public function __construct(Oxygen_Http_Request $request, Oxygen_Http_Response $response)
    {
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @return Oxygen_Http_Request
     */
    public function getRequest()
    {
        return $this->request;
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
}

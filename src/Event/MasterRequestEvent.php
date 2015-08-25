<?php

class Oxygen_Event_MasterRequestEvent extends Oxygen_EventDispatcher_Event
{
    /**
     * @var Oxygen_Http_Request
     */
    private $request;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Oxygen_Http_Response|null
     */
    private $response;

    /**
     * @param Oxygen_Http_Request $request
     * @param array               $data
     */
    public function __construct(Oxygen_Http_Request $request, array $data)
    {
        $this->request = $request;
        $this->data    = $data;
    }

    /**
     * @return Oxygen_Http_Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
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

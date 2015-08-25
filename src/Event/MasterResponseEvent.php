<?php

class Oxygen_Event_MasterResponseEvent extends Oxygen_EventDispatcher_Event
{
    /**
     * @var Oxygen_Http_Response|null
     */
    private $response;

    /**
     * @param Oxygen_Http_Response $response
     */
    public function __construct(Oxygen_Http_Response $response)
    {
        $this->response = $response;
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
}

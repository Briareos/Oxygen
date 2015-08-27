<?php

class Oxygen_Event_PublicRequestEvent extends Oxygen_EventDispatcher_Event
{
    /**
     * @var Oxygen_Http_Request
     */
    private $request;

    /**
     * @var Oxygen_Http_Response|null
     */
    private $response;

    /**
     * @var Oxygen_Util_HookedClosure
     */
    private $deferredResponse;

    /**
     * @param Oxygen_Http_Request $request
     */
    public function __construct(Oxygen_Http_Request $request)
    {
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
     * @return Oxygen_Http_Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Oxygen_Http_Response|null $response
     */
    public function setResponse(Oxygen_Http_Response $response = null)
    {
        $this->response = $response;
    }

    public function hasResponse()
    {
        return $this->response !== null;
    }

    /**
     * @return Oxygen_Util_HookedClosure
     */
    public function getDeferredResponse()
    {
        return $this->deferredResponse;
    }

    /**
     * @param Oxygen_Util_HookedClosure $deferredResponse
     */
    public function setDeferredResponse(Oxygen_Util_HookedClosure $deferredResponse)
    {
        $this->deferredResponse = $deferredResponse;
    }

    /**
     * @return bool
     */
    public function hasDeferredResponse()
    {
        return $this->deferredResponse !== null;
    }
}

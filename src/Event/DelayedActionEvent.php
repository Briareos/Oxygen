<?php

class Oxygen_Event_DelayedActionEvent
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
     * @param Oxygen_Http_Request     $request
     * @param Oxygen_Util_RequestData $requestData
     */
    public function __construct(Oxygen_Http_Request $request, $requestData)
    {
        $this->request     = $request;
        $this->requestData = $requestData;
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
}

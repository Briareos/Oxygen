<?php

class Oxygen_EventListener_SetResponseListener
{
    public function onActionData(Oxygen_Event_ActionDataEvent $event)
    {
        $response = new Oxygen_Http_JsonResponse(array(
            'oxygenResponseId' => $event->getRequestData()->oxygenRequestId,
            'actionResult'     => $event->getResult(),
        ));

        $event->setResponse($response);
    }
}

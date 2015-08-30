<?php

class Oxygen_EventListener_SetResponseListener
{
    public function onActionData(Oxygen_Event_ActionDataEvent $event)
    {
        $response = new Oxygen_Http_JsonResponse(array(
            // Kind of like str_rot18 that includes support for numbers.
            'oxygenResponseId' => strtr($event->getRequestData()->oxygenRequestId, 'abcdefghijklmnopqrstuvwxyz0123456789', 'stuvwxyz0123456789abcdefghijklmnopqr'),
            'actionResult'     => $event->getResult(),
        ));

        $event->setResponse($response);
    }
}

<?php

class Oxygen_EventListener_SetResponseListener
{
    public function onActionData(Oxygen_Event_ActionDataEvent $event)
    {
        $response = new Oxygen_Http_JsonResponse(array(
            // Kind of like str_rot8, for hexadecimal strings.
            'oxygenResponseId' => strtr($event->getRequestData()->oxygenRequestId, 'abcdef0123456789', '23456789abcdef01'),
            'actionResult'     => $event->getResult(),
        ));

        $event->setResponse($response);
    }
}

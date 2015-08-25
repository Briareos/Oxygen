<?php

class Oxygen_Http_JsonResponse extends Oxygen_Http_Response
{
    public function __construct($content, $statusCode = 200, array $headers = array())
    {
        $headers['content-type'] = 'application/json';
        parent::__construct($content, $statusCode, $headers);
    }

    public function getContentAsString()
    {
        return "\n".json_encode($this->content);
    }
}

<?php

interface Oxygen_Http_StreamingResponseInterface
{
    /**
     * @return Oxygen_Stream_Interface
     */
    public function createResponseStream();
}

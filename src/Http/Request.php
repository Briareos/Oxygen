<?php

class Oxygen_Http_Request
{
    /**
     * @return Oxygen_Http_Request
     */
    public static function createFromGlobals() {
        return new self();
    }
}

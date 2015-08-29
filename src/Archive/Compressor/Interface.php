<?php

interface Oxygen_Archive_Compressor_Interface
{
    /**
     * @param string $file
     *
     * @return resource
     */
    public function open($file);

    /**
     * @param resource $resource
     *
     * @return string Binary data.
     */
    public function readBlock($resource);
}

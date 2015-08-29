<?php

class Oxygen_Archive_Compressor_None implements Oxygen_Archive_Compressor_Interface
{
    /**
     * {@inheritdoc}
     */
    public function open($file)
    {
        return fopen($file, 'rb');
    }

    /**
     * {@inheritdoc}
     */
    public function readBlock($resource)
    {
        return fread($resource, 512);
    }
}

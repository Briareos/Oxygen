<?php

class Oxygen_Archive_Compressor_Gz implements Oxygen_Archive_Compressor_Interface
{
    /**
     * {@inheritdoc}
     */
    public function open($file)
    {
        return gzopen($file, 'rb');
    }

    /**
     * {@inheritdoc}
     */
    public function readBlock($resource)
    {
        return gzread($resource, 512);
    }
}

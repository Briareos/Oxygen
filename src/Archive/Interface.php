<?php

interface Oxygen_Archive_Interface
{
    /**
     * @param $file
     *
     * @return $this
     */
    public static function openLocalFile($file);

    /**
     * @param $path
     */
    public function extract($path);
}

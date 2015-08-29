<?php

interface Oxygen_Archive_Interface
{
    public static function openLocalFile($fileName);

    public function extract($path);
}

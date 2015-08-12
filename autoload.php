<?php

function oxygen_autoload($class)
{
    static $srcDirectory;

    if ($srcDirectory === null) {
        $srcDirectory = dirname(__FILE__).'/src';
    }

    if (substr($class, 0, 7) === 'Oxygen_') {
        /** @noinspection PhpIncludeInspection */
        require $srcDirectory.'/'.str_replace('_', '/', substr($class, 7)).'.php';
    }
}

spl_autoload_register('oxygen_autoload');

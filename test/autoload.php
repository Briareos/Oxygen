<?php

/**
 * Autoloader file for tests.
 */

require_once __DIR__.'/../autoload.php';

/**
 * @param string $class
 */
function oxygen_autoload_tests($class)
{
    static $srcDirectory;

    if ($srcDirectory === null) {
        $srcDirectory = dirname(__FILE__);
    }

    if (substr($class, 0, 13) === 'Oxygen\\Tests\\') {
        /** @noinspection PhpIncludeInspection */
        require $srcDirectory.'/'.str_replace('_', '/', substr($class, 13)).'.php';
    }
}

spl_autoload_register('oxygen_autoload_tests');

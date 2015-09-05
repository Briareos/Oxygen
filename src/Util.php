<?php

class Oxygen_Util
{
    private function __construct()
    {
    }

    /**
     * Strips www. prefix and protocol, and also trims leading and trailing slash in URL path. The point is to normalize
     * the URL so minor differences between URLs result in the same slug.
     *
     * Return examples: example.com/blog, example.com, ., example.com/nested-path/blog, example.com:81/installation-path
     *
     * @param string $url
     *
     * @return string
     */
    public static function getUrlSlug($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $path = parse_url($url, PHP_URL_PATH);

        return sprintf('%s%s%s', $host, (is_int($port) ? ':'.$port : ''), (is_string($path) ? rtrim($path, '/') : ''));
    }

    /**
     * @param string $knownString
     * @param string $userString
     *
     * @return bool
     */
    public static function hashEquals($knownString, $userString)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($knownString, $userString);
        }

        $res = $knownString ^ $userString;
        $ret = 0;
        for ($i = strlen($res) - 1; $i >= 0; $i--) {
            $ret |= ord($res[$i]);
        }

        return !$ret;
    }

    /**
     * Converts value of 'memory_limit' php.ini directive to bytes.
     *
     * @param int|string $memoryLimit
     *
     * @return int Limit in bytes or -1 if it's unlimited.
     */
    public static function convertToBytes($memoryLimit)
    {
        $memoryLimit = (string)$memoryLimit;

        if ('-1' === $memoryLimit) {
            return -1;
        }

        $memoryLimit = strtolower($memoryLimit);
        $max         = strtolower(ltrim($memoryLimit, '+'));
        if (0 === strpos($max, '0x')) {
            $max = intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = intval($max);
        }

        switch (substr($memoryLimit, -1)) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 't':
                $max *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $max *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'm':
                $max *= 1024;
            case 'k':
                $max *= 1024;
        }

        return $max;
    }

    /**
     * Converts path with back-slashes to forward-slashes. This is OS-aware, and WILL NOT do anything if the OS already
     * uses forward-slashes as the directory separator.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath($path)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return $path;
        }

        return strtr('\\', '/', $path);
    }
}

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
}

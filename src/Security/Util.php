<?php

class Oxygen_Security_Util
{
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

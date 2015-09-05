<?php

class Oxygen_System_Environment
{
    /**
     * @var array
     */
    private $override;

    /**
     * @param array $override An array of overrideKey => overrideValue. Recognized keys are:
     *                        - memory_limit => string - The limit from php.ini.
     *                        - openssl      => bool   - PHP extension enabled.
     */
    public function __construct(array $override = array())
    {
        $this->override = $override;
    }

    /**
     * @return int PHP memory limit in bytes.
     */
    public function getMemoryLimit()
    {
        if (isset($this->override['memory_limit'])) {
            $limit = $this->override['memory_limit'];
        } else {
            $limit = ini_get('memory_limit');
        }
        return Oxygen_Util::convertToBytes($limit);
    }

    /**
     * @return bool
     */
    public function isOpensslEnabled()
    {
        if (isset($this->override['openssl'])) {
            return $this->override['openssl'];
        }

        return extension_loaded('openssl');
    }
}

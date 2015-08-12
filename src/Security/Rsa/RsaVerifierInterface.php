<?php

interface Oxygen_Security_Rsa_RsaVerifierInterface
{
    /**
     * Same as self::mustVerify(), only it returns a value instead of throwing an exception.
     *
     * @param string $publicKey Public key in base64-encoded format (with -----BEGIN PUBLIC KEY----- header and footer).
     * @param string $data      Data to verify.
     * @param string $signature Signature in base64-encoded format.
     *
     * @return boolean
     *
     * @throws Oxygen_Exception If the key provided is not valid.
     */
    public function verify($publicKey, $data, $signature);
}

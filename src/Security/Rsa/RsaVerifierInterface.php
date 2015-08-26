<?php

interface Oxygen_Security_Rsa_RsaVerifierInterface
{
    /**
     * @param string $publicKey Public key in base64-encoded format (with -----BEGIN/END PUBLIC KEY----- header and footer).
     * @param string $data      Data to verify.
     * @param string $signature Signature in base64-encoded format.
     *
     * @return bool
     *
     * @throws Oxygen_Exception If the signature format is not valid, or verification fails.
     */
    public function verify($publicKey, $data, $signature);
}

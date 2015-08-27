<?php

interface Oxygen_Security_Nonce_NonceManagerInterface
{
    /**
     * @param string $nonce     Nonce, 32 alphanumeric characters.
     * @param int    $expiresAt Unix timestamp when the nonce expires.
     *
     * @throws Oxygen_Exception If the nonce format is invalid; if the nonce has expired, or if the nonce was already used.
     */
    public function useNonce($nonce, $expiresAt);
}

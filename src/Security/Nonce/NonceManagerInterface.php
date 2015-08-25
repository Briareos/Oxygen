<?php

interface Oxygen_Security_Nonce_NonceManagerInterface
{
    /**
     * @param string $nonce Nonce in format {nonceValue}_{nonceCreatedAt(unixTimestamp)}_{nonceLifetime(seconds)}
     *
     * @throws Oxygen_Exception If the nonce format is invalid; if the nonce has expired, or if the nonce was already used.
     */
    public function useNonce($nonce);
}

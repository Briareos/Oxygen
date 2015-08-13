<?php

interface Oxygen_Container_Interface
{
    /**
     * @return Oxygen_Security_Rsa_RsaVerifierInterface
     */
    public function getRsaVerifier();
}

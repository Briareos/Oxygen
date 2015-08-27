<?php

class Oxygen_Security_Rsa_OpensslRsaVerifier implements Oxygen_Security_Rsa_RsaVerifierInterface
{
    /**
     * {@inheritdoc}
     */
    public function verify($publicKey, $data, $signature)
    {
        /** @handled function */
        $verify = @openssl_verify($data, base64_decode($signature), $publicKey);

        if ($verify === -1) {
            $error     = $errorRow = '';
            $lastError = error_get_last();

            /** @handled function */
            while (($errorRow = openssl_error_string()) !== false) {
                $error = $errorRow."\n".$error;
            }

            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_OPENSSL_VERIFY_ERROR, "There was an error while trying to use OpenSSL to verify a message.", array(
                'opensslError' => $error,
                'error'        => isset($lastError['message']) ? $lastError['message'] : null,
            ));
        }

        return (bool) $verify;
    }
}

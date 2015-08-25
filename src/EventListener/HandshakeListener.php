<?php

class Oxygen_EventListener_HandshakeListener
{
    /**
     * @var Oxygen_Drupal_StateInterface
     */
    private $state;

    /**
     * @var Oxygen_Security_Rsa_RsaVerifierInterface
     */
    private $rsaVerifier;
    /**
     * @var Oxygen_Security_Nonce_NonceManagerInterface
     */
    private $nonceManager;

    /**
     * @param Oxygen_Drupal_StateInterface                $state
     * @param Oxygen_Security_Rsa_RsaVerifierInterface    $rsaVerifier
     * @param Oxygen_Security_Nonce_NonceManagerInterface $nonceManager
     */
    public function __construct(Oxygen_Drupal_StateInterface $state, Oxygen_Security_Rsa_RsaVerifierInterface $rsaVerifier, Oxygen_Security_Nonce_NonceManagerInterface $nonceManager)
    {
        $this->state       = $state;
        $this->rsaVerifier = $rsaVerifier;
        $this->nonceManager = $nonceManager;
    }

    public function onMasterRequest(Oxygen_Event_MasterRequestEvent $event)
    {
        $data              = $event->getData();
        $existingPublicKey = $this->state->get('oxygen_public_key');

        if (empty($data['publicKey'])) {
            throw new Oxygen_Exception(Oxygen_Exception::HANDSHAKE_PUBLIC_KEY_NOT_PROVIDED);
        }

        $providedPublicKey = $data['publicKey'];

        if (empty($data['signature'])) {
            throw new Oxygen_Exception(Oxygen_Exception::HANDSHAKE_SIGNATURE_NOT_PROVIDED);
        }

        $signature = $data['signature'];

        if (empty($data['nonce'])) {
            throw new Oxygen_Exception(Oxygen_Exception::HANDSHAKE_NONCE_NOT_PROVIDED);
        }

        $nonce = $data['nonce'];

        if (empty($existingPublicKey)) {
            // There is no public key set, use the provided one to verify SSL implementation.
            $verifyPublicKey = $providedPublicKey;
        } else {
            $verifyPublicKey = $existingPublicKey;
        }

        $verified = $this->rsaVerifier->verify($verifyPublicKey, $nonce, $signature);

        if (!$verified) {
            if (empty($existingPublicKey)) {
                // A public key is not set, but the handshake failed. There might be a problem with the OpenSSL implementation.
                throw new Oxygen_Exception(Oxygen_Exception::HANDSHAKE_VERIFY_TEST_FAILED);
            } else {
                throw new Oxygen_Exception(Oxygen_Exception::HANDSHAKE_VERIFY_FAILED);
            }
        }

        $this->nonceManager->useNonce($nonce);

        if (empty($existingPublicKey)) {
            $this->state->set('oxygen_public_key', $providedPublicKey);
        }
    }
}

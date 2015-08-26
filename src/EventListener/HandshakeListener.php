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
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $modulePath;

    /**
     * @param Oxygen_Drupal_StateInterface                $state
     * @param Oxygen_Security_Rsa_RsaVerifierInterface    $rsaVerifier
     * @param Oxygen_Security_Nonce_NonceManagerInterface $nonceManager
     * @param string                                      $baseUrl
     * @param string                                      $modulePath
     */
    public function __construct(Oxygen_Drupal_StateInterface $state, Oxygen_Security_Rsa_RsaVerifierInterface $rsaVerifier, Oxygen_Security_Nonce_NonceManagerInterface $nonceManager, $baseUrl, $modulePath)
    {
        $this->state        = $state;
        $this->rsaVerifier  = $rsaVerifier;
        $this->nonceManager = $nonceManager;
        $this->baseUrl      = $baseUrl;
        $this->modulePath   = $modulePath;
    }

    public function onMasterRequest(Oxygen_Event_MasterRequestEvent $event)
    {
        $data              = $event->getData();
        $existingPublicKey = $this->state->get('oxygen_public_key');

        $providedPublicKey = $data['publicKey'];
        $signature         = $data['signature'];
        $nonce             = $data['nonce'];

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

        if (!empty($existingPublicKey)) {
            // We validated against an existing key.
            $this->nonceManager->useNonce($nonce);
            return;
        }

        $handshakeKey = @file_get_contents($this->modulePath.'/keys/'.$data['handshakeKey'].'.key');

        if ($handshakeKey === false) {
            $lastError = error_get_last();
            throw new Oxygen_Exception(Oxygen_Exception::HANDSHAKE_LOCAL_KEY_NOT_FOUND, null, array(
                'lastError' => $lastError['message'],
                'keyPath'   => $this->modulePath.'/'.$data['handshakeKey'],
            ));
        }

        $urlSlug = Oxygen_Util::getUrlSlug($this->baseUrl);

        $verifiedHandshake = $this->rsaVerifier->verify($handshakeKey, $urlSlug, $data['handshakeSignature']);

        if (!$verifiedHandshake) {
            throw new Oxygen_Exception(Oxygen_Exception::HANDSHAKE_LOCAL_VERIFY_FAILED);
        }

        $this->nonceManager->useNonce($nonce);
        $this->state->set('oxygen_public_key', $providedPublicKey);
    }
}

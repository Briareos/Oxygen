<?php

/**
 * Validates the request parameters and basic validation constraints.
 */
class Oxygen_EventListener_ProtocolListener
{
    /**
     * @var string In format X.Y
     */
    private $version;
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param string $version
     * @param string $baseUrl
     */
    public function __construct($version, $baseUrl)
    {
        $this->version = $version;
        $this->baseUrl = $baseUrl;
    }

    public function onMasterRequest(Oxygen_Event_MasterRequestEvent $event)
    {
        $data = $event->getRequestData();

        if (empty($data->oxygenRequestId)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_REQUEST_ID_NOT_PROVIDED);
        }

        if (!is_string($data->oxygenRequestId) || !strlen($data->oxygenRequestId)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_REQUEST_ID_NOT_VALID);
        }

        if (empty($data->publicKey)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_PUBLIC_KEY_NOT_PROVIDED);
        }

        if (!is_string($data->publicKey) || !strlen($data->publicKey)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_PUBLIC_KEY_NOT_VALID);
        }

        if (!isset($data->username)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_USERNAME_NOT_PROVIDED);
        }

        if (!is_string($data->username)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_USERNAME_NOT_VALID);
        }

        if (empty($data->signature)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_SIGNATURE_NOT_PROVIDED);
        }

        if (!is_string($data->signature) || !preg_match('{^[a-zA-Z\d/+]+={0,2}$}', $data->signature)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_SIGNATURE_NOT_VALID);
        }

        if (empty($data->handshakeKey)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_HANDSHAKE_KEY_NOT_PROVIDED);
        }

        if (!is_string($data->handshakeKey) || !preg_match('{^[a-z0-9_]+$}', $data->handshakeKey)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_HANDSHAKE_KEY_NOT_VALID);
        }

        if (empty($data->handshakeSignature)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_HANDSHAKE_SIGNATURE_NOT_PROVIDED);
        }

        if (!is_string($data->handshakeSignature) || !preg_match('{^[a-zA-Z\d/+]+={0,2}$}', $data->handshakeSignature)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_HANDSHAKE_SIGNATURE_NOT_VALID);
        }

        if (empty($data->requestExpiresAt)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_EXPIRATION_NOT_PROVIDED);
        }

        if (!is_int($data->requestExpiresAt)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_EXPIRATION_NOT_VALID);
        }

        if (empty($data->requiredVersion)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_REQUIRED_VERSION_NOT_PROVIDED);
        }

        if (!is_string($data->requiredVersion) || !preg_match('{^\d+\.\d+$}', $data->requiredVersion)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_REQUIRED_VERSION_NOT_VALID);
        }

        if (version_compare($data->requiredVersion, $this->version, '>')) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_VERSION_TOO_LOW);
        }

        if (empty($data->actionName)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_ACTION_NAME_NOT_PROVIDED);
        }

        if (!is_string($data->actionName)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_ACTION_NAME_NOT_VALID);
        }

        if (!isset($data->actionParameters)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_ACTION_PARAMETERS_NOT_PROVIDED);
        }

        if (!is_array($data->actionParameters)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_ACTION_PARAMETERS_NOT_VALID);
        }

        if (empty($data->baseUrl)) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_BASE_URL_NOT_PROVIDED);
        }

        if (!is_string($data->baseUrl) || !in_array(parse_url($data->baseUrl, PHP_URL_SCHEME), array('http', 'https')) || !is_string(parse_url($data->baseUrl, PHP_URL_HOST))) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_BASE_URL_NOT_VALID);
        }

        $providedBaseUrlSlug = Oxygen_Util::getUrlSlug($data->baseUrl);
        $currentBaseUrlSlug  = Oxygen_Util::getUrlSlug($this->baseUrl);

        if ($providedBaseUrlSlug !== $currentBaseUrlSlug) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_BASE_URL_SLUG_MISMATCHES, array(
                'providedBaseUrl'     => $data->baseUrl,
                'providedBaseUrlSlug' => $providedBaseUrlSlug,
                'currentBaseUrl'      => $this->baseUrl,
                'currentBaseUrlSlug'  => $currentBaseUrlSlug,
            ));
        }
    }
}

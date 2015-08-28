<?php

/**
 * This is only a helper class for documentation purposes and to enable auto-complete in IDEs.
 * It is NEVER really used, so don't typecast it anywhere.
 *
 * @see Oxygen_EventListener_ProtocolListener
 */
final class Oxygen_Util_RequestData
{
    /**
     * @var string
     */
    public $oxygenRequestId;

    /**
     * @var string
     */
    public $publicKey;

    /**
     * Can be an empty string.
     *
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $signature;

    /**
     * @var string
     */
    public $handshakeKey;

    /**
     * @var string
     */
    public $handshakeSignature;

    /**
     * @var int
     */
    public $requestExpiresAt;

    /**
     * @var string
     */
    public $requiredVersion;

    /**
     * @var string
     */
    public $actionName;

    /**
     * @var string
     */
    public $actionParameters;

    /**
     * @var string
     */
    public $baseUrl;

    private function __construct()
    {
    }
}

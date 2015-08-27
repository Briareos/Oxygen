<?php

class Oxygen_Exception extends Exception
{
    const GENERAL_ERROR = 10000;

    const FATAL_ERROR = 10046;

    const RSA_KEY_OPENSSL_VERIFY_ERROR = 10001;

    const RSA_KEY_PARSING_FAILED = 10002;
    const RSA_KEY_MISSING_ASN1_SEQUENCE = 10003;
    const RSA_KEY_MISSING_ASN1_OBJECT = 10004;
    const RSA_KEY_MISSING_ASN1_BITSTRING = 10005;
    const RSA_KEY_MISSING_ASN1_INTEGER = 10006;
    const RSA_KEY_INVALID_LENGTH = 10007;
    const RSA_KEY_UNSUPPORTED_ENCRYPTION = 10008;
    const RSA_KEY_SIGNATURE_REPRESENTATIVE_OUT_OF_RANGE = 10009;
    const RSA_KEY_SIGNATURE_INVALID = 10010;
    const RSA_KEY_INVALID_FORMAT = 10011;
    const RSA_KEY_SIGNATURE_SIZE_INVALID = 10012;
    const RSA_KEY_MODULUS_SIZE_INVALID = 10013;
    const RSA_KEY_ENCODED_SIZE_INVALID = 10014;

    const ACTION_NOT_FOUND = 10015;
    const ACTION_ARGUMENT_EMPTY = 10024;

    const NONCE_EXPIRED = 10017;
    const NONCE_ALREADY_USED = 10018;

    const HANDSHAKE_VERIFY_TEST_FAILED = 10022;
    const HANDSHAKE_VERIFY_FAILED = 10023;
    const HANDSHAKE_LOCAL_KEY_NOT_FOUND = 10042;
    const HANDSHAKE_LOCAL_VERIFY_FAILED = 10043;

    const PROTOCOL_PUBLIC_KEY_NOT_PROVIDED = 10019;
    const PROTOCOL_PUBLIC_KEY_NOT_VALID = 10025;
    const PROTOCOL_SIGNATURE_NOT_VALID = 10026;
    const PROTOCOL_SIGNATURE_NOT_PROVIDED = 10020;
    const PROTOCOL_EXPIRATION_NOT_PROVIDED = 10021;
    const PROTOCOL_EXPIRATION_NOT_VALID = 10027;
    const PROTOCOL_ACTION_NAME_NOT_PROVIDED = 10030;
    const PROTOCOL_ACTION_NAME_NOT_VALID = 10031;
    const PROTOCOL_ACTION_PARAMETERS_NOT_PROVIDED = 10032;
    const PROTOCOL_ACTION_PARAMETERS_NOT_VALID = 10033;
    const PROTOCOL_REQUIRED_VERSION_NOT_PROVIDED = 10028;
    const PROTOCOL_REQUIRED_VERSION_NOT_VALID = 10029;
    const PROTOCOL_VERSION_TOO_LOW = 10034;
    const PROTOCOL_HANDSHAKE_KEY_NOT_PROVIDED = 10035;
    const PROTOCOL_HANDSHAKE_KEY_NOT_VALID = 10036;
    const PROTOCOL_HANDSHAKE_SIGNATURE_NOT_PROVIDED = 10037;
    const PROTOCOL_HANDSHAKE_SIGNATURE_NOT_VALID = 10038;
    const PROTOCOL_BASE_URL_NOT_PROVIDED = 10039;
    const PROTOCOL_BASE_URL_NOT_VALID = 10040;
    const PROTOCOL_BASE_URL_SLUG_MISMATCHES = 10041;
    const PROTOCOL_REQUEST_ID_NOT_PROVIDED = 10044;
    const PROTOCOL_REQUEST_ID_NOT_VALID = 10045;

    /**
     * List of constants defined in this class.
     *
     * @internal
     */
    static $codes = array();

    /**
     * One of the constants defined in this class.
     */
    protected $errorName;

    /**
     * Optional exception context.
     *
     * @var array
     */
    protected $context;

    /**
     * Here for PHP 5.2 compatibility reasons.
     *
     * @var Exception|null
     */
    private $previousException;

    public function __construct($code, $message = null, array $context = array(), Exception $previous = null)
    {
        $this->errorName = $this->getTypeForCode($code);
        $this->context   = $context;

        if ($message === null) {
            $message = sprintf('Error [%d]: %s', $code, $this->errorName);
        }

        $this->previousException = $previous;
        parent::__construct($message, $code);
    }

    public function getPreviousException()
    {
        return $this->previousException;
    }

    private function getTypeForCode($code)
    {
        if (count(self::$codes) === 0) {
            $reflectionClass = new ReflectionClass(__CLASS__);
            self::$codes     = array_flip($reflectionClass->getConstants());
        }

        if (array_key_exists($code, self::$codes)) {
            return self::$codes[$code];
        }

        return self::GENERAL_ERROR;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->errorName;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}

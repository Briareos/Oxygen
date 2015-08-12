<?php

class Oxygen_Exception extends Exception
{
    const GENERAL_ERROR = 10000;

    const RSA_KEY_OPENSSL_VERIFY_ERROR = 10001;

    /**
     * Did not get a valid base64-encoded public key.
     */
    const RSA_KEY_PARSING_FAILED = 10002;

    const RSA_KEY_MISSING_ASN1_SEQUENCE = 10003;

    const RSA_KEY_MISSING_ASN1_OBJECT = 10004;

    const RSA_KEY_MISSING_ASN1_BITSTRING = 10005;

    const RSA_KEY_MISSING_ASN1_INTEGER = 10006;

    const RSA_KEY_INVALID_LENGTH = 10007;

    /**
     * Expected RSA key byte sequence not found.
     */
    const RSA_KEY_UNSUPPORTED_ENCRYPTION = 10008;

    /**
     * RSAVP1 validation failed.
     */
    const RSA_KEY_SIGNATURE_REPRESENTATIVE_OUT_OF_RANGE = 10009;

    /**
     * The signature provided is signed with another private key.
     */
    const RSA_KEY_SIGNATURE_INVALID = 10010;

    /**
     * The key provided cannot be parsed as an RSA key.
     */
    const RSA_KEY_INVALID_FORMAT = 10011;

    const RSA_KEY_SIGNATURE_SIZE_INVALID = 10012;

    const RSA_KEY_MODULUS_SIZE_INVALID = 10013;
    const RSA_KEY_ENCODED_SIZE_INVALID = 10014;

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
        $this->errorName = $this->getErrorNameForCode($code);
        $this->context = $context;

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

    private function getErrorNameForCode($code)
    {
        if (count(self::$codes) === 0) {
            $reflectionClass = new ReflectionClass(__CLASS__);
            self::$codes = array_flip($reflectionClass->getConstants());
        }

        if (array_key_exists($code, self::$codes)) {
            return self::$codes[$code];
        }

        return self::GENERAL_ERROR;
    }

    /**
     * @return string
     */
    public function getErrorName()
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

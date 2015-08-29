<?php

class Oxygen_Exception extends Exception
{
    const GENERAL_ERROR = 10000;

    const FATAL_ERROR = 10076;

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
    const PROTOCOL_USERNAME_NOT_PROVIDED = 10049;
    const PROTOCOL_USERNAME_NOT_VALID = 10048;
    const PROTOCOL_USER_UID_NOT_PROVIDED = 10050;
    const PROTOCOL_USER_UID_NOT_VALID = 10051;

    const PUBLIC_KEY_MISSING = 10046;

    const AUTO_LOGIN_CAN_NOT_FIND_USER = 10047;

    const MULTIPART_ENCODING_NOT_SUPPORTED = 10052;

    const ARCHIVE_FILE_NOT_FOUND = 10053;
    const ARCHIVE_TAR_ZLIB_EXTENSION_NOT_LOADED = 10054;
    const ARCHIVE_TAR_DESTINATION_DOES_NOT_EXIST = 10055;
    const ARCHIVE_TAR_FILE_EXISTS_AS_DIRECTORY = 10056;
    const ARCHIVE_TAR_DIRECTORY_EXISTS_AS_FILE = 10057;
    const ARCHIVE_TAR_FILE_IS_WRITE_PROTECTED = 10058;
    const ARCHIVE_TAR_DIRECTORY_CAN_NOT_BE_CREATED = 10059;
    const ARCHIVE_TAR_UNABLE_TO_OPEN_FILE_FOR_WRITING = 10060;
    const ARCHIVE_TAR_FILE_SIZE_MISMATCH = 10061;
    const ARCHIVE_TAR_INVALID_BLOCK_SIZE = 10062;
    const ARCHIVE_TAR_CHECKSUM_NOT_VALID = 10063;
    const ARCHIVE_TAR_FILE_NAME_CONTAINS_DIRECTORY_TRAVERSAL = 10064;

    const ARCHIVE_ZIP_EXTENSION_NOT_LOADED = 10065;
    const ARCHIVE_ZIP_EXTENSION_ERROR = 10066;

    const PROJECT_MANAGER_UNABLE_TO_RETRIEVE_DRUPAL_PROJECT = 10067;
    const PROJECT_MANAGER_EXTRACT_FAILED = 10068;
    const PROJECT_MANAGER_ARCHIVE_CONTAINS_NO_FILES = 10069;
    const PROJECT_MANAGER_ARCHIVE_VERIFY_ERROR = 10070;
    const PROJECT_MANAGER_CAN_NOT_FIND_APPROPRIATE_UPDATER = 10071;
    const PROJECT_MANAGER_UNABLE_TO_PARSE_PROJECT_INFO = 10072;
    const PROJECT_MANAGER_UNABLE_TO_DETERMINE_PROJECT_NAME = 10073;
    const PROJECT_MANAGER_PROJECT_ALREADY_INSTALLED = 10074;
    const PROJECT_MANAGER_FILE_SYSTEM_NOT_WRITABLE = 10075;

    // Next error code: 10077

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
     * @var array|null
     */
    protected $context;

    /**
     * Here for PHP 5.2 compatibility reasons.
     *
     * @var Exception|null
     */
    private $previousException;

    public function __construct($code, array $context = null, Exception $previous = null)
    {
        $context['lastError'] = error_get_last();
        $this->errorName      = $this->getTypeForCode($code);
        $this->context        = $context;


        $message = sprintf('Error [%d]: %s', $code, $this->errorName);

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
     * @return array|null
     */
    public function getContext()
    {
        return $this->context;
    }
}

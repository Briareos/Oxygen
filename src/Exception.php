<?php

class Oxygen_Exception extends Exception
{
    const GENERAL_ERROR = 30000;

    const FATAL_ERROR = 30076;

    const RSA_KEY_OPENSSL_VERIFY_ERROR = 30001;

    const RSA_KEY_PARSING_FAILED = 30002;
    const RSA_KEY_MISSING_ASN1_SEQUENCE = 30003;
    const RSA_KEY_MISSING_ASN1_OBJECT = 30004;
    const RSA_KEY_MISSING_ASN1_BITSTRING = 30005;
    const RSA_KEY_MISSING_ASN1_INTEGER = 30006;
    const RSA_KEY_INVALID_LENGTH = 30007;
    const RSA_KEY_UNSUPPORTED_ENCRYPTION = 30008;
    const RSA_KEY_SIGNATURE_REPRESENTATIVE_OUT_OF_RANGE = 30009;
    const RSA_KEY_SIGNATURE_INVALID = 30010;
    const RSA_KEY_INVALID_FORMAT = 30011;
    const RSA_KEY_SIGNATURE_SIZE_INVALID = 30012;
    const RSA_KEY_MODULUS_SIZE_INVALID = 30013;
    const RSA_KEY_ENCODED_SIZE_INVALID = 30014;

    const ACTION_NOT_FOUND = 30015;
    const ACTION_ARGUMENT_NOT_PROVIDED = 30024;

    const NONCE_EXPIRED = 30017;
    const NONCE_ALREADY_USED = 30018;

    const HANDSHAKE_VERIFY_TEST_FAILED = 30022;
    const HANDSHAKE_VERIFY_FAILED = 30023;
    const HANDSHAKE_LOCAL_KEY_NOT_FOUND = 30042;
    const HANDSHAKE_LOCAL_VERIFY_FAILED = 30043;

    const PROTOCOL_PUBLIC_KEY_NOT_PROVIDED = 30019;
    const PROTOCOL_PUBLIC_KEY_NOT_VALID = 30025;
    const PROTOCOL_SIGNATURE_NOT_VALID = 30026;
    const PROTOCOL_SIGNATURE_NOT_PROVIDED = 30020;
    const PROTOCOL_EXPIRATION_NOT_PROVIDED = 30021;
    const PROTOCOL_EXPIRATION_NOT_VALID = 30027;
    const PROTOCOL_ACTION_NAME_NOT_PROVIDED = 30030;
    const PROTOCOL_ACTION_NAME_NOT_VALID = 30031;
    const PROTOCOL_ACTION_PARAMETERS_NOT_PROVIDED = 30032;
    const PROTOCOL_ACTION_PARAMETERS_NOT_VALID = 30033;
    const PROTOCOL_VERSION_NOT_PROVIDED = 30028;
    const PROTOCOL_VERSION_NOT_VALID = 30029;
    const PROTOCOL_VERSION_TOO_LOW = 30034;
    const PROTOCOL_HANDSHAKE_KEY_NOT_PROVIDED = 30035;
    const PROTOCOL_HANDSHAKE_KEY_NOT_VALID = 30036;
    const PROTOCOL_HANDSHAKE_SIGNATURE_NOT_PROVIDED = 30037;
    const PROTOCOL_HANDSHAKE_SIGNATURE_NOT_VALID = 30038;
    const PROTOCOL_BASE_URL_NOT_PROVIDED = 30039;
    const PROTOCOL_BASE_URL_NOT_VALID = 30040;
    const PROTOCOL_BASE_URL_SLUG_MISMATCHES = 30041;
    const PROTOCOL_REQUEST_ID_NOT_PROVIDED = 30044;
    const PROTOCOL_REQUEST_ID_NOT_VALID = 30045;
    const PROTOCOL_USER_NAME_NOT_PROVIDED = 30049;
    const PROTOCOL_USER_NAME_NOT_VALID = 30048;
    const PROTOCOL_USER_UID_NOT_PROVIDED = 30050;
    const PROTOCOL_USER_UID_NOT_VALID = 30051;
    const PROTOCOL_STATE_PARAMETERS_NOT_PROVIDED = 30077;
    const PROTOCOL_STATE_PARAMETERS_NOT_VALID = 30078;

    const PUBLIC_KEY_MISSING = 30046;

    const AUTO_LOGIN_CAN_NOT_FIND_USER = 30047;

    const MULTIPART_ENCODING_NOT_SUPPORTED = 30052;

    const ARCHIVE_FILE_NOT_FOUND = 30053;
    const ARCHIVE_TAR_ZLIB_EXTENSION_NOT_LOADED = 30054;
    const ARCHIVE_TAR_DESTINATION_DOES_NOT_EXIST = 30055;
    const ARCHIVE_TAR_FILE_EXISTS_AS_DIRECTORY = 30056;
    const ARCHIVE_TAR_DIRECTORY_EXISTS_AS_FILE = 30057;
    const ARCHIVE_TAR_FILE_IS_WRITE_PROTECTED = 30058;
    const ARCHIVE_TAR_DIRECTORY_CAN_NOT_BE_CREATED = 30059;
    const ARCHIVE_TAR_UNABLE_TO_OPEN_FILE_FOR_WRITING = 30060;
    const ARCHIVE_TAR_FILE_SIZE_MISMATCH = 30061;
    const ARCHIVE_TAR_INVALID_BLOCK_SIZE = 30062;
    const ARCHIVE_TAR_CHECKSUM_NOT_VALID = 30063;
    const ARCHIVE_TAR_FILE_NAME_CONTAINS_DIRECTORY_TRAVERSAL = 30064;

    const ARCHIVE_ZIP_EXTENSION_NOT_LOADED = 30065;
    const ARCHIVE_ZIP_EXTENSION_ERROR = 30066;

    const PROJECT_MANAGER_UNABLE_TO_RETRIEVE_DRUPAL_PROJECT = 30067;
    const PROJECT_MANAGER_EXTRACT_FAILED = 30068;
    const PROJECT_MANAGER_ARCHIVE_CONTAINS_NO_FILES = 30069;
    const PROJECT_MANAGER_ARCHIVE_VERIFY_ERROR = 30070;
    const PROJECT_MANAGER_CAN_NOT_FIND_APPROPRIATE_UPDATER = 30071;
    const PROJECT_MANAGER_UNABLE_TO_PARSE_PROJECT_INFO = 30072;
    const PROJECT_MANAGER_UNABLE_TO_DETERMINE_PROJECT_NAME = 30073;
    const PROJECT_MANAGER_PROJECT_ALREADY_INSTALLED = 30074;
    const PROJECT_MANAGER_FILE_SYSTEM_NOT_WRITABLE = 30075;

    // Next error code: 30079

    /**
     * List of constants defined in this class.
     *
     * @internal
     */
    private static $codes = array();

    /**
     * One of the constants defined in this class.
     *
     * @string
     */
    protected $type;

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

    public function __construct($code, array $context = array(), Exception $previous = null)
    {
        if ($previous instanceof self) {
            throw new LogicException(sprintf('The previous exception must not be a subclass of %s.', __CLASS__));
        }

        $context['lastError'] = error_get_last();
        $this->type           = self::getTypeForCode($code);
        $this->context        = $context;

        $message = sprintf('Error [%d]: %s', $code, $this->type);

        $this->previousException = $previous;
        parent::__construct($message, $code);
    }

    public function getPreviousException()
    {
        return $this->previousException;
    }

    private static function getTypeForCode($code)
    {
        if (!self::$codes) {
            $reflectionClass = new ReflectionClass(__CLASS__);
            self::$codes     = array_flip($reflectionClass->getConstants());
        }

        if (array_key_exists($code, self::$codes)) {
            return self::$codes[$code];
        }

        return self::$codes[self::GENERAL_ERROR];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}

<?php

class Oxygen_Http_Response
{
    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2012-02-13).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );

    protected $protocol = 'HTTP/1.1';

    /**
     * @var mixed
     */
    protected $content;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string[]
     */
    protected $headers = array();

    public function __construct($content, $statusCode = 200, array $headers = array())
    {
        $this->content    = $content;
        $this->statusCode = $statusCode;
        $this->headers    = array_change_key_case($headers, CASE_LOWER);
    }

    public function prepare(Oxygen_Http_Request $request)
    {

    }

    public function getContentAsString()
    {
        if (null !== $this->content && !is_string($this->content) && !is_numeric($this->content) && !is_callable(array($this->content, '__toString'))) {
            throw new LogicException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($this->content)));
        }

        return (string)$this->content;
    }

    /**
     * @param mixed $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return void
     */
    public function send()
    {
        $this->sendHeaders();
        $this->emptyBuffers();

        print $this->getContentAsString();
    }

    protected function sendHeaders()
    {
        $protocol = 'HTTP/1.1';
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') {
            $protocol = 'HTTP/1.0';
        }

        if (isset(self::$statusTexts[$this->statusCode])) {
            header(sprintf('%s %s %s', $protocol, $this->statusCode, self::$statusTexts[$this->statusCode]), true, $this->statusCode);
        }

        foreach ($this->headers as $headerName => $headerValue) {
            header(sprintf('%s: %s', $headerName, $headerValue), false, $this->statusCode);
        }
    }

    protected function emptyBuffers()
    {
        // Some modules may leave open buffers.
        $bufferCount = count(ob_list_handlers());
        while ($bufferCount) {
            ob_end_clean();
            --$bufferCount;
        }
    }
}

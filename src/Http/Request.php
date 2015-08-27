<?php

class Oxygen_Http_Request
{
    /**
     * The GET parameters.
     *
     * @var array
     */
    private $query;

    /**
     * The POST parameters.
     *
     * @var array
     */
    private $request;

    /**
     * The request attributes.
     *
     * @var array
     */
    private $attributes;

    /**
     * The COOKIE parameters.
     *
     * @var array
     */
    private $cookies;

    /**
     * The FILES parameters.
     *
     * @var array
     */
    private $files;

    /**
     * The SERVER parameters.
     *
     * @var array
     */
    private $server;

    /**
     * The raw request body data.
     *
     * @var string
     */
    private $content;

    /**
     * @var bool
     */
    private $authenticated;

    /**
     * @param array       $query      The GET parameters.
     * @param array       $request    The POST parameters.
     * @param array       $attributes The request attributes.
     * @param array       $cookies    The COOKIE parameters.
     * @param array       $files      The FILES parameters.
     * @param array       $server     The SERVER parameters.
     * @param null|string $content    The raw request body data. If null, it will be lazy-loaded.
     */
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->query      = $query;
        $this->request    = $request;
        $this->attributes = $attributes;
        $this->cookies    = $cookies;
        $this->files      = $files;
        $this->server     = $server;
        $this->content    = $content;
    }

    /**
     * @return Oxygen_Http_Request
     */
    public static function createFromGlobals()
    {
        return new self($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
    }

    /**
     * @param bool $asResource If true, a resource will be returned
     *
     * @return resource|string The request body content or a resource to read the body stream.
     * @throws RuntimeException If attempting to call the method again after getting it as a resource previously.
     */
    public function getContent($asResource = false)
    {
        if (false === $this->content || (true === $asResource && null !== $this->content)) {
            throw new RuntimeException('getContent() can only be called once when using the resource return type.');
        }

        if (true === $asResource) {
            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * @param string $type Content type.
     *
     * @return bool Whether or not the "Content-Type" header is of the provided content type.
     */
    public function isContentType($type)
    {
        if (!isset($this->server['CONTENT_TYPE'])) {
            return false;
        }

        return preg_match('{(\s|,|;|^)'.preg_quote($type).'(\s|,|;|$)}', $this->server['CONTENT_TYPE']);
    }

    /**
     * @param string $type Content type.
     *
     * @return bool Whether or not the "Accepts" header explicitly accepts the provided content type.
     */
    public function accepts($type)
    {
        if (!isset($this->server['HTTP_ACCEPT'])) {
            return false;
        }

        return preg_match('{(\s|,|;|^)'.preg_quote($type).'(\s|,|;|$)}', $this->server['HTTP_ACCEPT']);
    }

    /**
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * @param bool $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }
}

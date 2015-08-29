<?php

class Oxygen_Http_MultipartResponse extends Oxygen_Http_Response
{
    /**
     * @var string Multipart boundary
     */
    private $boundary = '';

    public function __construct($parts, $boundary = null, $statusCode = 200, array $headers = array())
    {
        if ($boundary === null) {
            $boundary = uniqid();
        }

        $this->boundary          = $boundary;
        $headers["content-type"] = "multipart/mixed; boundary=".$this->boundary;

        if (!isset($headers["content-transfer-encoding"])) {
            $headers["content-transfer-encoding"] = "binary";
        }

        parent::__construct($parts, $statusCode, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentAsString()
    {
        /** @var Oxygen_Http_MultipartResponsePart[] $parts */
        $parts = $this->content;

        // Multipart header.
        $output = "\r\n".$this->getMultipartBoundary()."\r\n";

        foreach ($parts as $part) {
            $output .= $this->createPartResponse($part).$this->getMultipartBoundary();
        }

        // End multipart boundary.
        $output .= '--';

        return $output;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->emptyBuffers();

        $stream = $this->createResponseStream();

        while (!$stream->eof()) {
            print $stream->read(1048576);
        }
    }

    /**
     * @return Oxygen_Stream_Interface
     */
    private function createResponseStream()
    {
        /** @var Oxygen_Http_MultipartResponsePart[] $parts */
        $parts  = $this->content;
        $stream = new Oxygen_Stream_Append();

        $stream->addStream(Oxygen_Stream_Stream::factory("\r\n".$this->getMultipartBoundary()));

        foreach ($parts as $part) {
            $stream->addStream(Oxygen_Stream_Stream::factory("\r\n"));
            $stream->addStream($this->createPartStream($part));
            $stream->addStream(Oxygen_Stream_Stream::factory("\r\n".$this->getMultipartBoundary()));
        }

        $stream->addStream(Oxygen_Stream_Stream::factory("--"));

        return $stream;
    }

    /**
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    private function createPartResponse(Oxygen_Http_MultipartResponsePart $part)
    {
        $response = '';

        foreach ($part->getHeaders() as $header => $value) {
            if (strcasecmp("content-location", $header) === 0) {
                // Content-Location can contain special characters (like \r\n for example)
                $value = urlencode($value);
            }
            $response .= sprintf("%s: %s\r\n", strtolower($header), $value);
        }

        $response .= sprintf("\r\n%s\r\n", (string)$part->getBody());

        return $response;
    }

    private function createPartStream(Oxygen_Http_MultipartResponsePart $part)
    {
        $stream = new Oxygen_Stream_Append();

        foreach ($part->getHeaders() as $header => $value) {
            if (strcasecmp("content-location", $header) === 0) {
                // Content-Location can contain special characters (like \r\n for example)
                $value = urlencode($value);
            }
            $stream->addStream(Oxygen_Stream_Stream::factory(sprintf("%s: %s\r\n", strtolower($header), $value)));
        }

        $body = $part->getBody();

        // Manually output content-transfer-encoding header
        $stream->addStream(Oxygen_Stream_Stream::factory(sprintf("content-transfer-encoding: %s\r\n", $part->getEncoding())));

        switch ($part->getEncoding()) {
            case 'binary':
                // No action required
                break;
            case 'base64':
                $body = new Oxygen_Stream_Base64EncodedStream($body);
                break;
            default:
                throw new Oxygen_Exception(Oxygen_Exception::MULTIPART_ENCODING_NOT_SUPPORTED, array(
                    'encoding' => $part->getEncoding(),
                ));
        }

        $stream->addStream(Oxygen_Stream_Stream::factory("\r\n"));
        $stream->addStream($body);

        return $stream;
    }

    /**
     * @return string
     */
    private function getMultipartBoundary()
    {
        return sprintf("--%s", $this->boundary);
    }
}

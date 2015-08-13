<?php

/**
 * Lazily open file stream for reading and close it when EOF is reached.
 */
class Oxygen_Stream_LazyFile implements Oxygen_Stream_Interface
{

    /**
     * @var Oxygen_Stream_Interface
     */
    private $stream = null;

    /**
     * @var mixed
     */
    private $realPath;

    /**
     * @var bool
     */
    private $initialized = false;

    public function __construct($realPath)
    {
        $this->realPath = $realPath;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->stream !== null) {
            $this->stream->close();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        if (!$this->initialized) {
            return 0;
        }

        return $this->stream !== null ? $this->stream->tell() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        $this->initialize();

        return $this->stream !== null ? $this->stream->isSeekable() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->initialize();

        return $this->stream !== null ? $this->stream->seek($offset, $whence) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        $this->initialize();

        return $this->stream !== null ? $this->stream->eof() : true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $this->initialize();

        if ($this->stream === null) {
            return null;
        }

        $data = $this->stream->read($length);

        if ($this->eof()) {
            $this->close();
        }

        return $data;
    }

    public function __toString()
    {
        $buffer = '';

        while (!$this->eof()) {
            $buffer .= $this->read(1048576);
        }

        return $buffer;
    }

    private function initialize()
    {
        if ($this->initialized === false) {
            if (file_exists($this->realPath)) {
                $handle = @fopen($this->realPath, "rb");
                if ($handle !== false) {
                    $this->stream = Oxygen_Stream_Stream::factory($handle);
                }
            }
            $this->initialized = true;
        }
    }
}

<?php

abstract class Oxygen_Stream_Decorator implements Oxygen_Stream_Interface
{

    private $initialized = false;

    /**
     * @var Oxygen_Stream_Interface
     */
    private $stream;

    public function __construct(Oxygen_Stream_Interface $stream = null)
    {
        $this->stream = $stream;

        if ($this->stream) {
            $this->initialized = true;
        }
    }

    protected function getStream()
    {
        if (!$this->stream) {
            $this->stream = $this->createStream();
        }

        return $this->stream;
    }

    protected function createStream()
    {
        return null;
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close()
    {
        $this->getStream()->close();
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int|bool Returns the position of the file pointer or false on error
     */
    public function tell()
    {
        return $this->getStream()->tell();
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        return $this->getStream()->isSeekable();
    }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return bool
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->getStream()->seek($offset, $whence);
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return $this->getStream()->eof();
    }

    /**
     * Read data from the stream
     *
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if
     *                    underlying stream call returns fewer bytes.
     *
     * @return string     Returns the data read from the stream.
     */
    public function read($length)
    {
        return $this->getStream()->read($length);
    }
}

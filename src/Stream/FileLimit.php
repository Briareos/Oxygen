<?php

/**
 * Unlike Oxygen_Stream_Limit, this stream reader is specialized for limited reading of files because
 * it is not seekable and automatically closes the underlying stream after it has been read to the end (or limit is exceeded).
 */
class Oxygen_Stream_FileLimit extends Oxygen_Stream_Limit
{

    /**
     * If EOF is ever reached, it gets remembered so file handle reinitializing is prevented
     * since eof will always return true from then on.
     *
     * @var bool|null
     */
    private $eof = null;

    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        if ($this->eof === true) {
            return true;
        }

        $this->eof = parent::eof();

        return $this->eof;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $buffer = parent::read($length);
        if ($this->eof()) {
            $this->close();
        }

        return $buffer;
    }
}

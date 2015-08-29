<?php

class Oxygen_Archive_Zip implements Oxygen_Archive_Interface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public static function openLocalFile($file)
    {
        if (!file_exists($file)) {
            throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_FILE_NOT_FOUND);
        }
        if (!extension_loaded('zip')) {
            throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_ZIP_EXTENSION_NOT_LOADED);
        }

        return new self($file);
    }

    /**
     * {@inheritdoc}
     */
    public function extract($path)
    {
        $zip = new ZipArchive();

        $zip->open($this->file);
        $this->checkStatus($zip);

        $zip->extractTo($path);
        $this->checkStatus($zip);

        $zip->close();
        $this->checkStatus($zip);
    }

    /**
     * Throws an exception if the ZipArchive status is not OK.
     *
     * @param ZipArchive $zip
     *
     * @throws Oxygen_Exception
     */
    private function checkStatus(ZipArchive $zip)
    {
        if ($zip->status !== ZipArchive::ER_OK) {
            throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_ZIP_EXTENSION_ERROR, array(
                'code'    => $zip->status,
                'message' => $zip->getStatusString(),
            ));
        }
    }
}

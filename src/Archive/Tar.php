<?php

/**
 * @link http://www.gnu.org/software/tar/manual/html_node/Standard.html
 */
class Oxygen_Archive_Tar implements Oxygen_Archive_Interface
{
    const TYPE_FLAG_FILE = '0';

    const TYPE_FLAG_LONG_FILE_NAME = 'L';

    const TYPE_FLAG_DIR = '5';

    const TYPE_FLAG_SYMLINK = '2';

    /**
     * @var string
     */
    private $file;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var Oxygen_Archive_Compressor_Interface
     */
    private $compressor;

    public function __construct($file, Oxygen_Archive_Compressor_Interface $compressor)
    {
        $this->file       = $file;
        $this->compressor = $compressor;
    }

    /**
     * {@inheritdoc}
     */
    public static function openLocalFile($file)
    {
        if (!file_exists($file)) {
            throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_FILE_NOT_FOUND);
        }
        if (file_get_contents($file, null, null, 0, 2) === "\37\213") {
            if (!extension_loaded('zlib')) {
                throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_ZLIB_EXTENSION_NOT_LOADED);
            }
            return new self($file, new Oxygen_Archive_Compressor_Gz);
        }

        return new self($file, new Oxygen_Archive_Compressor_None());
    }

    /**
     * {@inheritdoc}
     */
    public function extract($path)
    {
        $this->resource = $this->compressor->open($this->file);
        try {
            $this->doExtract($path);
        } catch (Exception $e) {
            throw $e;
        }
        $this->compressor->close($this->resource);
        $this->resource = null;
    }

    /**
     * @return string
     */
    private function readBlock()
    {
        return $this->compressor->readBlock($this->resource);
    }

    private function doExtract($path)
    {
        if (!is_dir($path)) {
            throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_DESTINATION_DOES_NOT_EXIST);
        }

        $path = rtrim($path, '/');

        clearstatcache();

        while (strlen($binaryData = $this->readBlock()) !== 0) {
            $this->readHeader($binaryData, $header);

            if ($header['filename'] === '') {
                continue;
            }

            // Extract long file name.
            if ($header['typeflag'] == self::TYPE_FLAG_LONG_FILE_NAME) {
                $this->readLongHeader($header);
            }

            if (substr($header['filename'], 0, 2) === './') {
                $header['filename'] = substr($header['filename'], 2);
            }
            $fileName = $path.'/'.$header['filename'];
            $typeFlag = $header['typeflag'];
            $size     = $header['size'];
            $mtime    = $header['mtime'];
            $mode     = $header['mode'];

            if (file_exists($fileName)) {
                if (is_dir($fileName) && $typeFlag === self::TYPE_FLAG_FILE) {
                    throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_FILE_EXISTS_AS_DIRECTORY, array(
                        'file' => $fileName,
                    ));
                }
                if (is_file($fileName) && ($typeFlag === self::TYPE_FLAG_DIR)) {
                    throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_DIRECTORY_EXISTS_AS_FILE, array(
                        'file' => $fileName,
                    ));
                }
                if (!is_writable($fileName)) {
                    throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_FILE_IS_WRITE_PROTECTED, array(
                        'file' => $fileName,
                    ));
                }
            } else {
                // File/directory does not exist, create it if necessary.
                $dirName = ($typeFlag === self::TYPE_FLAG_DIR) ? $fileName : dirname($fileName);
                if (!is_dir($dirName) && !@mkdir($dirName, 0777, true)) {
                    throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_DIRECTORY_CAN_NOT_BE_CREATED, array(
                        'directory' => $dirName,
                    ));
                }
            }

            // Symlinks are not handled by this implementation.
            if ($typeFlag === self::TYPE_FLAG_DIR || $typeFlag === self::TYPE_FLAG_SYMLINK) {
                continue;
            }

            // Extract file.
            $fd = @fopen($fileName, 'wb');
            if (!$fd) {
                throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_UNABLE_TO_OPEN_FILE_FOR_WRITING, array(
                    'file' => $fileName,
                ));
            }

            $n = floor($size / 512);
            for ($i = 0; $i < $n; $i++) {
                $content = $this->readBlock();
                fwrite($fd, $content, 512);
            }
            if (($size % 512) !== 0) {
                $content = $this->readBlock();
                fwrite($fd, $content, ($size % 512));
            }

            @fclose($fd);

            @touch($fileName, $mtime);
            if ($mode & 0111) {
                // Make file executable, obey umask.
                $mode = fileperms($fileName) | (~umask() & 0111);
                @chmod($fileName, $mode);
            }

            // Check the file size.
            clearstatcache();

            $realSize = filesize($fileName);
            if ($realSize !== $size) {
                throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_FILE_SIZE_MISMATCH, array(
                    'file'        => $fileName,
                    'realSize'    => $realSize,
                    'archiveSize' => $size,
                ));
            }
        }
    }

    /**
     * @param string $binaryData
     *
     * @param string $header
     *
     * @return array
     * @throws Oxygen_Exception
     */
    private function readHeader($binaryData, &$header)
    {
        if (strlen($binaryData) === 0) {
            $header['filename'] = '';
            return;
        }

        if (strlen($binaryData) !== 512) {
            throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_INVALID_BLOCK_SIZE);
        }

        $checksum = 0;

        // First part of checksum sum(ord()) of every byte for filename/mode/uid/gid/size/mtime.
        for ($i = 0; $i < 148; $i++) {
            $checksum += ord($binaryData[$i]);
        }
        // Next 8 bytes is the checksum; replace it with spaces.
        $checksum += 32 * 8;
        for ($i = 156; $i < 512; $i++) {
            $checksum += ord($binaryData[$i]);
        }

        $header = unpack('a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor', $binaryData);

        $header['checksum'] = octdec(trim($header['checksum']));

        if ($header['checksum'] !== $checksum) {
            if ($checksum === 256 && $header['checksum'] === 0) {
                $header['filename'] = '';
                // Last block is empty, has a calculated checksum of 256, and the checksum in its header is 0.
                return;
            }
            throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_CHECKSUM_NOT_VALID);
        }

        $this->rejectDirectoryTraversal($header['filename']);

        $header['filename'] = trim($header['filename']);
        $header['link']     = trim($header['link']);
        $header['mode']     = octdec(trim($header['mode']));
        $header['uid']      = octdec(trim($header['uid']));
        $header['gid']      = octdec(trim($header['gid']));
        $header['size']     = octdec(trim($header['size']));
        $header['mtime']    = octdec(trim($header['mtime']));
        if ($header['typeflag'] === self::TYPE_FLAG_DIR) {
            $header['size'] = 0;
        }
    }

    /**
     * @param $fileName
     *
     * @throws Oxygen_Exception If the file name contains relative paths.
     */
    private function rejectDirectoryTraversal($fileName)
    {
        if ((strpos($fileName, '/../') !== false)
            || (strpos($fileName, '../') === 0)
        ) {
            throw new Oxygen_Exception(Oxygen_Exception::ARCHIVE_TAR_FILE_NAME_CONTAINS_DIRECTORY_TRAVERSAL);
        }
    }

    /**
     * @param array $header
     *
     * @throws Oxygen_Exception
     */
    function readLongHeader(&$header)
    {
        $fileName   = '';
        $blockCount = floor($header['size'] / 512);
        for ($i = 0; $i < $blockCount; $i++) {
            $fileName .= $this->readBlock();
        }
        if (($header['size'] % 512) !== 0) {
            $fileName .= $this->readBlock();
        }

        // Read the next header.
        $binaryData = $this->readBlock();
        $this->readHeader($binaryData, $header);
        $fileName = trim($fileName);
        $this->rejectDirectoryTraversal($fileName);
        $header['filename'] = $fileName;
    }
}

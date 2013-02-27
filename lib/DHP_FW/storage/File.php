<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\storage;
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-02-04 16:33:55
 *
 */

const OPEN_READONLY  = 'rb';
const OPEN_OVERWRITE = 'w+b';
const OPEN_AMEND     = 'a+b';
class File {
    protected $_fileHandle    = NULL;
    protected $path           = NULL;
    protected $_readable      = NULL;
    protected $_writable      = NULL;
    protected $exists         = NULL;
    protected $isTemp         = FALSE;
    protected $chunkSize      = 10;
    protected $openForWriting = FALSE;
    protected $writePosition  = 0;
    protected $readPosition   = 0;

    /**
     * Sets up the object, if file is provided, checks that.
     *
     * @param null                   $fileToOpen
     * @param \DHP_FW\EventInterface $Event
     */
    function __construct($fileToOpen = NULL, \DHP_FW\EventInterface $Event = NULL) {
        if ( isset( $fileToOpen ) ) {
            $this->exists = file_exists($fileToOpen);
            if ( !file_exists($fileToOpen) ) {
                $this->path = $fileToOpen;
            } else {
                $path = realpath($fileToOpen);
                 # checks to make sure file really is a file
                if (is_file($path) && is_readable($path) && stat($path) !== FALSE) {
                    $this->path      = $path;
                    $this->_readable = is_readable($this->path);
                    $this->_writable = is_writable($this->path);
                }
            }
        }
        if ( isset( $Event ) ) {
            $that = & $this;
            $Event->register('system.end', function () use ($that) {
                $that->close();
            });
        }
    }

    /**
     * Will close open files, upon destruction
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Reads part of the file and returns that data
     *
     * @param $start
     * @param $length
     *
     * @return null|string
     */
    public function readPart($start, $length) {
        $this->openFile(OPEN_READONLY);
        # lets position the pointer at the start
        fseek($this->_fileHandle, $start);
        $return = NULL;
        $end    = $start + $length;
        $stat   = fstat($this->_fileHandle);
        if ( $end > $stat['size'] ) {
            $end = $stat['size'];
        }
        $pos = ftell($this->_fileHandle);
        while ($pos < $end) {
            $len     = ( $pos + $this->chunkSize ) > $end ?
              ( $end - $pos ) : $this->chunkSize;
            $return .= fread($this->_fileHandle, $len);
            $pos     = ftell($this->_fileHandle);
        }
        fseek($this->_fileHandle, $this->readPosition);
        return $return;
    }

    /**
     * Will truncate a file, erasing all the content, set the pointer to 0
     * @throws \RuntimeException
     */
    public function truncate() {
        $this->openFile(OPEN_OVERWRITE);
        if (ftruncate($this->_fileHandle, 0)) {
            rewind($this->_fileHandle);
            $this->readPosition = $this->writePosition = ftell($this->_fileHandle);
        }else{
            throw new \RuntimeException('Unable to truncate file');
        }
    }

    /**
     * Adds data to the file. Amend will *always* add data to the end of file, not
     * the current cursor positon.
     * @param $data
     */
    public function amend($data) {
        $this->openFile(OPEN_AMEND);
        if ( isset( $this->readPosition ) ) {
            # so lets seek to the new position
            fseek($this->_fileHandle, $this->writePosition);
        }
        fwrite($this->_fileHandle, $data);
        $this->writePosition = ftell($this->_fileHandle);
    }

    /**
     * Reads the file and returns
     *
     * @param null $len
     *
     * @return null|string
     */
    # todo : perhaps file_get_contents or equivalen is better...?
    # todo : fix empty catch statement
    public function read($len = NULL) {
        $this->openFile(OPEN_READONLY);
        $len    = $len == NULL ? $this->chunkSize : $len;
        $return = NULL;
        fseek($this->_fileHandle, $this->readPosition);
        try {
            while ($len > 0) {
                $return            .= fread($this->_fileHandle, $this->chunkSize);
                $len               -= $this->chunkSize;
                $this->readPosition = ftell($this->_fileHandle);
            }
        } catch (\Exception $e) {}
        return $return;
    }

    /**
     * Closes the file
     * @return bool|null
     */
    public function close() {
        return $this->closeFile();
    }

    /**
     * Rewinds the file and puts the current file pointer at position 0
     */
    public function rewind() {
        $this->openFile(OPEN_READONLY);
        rewind($this->_fileHandle);
        $this->readPosition = $this->writePosition = ftell($this->_fileHandle);
    }

    /**
     * Will delete, unlink , the file
     */
    public function delete() {
        $this->close();
        if ( isset( $this->path ) ) {
            unlink($this->path);
        }
    }

    /**
     * Returns the path of the file
     * @return null|string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * true/false if the file is a temp file... or not
     * @return bool
     */
    public function isTempFile() {
        return $this->isTemp;
    }

    /**
     * This will open a file. It will reopen a file if it's already open but without
     * enough permissions, so if we have opened the file for reading, we can reopen
     * it for writing if that is what we want.
     *
     * @param $fileAccessType
     *
     * @throws \RuntimeException
     */
    protected function openFile($fileAccessType) {
        /* actually lets reopen the file, if, we want to write to it and it is only
         * in read mode
         */
        if ( $fileAccessType != OPEN_READONLY && $this->openForWriting == FALSE ) {
            $this->close();
        }
        if ( empty( $this->_fileHandle ) ) {
            $this->isTemp = FALSE;
            if ( !isset( $this->path ) ) {
                $this->path = tempnam(sys_get_temp_dir(), 'DHP_FW_');
                #$this->_fileHandle = tmpfile();
                #$this->_readable   = TRUE;
                #$this->_writable   = TRUE;
                $this->isTemp = TRUE;
            }
            if (!file_exists($this->path)) { # could we create the file...?
                touch($this->path);
            }
            $this->_readable = is_readable($this->path);
            $this->_writable = is_writable($this->path);
            /* check if fileAccessType will work considering if we want to
             * read/write to the file and if the file is read/writable
             */
            if ($fileAccessType == OPEN_READONLY && !$this->_readable){
                throw new \RuntimeException("File is not readable");
            }
            if( $fileAccessType == OPEN_AMEND && !$this->_writable){
                throw new \RuntimeException("File is not writable");
            }
            if( $fileAccessType == OPEN_OVERWRITE && !$this->_writable){
                throw new \RuntimeException("File is not writable");
            }
            $this->_fileHandle  = fopen($this->path, $fileAccessType);
            $this->readPosition = $this->readPosition = ftell($this->_fileHandle);

            if ($fileAccessType == OPEN_READONLY) {
                $this->openForWriting = FALSE;
            }
            else {
                $this->openForWriting = TRUE;
            }
            if ($fileAccessType == OPEN_READONLY) {
                $lockType = \LOCK_SH;
            }
            else {
                $lockType = \LOCK_EX;
            }
            flock($this->_fileHandle, $lockType);
        }
    }

    /**
     * Closes the file, releases any file locks that may be on the file.
     * @return bool|null
     */
    protected function closeFile() {
        if ( isset( $this->_fileHandle ) ) {
            flock($this->_fileHandle, \LOCK_UN);
            fclose($this->_fileHandle);
            $this->_fileHandle = NULL;
            return TRUE;
        }
        return NULL;
    }
}

<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;
use DHP_FW\Event;
use DHP_FW\cache\CacheBucketProxyInterface;
use DHP_FW\Request;


/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:35
 */

class Response implements ResponseInterface {

    const DATASENDSTATUS_NOT_STARTED = 0;
    const DATASENDSTATUS_STARTED     = 1;
    const DATASENDSTATUS_COMPLETE    = 2;

    private $headersSent    = FALSE;
    private $dataSendStatus = self::DATASENDSTATUS_NOT_STARTED;
    private $data           = NULL;
    private $headers        = array();
    private $headerStatus   = array(100 => 'Continue',
                                    101 => 'Switching Protocols',
                                    200 => 'OK', 201 => 'Created',
                                    202 => 'Accepted',
                                    203 => 'Non-Authoritative Information',
                                    204 => 'No Content',
                                    205 => 'Reset Content',
                                    206 => 'Partial Content',
                                    300 => 'Multiple Choices',
                                    301 => 'Moved Permanently',
                                    302 => 'Found', 303 => 'See Other',
                                    304 => 'Not Modified',
                                    305 => 'Use Proxy',
                                    307 => 'Temporary Redirect',
                                    400 => 'Bad Request',
                                    401 => 'Unauthorized',
                                    402 => 'Payment Required',
                                    403 => 'Forbidden',
                                    404 => 'Not Found',
                                    405 => 'Method Not Allowed',
                                    406 => 'Not Acceptable',
                                    407 => 'Proxy Authentication Required',
                                    408 => 'Request Timeout',
                                    409 => 'Conflict', 410 => 'Gone',
                                    411 => 'Length Required',
                                    412 => 'Precondition Failed',
                                    413 => 'Request Entity Too Large',
                                    414 => 'Request-URI Too Long',
                                    415 => 'Unsupported Media Type',
                                    416 => 'Requested Range Not Satisfiable',
                                    417 => 'Expectation Failed',
                                    418 => 'I\'m a teapot',
                                    500 => 'Internal Server Error',
                                    501 => 'Not Implemented',
                                    502 => 'Bad Gateway',
                                    503 => 'Service Unavailable',
                                    504 => 'Gateway Timeout',
                                    505 => 'HTTP Version Not Supported',);

    private $headerDataSent = array();
    private $supressHeader  = FALSE;
    private $dataIsCache    = FALSE;


    private $request, $cacheObject;

    /**
     * @param EventInterface $event
     */
    # public function __construct(EventInterface $event = NULL) {
    public function __construct(EventInterface $event = NULL, Request $Request = NULL, CacheBucketProxyInterface $cache = NULL) {
        $this->event       = $event;
        $this->request     = $Request;
        $this->cacheObject = $cache;
    }

    # todo : this is most likely not necessary - refactor away please!
    /**
     * @return boolean if cacheObject already have been sent or not
     */
    public function cacheSent() {
        return $this->dataIsCache;
    }

    /**
     * Declares weather cache is available or not
     *
     * @return bool
     */
    public function cacheAvailable() {
        if (!isset($this->request) || !isset($this->cacheObject)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * This will send headers and echo whatever is in the $data-variable
     *
     * @param int  $dataOrStatus the status, an int, http status
     * @param null $data         Data to be sent
     *
     * @return null
     */
    public function send($dataOrStatus, $data = NULL) {
        if ( $data !== NULL ) {
            $this->status($dataOrStatus);
        } else {
            $this->status(200);
            $data = $dataOrStatus;
        }
        $this->event->trigger('DHP_FW.Response.send', $dataOrStatus, $data);
        switch (gettype($data)) {
            case 'object':
            case 'array':
                $this->data = json_encode($data,
                                          \JSON_FORCE_OBJECT | \JSON_NUMERIC_CHECK);
                break;
            case 'string':
            case 'double':
            case 'float':
            case 'interger':
                $this->data = $data;
                break;
            case 'resource':
            default:
                $this->data = '';
                break;
        }
        # lets get the size of the data
        try{
            ob_start();
            $this->sendData();
            $this->header('Content-Length',ob_get_length());
            $data = ob_get_clean();
        }catch(\Exception $e){
            // being unable to figure out the length of the data, we do... what? Nothing?
        }

        $this->sendHeaders();
        echo $data;
        if ( $this->dataIsCache == FALSE ) {
            if(isset($this->cacheObject) && isset($this->request)){
                $cacheData = array(
                    'headers' => $this->headerDataSent,
                    'data'    => $data
                );
                $this->cacheObject->set('uri_'.$this->request->getUri().'_data',$cacheData,600);
            }
        }
    }

    /**
     * This sets a header. Since not all headers have a :, you do not need to
     * include the second parameter, '$value'.
     *
     * @param string $name  Name of the header, whatever is before :
     * @param null   $value the value of the header, whatever is after :
     * @param bool   $processHeaderName
     *
     * @return $this
     */
    public function header($name, $value = NULL, $processHeaderName = TRUE) {
        if ( $processHeaderName ) {
            $name = str_replace(' ', '-', ucwords(str_replace(array('_','-'), ' ',
                                                              strtolower($name))));
        }
        $this->event->trigger('DHP_FW.Response.header', $name, $value);
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * This sets the status of the response. This method will
     * generate a statu-header.
     *
     * @param int  $httpNumber  the status number, an int
     * @param null $httpMessage the message accompaning the http-status number
     *
     * @return null
     */
    public function status($httpNumber, $httpMessage = NULL) {
        if ( !isset( $httpMessage ) ) {
            $httpMessage = isset( $this->headerStatus[$httpNumber] ) ?
              $this->headerStatus[$httpNumber] : $httpMessage;
        }
        $statusHeader = trim(sprintf('%d %s', $httpNumber, $httpMessage));
        $this->header('Status', $statusHeader);
    }

    /**
     * This will send a file, together with headers for mimetype, filename.
     * Setting downloadfile to true should force the browser to download the file.
     *
     * If no mimetype is specified, finfo will be used to try to figure out mimetype
     *
     * @param String $filePath     Path to the file that should be sent
     * @param null   $mimeType     string, mimetype
     * @param null   $fileName     optional filename
     * @param bool   $downLoadFile If the file should be downloaded or not
     *
     * @return null
     * @throws \Exception
     */
    public function sendFile($filePath, $mimeType = NULL, $fileName = NULL,
        $downLoadFile = FALSE) {
        $this->event->trigger('DHP_FW.Response.sendFile', $filePath, $mimeType,
                              $fileName, $downLoadFile);
        $realPath = realpath($filePath);
        if ( FALSE === $realPath || FALSE === file_exists(realpath($realPath)) ) {
            throw new \Exception( "File does not exist" );
        }
        if ( FALSE === is_readable($realPath) ) {
            throw new \Exception( "Unable to read file" );
        }
        if ( !isset( $mimeType ) ) {
            $fileHandle = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType   = finfo_file($fileHandle, $realPath);
            finfo_close($fileHandle);
        }
        if ( !isset( $fileName ) ) {
            $fileName = basename($realPath);
        }
        $this->sendFileData($realPath, $fileName, $mimeType, $downLoadFile);
    }

    /**
     * A wrapper function for sendFile, with $downloadFile set to true
     *
     * @param String $filePath Path to the file that should be sent
     * @param null   $mimeType string, mimetype
     * @param null   $fileName optional filename
     *
     * @return null
     * @throws \Exception
     */
    public function downloadFile($filePath, $mimeType = NULL, $fileName = NULL) {
        $this->sendFile($filePath, $mimeType, $fileName, TRUE);
    }

    /**
     * Will send redirect headers.
     *
     * @param string $url         string, the url to redirect to, complete URL
     * @param int    $httpStatus  , together with status int
     * @param null   $httpMessage and optional http-status message
     *
     * @return null
     */
    public function redirect($url, $httpStatus = 301, $httpMessage = NULL) {
        $this->event->trigger('DHP_FW.Response.redirect', $url, $httpStatus,
                              $httpMessage);
        $this->resetHeaders();
        $this->status($httpStatus, $httpMessage);
        $this->header('Location', $url);
        $this->sendHeaders();
    }

    /**
     * If we should skip sending the headers. IF set to true, no headers will
     * be sent.
     *
     * @param bool $doSurpress if headers should be surpressed or not
     *
     * @return null
     */
    public function supressHeaders($doSurpress = TRUE) {
        $this->supressHeader = $doSurpress === TRUE ? TRUE : FALSE;
    }

    /**
     * This method will check if a cacheObject of the response exists in cacheObject
     * and will send that immediately.
     *
     * @return boolean true if cacheObject was used, false if not
     */
    public function checkCache() {
        if( !isset($this->request) || !isset($this->cacheObject) ){
            return TRUE;
        }else{  # todo : update to use di injected cache and request instead
            if ( $this->request->getMethod() == 'GET' ) {
                $uri       = $this->request->getUri();
                $__cache__ = $this->cacheObject->get("uri_{$uri}_data");
                if ( isset( $__cache__ ) && is_array($__cache__) ) {
                    $this->dataIsCache = TRUE;
                    foreach ($__cache__['headers'] as $header) {
                        $this->sendHeaderData($header);
                    }
                    $this->sendHeaderData('DHP_FW_CACHE: YES!');
                    $this->data = $__cache__['data'];
                    $this->sendData();
                    return TRUE;
                }
            }
        }
    }

    /**
     * Sends the headers
     */
    private function sendHeaders() {
        if ( FALSE == $this->headersSent && \headers_sent() == FALSE ) {
            foreach ($this->headers as $header => $value) {
                switch ($header) {
                    case 'Status':
                        $this->sendHeaderData("HTTP/1.1 {$value}");
                        break;
                }
                if ( !isset( $value ) ) {
                    $completeHeader = $header;
                } else {
                    $completeHeader = sprintf('%s: %s', $header, $value);
                }
                $this->sendHeaderData($completeHeader);
            }
        }
        $this->headersSent = TRUE;
    }

    /**
     * Used to send the file to the client
     * @param string     $filePath
     * @param string     $fileName
     * @param string     $mimeType
     * @param bool $downloadHeaders
     */
    private function sendFileData($filePath, $fileName, $mimeType,
        $downloadHeaders = FALSE) {
        $this->resetHeaders()->header('Content-Type', $mimeType)
          ->header('Content-Transfer-Encoding', 'binary')->status(200);
        if ( $downloadHeaders == TRUE ) {
            $this->header('Content-Description', 'File Transfer')
             ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        }
        $this->sendHeaders();
        $this->dataSendStatus = self::DATASENDSTATUS_STARTED;
        readfile($filePath);
        $this->dataSendStatus = self::DATASENDSTATUS_COMPLETE;

    }

    /**
     * Sends data
     */
    private function sendData() {
        if ( $this->dataIsCache == FALSE ) {
            $this->event->trigger('DHP_FW.Response.sendData', $this->data);
            $this->event->trigger('DHP_FW.Response.afterSendData', $this->data);
        }
        $this->dataSendStatus = self::DATASENDSTATUS_STARTED;
        echo $this->data;
        $this->dataSendStatus = self::DATASENDSTATUS_COMPLETE;
    }

    /**
     * Resets the headers.
     * @return Response
     */
    private function resetHeaders() {
        $this->headers = array();
        return $this;
    }

    /**
     * We need wrapper since sending the header will generate errors in
     * say CLI
     *
     * @param $headerData : String
     */
    private function sendHeaderData($headerData) {
        if ( FALSE === $this->supressHeader ) {
            \header($headerData);
        }
        if ( $this->dataIsCache == FALSE ) {
            $this->headerDataSent[] = $headerData;
        }
    }
}

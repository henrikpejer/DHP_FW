<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:35
 */

class Response {

    const DATASENDSTATUS_NOT_STARTED = 0;
    const DATASENDSTATUS_STARTED     = 1;
    const DATASENDSTATUS_COMPLETE    = 2;

    private $headersSent = FALSE;
    private $dataSendStatus = self::DATASENDSTATUS_NOT_STARTED;
    private $data = NULL;
    private $headers = array();
    private $headerStatus = array(
        100 => 'Continue', 101 => 'Switching Protocols', 200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 307 => 'Temporary Redirect', 400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Timeout', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Long', 415 => 'Unsupported Media Type', 416 => 'Requested Range Not Satisfiable', 417 => 'Expectation Failed', 418 => 'I\'m a teapot', 500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported',
    );
    
    private $headerDataSent = array();
    private $doNotSendHeaders = FALSE;
    public function send($data){
        switch (gettype($data)) {
            case 'object':
            case 'array':
                $this->data = json_encode($data, \JSON_FORCE_OBJECT | \JSON_NUMERIC_CHECK);
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
        $this->sendHeaders();
        $this->sendData();
    }

    public function header($name, $value = NULL, $processHeaderName = TRUE){
        if ( $processHeaderName ) {
            $name = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower($name))));
        }
        $this->headers[$name] = $value;
        return $this;
    }

    public function status($httpNumber, $httpMessage = NULL){
        if ( !isset( $httpMessage ) ) {
            $httpMessage = isset( $this->headerStatus[$httpNumber] ) ? $this->headerStatus[$httpNumber] : $httpMessage;
        }
        $httpHeader   = sprintf('HTTP/1.1 %d %s', $httpNumber, $httpMessage);
        $statusHeader = sprintf('%d %s', $httpNumber, $httpMessage);
        $this
          ->header($httpHeader, NULL, FALSE)
          ->header('Status', $statusHeader);
    }

    public function sendFile($filePath, $mimeType = NULL, $fileName = NULL){
        $realPath = realpath($filePath);
        if ( FALSE === $realPath || FALSE === file_exists(realpath($realPath)) ) {
            throw new \Exception( "File does not exist" );
        }
        if ( FALSE === is_readable($realPath) ) {
            throw new \Exception( "Unable to read file" );
        }
        if ( !isset( $mimeType ) ) {
            $fh = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fh,$realPath);
            finfo_close($fh);
        }
        if ( !isset( $fileName ) ) {
            $fileName = basename($realPath);
        }
        $this
          ->header('Content-Description', 'File Transfer')
          ->header('Content-Type', $mimeType)
          ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"")
          ->header('Content-Transfer-Encoding', 'binary')
            ->status(200);
        $this->sendHeaders();
        $this->dataSendStatus = self::DATASENDSTATUS_STARTED;
        readfile($filePath);
        $this->dataSendStatus = self::DATASENDSTATUS_COMPLETE;
    }

    public function redirect($url, $httpStatus = 301, $httpMessage = NULL){
        $this->resetHeaders();
        $this->status($httpStatus, $httpMessage);
        $this->header('Location', $url);
        $this->sendHeaders();
    }
    
    public function surpressHeaders(){
        $this->doNotSendHeaders = TRUE;
    }

    private function sendHeaders(){
        if ( FALSE == $this->headersSent && \headers_sent() == FALSE ) {
            foreach ($this->headers as $header => $value) {
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

    private function sendData(){
        $this->dataSendStatus = self::DATASENDSTATUS_STARTED;
        echo $this->data;
        $this->dataSendStatus = self::DATASENDSTATUS_COMPLETE;
    }

    private function resetHeaders(){
        $this->headers = array();
    }

    /**
     * We need wrapper since sending the header will generate errors in 
     * say CLI
     * @param $headerData : String
     */
    private function sendHeaderData($headerData){
        if( TRUE === $this->doNotSendHeaders){
            \header($headerData);
        }
        $this->headerDataSent[] = $headerData;
    }
}

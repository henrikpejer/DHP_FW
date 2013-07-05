<?php
declare(encoding = "UTF8");
namespace DHP;
use DHP\Request;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:35
 */

class Response
{
    private $headerStatus = array('statusCode' => '200',
        'headerData' => NULL);
    private $headerStatusCodes = array(
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        409 => 'Conflict',
        500 => 'Internal Server Error'
    );

    private $headers = array();
    private $body = NULL;
    private $request = NULL;
    private $headersSent = FALSE;

    /**
     * Sets up the request
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        ob_start(array($this, 'outputBufferCollector'),1);
    }

    public function __destruct(){
    }

    public function outputBufferCollector($buffer, $phase){
        try{
            $this->appendContent($buffer);
        }catch(\BadMethodCallException $e){

        }
        return true;
    }

    /**
     * The body can contain either a string or object / array. IF it is an object or array
     * it will, later on, get json-serialized and sent as json.
     *
     * @param mixed $bodyData set the data to be sent via the request
     */
    public function setContent($bodyData)
    {
        $this->body = $bodyData;
    }

    /**
     * This will append data to the body of the response.
     *
     * Should only be used with strings
     *
     * @param String $dataToAppend since appending to already set content, we only support String values
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function appendContent($dataToAppend)
    {
        # make sure we append with string values only
        if (!is_string($dataToAppend)) {
            throw new \InvalidArgumentException("When appending, append with string data");
        }
        if (isset($this->body) && !is_string($this->body)) {
            throw new \BadMethodCallException("Cannot append string to content, the content is of type " . gettype($this->body));
        }
        $this->body .= $dataToAppend;
    }

    /**
     * A simply way of setting a status header
     *
     * @param $int
     * @param array|null $headerData extra, header name and data to be used in conjuction with status header, 201-created for instance
     */
    public function setStatus($int, Array $headerData = NULL)
    {
        #$this->headerStatus = array('statusCode' => $int, 'headerData' => $headerData);
        $this->addHeader('status', sprintf('HTTP/1.1 %d %s', $int, $this->headerStatusCodes[$int]), $int);
    }

    /**
     * Adds a header to be sent later on.
     * The header name and value will be reformated with
     *
     * @param String $headerName name of the header
     * @param String $headerValue Optional, value of header, if needed
     * @param null $statusCode
     * @internal param Int $statusValue a int value for status
     */
    public function addHeader($headerName, $headerValue = NULL, $statusCode = NULL)
    {
        $headerKeyName = $headerName;
        switch ($headerName) {
            case 'status':
                $headerName = '';
                break;
        }
        $this->headers[$headerKeyName] = array('value' => trim(sprintf("%s: %s", $this->formatHeaderName($headerName), $headerValue), ' :'), 'statusCode' => $statusCode);
    }

    /**
     * This will send the headers and the data
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();
    }

    /**
     * Here we send the headers of the application
     * @return bool
     * @throws \RuntimeException
     */
    private function sendHeaders()
    {
        if (php_sapi_name() == 'cli' or PHP_SAPI == 'cli') {
            return true;
        }
        if (headers_sent() === TRUE && $this->headersSent === FALSE) {
            throw new \RuntimeException("Headers already sent");
        }
        foreach ($this->headers as $headerType => $headerDataArray) {

            if (isset($headerDataArray['statusCode'])) {
                \header($headerDataArray['value'], true, $headerDataArray['statusCode']);
            } else {
                \header($headerDataArray['value'], true);
            }
        }
        $this->headersSent = TRUE;
        return true;
    }

    /**
     * Format header name and returns it
     * @param $headerName
     * @return mixed
     */
    private function formatHeaderName($headerName)
    {

        return str_replace(' ', '-', ucwords(strtolower(str_replace(array('-', '_'), ' ', $headerName))));
    }

    /**
     * Here we format and send the body
     */
    private function sendBody()
    {
        ob_end_clean();
        if (isset($this->body)) {
            if (is_string($this->body)) {
                echo $this->body;
            } else {
                echo json_encode($this->body, JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT);
            }
        }
    }
}

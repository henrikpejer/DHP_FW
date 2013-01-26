<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:34
 */
class Request implements \DHP_FW\RequestInterface {
    private $event;
    private $uri = NULL;
    private $method = NULL;
    private $headers = NULL;
    private $_body = NULL;
    private $publicValues = NULL;

    /**
     * This will set the basic values of a request.
     *
     * @param String           $method The method of the request (usually GET, POST etc..)
     * @param String           $uri The uri of the request
     * @param null             $body The body of the request
     * @param EventInterface   $event
     */
    public function __construct($method = NULL, $uri = NULL, $body = NULL, \DHP_FW\EventInterface $event) {
        $this->method =
                $method === NULL ? $this->generateMethod() : $method;
        $this->uri    =
                $uri === NULL ? $this->generateUri() : ltrim($uri, '/');
        $this->_body  = $body;
        $this->event  = $event;
        $this->parseRequestHeaders();
        $this->parseInputData();
    }

    /**
     * Returns the method of the request
     * @return null|string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Sets the methods of the request
     * @param $method
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * Sets the uri of the request
     * @param $uri
     */
    public function setUri($uri) {
        $this->uri = $uri;
    }

    /**
     * Returns the current uri
     * @return null|string
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Returns the value of the header, null if not set.
     * @param $name
     * @return String|null
     */
    public function header($name) {
        return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
    }

    /**
     * Returns all the headers in the request
     * @return null
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * This parses the input data and sets up query, param,file and params values
     */
    private function parseInputData() {
        $values             =
                array('query'  => new ParameterBagReadOnly($_GET, $this->event),
                      'param'  => new ParameterBagReadOnly($_POST, $this->event),
                      'files'  => new ParameterBagReadOnly($_FILES, $this->event),
                      'params' => new ParameterBagReadOnly(array_merge($_GET, $_POST), $this->event));
        $body               = $this->parseBodyData();
        $values['body']     =
                is_array($body) ? new ParameterBagReadOnly($body, $this->event) : $body;
        $this->publicValues = $values;
    }

    /**
     * Able to get public values on the request object.
     *
     * Usually used for middleware or custom parameter types
     * @param String $name
     * @return mixed | null
     */
    public function __get($name) {
        return isset($this->publicValues[$name]) ? $this->publicValues[$name] : NULL;
    }

    /**
     * Able to set public values on the request object.
     *
     * Usually used for middleware or custom parameter types
     * @param String $name
     * @param mixed  $value
     * @return mixed | null
     */
    public function __set($name, $value) {
        return $this->publicValues[$name] = $value;
    }

    /**
     * This will parse request body. For the moment only used
     * when requests content-type is json, will json_decode
     * the body.
     *
     * @return mixed|null
     */
    protected function parseBodyData() {
        if (!isset($this->_body)) {
            $this->_body = file_get_contents('php://input');
        }
        $__body__ = NULL;
        switch (TRUE) {
            case $this->header('Content-Type') !== NULL:
                if (strpos($this->header('Content-Type'), 'json') !== FALSE) {
                    # most likely, it IS json
                    $__body__ = json_decode($this->_body);
                }
                break;
            default:
                $__body__ = NULL;
                break;
        }
        return $__body__;
    }

    /**
     * If no method is present, tries to figure out the method
     * @return string
     */
    protected function generateMethod() {
        $method = 'GET';
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        return $method;
    }

    /**
     * If no uri is present, tries to figure out the uri
     * @return string
     */
    protected function generateUri() {
        $uri = NULL;
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }
        $uri = ltrim($uri, '/');
        return $uri;
    }

    /**
     * Parses the request-headers and normalize their name
     */
    protected function parseRequestHeaders() {
        $this->headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header                 = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $this->headers[$header] = $value;
            }
        }
    }
}
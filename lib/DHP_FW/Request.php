<?php
declare( encoding = "UTF8" ) ;
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

    #public $query, $param, $params, $body, $files = NULL;

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

    public function getMethod() {
        return $this->method;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }

    public function getUri() {
        return $this->uri;
    }

    public function header($name) {
        return isset( $this->headers[$name] ) ? $this->headers[$name] : NULL;
    }

    public function getHeaders() {
        return $this->headers;
    }

    private function parseInputData() {
        $values             =
          array('query'  => new ParameterBagReadOnly( $_GET, $this->event ),
                'param'  => new ParameterBagReadOnly( $_POST, $this->event ),
                'files'  => new ParameterBagReadOnly( $_FILES, $this->event ),
                'params' => new ParameterBagReadOnly( array_merge($_GET, $_POST), $this->event ));
        $body               = $this->parseBodyData();
        $values['body']     =
          is_array($body) ? new ParameterBagReadOnly( $body, $this->event ) : $body;
        $this->publicValues = $values;
    }

    public function __get($name) {
        return isset( $this->publicValues[$name] ) ? $this->publicValues[$name] : NULL;
    }

    public function __set($name, $value) {
        return $this->publicValues[$name] = $value;
    }

    protected function parseBodyData() {
        if ( !isset( $this->_body ) ) {
            $this->_body = file_get_contents('php://input');
        }
        $__body__ = NULL;
        switch (TRUE) {
            case $this->header('Content-Type') !== NULL:
                if ( strpos($this->header('Content-Type'), 'json') !== FALSE ) {
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

    protected function generateMethod() {
        $method = 'GET';
        if ( isset( $_SERVER['REQUEST_METHOD'] ) ) {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        return $method;
    }

    protected function generateUri() {
        $uri = NULL;
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }
        $uri = ltrim($uri, '/');
        return $uri;
    }

    protected function parseRequestHeaders() {
        $this->headers = array();
        foreach ($_SERVER as $key => $value) {
            if ( substr($key, 0, 5) === 'HTTP_' ) {
                $header                 =
                  str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $this->headers[$header] = $value;
            }
        }
    }
}
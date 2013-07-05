<?php
declare(encoding = "UTF8");
namespace DHP;

use DHP\utility\Constants;
use DHP\utility\Util;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:34
 */
class Request
{
    protected $method, $uri, $body, $post, $get, $files;

    /**
     * This sets up the Request object.
     *
     * @param String $method http-method
     * @param String $uri uri of the request
     * @param String $body if a body was sent with the request, this is it contents
     * @param Array $post the post-data from the request
     * @param Array $get the get-data from the request
     * @param Array $files the files sent with the request
     * @param array $headers the headers sent with the request
     */
    public function __construct(
        $method = "HEADER",
        $uri = null,
        $body = null,
        $post = array(),
        $get = array(),
        $files = array(),
        $headers = array()
    ) {
        $this->method = $method;
        $this->uri    = $uri;
        $this->body   = $body;
        $this->post   = $post;
        $this->get    = $get;
        $this->files  = $files;
        $this->headers = $headers;
    }

    /**
     * Returns the http-method for the request
     *
     * @return String
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the headers in the request
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the uri for the request
     *
     * @return String
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns body value
     *
     * @return null|String
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * This sets default values based on the current environment
     */
    public function setupWithEnvironment()
    {
        $this->useHttpRequestUri();
        $this->useHttpMethod();
        $this->getBodyContents();
        $this->post  = $_POST;
        $this->get   = $_GET;
        $this->files = $_FILES;
        $this->headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
    }



    /**
     * Gets uri from $_SERVER and uses that for uri.
     */
    private function useHttpRequestUri()
    {
        $uri = null;
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $this->uri = $uri;
        }
        if (DHP_CLI) {
            $this->uri = Util::parseArgv('uri');
        }
        $this->uri = trim($this->uri, '/');
    }

    /**
     * Gets http method from $_SERVER and uses that for method.
     */
    private function useHttpMethod()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->method = $_SERVER['REQUEST_METHOD'];
        }
        if (DHP_CLI) {
            $this->method = Util::parseArgv('method');
        }
    }

    /**
     * Reads php://input and uses that for body of request
     */
    private function getBodyContents()
    {
        if (DHP_HTTP) {
            $this->body = file_get_contents('php://input');
        }
        if (DHP_CLI) {
            $this->body = Util::parseArgv('body');
        }
    }

    /**
     * Setter for body data
     *
     * @param $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
}
<?php
declare(encoding = "UTF8");
namespace DHP;

use DHP\utility\Constants;
use DHP\utility\Util;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:34
 */
if (!defined('CLI')) {
    if (php_sapi_name() == 'cli' or PHP_SAPI == 'cli') {
        define('CLI', TRUE);
    } else {
        define('CLI', FALSE);
    }
    define('HTTP', !CLI);
}

class Request
{
    protected $method, $uri, $body, $post, $get, $files, $headers;

    /**
     * This sets up the Request object.
     *
     * @param String $method http-method
     * @param String $uri uri of the request
     * @param String $body if a body was sent with the request, this is it contents
     * @param Array  $post the post-data from the request
     * @param Array  $get the get-data from the request
     * @param Array  $files the files sent with the request
     * @param array  $headers the headers sent with the request
     */
    public function __construct(
        $method = "HEADER",
        $uri = NULL,
        $body = NULL,
        $post = array(),
        $get = array(),
        $files = array(),
        $headers = array()
    )
    {
        $this->method = $method;
        $this->uri    = $uri;
        $this->setBodyContents($body);
        $this->post    = $post;
        $this->get     = $get;
        $this->files   = $files;
        $this->headers = $headers;
    }

    /**
     * Reads php://input and uses that for body of request
     */
    private function setBodyContents($bodyContent = NULL)
    {
        if (!isset($bodyContent)) {
            if (HTTP) {
                $rawData = file_get_contents('php://input');
            }
            if (CLI) {
                $rawData = Util::parseArgv('body');
            }
        } else {
            $rawData = $bodyContent;
        }
        $this->body = $rawData;
    }

    /**
     * This sets default values based on the current environment
     */
    public function setupWithEnvironment()
    {
        $this->useHttpRequestUri();
        $this->useHttpMethod();
        $this->setBodyContents();
        $this->post    = $_POST;
        $this->get     = $_GET;
        $this->files   = $_FILES;
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
        $uri = NULL;
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $this->uri = $uri;
        }
        if (CLI) {
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
        if (CLI) {
            $this->method = Util::parseArgv('method');
        }
    }

    /**
     * Setter for body data
     *
     * @param $body
     */
    public function setBody($body)
    {
        $this->setBodyContents($body);
    }

    public function __get($name)
    {
        $return = NULL;
        switch (strtolower($name)) {
            case 'get':
                $return = $this->get;
                break;
            case 'post':
                $return = $this->post;
                break;
            case 'files':
                $return = $this->files;
                break;
            case 'headers':
                $return = $this->headers;
                break;
            case 'uri':
                $return = $this->uri;
                break;
            case 'body':
                $return = $this->body;
                break;
            case 'method':
                $return = $this->method;
                break;
        }
        return $return;
    }

}
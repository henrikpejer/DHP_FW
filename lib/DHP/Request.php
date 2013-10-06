<?php
declare(encoding = "UTF8");
namespace DHP;

use DHP\utility\Constants;
use DHP\utility\Util;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:34
 */
/**
 * Class Request
 * @package DHP
 *
 * @property-read string method
 * @property-read string uri
 * @property-read string body
 * @property-read array  post
 * @property-read array  get
 * @property-read array  files
 * @property-read array  headers
 */
class Request
{
    protected $requestMethod;
    protected $requestUri;
    protected $requestBody;
    protected $requestPost;
    protected $requestGet;
    protected $requestFiles;
    protected $requestHeaders;

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
        $uri = null,
        $body = null,
        $post = array(),
        $get = array(),
        $files = array(),
        $headers = array()
    ) {
        $this->requestMethod = $method;
        $this->requestUri    = $uri;
        if (isset($body)) {
            $this->setBodyContents($body);
        }
        $this->requestPost    = $post;
        $this->requestGet     = $get;
        $this->requestFiles   = $files;
        $this->requestHeaders = $headers;
    }

    /**
     * Reads php://input and uses that for body of request
     */
    private function setBodyContents($bodyContent = null)
    {
        if (!isset($bodyContent)) {
            if (PHP_SAPI != 'cli') {
                $rawData = file_get_contents('php://input');
            } else {
                $rawData = Util::parseArgv('body');
            }
        } else {
            $rawData = $bodyContent;
        }
        $this->requestBody = $rawData;
    }

    /**
     * This sets default values based on the current environment
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function setupWithEnvironment()
    {
        $this->useHttpRequestUri();
        $this->useHttpMethod();
        $this->setBodyContents();
        $this->post           = $_POST;
        $this->get            = $_GET;
        $this->files          = $_FILES;
        $this->requestHeaders = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $this->requestHeaders[str_replace(
                    ' ',
                    '-',
                    ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                )] = $value;
            }
        }
    }

    /**
     * Gets uri from $_SERVER and uses that for uri.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function useHttpRequestUri()
    {
        $uri = null;
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $this->uri = $uri;
        }
        if (PHP_SAPI == 'cli') {
            $this->uri = Util::parseArgv('uri');
        }
        $this->uri = trim($this->uri, '/');
    }

    /**
     * Gets http method from $_SERVER and uses that for method.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function useHttpMethod()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        }
        if (PHP_SAPI == 'cli') {
            $this->requestMethod = Util::parseArgv('method');
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

    /**
     * @param $name
     * @return array|null|String
     */
    public function __get($name)
    {
        $return = null;
        switch (strtolower($name)) {
            case 'get':
                $return = $this->requestGet;
                break;
            case 'post':
                $return = $this->requestPost;
                break;
            case 'files':
                $return = $this->requestFiles;
                break;
            case 'headers':
                $return = $this->requestHeaders;
                break;
            case 'uri':
                $return = $this->requestUri;
                break;
            case 'body':
                $return = $this->requestBody;
                break;
            case 'method':
                $return = $this->requestMethod;
                break;
        }
        return $return;
    }
}

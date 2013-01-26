<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-21 19:28
 */

interface RequestInterface {

    /**
     * This will set the basic values of a request.
     *
     * @param String           $method The method of the request (usually GET, POST etc..)
     * @param String           $uri The uri of the request
     * @param null             $body The body of the request
     * @param EventInterface   $event
     */
    function __construct($method = NULL, $uri = NULL, $body = NULL, \DHP_FW\EventInterface $event);

    /**
     * Returns the method of the request
     * @return null|string
     */
    function getMethod();

    /**
     * Sets the methods of the request
     * @param $method
     */
    function setMethod($method);

    /**
     * Sets the uri of the request
     * @param $uri
     */
    function setUri($uri);

    /**
     * Returns the current uri
     * @return null|string
     */
    function getUri();

    /**
     * Returns the value of the header, null if not set.
     * @param $name
     * @return String|null
     */
    function header($name);

    /**
     * Returns all the headers in the request
     * @return null
     */
    function getHeaders();
}

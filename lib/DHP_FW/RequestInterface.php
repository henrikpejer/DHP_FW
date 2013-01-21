<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-21 19:28
 */

interface RequestInterface {
    function __construct($method = NULL, $uri = NULL, $body = NULL, \DHP_FW\EventInterface $event);

    function getMethod();

    function setMethod($method);

    function setUri($uri);

    function getUri();

    function header($name);

    function getHeaders();
}

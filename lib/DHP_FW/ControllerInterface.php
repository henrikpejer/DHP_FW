<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-06 10:20
 */
interface ControllerInterface {


    /**
     * Generic constructor. Set request response and event so
     * that routes can easily access them
     *
     * Also register the controller to catch _all_ events
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param EventInterface    $event
     */
    function __construct(\DHP_FW\RequestInterface $request, \DHP_FW\ResponseInterface $response, \DHP_FW\EventInterface $event);


    /**
     * A catch all for all events, stub, extendable
     */
    function __eventCatchall();
}

<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-06 10:20
 */
interface ControllerInterface {

    function __construct(\DHP_FW\RequestInterface $request, \DHP_FW\ResponseInterface $response, \DHP_FW\EventInterface $event);

    function __eventCatchall();
}

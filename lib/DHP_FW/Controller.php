<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
use DHP_FW\Response;
use DHP_FW\Request;
use DHP_FW\Event;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-06 10:20
 */
class Controller implements \DHP_FW\ControllerInterface {

    protected $request, $response;

    /**
     * Generic constructor. Set request response and event so
     * that routes can easily access them
     *
     * Also register the controller to catch _all_ events
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param EventInterface    $event
     */
    public function __construct(\DHP_FW\RequestInterface $request, \DHP_FW\ResponseInterface $response, \DHP_FW\EventInterface $event) {
        $this->request  = $request;
        $this->response = $response;
        $this->event    = $event;
        $this->event->register('__controller__', array($this, '__eventCatchall'));
    }

    /**
     * A catch all for all events, stub, extendable
     */
    public function __eventCatchall() {
    }
}

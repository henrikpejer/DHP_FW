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
class Controller implements \DHP_FW\ControllerInterface{

    protected $request, $response;

    public function __construct(\DHP_FW\RequestInterface $request, \DHP_FW\ResponseInterface $response, \DHP_FW\EventInterface $event) {
        $this->request  = $request;
        $this->response = $response;
        $this->event    = $event;
        $this->event->register('__controller__', array($this, '__eventCatchall'));
    }

    public function __eventCatchall() {
    }
}

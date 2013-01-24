<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\middleware;
use DHP_FW\ParameterBag;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-18 23:40
 */
class Session implements MiddlewareInterface {

    private $sessionData = NULL;
    private $dataChanged = FALSE;
    private $event = NULL;
    public $flash = NULL;

    public function __construct(\DHP_FW\RequestInterface $req, \DHP_FW\EventInterface $event) {
        $this->load();
        $this->event = $event;
        $this->setupFlashData();
        $req->session = $this;
    }

    private function load() { }

    public function __set($name, $value) {
        $this->dataChanged        = TRUE;
        $this->sessionData[$name] = $value;
    }

    public function __get($name) {
        return isset( $this->sessionData[$name] ) ? $this->sessionData[$name] : NULL;
    }

    public function dataChanged() {
        $this->dataChanged = TRUE;
    }

    public function dataIsChanged() {
        return $this->dataChanged;
    }

    private function setupFlashData() {
        # todo : refactor away this, right?
        $this->flash = new ParameterBag( array(), $this->event );
        $this->event->subscribe($this->flash, $this);
    }
}
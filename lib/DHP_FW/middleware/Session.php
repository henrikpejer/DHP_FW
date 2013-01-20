<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\middleware;
use DHP_FW\ParameterBag;
use DHP_FW\Event;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-18 23:40
 */
class Session {

    private $sessionData = NULL;
    private $dataChanged = FALSE;
    private $event = NULL;
    public $flash = NULL;

    public function __construct(\DHP_FW\Request $req){
        $this->load();
        $this->setupFlashData();
        $req->session = $this;
    }

    private function load(){}

    public function __set($name,$value){
        $this->dataChanged = TRUE;
        $this->sessionData[$name] = $value;
    }

    public function __get($name){
        return isset($this->sessionData[$name])?$this->sessionData[$name]:NULL;
    }

    public function dataChanged(){
        $this->dataChanged = TRUE;
    }

    private function setupFlashData() {
        $this->flash = new ParameterBag(array());
        $e = \app\DI()->get('DHP_FW\\Event');
        $e->delegate($this->flash,$this);
    }
}
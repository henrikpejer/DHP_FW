<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\dependencyInjection;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 20:44
 */
class DIProxy implements DIProxyInterface{
    private $classToInstantiate = NULL;
    private $argumentsToConstructor = array();
    private $methodCalls = array();
    private $object = NULL;

    public function __construct($class, DIInterface &$DI) {
        $this->classToInstantiate     = $class;
        $this->DI = $DI;
    }

    public function get() {
        return array('class'   => $this->classToInstantiate,
                     'args'    => $this->argumentsToConstructor,
                     'methods' => $this->methodCalls);
    }

    public function addMethodCall($method, $args = array()) {
        $args = !is_array($args)?array($args):$args;
        $this->methodCalls[] = array('method' => $method, 'args' => $args);
        return $this;
    }

    public function setArguments(Array $args) {
        $this->argumentsToConstructor = $args;
        return $this;
    }

    public function init(){
        if(!isset($this->object)){
            $this->object = $this->DI->instantiateObject($this->classToInstantiate,$this->argumentsToConstructor);
            foreach($this->methodCalls as $methodCall){
                call_user_func_array(array($this->object,$methodCall['method']),$methodCall['args']);
            }
        }
        return $this->object;
    }
}

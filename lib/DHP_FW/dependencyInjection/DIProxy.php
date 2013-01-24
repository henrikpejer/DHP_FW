<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\dependencyInjection;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 20:44
 */
class DIProxy implements DIProxyInterface {
    private $classToInstantiate = NULL;
    private $argumentsToConstructor = array();
    private $methodCalls = array();

    public function __construct($class){
        $this->classToInstantiate = $class;
    }

    public function get(){
        return array('class'   => $this->classToInstantiate,
                     'args'    => $this->argumentsToConstructor,
                     'methods' => $this->methodCalls);
    }

    public function addMethodCall($method, $args = array()){
        $args                = !is_array($args) ? array($args) : $args;
        $this->methodCalls[] = (object) array('method' => $method,
                                              'args'   => $args);
        return $this;
    }

    public function setArguments(Array $args){
        $this->argumentsToConstructor = $args;
        return $this;
    }
}

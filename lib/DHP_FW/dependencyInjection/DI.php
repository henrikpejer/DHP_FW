<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\dependencyInjection;
use DHP_FW\Utils;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 20:42
 */
class DI {

    private $event = NULL;

    private $container = array(
        'object' => array(), 'class' => array(), 'parameters' => array()
    );

    public function __construct(\DHP_FW\Event $Event){
        $this->event                                 = $Event;
        $this->container['object'][get_class($this)] = $this;
        $this->container['object'][get_class($Event)] = $Event;
        $this->addObjectAlias('DI', get_class($this));
    }

    public function get($name, array $args = array()){
        $name = trim($name,'\\');
        $this->event->trigger('DHP_FW.DI.get', $name);
        $objectName = str_replace(array('/', '.'), '\\', $name);
        if ( isset( $this->container['object'][$objectName] ) ) {
            return $this->container['object'][$objectName];
        }
        if ( isset( $this->container['class'][$objectName] ) ) {
            if ( is_object($this->container['class'][$objectName]) && is_a($this->container['class'][$objectName], 'DHP_FW\\dependencyInjection\\DIProxy') ) {
                $this->initClass($objectName);
            }
            return $this->container['class'][$objectName];
        }
        # last thing to try -> load it as if it were a class, right?
        try {
            $o = $this->instantiateObject($name, $args);
        }
        catch (\Exception $e) {
            return NULL;
        }
        return $o;
    }

    public function addObjectAlias($name, $reference){
        $this->container['object'][$name] = $this->container['object'][$reference];
        return $this;
    }

    public function addClassAlias($name, $reference){
        $this->container['class'][$name] = $this->container['class'][$reference];
        return $this;
    }

    public function addObject($object, $name = NULL){
        $name                             = $name === NULL ? get_class($object) : $name;
        $this->container['object'][$name] = $object;
        return $this;
    }

    public function addClass($class, array $constructorArgs = array()){
        return $this->container['class'][$class] = new DIProxy( $class, $constructorArgs, $this );
    }

    public function getObjectsInDI(){
        $return = array();
        foreach($this->container as $key => $object){
            $return[$key] = array_keys($object);
        }
        return $return;
    }

    private function initClass($classToInit){
        $key = $classToInit;
        /**
         * Here is a tricky part - we could have aliases to unfinished
         * objects here and the way to figure this out is to look at the
         * value we get. If it is a string, it is an alias and we should
         * continue to look for the actual object we should init.
         *
         */
        do {
            $key = $this->container['class'][$key];
        } while (is_string($key));
        $o                                        = $key->init();
        $this->container['object'][get_class($o)] = $o;
        $this->container['class'][$classToInit]   = $this->container['object'][get_class($o)];
        return $o;
    }

    public function instantiateObject($class, array $__args__ = array()){
        $this->event->trigger('DHP_FW.DI.instantiate', $class, $__args__);
        $constructorArguments = Utils::classConstructorArguments($class);
        $classReflector       = new \ReflectionClass( $class );
        $args                 = array();
        foreach ($constructorArguments as $key => $constructorArgument) {
            # get a value, if possible...
            switch(TRUE){
                case ( !empty( $constructorArgument['class'] ) && ($__arg__ = $this->instantiateViaConstructor($constructorArgument['class'])) !== NULL):
                case ( !empty( $constructorArgument['name'] ) && ($__arg__ = $this->instantiateViaConstructor($constructorArgument['name'])) !== NULL):
                    $args[] = $__arg__;
                    break;
                case isset( $__args__[$constructorArgument['name']] ):
                    $args[] = $__args__[$constructorArgument['name']];
                    break;
                case isset( $__args__[$key] ):
                    $args[] = $__args__[$key];
                break;
                default:
                    $args[] = NULL;
            }
        }
        $return = sizeof($args) == 0 ? $classReflector->newInstance() : $classReflector->newInstanceArgs($args);
        $this->container['object'][get_class($return)] = $return;
        return $return;
    }

    private function instantiateViaConstructor($constructor){
        $return = NULL;
        try {
            $__name__ = $this->get($constructor);
            if ( !empty( $__name__ ) ) {
                $return = $__name__;
            }
        }
        catch (\Exception $e) {
        }
        return $return;
    }
}
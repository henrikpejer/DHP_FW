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

    public $container = array(
        'object' => array(), 'class' => array(), 'parameters' => array()
    );

    public function __construct(\DHP_FW\Event $Event){
        $this->event                                 = $Event;
        $this->container['object'][get_class($this)] = $this;
        $this->addObjectAlias('DI', get_class($this));
    }

    public function get($name){
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
            $o = $this->instantiateObject($name);
        }
        catch (\Exception $e) {
            //throw $e;
            return NULL;
        }
        return $o;
    }

    public function addObjectAlias($name, $reference){
        $this->container['object'][$name] = & $this->container['object'][$reference];
        return $this;
    }

    public function addClassAlias($name, $reference){
        $this->container['class'][$name] = & $this->container['class'][$reference];
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
        $this->container['class'][$classToInit]   = & $this->container['object'][get_class($o)];
        return $o;
    }

    public function instantiateObject($class, array $__args__ = array()){
        $this->event->trigger('DHP_FW.DI.instantiate', $class);
        $constructorArguments = Utils::classConstructorArguments($class);
        $classReflector       = new \ReflectionClass( $class );
        $args                 = array();
        foreach ($constructorArguments as $key => $constructorArgument) {
            # get a value, if possible...
            if ( !empty( $constructorArgument['class'] ) && ($__arg__ = $this->instantiateViaConstructor($constructorArgument['class'])) !== NULL) {
                $args[] = $__arg__;
                continue;
            }
            if ( !empty( $constructorArgument['name'] ) && ($__arg__ = $this->instantiateViaConstructor($constructorArgument['name'])) !== NULL) {
                $args[] = $__arg__;
                continue;
            }
            if ( isset( $__args__[$constructorArgument['name']] ) ) {
                $args[] = $__args__[$constructorArgument['name']];
            } elseif ( isset( $__args__[$key] ) ) {
                $args[] = $__args__[$key];
            }
            # todo : dbl check that these commented rows are really not needed...
            /*
            switch (TRUE) {
                
                case isset( $this->container['class'][$constructorArgument['class']] ) && !is_a($this->container['class'][$constructorArgument['class']], 'DHP_FW\\dependencyInjection\\DIProxy'):
                    $args[] = $this->container['class'][$constructorArgument['class']];
                    break;
                case isset( $this->container['class'][$constructorArgument['name']] ) && !is_a($this->container['class'][$constructorArgument['name']], 'DHP_FW\\dependencyInjection\\DIProxy'):
                    $args[] = $this->container['class'][$constructorArgument['name']];
                    break;
                case isset( $this->container['object'][$constructorArgument['class']] ):
                    $args[] = $this->container['object'][$constructorArgument['class']];
                    break;
                case isset( $this->container['object'][$constructorArgument['name']] ):
                    $args[] = $this->container['object'][$constructorArgument['name']];
                    break;

                case isset( $this->container['class'][$constructorArgument['class']] ):
                    $args[] = $this->initClass($constructorArgument['class']);
                    break;
                
                case isset( $constructorArgument['class'] ):
                    $args[] = $this->instantiateObject($constructorArgument['class']);
                    break;

                default:
                    if ( isset( $__args__[$constructorArgument['name']] ) ) {
                        $args[] = $__args__[$constructorArgument['name']];
                    } elseif ( isset( $__args__[$key] ) ) {
                        $args[] = $__args__[$key];
                    }
                    break;
            }*/
        }
        return sizeof($args) == 0 ? $classReflector->newInstance() : $classReflector->newInstanceArgs($args);
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
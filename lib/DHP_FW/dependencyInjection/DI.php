<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\dependencyInjection;
use DHP_FW\Utils;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-22 20:32
 */
class DI implements \DHP_FW\dependencyInjection\DIInterface {
    private $config, $store;

    /**
     * Starts the whole DI off.
     *
     * Config should be initial values for what means what, so to speak.
     *
     * This will also load the event-library, since it is used throughout
     * the application.
     *
     * @param array $config
     */
    function __construct(array $config = array()) {
        $this->config = (object)$config;
        $this->store  = new \StdClass;
    }

    /**
     * Here we set a key, value. This will be used when we further down the road
     * need to get something.
     *
     * These will also be used when we want to instantiate things.
     *
     * @param $name name of key, string
     * @param $value value, could be anything
     * @return \DHP_FW\dependencyInjection\DIProxyInterface
     */
    function set($name, $value) {
        return $this->store->{$name} = new DIProxy($value, $this);
    }

    /**
     * Here we set an alias for an key:
     *
     * alias('DHP_FW\Request','app\Request')
     *
     * means that if one asks for DHP_FWE/Request, you get app/request instead.
     *
     * Original has to exist already, or an exception will be thrown.
     *
     * @param $alias the alias for...
     * @param $original original
     * @return $this
     */
    function alias($alias, $original) {
        if (!isset($this->store->{$original})) {
            throw new \InvalidArgumentException('Original must already exist');
        }
        $this->store->{$alias} = & $this->store->{$original};
    }

    /**
     * This will return whatever it is that we want. IF object has been loaded,
     * return that. If not, instantiate it and.... be happy with it!
     *
     * @param $name name of object to load
     * @return mixed
     */
    function get($name) {
        if (!isset($this->store->{$name})) {
            return NULL;
        }else {
            if (is_a($this->store->{$name}, '\DHP_FW\dependencyInjection\DIProxy')) {
                $this->store->{$name} = $this->store->{$name}->init();
            }
        }
        return $this->store->{$name};
    }

    /**
     * A short for set($name,$value)
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    function __set($name, $value) {
        return $this->set($name, $value);
    }

    /**
     * A short for get($name);
     * @param $name
     * @return mixed
     */
    function __get($name) {
        return $this->get($name);
    }

    public function instantiateObject($class, array $__args__ = array()) {
        # $this->event->trigger('DHP_FW.DI.instantiate', $class, $__args__);
        $constructorArguments = Utils::classConstructorArguments($class);
        $classReflector       = new \ReflectionClass($class);
        if ($classReflector->isInterface()) {
            return NULL;
        }
        $args = array();
        foreach ($constructorArguments as $key => $constructorArgument) {
            # get a value, if possible...
            switch (TRUE) {
                case (!empty($constructorArgument['class']) && (
                $__arg__ = $this->instantiateViaConstructor($constructorArgument['class'])) !== NULL):
                case (!empty($constructorArgument['name']) && (
                $__arg__ = $this->instantiateViaConstructor($constructorArgument['name'])) !== NULL):
                    $args[] = $__arg__;
                    break;
                case isset($__args__[$constructorArgument['name']]):
                    $args[] = $__args__[$constructorArgument['name']];
                    break;
                case isset($__args__[$key]):
                    $args[] = $__args__[$key];
                    break;
                default:
                    $args[] = NULL;
            }
        }
        $return =
                sizeof($args) == 0 ? $classReflector->newInstance() : $classReflector->newInstanceArgs($args);
        # $this->container['object'][get_class($return)] = $return;
        return $return;
    }

    private function instantiateViaConstructor($constructor) {
        $return = NULL;
        try {
            if (isset($this->storage->{$constructor})) {
                $__name__ = $this->storage->{$constructor};
                if (!empty($__name__)) {
                    $return = $__name__;
                }
            }
        }
        catch (\Exception $e) {
        }
        return $return;
    }
}

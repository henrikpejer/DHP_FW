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
        $this->config                                            =
                (object)$config;
        $this->store                                             =
                new \StdClass;
        $this->store->{'DHP_FW\dependencyInjection\DIInterface'} =
                $this;
    }

    /**
     * Here we set a key, value. This will be used when we further down the road
     * need to get something.
     *
     * These will also be used when we want to instantiate things.
     *
     * @param $name  String, name of key, string
     * @param $value String, value could be anything
     *
     * @return \DHP_FW\dependencyInjection\DIProxyInterface
     */
    public function set($name, $value) {
        if (is_string($value)) {
            $this->store->{$name} = new DIProxy($value);
        }
        else {
            $this->store->{$name} = $value;
        }
        return $this->store->{$name};
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
     * @param $alias    String, alias for...
     * @param $original String,
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function alias($alias, $original) {
        if (!isset($this->store->{$original})) {
            throw new \InvalidArgumentException('Original must already exist');
        }
        $this->store->{$alias} = & $this->store->{$original};
    }

    /**
     * This will return whatever it is that we want. IF object has been loaded,
     * return that. If not, instantiate it and.... be happy with it!
     *
     * @param $name String name of object to load
     *
     * @return mixed
     */
    function get($name) {
        if (!isset($this->store->{$name})) {
            $frameworkClass = $this->findMatchWithinFramework($name);
            if ($frameworkClass == NULL) {
                # lets try to load this as-if it where a class being called, ok?
                try {
                    if (class_exists($name)) {
                        $frameworkClass = $this->instantiateObject($name);
                    }
                    else {
                        return NULL;
                    }
                }
                catch (\Exception $e) {
                    return NULL;
                }
            }
            $this->set($name, $frameworkClass);
        }
        $__object__ = $this->store->{$name};
        if (is_a($__object__, '\DHP_FW\dependencyInjection\DIProxy')) {
            $__initProcess__ = $__object__->get();
            $instance        =
                    $this->instantiateObject($__initProcess__['class'], $__initProcess__['args']);
            foreach ($__initProcess__['methods'] as $methodAndArgs) {
                call_user_func_array(array($instance, $methodAndArgs->method), $methodAndArgs->args);
            }

            $this->store->{$name} = & $instance;
            # alias?
            $this->alias(get_class($instance), $name);
        }

        return $this->store->{$name};
    }

    /**
     * A short for set($name,$value)
     *
     * @param $name
     * @param $value
     *
     * @return mixed
     */
    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    /**
     * A short for get($name);
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name) {
        return $this->get($name);
    }

    public function instantiateObject($class, array $__args__ = array()) {
        # $this->event->trigger('DHP_FW.DI.instantiate', $class, $__args__);
        $constructorArguments =
                Utils::classConstructorArguments($class);
        $classReflector       = new \ReflectionClass($class);
        if ($classReflector->isInterface()) {
            return NULL;
        }
        $args = array();
        foreach ($constructorArguments as $key => $constructorArgument) {
            # get a value, if possible...
            switch (TRUE) {
                case isset($__args__[$key]):
                    $args[] = $__args__[$key];
                    break;
                case (!empty($constructorArgument['class']) && (
                $__arg__ =
                        $this->get($constructorArgument['class'])) !== NULL):
                case (!empty($constructorArgument['name']) && (
                $__arg__ =
                        $this->get($constructorArgument['name'])) !== NULL):
                    $args[] = $__arg__;
                    break;
                case isset($__args__[$constructorArgument['name']]):
                    $args[] = $__args__[$constructorArgument['name']];
                    break;
                case isset($constructorArgument['default']):
                    $args[] = $constructorArgument['default'];
                    break;
                default:
                    $args[] = NULL;
            }
        }
        $return =
                sizeof($args) == 0 ? $classReflector->newInstance() : $classReflector->newInstanceArgs($args);
        return $return;
    }

    private function findMatchWithinFramework($name) {
        $DHP_FWClass = str_replace('Interface', '', $name);
        return strpos($name, 'DHP_FW\\') === 0 && class_exists($DHP_FWClass) ? $DHP_FWClass : NULL;
    }
}

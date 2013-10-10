<?php
declare(encoding = "UTF8");
namespace DHP\dependencyInjection;

use DHP\utility\Util;

/**
 * Basic Dependency Injection container.
 *
 * A few notes :
 *  + Everything loaded through this container will be returned by reference if
 *    found. This means that instantiating objects from the same class with this
 *    container will fail.
 *  + IF something extends \DHP\blueprint\Component - the container will NOT
 *    return already instantiated object but instead create a new one for each
 *    call. Modules will, however, be stored only once and referenced from that
 *    first instantiated object.
 *
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-22 20:32
 */
class DI
{
    public  $config, $store;

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
    function __construct(array $config = array())
    {
        $this->config                    = (object)$config;
        $this->store                     = new \StdClass;
        $this->store->{'DI'}             = & $this;
        $this->store->{get_class($this)} = & $this;
    }

    /**
     * A short for get($name);
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * A short for set($name,$value)
     *
     * @param $name
     * @param $value
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
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
     * @return $value
     */
    public function set($name, $value = NULL)
    {
        if ($value === NULL) {
            $value = $name;
        }
        if (is_string($value)) {
            $this->store->{$name} = new DIProxy($value);
        } else {
            $this->store->{$name} = $value;
        }
        return $this->store->{$name};
    }

    /**
     * This will return whatever it is that we want. IF object has been loaded,
     * return that. If not, instantiate it and.... be happy with it!
     *
     * @param $name String name of object to load
     *
     * @return mixed
     */
    function get($name)
    {
        if (!isset($this->store->{$name})) {
            $frameworkClass = $this->findMatchWithinFramework($name);
            if ($frameworkClass == NULL) {
                # lets try to load this as-if it where a class being called, ok?
                try {
                    if (class_exists($name)) {
                        $frameworkClass = $this->instantiateObject($name);
                    } else {
                        return NULL;
                    }
                } catch (\Exception $e) {
                    return NULL;
                }
            }
            $temp = new \ReflectionClass($frameworkClass);
            switch (TRUE) {
                case ($temp->isSubclassOf('DHP\blueprint\Component')):
                    return $this->instantiateObject($frameworkClass);
            }
            unset($temp);
            $this->set($name, $frameworkClass);
        }
        if (is_string($this->store->{$name}) && isset($this->store->{$this->store->{$name}})) {
            if ( is_string($this->store->{$this->store->{$name}})){
                $objectToGet = $this->get($this->store->{$this->store->{$name}});
            } else {
                $objectToGet = $this->store->{$this->store->{$name}};
            }
        } else {
            $objectToGet = $this->store->{$name};
        }
        if ( is_string($objectToGet) ){
            $objectToGet = $this->instantiateObject($objectToGet);
        }
        switch (TRUE) {
            case (is_a($objectToGet, '\DHP\dependencyInjection\DiProxy')):
                $initProcess = $objectToGet->get();
                $instance    = $this->instantiateObject(
                    $initProcess['class'],
                    $initProcess['args']
                );
                foreach ($initProcess['methods'] as $methodAndArgs) {
                    call_user_func_array(
                        array(
                             $instance,
                             $methodAndArgs->method
                        ),
                        $methodAndArgs->args
                    );
                }
                $this->store->{$name} = & $instance;
                $this->store->{get_class($instance)} = & $instance;
                #$this->alias(get_class($instance), $name);
                $objectToGet = & $instance;
        }
        return $objectToGet;
        #return $this->store->{$name};
    }

    /**
     * Used to try to find matches within the framework. For now we only
     * re-check with 'Interface' removed.
     *
     * @param $name
     *
     * @return mixed|null
     */
    private function findMatchWithinFramework($name)
    {
        $dhpFwClass = str_replace('Interface', '', $name);
        return strpos($name, 'DHP\\') === 0 && class_exists($dhpFwClass) ?
            $dhpFwClass : NULL;
    }

    /**
     * Instantiates an object
     *
     * @param       $class
     * @param array $argsForObject
     *
     * @return null|object
     */
    public function instantiateObject($class, array $argsForObject = array())
    {
        # $this->event->trigger('DHP_FW.DI.instantiate', $class, $__args__);
        $constructorArguments = Util::classConstructorArguments($class);
        $classReflector       = new \ReflectionClass($class);
        if ($classReflector->isInterface() || $classReflector->isAbstract()) {
            return NULL;
        }
        $args = array();
        $argsNotUsed = array_values($argsForObject);
        try {
            foreach ($constructorArguments as $key => $constructorArgument) {
                # get a value, if possible...
                switch (TRUE) {
                    case (!empty($constructorArgument['class']) &&
                          ($arg =
                              $this->get($constructorArgument['class'])) !== NULL):
                    case (!empty($constructorArgument['name']) &&
                          ($arg =
                              $this->get($constructorArgument['name'])) !== NULL):
                        $args[] = $arg;
                        break;
                    case isset($argsForObject[$key]):
                        $args[] = $argsForObject[$key];
                        break;
                    case isset($argsForObject[$constructorArgument['name']]):
                        $args[] = $argsForObject[$constructorArgument['name']];
                        break;
                    case isset($constructorArgument['default']):
                        $args[] = $constructorArgument['default'];
                        break;
                    default:
                        #$args[] = current($argsNotUsed);
                        #next($argsNotUsed);
                        $args[] = NULL;
                }
            }
            # add the argsNotUsed to the end, right?
            $args +=$argsNotUsed;
            $return = count($args) == 0 ? $classReflector->newInstance() :
                $classReflector->newInstanceArgs($args);
        } catch (\Exception $e) {
            try {
                $return = count($args) == 0 ? $classReflector->newInstance() :
                    $classReflector->newInstanceArgs($args);
            } catch (\Exception $e) {
                $return = NULL;
            }
        }
        return $return;
    }

    # todo : alias messes things up, somehing is really wonky when you set alias and it already exists etc, check the following cases : when DI::set('namespace') have been set before alias call, alias call with no original set before...
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
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function alias($alias, $original)
    {
        $this->store->{$alias} = $original;
        return $this;
    }
}

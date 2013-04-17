<?php
declare(encoding = "UTF8");
namespace DHP\dependencyInjection;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-30 16:51
 */
class DiProxy
{
    private $classToInstantiate = null;
    private $argumentsToConstructor = array();
    private $methodCalls = array();

    /**
     * Initiates the proxy.
     * @param String $class name of the class to, later on, instantiate
     */
    public function __construct($class)
    {
        $this->classToInstantiate = $class;
    }

    /**
     * Get the values needed to instantiate the class
     *
     * @return array
     */
    public function get()
    {
        return array(
            'class'   => $this->classToInstantiate,
            'args'    => $this->argumentsToConstructor,
            'methods' => $this->methodCalls
        );
    }

    /**
     * Adds a method to be called once the object has been instantiated.
     *
     * @param String $method
     * @param array  $args
     * @return $this
     */
    public function addMethodCall($method, $args = array())
    {
        $args                = !is_array($args) ? array($args) : $args;
        $this->methodCalls[] = (object)array('method' => $method, 'args' => $args);
        return $this;
    }

    /**
     * Sets the constructor arguments
     * @param array $args
     * @return $this
     */
    public function setArguments(Array $args)
    {
        $this->argumentsToConstructor = $args;
        return $this;
    }

}

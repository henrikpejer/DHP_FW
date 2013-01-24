<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\dependencyInjection;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 20:44
 */
interface DIProxyInterface {

    /**
     * Sets up the class so that it can instantiate the class
     *
     * @param $class String, class to proxy
     */
    function __construct($class);

    /**
     * Returns the values needed for the proxy to instantiate the class
     *
     * @return array[class,args,methods]
     */
    function get();

    /**
     * If we, for some reason, need to call a method on the object once it has been
     * instantiated, we add that method and arguments here. They will be called in the
     * order they where added.
     *
     * @param       $method String, name  of the method
     * @param array $args   arguments to that method
     *
     * @return mixed
     */
    function addMethodCall($method, $args = array());

    /**
     * This will set arguments needed when instantiating the object, ie arguments needed for
     * __construct.
     *
     * @param array $args array of args, in the order they will be called
     *
     * @return $this
     */
    function setArguments(Array $args);
}

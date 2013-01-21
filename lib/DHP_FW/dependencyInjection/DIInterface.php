<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\dependencyInjection;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 20:42
 */
interface DIInterface {

    /**
     * Adds the objects needed to DI to function
     *
     * @param Event $Event
     */
    function __construct(\DHP_FW\EventInterface $Event);

    /**
     * This method is responsible for getting and returning and object. If
     * the object is not already instantiated, it will try to create it for us.
     *
     * @param       $name what we want
     * @param array $args some arguments that we want to instantiate it with
     * @return mixed
     */
    # todo : should we have args here... really? Probably not, right?
    function get($name, array $args = array());

    /**
     * Sets an alias for a object already in the container
     *
     * @param $name
     * @param $reference
     * @return mixed
     */
    # todo : perhaps refactor away...?
    function addObjectAlias($name, $reference);

    /**
     * Adds an alias for a class already in the container
     *
     * @param $name
     * @param $reference
     * @return mixed
     */
    # todo : refactor away, right?
    function addClassAlias($name, $reference);

    /**
     * Adds an already instantiated object
     *
     * @param      $object
     * @param null $name An 'alias', if none given, the class of the object
     * @return mixed
     */
    function addObject($object, $name = NULL);

    /**
     * Adds a class that should be instantiated and returns that object. This is
     * so that the DI knows how to instantiate that class, once it is needed
     *
     * @param       $class
     * @param array $constructorArgs
     * @return mixed
     */
    function addClass($class, array $constructorArgs = array());

    /**
     * This will return an array with ONLY the names of the objects
     * not all the objects
     *
     * @return array
     */
    function getObjectsInDI();

    /**
     * Used to instantiate an object
     * @param       $class name of the class to instantiate
     * @param array $__args__ the args used to instantiate the clas
     * @return mixed
     */
    # todo : why is this not private....?
    function instantiateObject($class, array $__args__ = array());
}
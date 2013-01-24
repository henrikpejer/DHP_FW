<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\dependencyInjection;

    /**
     * User: Henrik Pejer mr@henrikpejer.com
     * Date: 2013-01-01 20:42
     */

/**
 * Lets redo this the right way. Big job, got to be done, no other way around it.
 *
 * The thought here being, that, we should have some sort of basic key -> value store
 * where we use the keys as hints to when we want to load something.
 *
 * This loading can either be a concrete class or an interface : if interface, we must
 * resolve to what that actually means.
 *
 */
interface DIInterface {

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
    function __construct(array $config = NULL);

    /**
     * Here we set a key, value. This will be used when we further down the road
     * need to get something.
     *
     * These will also be used when we want to instantiate things.
     *
     * @param $name  String of key, string
     * @param $value String could be anything
     *
     * @return DIProxyInterface instance, most likely a reference :)
     */
    function set($name, $value);

    /**
     * Here we set an alias for an key:
     *
     * alias('DHP_FW\Request','app\Request')
     *
     * means that if one asks for DHP_FWE/Request, you get app/request instead.
     *
     * Original has to exist already, or an exception will be thrown.
     *
     * @param $alias    String alias for...
     * @param $original String
     *
     * @return $this
     */
    function alias($alias, $original);

    /**
     * This will return whatever it is that we want. IF object has been loaded,
     * return that. If not, instantiate it and.... be happy with it!
     *
     * This will use the values in DIProxy to instantiate and if needed
     * call a few methods to setup whatever is needed for that object
     * to get it going
     *
     * @param $name String of object to load
     *
     * @return mixed
     */
    function get($name);

    /**
     * A short for set($name,$value)
     *
     * @param $name
     * @param $value
     *
     * @return mixed
     */
    function __set($name, $value);

    /**
     * A short for get($name);
     *
     * @param $name
     *
     * @return mixed
     */
    function __get($name);
}
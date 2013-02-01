<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:53
 *
 */
interface AppInterface {


    /**
     * Sets up the routes possible, setups the cacheObject
     *
     * @param \DHP_FW\RequestInterface                           $Request
     * @param dependencyInjection\DIInterface                    $DependencyInjector
     * @param \DHP_FW\Event|\DHP_FW\EventInterface               $event
     * @param RoutingInterface                                   $route
     *
     * @internal param \DHP_FW\dependencyInjection\DIInterface $DI $DI
     */
    function __construct(\DHP_FW\RequestInterface $Request, \DHP_FW\dependencyInjection\DIInterface $DependencyInjector, EventInterface $event, RoutingInterface $route);


    /**
     * Sets a config value to true
     *
     * @param $configToEnable name of config to set to true
     *
     * @return $this
     */
    function enable($configToEnable);

    /**
     * Checks if a config value is true or false
     *
     * @param $configToCheck
     *
     * @return boolean
     */
    function enabled($configToCheck);

    /**
     * Sets a config value to FALSE
     *
     * @param $configToDisable config value to set to FALSE
     *
     * @return $this
     */
    function disable($configToDisable);


    /**
     * Here we can add a middleware to be used, essentially a middleware is a plugin that
     * will be instantiated before routes have been resolved. For instance, session, is a
     * middleware.
     *
     * @param $middleware String, the name of the middleware we want to load...
     *
     * @return $this
     */
    function middleware($middleware);

    /**
     * This method starts the app, basicly resolves routes, call the current route.
     *
     * @return mixed
     */
    function start();
}

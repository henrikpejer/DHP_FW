<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:53
 *
 */
interface AppInterface {
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_ANY    = 'ANY';
    const ROUTE_CONTINUE     = 'YES';

    /**
     * Sets up the routes possible, setups the cacheObject
     *
     * @param \DHP_FW\RequestInterface                           $Request
     * @param \DHP_FW\dependencyInjection\DIInterface            $DI $DI
     * @param \DHP_FW\Event|\DHP_FW\EventInterface               $event
     */
    function __construct(\DHP_FW\RequestInterface $Request, \DHP_FW\dependencyInjection\DIInterface $DI, EventInterface $event);

    /**
     * Sets routes for GET-requests
     *
     * @param $uri     string, uri to match
     * @param $closure callable to call when matched
     *
     * @return mixed
     */
    function get($uri, callable $closure);

    /**
     * Sets routes for POST-requests
     *
     * @param $uri     string, uri to match
     * @param $closure callable to call when matched
     *
     * @return mixed
     */
    function post($uri, callable $closure);

    /**
     * Sets routes for DELETE-requests
     *
     * @param $uri     string, uri to match
     * @param $closure callable to call when matched
     *
     * @return mixed
     */
    function delete($uri, callable $closure);

    /**
     * Sets routes for PUT-requests
     *
     * @param $uri     string, uri to match
     * @param $closure callable to call when matched
     *
     * @return mixed
     */
    function put($uri, callable $closure);

    /**
     * Sets routes for HEAD-requests
     *
     * @param $uri     string, uri to match
     * @param $closure callable to call when matched
     *
     * @return mixed
     */
    function head($uri, callable $closure);

    /**
     * Sets routes for any and all types of requests
     *
     * @param $uri     string, uri to match
     * @param $closure callable to call when matched
     *
     * @return mixed
     */
    function any($uri, callable $closure);

    /**
     * Sets routes for an array of methods at the same time, ie possible
     * to set routes for GET, POST at the same time, unless any is suitable
     *
     * @param array $methods an array of methods to set this route for
     * @param       $uri
     * @param       $closure
     *
     * @return mixed
     */
    public function verb(array $methods, $uri, callable $closure);

    /**
     * Return the routes set.
     *
     * @return array
     */
    public function routes();

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
     * When we have named parts of a uri i routing, say admin/user/:user/edit, we here
     * set it up so that if a route with that particular named part is used
     * the result of the callable will be used as that method parameter value instead.
     *
     * In the example above, we use the value of :user, probably a user-id, loads that user
     * and returns that user to the controller method, thus injecting a user object.
     *
     * @param $paramName name of param in route uri
     * @param $closure   a closure to run when that param name is used
     *
     * @return mixed
     */
    function param($paramName, callable $closure);

    /**
     * This is used when a route is matched and called. When calling this, we make sure
     * that the routing will continue and find another match eventhough this one matched.
     *
     * This can be used, for instance, to make sure that all uris to admin are checked
     * if the user is logged in and have admin rights.
     *
     * Example
     *
     * $app->any('admin/*',function(){#executes on all admin/*routes});
     *
     * Since routes are matched on a first-come-first-serve-basis, make sure to have
     * these sort of routes EARLY in your route definitions
     *
     * @return mixed
     */
    function continueWithNextRoute();

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

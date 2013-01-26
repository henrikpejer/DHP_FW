<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:53
 *
 * App class, used to handle app routes and more.
 */
class App implements \DHP_FW\AppInterface {

    protected $routes = array();
    protected $configs = array('use_cache' => FALSE);
    protected $cacheObject = NULL;

    private $customParamTypes = array();
    private $CONTINUEROUTE = FALSE;

    public function __construct(\DHP_FW\RequestInterface $Request, \DHP_FW\dependencyInjection\DIInterface $DI, \DHP_FW\EventInterface $event) {
        $this->routes  =
                array(self::HTTP_METHOD_GET    => array(),
                      self::HTTP_METHOD_POST   => array(),
                      self::HTTP_METHOD_DELETE => array(),
                      self::HTTP_METHOD_PUT    => array(),
                      self::HTTP_METHOD_ANY    => array());
        $this->request = $Request;
        $this->DI      = $DI;
        $this->event   = $event;
        $this->setupCache();
    }


    /**
     * This will set up routing for a GET request. $closure will be called when a route
     * is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: get('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in the closure above to "hello world blog post"
     *
     * @param string   $uri
     * @param callable $closure
     * @return App|mixed
     */
    public function get($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_GET, $uri, $closure);
    }

    /**
     * This will set up routing for a POST request. $closure will be called when a route
     * is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: post('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in the closure above to "hello world blog post"
     * @param string   $uri
     * @param callable $closure
     * @return App|mixed
     */
    public function post($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_POST, $uri, $closure);
    }

    /**
     * This will set up routing for a DELETE request. $closure will be called when a route
     * is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: delete('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in the closure above to "hello world blog post"
     * @param string   $uri
     * @param callable $closure
     * @return App|mixed
     */
    public function delete($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_DELETE, $uri, $closure);
    }

    /**
     * This will set up routing for a PUT request. $closure will be called when a route
     * is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: put('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in the closure above to "hello world blog post"
     * @param string   $uri
     * @param callable $closure
     * @return App|mixed
     */
    public function put($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_PUT, $uri, $closure);
    }

    /**
     * This will set up routing for a HEAD request. $closure will be called when a route
     * is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: head('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in the closure above to "hello world blog post"
     * @param string   $uri
     * @param callable $closure
     * @return App|mixed
     */
    public function head($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_HEAD, $uri, $closure);
    }

    /**
     * This will set up routing for any type of request. $closure will be called when a route
     * is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: any('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in the closure above to "hello world blog post"
     *
     * @param string   $uri
     * @param callable $closure
     * @return App|mixed
     */
    public function any($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_ANY, $uri, $closure);
    }


    /**
     * This will set up routing for several types of requests at once. So if you want the same
     * route to get triggered for GET and POST-requests, the $methods parameter is an array
     * with POST and GET as values.
     * $closure will be called when a route
     * is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: verb(array('POST','GET'),'blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in the closure above to "hello world blog post"
     *
     * @param array $methods
     * @param       $uri
     * @param       $closure
     * @return App|mixed
     */
    public function verb(array $methods, $uri, callable $closure) {
        return $this->registerRoute($methods, $uri, $closure);
    }

    /**
     * Returns the current set routes
     * @return array
     */
    public function routes() {
        return $this->routes;
    }

    /**
     * @param String $configToEnable
     * @return App|AppInterface
     */
    public function enable($configToEnable) {
        return $this->configs[$configToEnable] = TRUE;
    }

    /**
     * Enable a config value. Sets that config to TRUE
     *
     * @param $configToCheck
     * @return bool
     */
    public function enabled($configToCheck) {
        $return = isset($this->configs[$configToCheck]) && $this->configs[$configToCheck] === TRUE ? TRUE : FALSE;
        return $return;
    }

    /**
     * Sets a config value to FALSE
     *
     * @param config $configToDisable
     * @return App|AppInterface
     */
    public function disable($configToDisable) {
        $this->configs[$configToDisable] = FALSE;
        return $this;
    }

    /**
     * Adds possibility to execute a closure when a certain parameter type
     * is matched for route. When closure is called, whatever is returned
     * will be passed to the route-method as a parameter.
     *
     * Lets say you want to have a user-object populated when a user-id
     * exists in a uri, then with this method you can add that functionality.
     *
     * Example:
     * url: http://example.com/admin/user/4/edit
     * route uri: admin/user/:userId/edit
     *
     * param('userId',function($userId){return loadUserWithID($userId);});
     *
     * The above code will execute the route code and whatever :userId was
     * will be switched for the value that the loadUserWithID-returns
     * @param string      $paramName
     * @param callable    $closure
     * @return this
     */
    public function param($paramName, callable $closure) {
        $this->customParamTypes[$paramName] = $closure;
        return $this;
    }

    /**
     * Adds the possibility to continue matching routes even though
     * we already found a match.
     *
     * This is usefull if we want to add a check if the user is logged
     * in and have admin rights for all uris that start with admin.
     *
     * Calling continueWithNextRoute would make app continue to look for
     * the next match.
     *
     * This must be called in every route that is not the final one.
     *
     * @return mixed|void
     */
    public function continueWithNextRoute() {
        $this->CONTINUEROUTE = self::ROUTE_CONTINUE;
    }

    /**
     * Loads middleware.
     *
     * Middleware sits 'in between' and adds extra functionality. Most
     * of the time it is something that will either set things up before
     * the controller is called or something that will react to events
     * fired in the application.
     *
     * This function will try to help you load middlewar, fi it cannot find
     * it. So you can do : middleware('Cache') and this method will look for
     *
     * + /Cache
     * + /app/middleware/Cache
     * + /DHP_FW/middleware/Cache
     *
     * Providing with the correct class, with namespace, will be the best option
     *
     * @param String $middleware class name that will get loaded with DI
     * @return App|AppInterface
     */
    # todo : figure out dependencies... or not?
    public function middleware($middleware) {
        if (!class_exists($middleware, TRUE)) {
            if (class_exists('DHP_FW\middleware\\' . $middleware, TRUE)) {
                $middleware = 'DHP_FW\middleware\\' . $middleware;
            }
            elseif (class_exists('app\middleware\\' . $middleware, TRUE)) {
                $middleware = 'app\middleware\\' . $middleware;
            }
        }
        $this->DI->get($middleware);
        return $this;
    }

    /**
     * This starts the application.
     *
     * Main responsibility is to star the route matching process and run
     * the routes matched.
     *
     * @return array|bool|mixed|null
     */
    public function start() {
        $uri = $this->request->getUri();
        # do we have a cache, of the data, for this request?
        $cacheForUri = $this->event->trigger('DHP_FW.app.cacheForRequest', $uri);
        if (isset($cacheForUri) && $cacheForUri != FALSE) {
            $this->response = $this->DI->get('DHP_FW\ResponseInterface');
            foreach ($cacheForUri['headers'] as $name => $value) {
                $this->response->header($name, $value);
            }
            $this->response->send($cacheForUri['data']);
            return TRUE;
        }
        $routesToProcess = isset($this->routes[$this->request->getMethod()]) ? array_merge($this->routes[self::HTTP_METHOD_ANY], $this->routes[$this->request->getMethod()]) : $this->routes[self::HTTP_METHOD_ANY];
        $uriToMatch      = trim($this->request->getUri(), '/');
        $routesMatched   = array();
        $cacheKey        = 'routes_' . $this->request->getMethod() . ':' . $uriToMatch;
        $routeKeys       = $this->cache_system($cacheKey);
        if ($routeKeys !== NULL && is_array($routeKeys)) {
            foreach ($routeKeys as $uri => $routeMatchReturn) {
                $routesMatched[] = array('closure' => $routesToProcess[$uri], 'route' => $routeMatchReturn);
            }
        }
        else {
            $routesMatched    = array();
            $routeKeysToCache = array();
            foreach ($routesToProcess as $uri => $closure) {
                $this->CONTINUEROUTE = FALSE;
                if (FALSE !== ($routeMatchReturn = $this->matchUriToRoute($uriToMatch, $uri))) {
                    $routeKeysToCache[$uri] = $routeMatchReturn;
                    $routesMatched[]        = array('closure' => $closure, 'route' => $routeMatchReturn);
                }
            }
            # save this in cache for later use, cache routes!
            $this->cache_system($cacheKey, $routeKeysToCache, 300);
        }
        $return = $this->runMatchedRoutes($routesMatched);
        return $return;
    }

    /**
     * Generic cache method. Used for getting / setting values.
     *
     *
     * @param String     $key
     * @param null       $value Use for cache write-through
     * @param null       $ttl TTL for this cache key
     * @return null
     */
    public function cache($key, $value = NULL, $ttl = NULL) {
        return $this->__setCache('app', $key, $value, $ttl);
    }

    /**
     * The key to delete from cache
     *
     * @param $key
     * @return mixed
     */
    public function cacheDelete($key) {
        return $this->__deleteCache('app', $key);
    }

    /**
     * This will flush all the cache
     */
    public function cache_flush() {
        if (isset($this->cacheObject)) {
            $this->cacheObject->bucket('app')->flush();
            $this->cacheObject->bucket('data')->flush();
            $this->cacheObject->bucket('sys')->flush();
        }
    }

    /**
     * Sets cache in the system-bucket.
     *
     * @param String     $key
     * @param null       $value
     * @param null       $ttl
     * @return null
     */
    private function cache_system($key, $value = NULL, $ttl = NULL) {
        return $this->__setCache('sys', $key, $value, $ttl);
    }

    /**
     * This is the generic setCache function. This uses the prefix to select
     * what bucket to use, then fetches the key for that bucket.
     *
     * @param  String    $prefix
     * @param  String    $key
     * @param null       $value
     * @param null       $ttl
     * @return null|mixed
     */
    private function __setCache($prefix, $key, $value = NULL, $ttl = NULL) {
        $return = NULL;
        if ($this->enabled('use_cache')) {
            if (isset($value)) {
                $value = $value !== NULL && is_callable($value) ? $value : function () use ($value) {
                    return $value;
                };
            }
            $return = $this->cacheObject->bucket($prefix)->get($key, $value, $ttl);
        }
        return $return;
    }

    /**
     * This will delete a key in a bucket.
     *
     * @param String $prefix
     * @param String $key
     * @return mixed
     */
    private function __deleteCache($prefix, $key) {
        return $this->cacheObject->bucket($prefix)->delete($key);
    }

    /**
     * Once we have found the routes that match, we run them here
     *
     * @param $routes
     * @return array|mixed|null
     */
    private function runMatchedRoutes($routes) {
        $return = NULL;
        foreach ($routes as $route) {
            $closure          = $route['closure'];
            $routeMatchReturn = $route['route'];
            if (is_array($routeMatchReturn)) {
                $closureResult = call_user_func_array($closure, $routeMatchReturn);
            }
            else {
                $closureResult = $closure();
            }
            switch (TRUE) {
                case is_array($closureResult) && isset($closureResult['controller']) && isset($closureResult['method']):
                    $controller = $this->loadController($closureResult);
                    if (is_array($routeMatchReturn)) {
                        $return = call_user_func_array(array($controller, $closureResult['method']), $routeMatchReturn);
                    }
                    else {
                        $return = $controller->$closureResult['method']();
                    }
                    break;
                default:
                    $return = $closureResult;
                    break;
            }
            if (self::ROUTE_CONTINUE !== $this->CONTINUEROUTE) {
                break;
            }

        }
        return $return;
    }

    /**
     * This function will load the controller ...
     * and run with it...?
     *
     * @param $controllerToLoad
     */
    private function loadController($controllerToLoad) {
        return $this->DI->instantiateObject('\\app\\controllers\\' . $controllerToLoad['controller']);
    }

    private function matchUriToRoute($__uri__, $routeUri) {
        $__haveParams__ = strpos($routeUri, ':');
        if ($__haveParams__ === FALSE && ($routeUri == $__uri__ || preg_match('#^' . str_replace('*', '.*', $routeUri) . '$#', $__uri__))) {
            return TRUE;
        }
        if (TRUE == $__haveParams__) {
            return $this->parseUriForParameters($__uri__, $routeUri);
        }
        return FALSE;
    }

    /*
     * This will parse a route, looking like this,
     * blog/:title
     * 
     * into
     * 
     * array('title'=>'value_in_url')
     */
    private function parseUriForParameters($uri, $routeUri) {
        # get parts of uri & routeUri, that is, split by /
        $routeUriParts = explode('/', trim($routeUri, '/'));
        $uriParts      = explode('/', trim($uri, '/'));
        if (sizeof($uriParts) != sizeof($routeUriParts)) {
            return FALSE;
        }
        $return = array();
        foreach ($routeUriParts as $index => $part) {
            if ($part != $uriParts[$index]) {
                if ($part{0} != ':') { #wrong route after all!
                    return FALSE;
                }
                $realValue =
                        $this->cleanUriPartForParam($uriParts[$index]);
                $return[]  =
                        $this->checkParameterType($part, $realValue);
            }
        }
        return $return;
    }

    /**
     * This will url-decode and normalize a part of a uri.
     *
     * - are treated as spaces ' '
     *
     * @param String $param
     * @return string
     */
    private function cleanUriPartForParam($param) {
        $param = str_replace('-', ' ', $param);
        $param = urldecode($param);
        return $param;
    }

    /**
     * Here a parameter type is checked against any custom types that might exist.
     * If a custom parameter type exist (:userId used in examples above), here
     * that closure will be called and whatever that closure returns, this method
     * returns.
     *
     * @param String $parameterType
     * @param String $paramValue
     * @return mixed
     */
    private function checkParameterType($parameterType, $paramValue) {
        $parameterType = str_replace(':', '', $parameterType);
        $return        = $paramValue;
        if (isset($this->customParamTypes[$parameterType])) {
            $return = call_user_func_array($this->customParamTypes[$parameterType], array($paramValue));
        }
        return $return;
    }

    /**
     * Generic internal method to register the routes.
     *
     * @param String   $httpMethod Should be one of the self::HTTP_METHOD_* constants, really
     * @param String   $uri
     * @param Callable $closure
     * @return App
     */
    private function registerRoute($httpMethod, $uri, callable $closure) {
        $this->event->trigger('DHP_FW.App.registerRoute', $httpMethod, $uri, $closure);
        if (!is_array($httpMethod)) {
            $httpMethod = array($httpMethod);
        }
        foreach ($httpMethod as $method) {
            $this->routes[$method][$uri] = $closure;
        }
        return $this;
    }

    /**
     * Gets cacheStorage, inits it and sets the cacheObject of!
     */
    public function setupCache() {
        if ($this->enabled('use_cache')) {
            $this->cacheObject = $this->DI->get('DHP_FW\cache\Cache');
            $this->cacheObject->bucket('app');
            $this->cacheObject->bucket('data');
            $this->cacheObject->bucket('sys');
        }
    }
}

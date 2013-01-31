<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-31 18:57
 */
class Routing implements RoutingInterface {

    private $event, $routes, $customParamTypes;
    private $CONTINUEROUTE = FALSE;

    /**
     * Sets up routing.
     *
     * Event is not necessary, but preferable.
     *
     * @param dependencyInjection\DIInterface $DependencyInjector
     * @param EventInterface                  $Event
     */
    public function __construct(\DHP_FW\dependencyInjection\DIInterface $DependencyInjector, EventInterface $Event = NULL) {
        $this->event              = $Event;
        $this->DependencyInjector = $DependencyInjector;
        $this->routes             = array(self::HTTP_METHOD_GET    => array(),
                                          self::HTTP_METHOD_POST   => array(),
                                          self::HTTP_METHOD_DELETE => array(),
                                          self::HTTP_METHOD_PUT    => array(),
                                          self::HTTP_METHOD_ANY    => array());

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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    public function param($paramName, callable $closure) {
        $this->customParamTypes[$paramName] = $closure;
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
     * Generic internal method to register the routes.
     *
     * @param String   $httpMethod Should be one of the self::HTTP_METHOD_* constants, really
     * @param String   $uri
     * @param          $closure
     * @param Callable $closure
     * @return \DHP_FW\Routing
     */
    private function registerRoute($httpMethod, $uri, callable $closure) {
        if (isset($this->event)) {
            $this->event->trigger('DHP_FW.App.registerRoute', $httpMethod, $uri, $closure);
        }
        if (!is_array($httpMethod)) {
            $httpMethod = array($httpMethod);
        }
        foreach ($httpMethod as $method) {
            $this->routes[$method][$uri] = $closure;
        }
        return $this;
    }

    /**
     * Used to match uri to routes
     *
     * @param $__uri__
     * @param $routeUri
     * @return array|bool
     */
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

    /**
     * This will parse a route, looking like this,
     * blog/:title
     *
     * into
     *
     * array('title'=>'value_in_url')
     *
     * @param $uri
     * @param $routeUri
     * @return array|bool
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
                $realValue = $this->cleanUriPartForParam($uriParts[$index]);
                $return[]  = $this->checkParameterType($part, $realValue);
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
     * returns routes matching the uri and the method
     *
     * @param String $method
     * @param String $uri
     * @return array
     */
    function match($method, $uri) {
        $routesToProcess = isset($this->routes[$method]) ? array_merge($this->routes[self::HTTP_METHOD_ANY], $this->routes[$method]) : $this->routes[self::HTTP_METHOD_ANY];
        $uriToMatch      = trim($uri, '/');
        $routesMatched   = array();
        foreach ($routesToProcess as $uri => $closure) {
            $this->CONTINUEROUTE = FALSE;
            if (FALSE !== ($routeMatchReturn = $this->matchUriToRoute($uriToMatch, $uri))) {
                $routesMatched[] = array('closure' => $closure, 'route' => $routeMatchReturn);
            }
        }
        return $this->runMatchedRoutes($routesMatched);
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
     * @return mixed
     */
    private function loadController($controllerToLoad) {
        return $this->DependencyInjector->instantiateObject('\\app\\controllers\\' . $controllerToLoad['controller']);
    }
}

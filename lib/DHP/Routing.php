<?php
declare(encoding = "UTF8");
namespace DHP;

use DHP\utility\Util;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-31 18:57
 */
class Routing
{
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_ANY    = 'ANY';
    const ROUTE_CONTINUE     = 'YES';
    private $routes = array(
        self::HTTP_METHOD_GET    => array(),
        self::HTTP_METHOD_POST   => array(),
        self::HTTP_METHOD_DELETE => array(),
        self::HTTP_METHOD_PUT    => array(),
        self::HTTP_METHOD_HEAD   => array(),
        self::HTTP_METHOD_ANY    => array()
    );
    private $allowedMethods = array(
        self::HTTP_METHOD_GET,
        self::HTTP_METHOD_POST,
        self::HTTP_METHOD_DELETE,
        self::HTTP_METHOD_PUT,
        self::HTTP_METHOD_HEAD,
        self::HTTP_METHOD_ANY
    );

    /**
     * returns routes matching the uri and the method
     *
     * @param String $method
     * @param String $uri
     *
     * @return array
     */
    public function match($method, $uri)
    {
        if (isset($this->routes[$method])) {
            $routesToProcess = array_merge(
                $this->routes[self::HTTP_METHOD_ANY],
                $this->routes[$method]
            );
        } else {
            $routesToProcess = $this->routes[self::HTTP_METHOD_ANY];
        }

        $uriToMatch    = trim($uri, '/');
        $routesMatched = array();
        foreach ($routesToProcess as $routeUri => $closure) {
            if (false !==
                    ($routeMatchReturn =
                            $this->matchUriToRoute($uriToMatch, $routeUri))
            ) {
                $routesMatched[] = array(
                    'closure' => $closure,
                    'route'   => $routeMatchReturn
                );
            }
        }
        return $routesMatched;
    }

    /**
     * Used to match uri to routes
     *
     * @param $uri
     * @param $routeUri
     *
     * @return array|bool
     */
    private function matchUriToRoute($uri, $routeUri)
    {
        $__haveParams__ = strpos($routeUri, ':');
        if ($__haveParams__ === false &&
                ($routeUri == $uri ||
                        preg_match(
                            '#^' . str_replace(
                                '*',
                                '.*',
                                $routeUri
                            ) . '$#',
                            $uri
                        )
                )
        ) {
            return array();
        }
        if (true == $__haveParams__) {
            return $this->parseUriForParameters($uri, $routeUri);
        }
        return false;
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
     *
     * @return array|bool
     */
    private function parseUriForParameters($uri, $routeUri)
    {
        # get parts of uri & routeUri, that is, split by /
        $routeUriParts = explode('/', trim($routeUri, '/'));
        $uriParts      = explode('/', trim($uri, '/'));
        if (count($uriParts) != count($routeUriParts)) {
            return false;
        }
        $return = array();
        foreach ($routeUriParts as $index => $part) {
            if ($part != $uriParts[$index]) {
                if ($part{0} != ':') { #wrong route after all!
                    return false;
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
     *
     * @return string
     */
    private function cleanUriPartForParam($param)
    {
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
     *
     * @return mixed
     */
    private function checkParameterType($parameterType, $paramValue)
    {
        $parameterType = str_replace(':', '', $parameterType);
        $return        = $paramValue;
        if (isset($this->customParamTypes[$parameterType])) {
            $return =
                    call_user_func_array(
                        $this->customParamTypes[$parameterType],
                        array($paramValue)
                    );
        }
        return $return;
    }

    public function makeRoutesForClass($controllerClass, $uriNamespace = null)
    {
        $controller = new \ReflectionClass($controllerClass);
        foreach ($controller->getMethods(\ReflectionMethod::IS_PUBLIC) as $controllerMethod) {
            $controllerMethodName = $controllerMethod->getName();
            $methodDocComments    = Util::methodDocComments($controllerMethod);
            if (isset($methodDocComments['method']) && isset($methodDocComments['uri'])) {
                $routeCall = array(
                    'controller' => $controllerClass,
                    'method'     => $controllerMethodName
                );
                $method    = explode(',', $methodDocComments['method']);
                $uri       = $methodDocComments['uri'];
                if (isset($uriNamespace)) {
                    $uri = trim($uriNamespace, '/') . '/' . trim($uri, '/');
                }
                $this->registerRoute($method, $uri, $routeCall);
            }
        }
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Registers a route with the routing module
     *
     * It also checks, somewhat convoluted, the method provided and matches it against
     * available methods
     *
     * @param String|array $httpMethods one or more methods this route is used for
     * @param String       $uri the uri for the route
     * @param mixed        $routeCall is either a callable or an array of controller, method
     */
    public function registerRoute($httpMethods, $uri, $routeCall)
    {
        $httpMethods =
                is_array($httpMethods) ? $httpMethods : array($httpMethods);
        $httpMethods = array_intersect($this->allowedMethods, $httpMethods);
        foreach ($httpMethods as $method) {
            if (in_array($method, $httpMethods)) {
                $this->routes[$method][$uri] = $routeCall;
            }
        }
    }
}

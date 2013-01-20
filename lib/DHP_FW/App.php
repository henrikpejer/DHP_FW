<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
use DHP_FW\dependencyInjection\DI;
use DHP_FW\Event;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:53
 *
 * App class, used to handle app routes and more.
 */
class App {

    protected $routes = array();
    protected $configs = array(
        'cacheStorage' => 'DHP_FW\\cache\\Apc',
    );
    protected $cache = NULL;

    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_ANY    = 'ANY';
    const ROUTE_CONTINUE     = 'YES';

    private $customParamTypes = array();
    private $CONTINUEROUTE = FALSE;

    public function __construct($Request, DI $DI, Event $event) {
        $this->routes  = array(
            self::HTTP_METHOD_GET    => array(),
            self::HTTP_METHOD_POST   => array(),
            self::HTTP_METHOD_DELETE => array(),
            self::HTTP_METHOD_PUT    => array(),
            self::HTTP_METHOD_ANY    => array()
        );
        $this->request = $Request;
        $this->DI      = $DI;
        $this->event   = $event;
        $this->setupCache();
    }

    # get routes
    public function get($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_GET, $uri, $closure);
    }

    #post routes
    public function post($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_POST, $uri, $closure);
    }

    #delete routes
    public function delete($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_DELETE, $uri, $closure);
    }

    #put routes
    public function put($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_PUT, $uri, $closure);
    }

    #head routes
    public function head($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_HEAD, $uri, $closure);
    }

    #any http method routes
    public function any($uri, callable $closure) {
        return $this->registerRoute(self::HTTP_METHOD_ANY, $uri, $closure);
    }

    public function verb(array $methods, $uri, $closure) {
        return $this->registerRoute($methods, $uri, $closure);
    }

    public function routes() {
        return $this->routes;
    }

    public function enable($configToEnable) {
        $this->configs[$configToEnable] = TRUE;
        return $this;
    }

    public function enabled($configToCheck) {
        return isset($this->configs[$configToCheck]) && $this->configs[$configToCheck] === TRUE ? TRUE : FALSE;
    }

    public function disable($configToDisable) {
        $this->configs[$configToDisable] = FALSE;
        return $this;
    }

    public function param($paramName, callable $closure) {
        $this->customParamTypes[$paramName] = $closure;
    }

    public function continueWithNextRoute() {
        $this->CONTINUEROUTE = self::ROUTE_CONTINUE;
    }

    # todo : figure out dependencies... or not?
    public function middleware($middleware) {
        if (!class_exists($middleware, TRUE)) {
            $middleware = '\\DHP_FW\\middleware\\' . $middleware;
        }
        if (!class_exists($middleware, TRUE)) {
            $middleware = '\\App\\middleware\\' . $middleware;
        }
        $this->DI->get($middleware);
        return $this;
    }

    public function start() {
        $routesToProcess =
                isset($this->routes[$this->request->getMethod()]) ? array_merge($this->routes[self::HTTP_METHOD_ANY], $this->routes[$this->request->getMethod()]) : $this->routes[self::HTTP_METHOD_ANY];
        $uriToMatch      = trim($this->request->getUri(), '/');
        $return          = NULL;
        foreach ($routesToProcess as $uri => $closure) {
            $this->CONTINUEROUTE = FALSE;
            if (FALSE !== ($routeMatchReturn = $this->matchUriToRoute($uriToMatch, $uri))) {
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
                            $return =
                                    call_user_func_array(array($controller, $closureResult['method']), $routeMatchReturn);
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
                $realValue = $this->cleanUriPartForParam($uriParts[$index]);
                $return[]  = $this->checkParameterType($part, $realValue);
            }
        }
        return $return;
    }

    private function cleanUriPartForParam($param) {
        $param = str_replace('-', ' ', $param);
        $param = urldecode($param);
        return $param;
    }

    private function checkParameterType($parameterType, $paramValue) {
        $parameterType = str_replace(':', '', $parameterType);
        $return        = $paramValue;
        if (isset($this->customParamTypes[$parameterType])) {
            $return = call_user_func_array($this->customParamTypes[$parameterType], array($paramValue));
        }
        return $return;
    }

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
     * Gets cacheStorage, inits it and sets the cache of!
     */
    private function setupCache() {
        $storage     = $this->DI->get($this->configs['cacheStorage']);
        $this->cache = $this->DI->get('\\DHP_FW\\cache\\Cache', array($storage));
        $this->cache->bucket('app');
        $this->cache->bucket('data');
        $this->cache->bucket('sys');
    }
}

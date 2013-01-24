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

    public function verb(array $methods, $uri, callable $closure) {
        return $this->registerRoute($methods, $uri, $closure);
    }

    public function routes() {
        return $this->routes;
    }

    public function enable($configToEnable) {
        $this->configs[$configToEnable] = TRUE;
        return $this->configs[$configToEnable];
    }

    public function enabled($configToCheck) {
        $return = isset($this->configs[$configToCheck]) && $this->configs[$configToCheck] === TRUE ? TRUE : FALSE;
        return $return;
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
        $routesMatched = array();
        $routeKeys = $this->cache_system('routes_'.$this->request->getMethod().':'.$this->request->getUri());
        if( !empty($routeKeys) ){
            var_dump($routeKeys);
            foreach($routeKeys as $uri => $routeMatchReturn){
                $routesMatched[] = array('closure'=>$routesToProcess[$uri],'route'=>$routeMatchReturn);
            }
        }else{
            $routesMatched = array();
            $routeKeysToCache = array();
            foreach ($routesToProcess as $uri => $closure) {
                $this->CONTINUEROUTE = FALSE;
                if (FALSE !== ($routeMatchReturn = $this->matchUriToRoute($uriToMatch, $uri))) {
                    $routeKeysToCache[$uri] = $routeMatchReturn;
                    $routesMatched[] = array('closure'=>$closure,'route'=>$routeMatchReturn);
                }
            }
        }
        $return = $this->runMatchedRoutes($routesMatched);
        # save this in cache for later use, cache routes!
        $this->cache_system('routes_'.$this->request->getMethod().':'.$this->request->getUri(),$routeKeysToCache,300);
        return $return;
    }

    public function cache($key, $value = NULL, $ttl = NULL) {
        return $this->__setCache('app',$key,$value,$ttl);
        $return = NULL;
        if ($this->enabled('use_cache')) {
            if (isset($value)) {
                $value = is_callable($value) ? $value : function () use ($value) {
                    return $value;
                };
            }
            $return = $this->cacheObject->bucket('app')->get($key, $value, $ttl);
        }
        return $return;
    }

    private function __setCache($prefix,$key,$value = NULL,$ttl = NULL){
        $return = NULL;
        if ($this->enabled('use_cache')) {
            if (isset($value)) {
                $value = is_callable($value) ? $value : function () use ($value) {
                    return $value;
                };
            }
            $return = $this->cacheObject->bucket($prefix)->get($key, $value, $ttl);
        }
        return $return;

    }

    private function cache_system($key, $value = NULL, $ttl = NULL){
        return $this->__setCache('sys',$key,$value,$ttl);
    }

    private function runMatchedRoutes($routes){
        $return = NULL;
        foreach($routes as $route){
            $closure = $route['closure'];
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

    private function cleanUriPartForParam($param) {
        $param = str_replace('-', ' ', $param);
        $param = urldecode($param);
        return $param;
    }

    private function checkParameterType($parameterType, $paramValue) {
        $parameterType = str_replace(':', '', $parameterType);
        $return        = $paramValue;
        if (isset($this->customParamTypes[$parameterType])) {
            $return =
                    call_user_func_array($this->customParamTypes[$parameterType], array($paramValue));
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

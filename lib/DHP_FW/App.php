<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
use DHP_FW\dependencyInjection\DI;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:53
 *
 * App class, used to handle app routes and more.
 */
class App {

    protected $routes = array();
    protected $configs = array();
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_ANY    = 'ANY';

    private $customParamTypes = array();


    public function __construct($Request, DI $DI) {
        $this->routes = array(
            self::HTTP_METHOD_GET    => array(),
            self::HTTP_METHOD_POST   => array(),
            self::HTTP_METHOD_DELETE => array(),
            self::HTTP_METHOD_PUT    => array(),
            self::HTTP_METHOD_ANY    => array()
        );
        $this->request = $Request;
        $this->DI = $DI;
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
        return isset($this->configs[$configToCheck]) && $this->configs[$configToCheck] === TRUE?TRUE:FALSE;
    }

    public function disable($configToDisable) {
        $this->configs[$configToDisable] = FALSE;
        return $this;
    }

    public function param($paramName, callable $closure){
        $this->customParamTypes[$paramName] = $closure;
    }

    public function start(){
        if(!isset($this->routes[$this->request->getMethod()])){
            return NULL;
        }
        foreach($this->routes[$this->request->getMethod()] as $uri => $closure){
            if( TRUE == $this->matchUriToRoute($uri)){
                $closureResult = $closure();
                switch(TRUE){
                    case is_array($closureResult) && isset($closureResult['controller']) && isset($closureResult['method']):
                        $controller = $this->loadController($closureResult);
                        return $controller->$closureResult['method']();
                        break;
                    default:
                        return $closureResult;
                        break;
                }
            }
        }
    }


    /**
     * This function will load the controller ...
     * and run with it...?
     *
     * @param $controllerToLoad
     */
    private function loadController($controllerToLoad){
        return $this->DI->instantiateObject('\\app\\controllers\\'.$controllerToLoad['controller']);
    }

    private function matchUriToRoute($routeUri){
        return $routeUri == $this->request->getUri()?TRUE:FALSE;
    }

    private function registerRoute($httpMethod, $uri, callable $closure) {
        if (!is_array($httpMethod)) {
            $httpMethod = array($httpMethod);
        }
        foreach ($httpMethod as $method) {
            $this->routes[$method][$uri] = $closure;
        }
        return $this;
    }
}

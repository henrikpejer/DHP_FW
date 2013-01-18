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
        $routesToProcess = isset($this->routes[$this->request->getMethod()])?array_merge($this->routes[self::HTTP_METHOD_ANY],$this->routes[$this->request->getMethod()]):$this->routes[self::HTTP_METHOD_ANY];
        $uriToMatch = trim($this->request->getUri(),'/');
        foreach($routesToProcess as $uri => $closure){
            if( FALSE !== ($routeMatchReturn = $this->matchUriToRoute($uriToMatch, $uri))){
                if( is_array($routeMatchReturn)){
                    $closureResult = call_user_func_array($closure,$routeMatchReturn);
                }else{
                    $closureResult = $closure();    
                }
                switch(TRUE){
                    case is_array($closureResult) && isset($closureResult['controller']) && isset($closureResult['method']):
                        $controller = $this->loadController($closureResult);
                        # todo: handle params in url and send them to controller
                        if( is_array($routeMatchReturn)){
                            return call_user_func_array(array($controller,$closureResult['method']),$routeMatchReturn);    
                        }else{
                            return $controller->$closureResult['method']();
                        }
                        break;
                    default:
                        return $closureResult;
                        break;
                }
            }
        }
        return NULL;
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

    # todo: handle params in url
    private function matchUriToRoute($__uri__,$routeUri){
        $__haveParams__ = strpos($routeUri,':');
        if($__haveParams__ === FALSE && $routeUri == $__uri__){
            return TRUE;
        }
        if( TRUE == $__haveParams__ ){
            return $this->parseUriForParameters($__uri__,$routeUri);
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
    private function parseUriForParameters($uri,$routeUri){
        # get parts of uri & routeUri, that is, split by /
        $routeUriParts = explode('/',trim($routeUri,'/'));
        $uriParts = explode('/',trim($uri,'/'));
        if(sizeof($uriParts) != sizeof($routeUriParts)){
            return FALSE;
        }
        $return = array();
        foreach($routeUriParts as $index => $part){
            if($part != $uriParts[$index]){
                if($part{0} != ':'){    #wrong route after all!
                    return FALSE;
                }
                $realValue = $this->cleanUriPartForParam($uriParts[$index]);
                $return[] = $this->checkParameterType($part,$realValue);
            }
        }
        return $return;
    }
    
    private function cleanUriPartForParam($param){
        $param = str_replace('-',' ',$param);
        $param = urldecode($param);
        return $param;
    }
    
    private function checkParameterType($parameterType,$paramValue){
        $parameterType = str_replace(':','',$parameterType);
        $return = $paramValue;
        if(isset($this->customParamTypes[$parameterType])){
            $return = call_user_func_array($this->customParamTypes[$parameterType],array($paramValue));
        }
        return $return;
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

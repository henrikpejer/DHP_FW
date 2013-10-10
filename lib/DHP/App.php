<?php
namespace DHP;

use DHP\Routing;
use DHP\blueprint\Module;
use DHP\dependencyInjection\DI;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 22:47
 */
class App extends Module
{

    protected $DependencyInjector = null;
    protected $routing = null;
    protected $request = null;
    protected $response = null;
    protected $routeNamespace = null;
    protected $stopRunningRoutes = true;
    protected $middlewares = array();

    /**
     * Sets up the app
     */
    public function __construct(
        DI $dependencyInjector,
        Routing $routing,
        Request $request,
        Response $response,
        Event $event
    ) {
        $this->configure          = new utility\Variables();
        $this->DependencyInjector = $dependencyInjector;
        $this->routing            = $routing;
        $this->request            = $request;
        $this->response           = $response;
        $this->event              = $event;
    }

    # todo : inject the router the app is currently using....?

    /**
     * Starts the app, find matching routes and invokes them
     */
    public function start($routesFile = null, $appConfig = null)
    {
        if (isset($routesFile)) {
            $this->loadRoutes($routesFile);
        }
        if (isset($appConfig)) {
            $this->loadAppConfig($appConfig);
        }
        $routes = $this->routing->match(
            $this->request->method,
            $this->request->uri
        );

        $that        = $this;
        $nextClosure = function () use ($that) {
            $that->stopRunningRoutes = false;
        };

        foreach ($routes as $route) {
            $this->stopRunningRoutes = true;

            $routeCallable = $route['closure'];
            if (is_array($route['closure']) &&
                isset($route['closure']['controller']) &&
                isset($route['closure']['method'])
            ) {
                $controller = $this->DependencyInjector->get($route['closure']['controller']);

                $routeCallable = array($controller, $route['closure']['method']);
            }
            $args = $route['route'];
            array_push($args, $nextClosure, $this->DependencyInjector);
            call_user_func_array($routeCallable, $args);
            if ($this->stopRunningRoutes) {
                break;
            }
        }
    }

    /**
     * Load routes from routes file, automatically set to routes.php in app root
     */
    private function loadRoutes($routesFile)
    {
        $this->routing->loadRoutes($routesFile);
    }

    /**
     * Load configs for app, automatically set to appConfig.php in app root
     */
    private function loadAppConfig($appConfigFile)
    {
        /** @noinspection PhpIncludeInspection */
        $configs = require_once $appConfigFile;
        foreach ($configs['controllers'] as $controller) {
            $controller[1] = isset($controller[1]) ? $controller[1] : null;
            $this->addController($controller[0], $controller[1]);
        }
        foreach ($configs['middleware'] as $middleware) {
            $middleware[1] = isset($middleware[1]) ? $middleware[1] : '';
            $this->apply($middleware[0], $middleware[1]);
        }
    }

    /**
     * Adds a controller file to the application.
     * @param String $controller
     * @param String $uriNamespace a namespace for the controller
     */
    public function addController($controller, $uriNamespace = null)
    {
        $this->routing->makeRoutesForClass($controller, $uriNamespace);
    }

    /**
     * This method lets us apply a module, component or middleware to the application
     * @param mixed $whatToApply an object or name of class (string) to apply
     */
    public function apply($whatToApply)
    {
        if (is_string($whatToApply)) {
            $whatToApply = $this->DependencyInjector->get($whatToApply);
        }
        if (is_a($whatToApply, '\DHP\blueprint\Middleware')) {
            $whatToApply->run();
        }
    }
}

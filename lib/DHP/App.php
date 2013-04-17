<?php
declare(encoding = "UTF8");
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
        Response $response
    ) {
        $this->configure          = new utility\Variables();
        $this->DependencyInjector = $dependencyInjector;
        $this->routing            = $routing;
        $this->request            = $request;
        $this->response           = $response;
    }

    # todo : inject the router the app is currently using....?

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
     */
    public function apply($whatToApply, $namespace = '')
    {
        if (is_a($whatToApply, '\DHP\blueprint\Middleware')) {
            $whatToApply->run();
        }
    }

    /**
     * Starts the app, find matching routes and invokes them
     */
    public function start()
    {
        $routes      = $this->routing->match(
            $this->request->getMethod(),
            $this->request->getUri()
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
            array_push($args, $nextClosure);
            call_user_func_array($routeCallable, $args);
            if ($this->stopRunningRoutes) {
                break;
            }
        }
    }
}

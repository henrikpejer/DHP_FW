<?php
declare(encoding = "UTF8");
namespace DHP\blueprint;

use DHP\Routing;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-30 16:36
 */
abstract class Module
{

    private $uriPrefix = '';
    private $routing = NULL;

    /**
     * Sets up the dependency on Routing
     *
     * @param Routing $routing
     */
    public function __construct(Routing $routing)
    {
        $this->routing = $routing;
    }

    /**
     * Used to set up a GET route for the module
     *
     * @param String   $uri
     * @param callable $callable
     */
    public function get($uri, callable $callable)
    {
        $this->routing->registerRoute(
            array(Routing::HTTP_METHOD_GET),
            $this->genUri($uri),
            $callable
        );
    }

    /**
     * Used to set up a POST route for the module
     *
     * @param String   $uri
     * @param callable $callable
     */
    public function post($uri, callable $callable)
    {
        $this->routing->registerRoute(
            array(Routing::HTTP_METHOD_POST),
            $this->genUri($uri),
            $callable
        );
    }

    /**
     * Used to set up a DELETE route for the module
     *
     * @param String   $uri
     * @param callable $callable
     */
    public function delete($uri, callable $callable)
    {
        $this->routing->registerRoute(
            array(Routing::HTTP_METHOD_DELETE),
            $this->genUri($uri),
            $callable
        );
    }


    /**
     * Used to set up a PUT route for the module
     *
     * @param String   $uri
     * @param callable $callable
     */
    public function put($uri, callable $callable)
    {
        $this->routing->registerRoute(
            array(Routing::HTTP_METHOD_PUT),
            $this->genUri($uri),
            $callable
        );
    }

    # todo : Can we return an object that will take all calls, call it on the master object but somehow set a prefix on a route...?
    /**
     *
     *
     * @param $namespace
     * @return $this
     */
    public function uriNamespace($namespace)
    {
        $this->uriPrefix = trim($namespace, '/ ') . '/';
        return $this;
    }

    /**
     * Generates URI. Mostly used together with a prefix.
     *
     * @param $uri
     * @return string
     */
    private function genUri($uri)
    {
        return $this->uriPrefix . trim($uri, '/ ');
    }
}

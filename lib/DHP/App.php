<?php
declare(encoding = "UTF8");
namespace DHP;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 22:47
 */
class App {

    /**
     * Sets up the app
     */
    public function __construct() {
        $this->settings = new utility\Variables();
    }

    /**
     * Used to set up a GET route for the app
     *
     * @param String   $uri
     * @param callable $callable
     */
    public function get($uri, callable $callable) {

    }

    /**
     * Used to set up a POST route for the app
     *
     * @param String   $uri
     * @param callable $callable
     */
    public function post($uri, callable $callable) {

    }

    /**
     * Used to set up a DELETE route for the app
     *
     * @param String   $uri
     * @param callable $callable
     */
    public function delete($uri, callable $callable) {

    }


    /**
     * Used to set up a PUT route for the app
     *
     * @param String   $uri
     * @param callable $callable
     */
    public function put($uri, callable $callable) {

    }


}

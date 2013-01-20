<?php
namespace{
    /**
     * User: Henrik Pejer mr@henrikpejer.com
     * Date: 2013-01-05 21:44
     */

ob_start();
date_default_timezone_set('Europe/Stockholm');
require_once 'lib/splClassLoader.php';
$classLoader = new SplClassLoader('DHP_FW', __DIR__ . DIRECTORY_SEPARATOR . 'lib');
$classLoader->register();
}
namespace app{
    $event = new \DHP_FW\Event();
    $di = new \DHP_FW\dependencyInjection\DI($event);
    $app = $di->get('\\DHP_FW\App');
    function next(){
        global $app;
        $app->continueWithNextRoute();
    }
    function getApp(){
        global $app;
        return $app;
    }
    function DI(){
        global $di;
        return $di;
    }
}
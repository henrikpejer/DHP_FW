<?php
namespace {
    declare(encoding = "UTF8") ;
    define('DHP_FW_BENCHMARK_TIMESTART', microtime(TRUE));
    define('DHP_FW_BENCHMARK_MEMORYSTART', memory_get_peak_usage());
    use DHP_FW\Request;
    use DHP_FW\App;
    use DHP_FW\dependencyInjection\DI;
    use DHP_FW\Utils;

    /**
     * User: Henrik Pejer mr@henrikpejer.com
     * Date: 2013-01-01 05:29
     */
# include loader
#require_once __DIR__.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'splClassLoader.php';
    require 'functions.php';
    require_once 'lib/splClassLoader.php';

    $classLoader = new SplClassLoader('DHP_FW', __DIR__ . DIRECTORY_SEPARATOR . 'lib');
    $classLoader->register();

    $event = new \DHP_FW\Event();
    $di    = new DI($event);
    $di->addClass('DHP_FW\\Request');
    $di->addClassAlias('Request', 'DHP_FW\\Request');
    $di->addClass('DHP_FW\\Response');
    $di->addClassAlias('Response', 'DHP_FW\\Response');
    $di->addClass('DHP_FW\\App');
    $di->addClassAlias('app', 'DHP_FW\\App');
    $app = $di->get('app');
    if ($di->get('Response')->checkCache()) {
        exit;
    }

#$di->addObjectAlias('event', 'DHP_FW\\Event');

    $appControllerLoader = new SplClassLoader('app', dirname($_SERVER['SCRIPT_FILENAME']));
    $appControllerLoader->register();


}
namespace app {
    function next() {
        global $app;
        $app->continueWithNextRoute();
    }

    function getApp() {
        global $app;
        return $app;
    }

    function DI() {
        global $di;
        return $di;
    }
}
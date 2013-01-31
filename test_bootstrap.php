<?php
namespace {
    /**
     * User: Henrik Pejer mr@henrikpejer.com
     * Date: 2013-01-05 21:44
     */

    ob_start();
    date_default_timezone_set('Europe/Stockholm');
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib/splClassLoader.php';
    $classLoader = new SplClassLoader('DHP_FW', __DIR__ . DIRECTORY_SEPARATOR . 'lib');
    $classLoader->register();

    $DI      = new DHP_FW\dependencyInjection\DI();
    $storage = $DI->get('DHP_FW\cache\Apc');
    $DI->set('DHP_FW\cache\Cache', 'DHP_FW\cache\Cache')
            ->setArguments(array($storage));
    $DI->set('DHP_FW\RoutingInterface','DHP_FW\Routing');
    $app                 = $DI->get('DHP_FW\AppInterface');
    $appControllerLoader = new SplClassLoader('app', __DIR__ . DIRECTORY_SEPARATOR);
    $appControllerLoader->register();
}
namespace app {
    $event = new \DHP_FW\Event();
    #$di = new \DHP_FW\dependencyInjection\DI($event);
    $app = $DI->get('DHP_FW\App');
    function next() {
        global $app;
        $app->continueWithNextRoute();
    }

    function getApp() {
        global $app;
        return $app;
    }

    function &DI() {
        global $DI;
        return $DI;
    }
}
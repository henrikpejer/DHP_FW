<?php
declare( encoding = "UTF8" ) ;
namespace {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib/splClassLoader.php';
    $classLoader = new SplClassLoader( 'DHP_FW', __DIR__ . DIRECTORY_SEPARATOR . 'lib' );
    $classLoader->register();

    $DI = new DHP_FW\dependencyInjection\DI();
    # $DI->set('DHP_FW\cache\Memcached','DHP_FW\cache\Memcached')->setArguments(array(array(array('localhost',11211))));
    $storage     = $DI->get('DHP_FW\cache\Apc');
    $DI
      ->set('DHP_FW\cache\Cache', 'DHP_FW\cache\Cache')
      ->setArguments(array($storage));
    $app = $DI->get('DHP_FW\AppInterface');
    $appControllerLoader = new SplClassLoader( 'app', dirname($_SERVER['SCRIPT_FILENAME']) );
    $appControllerLoader->register();

}
namespace app {
    function next(){
        global $app;
        $app->continueWithNextRoute();
    }

    function getApp(){
        global $app;
        return $app;
    }

    function DI(){
        global $DI;
        return $DI;
    }
}
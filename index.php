<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 23:00
 */
ini_set('DISPLAY_ERRORS', 1);
error_reporting(E_ALL);
require_once 'bootstrap.php';
$loader = new SplClassLoader('app', __DIR__);
$loader->register();
$di = new DHP\dependencyInjection\DI();
$di->set('DHP\Request')->addMethodCall('setupWithEnvironment');

($di->set('DHP\modules\Propel')->setArguments(array(2=>'api',3=>'config',4=>'includeDir')));
$di->get('DHP\modules\Propel');

$response = $di->get('DHP\Response');
$app = $di->get('DHP\App');
$app->start(
    __DIR__ . DIRECTORY_SEPARATOR . 'app/config/routes.php',
    __DIR__ . DIRECTORY_SEPARATOR . 'app/config/app.php'
);

$t     = microtime(true) - DHP_BENCHMARK_START;
$micro = sprintf("%06d", ($t - floor($t)) * 1000000);
$d     = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));

$response->addHeader('Generated-By','DHP FW');
$response->AddHeader('RAM-Usage',sprintf(
    "%0.3f Mb",
    ((memory_get_peak_usage() - DHP_BENCHMARK_MEMORY) / 1024) / 1024
) );
$response->addHeader('Page Time',sprintf("%0.3f seconds", $d->format('s.u'))."\n");
$response->send();
echo "\n";
foreach((array)$di->store as $key => $value){
    if(strpos($key,'Response') !== FALSE){
        var_dump(str_replace('0','',spl_object_hash($value)." : {$key}"));
    }
}

#var_dump($di->get('DHP\Response'));
#var_dump($di->get('DHP\Routing')->getRoutes());
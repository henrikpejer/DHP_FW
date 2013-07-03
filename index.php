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
$app = $di->get('DHP\App');
$app->start(
    __DIR__ . DIRECTORY_SEPARATOR . 'app/config/routes.php',
    __DIR__ . DIRECTORY_SEPARATOR . 'app/config/app.php'
);

echo sprintf(
    "%0.3f Mb",
        ((memory_get_peak_usage() - DHP_BENCHMARK_MEMORY) / 1024) / 1024
) . ' ';
$t     = microtime(true) - DHP_BENCHMARK_START;
$micro = sprintf("%06d", ($t - floor($t)) * 1000000);
$d     = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));
echo sprintf("%0.5f seconds", $d->format('s.u'))."\n";
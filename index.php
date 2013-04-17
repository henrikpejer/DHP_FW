<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 23:00
 */
require_once 'bootstrap.php';
$loader = new SplClassLoader('app',__DIR__);
$loader->register();
$di = new DHP\dependencyInjection\DI();
$di->set('DHP\Request')->addMethodCall('setupWithEnvironment');
$app = $di->get('app\App');
# $app = new \DHP\App();
$app->configure->henrik = 'pejer';
$app->configure->henrik('test','pejers');
$app->configure->setEnvironment('test');

$app->start();

echo sprintf("%0.3f Mb",((memory_get_peak_usage()-DHP_BENCHMARK_MEMORY)/1024)/1024).' ';
$t = microtime(TRUE) - DHP_BENCHMARK_START;
$micro = sprintf("%06d",($t - floor($t)) * 1000000);
$d = new DateTime(date('Y-m-d H:i:s.'.$micro,$t));
echo sprintf("%0.5f seconds",$d->format('s.u'));
<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-02 21:28
 */

require_once 'bootstrap.php';
#header('Status: 404 Not Found');
#var_dump($_SERVER);
#var_dump($_ENV);
#var_dump($di->get('request'));

$app->get('testing/hesting/flecking',function(){
    echo "Fake controller run!\n";
});

$app->get('blog',function(){
   return array('controller'=>'Blog','method'=>'index');
});
$app->start();
register_shutdown_function(function(){
    echo "\n\n";
    printf('time: %.4F s, memory: %.4F MB',(microtime(TRUE) - DHP_FW_BENCHMARK_TIMESTART),(((memory_get_peak_usage(TRUE) - DHP_FW_BENCHMARK_MEMORYSTART)/1024)/1024));
    echo "\n";
});
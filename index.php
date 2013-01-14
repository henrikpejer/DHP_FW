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


$app->get('blog/img',function(){
   return array('controller'=>'Blog','method'=>'img');
});


$app->get('blog/downloadimg',function(){
   return array('controller'=>'Blog','method'=>'downloadImg');
});
$di->get('event')->register('DHP_FW.Response.send',function($status,&$data){
    $data .= "\n\n";
    $data .= sprintf('time: %.4F s, memory: %.4F MB',(microtime(TRUE) - DHP_FW_BENCHMARK_TIMESTART),(((memory_get_peak_usage(TRUE) - DHP_FW_BENCHMARK_MEMORYSTART)/1024)/1024));
    $data .= "\n";
});


$app->start();
<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-02 21:28
 */

require_once 'lib/splClassLoader.php';

$classLoader = new SplClassLoader('DHP_FW', __DIR__ . DIRECTORY_SEPARATOR . 'lib');
$classLoader->register();

$DI = new DHP_FW\dependencyInjection\DI();

$DI->set('DHP_FW\EventInterface','\StdClass');
$DI->alias('event','DHP_FW\EventInterface');
var_dump($DI->get('event'));
/*
require_once 'bootstrap.php';
#header('Status: 404 Not Found');
#var_dump($_SERVER);
#var_dump($_ENV);
#var_dump($di->get('request'));
$app->middleware('Benchmark')->middleware('Cookie')->middleware('Session');

$app->get('testing/hesting/flecking',function(){
    echo "Fake controller run!\n";
});

$app->any('blog',function(){
   return array('controller'=>'Blog','method'=>'index');
});


$app->any('blog/page/:title',function(){
   return array('controller'=>'Blog','method'=>'page');
});

$app->get('blog/img',function(){
   return array('controller'=>'Blog','method'=>'img');
});


$app->get('blog/downloadimg',function(){
   return array('controller'=>'Blog','method'=>'downloadImg');
});
$app->start();*/
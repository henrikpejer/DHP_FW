<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-02 21:28
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'bootstrap.php';
#$e = $DI->get('DHP_FW\EventInterface');
#$e->register('test',function(){
#    echo 'done';
#});
#var_dump(spl_object_hash($e));
#var_dump($req);
#$e->trigger('test');

#require_once 'bootstrap.php';
#header('Status: 404 Not Found');
#var_dump($_SERVER);
#var_dump($_ENV);
#var_dump($di->get('request'));
$app->middleware('Benchmark');
$app->middleware('Cookie');
$app->middleware('Session');

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
$app->start();
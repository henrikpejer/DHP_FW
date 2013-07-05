<?php
# require_once 'bootstrap.php';
// lets automagicly try to include all lib/DHP files, ok?
$autoIncludeRecursive = function ($root) {
    if (file_exists($root) && is_file($root)){
        require_once $root;
        return true;
    }
    $dh = opendir($root);
    while ($file = readdir($dh)) {
        if ($file{0} == '.') {
            continue;
        }
        $path = $root . DIRECTORY_SEPARATOR . $file;
        if (is_file($path) && substr($file, -4) == '.php') {
            require_once $path;
        }
    }
};

$autoIncludeRecursive('lib/DHP/blueprint');
$autoIncludeRecursive('lib/DHP/Component');
$autoIncludeRecursive('lib/DHP/middleware');
$autoIncludeRecursive('lib/DHP/utility');
$autoIncludeRecursive('lib/DHP/dependencyInjection');
$autoIncludeRecursive('lib/DHP/EventInterface.php');
$autoIncludeRecursive('lib/DHP');
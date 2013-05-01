<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 22:33
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';
require_once __DIR__ . '/lib/splClassLoader.php';
$loader = new SplClassLoader('DHP', __DIR__ . DS . 'lib');
$loader->register();

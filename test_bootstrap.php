<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-05 21:44
 */
date_default_timezone_set('Europe/Stockholm');
require_once 'lib/splClassLoader.php';
$classLoader = new SplClassLoader('DHP_FW', __DIR__ . DIRECTORY_SEPARATOR . 'lib');
$classLoader->register();
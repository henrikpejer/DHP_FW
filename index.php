<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 23:00
 */
require_once 'bootstrap.php';
$app = new \DHP\App();

$app->settings->henrik = 'pejer';
$app->settings->henrik('test','pejers');
$app->settings->__setEnvironment('test');
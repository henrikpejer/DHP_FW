<?php
declare(encoding = "UTF8") ;
namespace app\controllers;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-05 16:15
 */
class Blog extends \DHP_FW\Controller{

    public function index(){
        echo "Blog controller running...";
    }

    public function img(){
        $this->response->sendFile(__DIR__.'/../img.jpg');
    }

    public function downloadImg(){
        $this->response->downloadFile(__DIR__.'/../img.jpg');
    }
}
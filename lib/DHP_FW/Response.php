<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:35
 */
class Response {

    public function send(){}
    
    public function header(){}
    
    public function status(){}
    
    public function sendFile($path, $mimeType){}
}

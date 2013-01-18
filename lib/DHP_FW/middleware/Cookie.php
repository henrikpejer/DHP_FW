<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\middleware;
use DHP_FW\Request;
use DHP_FW\Response;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-18 23:40
 */
class Cookie {
    private $req = NULL;
    function __construct(Request $req){
        $this->req = $req;

        $this->req->cookie = array('something'=>'Hey, how are ya?');
    }
}

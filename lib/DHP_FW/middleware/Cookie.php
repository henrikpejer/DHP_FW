<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\middleware;
use DHP_FW\Request;
use DHP_FW\Response;
use \DHP_FW\ParameterBagTestReadOnly;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-18 23:40
 */
class Cookie {
    private $req = NULL;
    function __construct(Request $req){
        $this->req = $req;

        $this->req->cookie = new \DHP_FW\ParameterBagReadOnly($_COOKIE);
    }
}

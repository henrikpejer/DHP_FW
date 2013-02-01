<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\middleware;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-18 23:40
 */
class Cookie implements MiddlewareInterface {
    private $req = NULL;

    function __construct(\DHP_FW\RequestInterface $req,
        \DHP_FW\EventInterface $event) {
        $this->req = $req;

        # todo : refactor away this... DARN IT! ;)
        $this->req->cookie =
          new \DHP_FW\ParameterBagReadOnly( $_COOKIE, $event );
    }
}

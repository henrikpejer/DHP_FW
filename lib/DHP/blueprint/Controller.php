<?php
declare(encoding = "UTF8");
namespace DHP\blueprint;

use DHP\Request;
use DHP\Response;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-31 21:59
 */
class Controller
{

    protected $request, $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }
}

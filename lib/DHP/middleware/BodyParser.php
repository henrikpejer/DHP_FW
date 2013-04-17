<?php
declare(encoding = "UTF8");
namespace DHP\middleware;

use DHP\Request;
use DHP\blueprint\Middleware;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-04-01 12:06
 */
class BodyParser extends Middleware
{

    private $request = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run()
    {
        $bodyData     = $this->request->getBody();
        $jsonDataTest = json_decode($bodyData);
        if (isset($jsonDataTest)) {
            $this->request->setBody($jsonDataTest);
        }
    }
}

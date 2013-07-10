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

    private $request = NULL;

    /**
     * Set up the body parser, injects the request object since we rely on it
     * to get and set the body of the request.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run()
    {
        $bodyData     = $this->request->body;
        $jsonDataTest = json_decode($bodyData, TRUE, 512, JSON_BIGINT_AS_STRING);
        if (isset($jsonDataTest)) {
            $this->request->setBody($jsonDataTest);
        }
    }
}

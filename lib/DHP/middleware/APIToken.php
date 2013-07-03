<?php
declare(encoding = "UTF8");
namespace DHP\middleware;
use DHP\Event;
use DHP\Request;

/**
 * A very basic token handler, complete with callbacks and other nifty things
 *
 * User: Henrik Pejer mr@henrikpejer.com
 */
class APIToken
{

    private $request, $event;

    public function __construct(Request $request,Event $event)
    {
        $this->request = $request;
        $this->event = $event;
        $this->handleRequest();
    }

    private function handleRequest()
    {
        $headers = $this->request->getHeaders();
        switch(true){
            case isset($headers['X-Auth-Token']):
                # todo: Send a auth-token event and either run a not allowed or continue
                break;
            case isset($headers['X-Auth']):
                # todo: send a auth request event and either continue or stop
                break;
        }
    }
}
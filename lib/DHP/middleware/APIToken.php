<?php
declare(encoding = "UTF8");
namespace DHP\middleware;

use DHP\blueprint\Middleware;
use DHP\Event;
use DHP\Request;
use DHP\Response;

/**
 * A very basic token handler, complete with callbacks and other nifty things
 *
 * User: Henrik Pejer mr@henrikpejer.com
 */
class APIToken extends Middleware
{

    private $request;
    private $event;

    /**
     * @param Request  $request
     * @param Event    $event
     * @param Response $response
     */
    public function __construct(Request $request, Event $event, Response $response)
    {
        $this->request  = $request;
        $this->event    = $event;
        $this->response = $response;
    }

    public function run()
    {
        $headers = $this->request->headers;
        switch (true) {
            case isset($headers['X-Auth-Token']):
                $xAuthTokenReturn = $this->event->trigger(
                    'APIToken.XAuthToken',
                    $headers['X-Auth-Token']
                );
                switch (true) {
                    case $xAuthTokenReturn === false:
                        throw new \RuntimeException("Not allowed");
                        break;
                    default:
                        break;
                }
                break;
            case isset($headers['X-Auth-User']) && isset($headers['X-Auth-Password']):
                $xAuthUserEventReturn = $this->event->trigger(
                    'APIToken.XAuthToken',
                    $headers['X-Auth-User'],
                    $headers['X-Auth-Password']
                );
                switch (true) {
                    case $xAuthUserEventReturn === false:
                    case $xAuthUserEventReturn === null:
                        throw new \RuntimeException("Not allowed");
                        break;
                    default:
                        $this->response->addHeader('X-AUTH-TOKEN', $xAuthUserEventReturn);
                }
        }
    }
}

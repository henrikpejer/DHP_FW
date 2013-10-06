<?php
declare(encoding = "UTF8");
namespace DHP\middleware;

use DHP\blueprint\Middleware;
use DHP\Event;
use DHP\Request;

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
     * @param Request $request
     * @param Event   $event
     */
    public function __construct(Request $request, Event $event)
    {
        $this->request = $request;
        $this->event   = $event;
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
                        # todo : make it so that we convey a meaningful message stating what the new token is, right? And if so, we need to include response here also
                        #echo "Token is: ".$XAuthUserEventReturn;
                }
        }
    }
}

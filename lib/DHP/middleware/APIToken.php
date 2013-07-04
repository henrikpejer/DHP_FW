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

    private $request, $event;

    public function __construct(Request $request, Event $event)
    {
        $this->request = $request;
        $this->event = $event;
    }

    public function run()
    {
        $headers = $this->request->getHeaders();
        switch (true) {
            case isset($headers['X-Auth-Token']):
                $XAuthTokenEventReturn = $this->event->trigger('APIToken.XAuthToken', $headers['X-Auth-Token']);
                switch (true) {
                    case $XAuthTokenEventReturn === FALSE:
                        throw new \RuntimeException("Not allowed");
                        break;
                    default:
                        break;
                }
                break;
            case isset($headers['X-Auth-User']) && isset($headers['X-Auth-Password']):
                $XAuthUserEventReturn = $this->event->trigger('APIToken.XAuthToken', $headers['X-Auth-User'],$headers['X-Auth-Password']);
                switch(true){
                    case $XAuthUserEventReturn === FALSE:
                    case $XAuthUserEventReturn === NULL:
                        throw new \RuntimeException("Not allowed");
                        break;
                    default:
                        # todo : make it so that we convey a meaningful message stating what the new token is, right?
                        #echo "Token is: ".$XAuthUserEventReturn;
                }
                break;
        }
    }
}
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
                if ($this->event->trigger('APIToken.XAuthToken', $headers['X-Auth-Token']) === FALSE) {
                    # todo : perhaps we should have response be the last catcher of exceptions... right?
                    throw new \RuntimeException("Not allowed");
                }
                break;
            case isset($headers['X-Auth']):
                # todo: send a auth request event and either continue or stop
                break;
        }
    }
}
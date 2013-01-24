<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\middleware;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-24 18:52
 */
class Cache implements MiddlewareInterface {

    private $request, $response, $app, $currentResponse = array('headers' => array(), 'data' => array());

    /**
     * Inits cacheObject middleware
     *
     * This will listen to events and trigger certain parts such as rendered pages etc
     *
     * @param \DHP_FW\RequestInterface  $request
     * @param \DHP_FW\ResponseInterface $response
     * @param \DHP_FW\AppInterface      $app
     * @param \DHP_FW\EventInterface    $event
     */
    public function __construct(\DHP_FW\RequestInterface $request, \DHP_FW\ResponseInterface $response, \DHP_FW\AppInterface $app, \DHP_FW\EventInterface $event) {
        $this->request  = $request;
        $this->response = $response;
        $this->app      = $app;
        $this->event    = $event;
        $this->event->register('DHP_FW.Response.afterSendData', array($this, 'eventResponseSendData'));
        $this->event->register('DHP_FW.Response.header', array($this, 'eventResponseSendHeader'));
        $this->event->register('DHP_FW.app.cacheForRequest', array($this, 'eventCacheForRequest'));
    }

    public function eventResponseSendData(&$data) {
        if ($this->event->trigger('app.shouldCacheUriData') !== FALSE && $this->request->getMethod() == \DHP_FW\App::HTTP_METHOD_GET) {
            $this->currentResponse['data'] = $data;
            $this->app->cache($this->generateCacheKey($this->request->getUri()), $this->currentResponse, 300);
        }
    }

    public function eventResponseSendHeader($name, $value) {
        $this->currentResponse['headers'][$name] = $value;
    }

    public function eventCacheForRequest($uri) {
        return $this->app->cache($this->generateCacheKey($this->request->getUri()));
    }

    private function generateCacheKey($cacheKey) {
        return '_cacheMiddleware_' . md5($cacheKey);
    }
}

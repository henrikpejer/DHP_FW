<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:53
 *
 * App class, used to handle app routes and more.
 */
class App implements \DHP_FW\AppInterface {

    public $routes         = NULL;
    protected $configs     = array('use_cache' => FALSE);
    protected $cacheObject = NULL;

    /**
     * @param RequestInterface                $Request
     * @param dependencyInjection\DIInterface $DependencyInjector
     * @param EventInterface                  $event
     * @param RoutingInterface                $routes
     */
    public function __construct(\DHP_FW\RequestInterface $Request,
        \DHP_FW\dependencyInjection\DIInterface $DependencyInjector,
        \DHP_FW\EventInterface $event,
        \DHP_FW\RoutingInterface $routes) {
        $this->request = $Request;
        $this->DI      = $DependencyInjector;
        $this->event   = $event;
        $this->setupCache();
        $this->routes = $routes;
    }

    /**
     * @param String $configToEnable
     *
     * @return App|AppInterface
     */
    public function enable($configToEnable) {
        return $this->configs[$configToEnable] = TRUE;
    }

    /**
     * Enable a config value. Sets that config to TRUE
     *
     * @param $configToCheck
     *
     * @return bool
     */
    public function enabled($configToCheck) {
        if ( isset( $this->configs[$configToCheck] )
          && $this->configs[$configToCheck]
        ) {
            $return = TRUE;
        } else {
            $return = FALSE;
        }
        return $return;
    }

    /**
     * Sets a config value to FALSE
     *
     * @param config $configToDisable
     *
     * @return App|AppInterface
     */
    public function disable($configToDisable) {
        $this->configs[$configToDisable] = FALSE;
        return $this;
    }

    /**
     * Loads middleware.
     *
     * Middleware sits 'in between' and adds extra functionality. Most
     * of the time it is something that will either set things up before
     * the controller is called or something that will react to events
     * fired in the application.
     *
     * This function will try to help you load middlewar, fi it cannot find
     * it. So you can do : middleware('Cache') and this method will look for
     *
     * + /Cache
     * + /app/middleware/Cache
     * + /DHP_FW/middleware/Cache
     *
     * Providing with the correct class, with namespace, will be the best option
     *
     * @param String $middleware class name that will get loaded with DI
     *
     * @return App|AppInterface
     */
    # todo : figure out dependencies... or not?
    public function middleware($middleware) {
        if ( !class_exists($middleware, TRUE) ) {
            if ( class_exists('DHP_FW\middleware\\' . $middleware, TRUE) ) {
                $middleware = 'DHP_FW\middleware\\' . $middleware;
            } elseif ( class_exists('app\middleware\\' . $middleware, TRUE) ) {
                $middleware = 'app\middleware\\' . $middleware;
            }
        }
        $this->DI->get($middleware);
        return $this;
    }

    /**
     * This starts the application.
     *
     * Main responsibility is to star the route matching process and run
     * the routes matched.
     *
     * @return array|bool|mixed|null
     */
    public function start() {
        $uri = $this->request->getUri();
        # do we have a cache, of the data, for this request?
        $cacheForUri = $this->event->trigger('DHP_FW.app.cacheForRequest',
                                             $uri);
        if ( isset( $cacheForUri ) && $cacheForUri != FALSE ) {
            $this->response = $this->DI->get('DHP_FW\ResponseInterface');
            foreach ($cacheForUri['headers'] as $name => $value) {
                $this->response->header($name, $value);
            }
            $this->response->send($cacheForUri['data']);
            return TRUE;
        }
        $return = $this->routes->match($this->request->getMethod(),
                                       $this->request->getUri());

        #$return = $this->runMatchedRoutes($routesMatches);
        return $return;
    }

    /**
     * Generic cache method. Used for getting / setting values.
     *
     *
     * @param String     $key
     * @param null       $value Use for cache write-through
     * @param null       $ttl   TTL for this cache key
     *
     * @return null
     */
    public function cache($key, $value = NULL, $ttl = NULL) {
        return $this->__setCache('app', $key, $value, $ttl);
    }

    /**
     * The key to delete from cache
     *
     * @param $key
     *
     * @return mixed
     */
    public function cacheDelete($key) {
        return $this->__deleteCache('app', $key);
    }

    /**
     * This will flush all the cache
     */
    public function cache_flush() {
        if ( isset( $this->cacheObject ) ) {
            $this->cacheObject->bucket('app')->flush();
            $this->cacheObject->bucket('data')->flush();
            $this->cacheObject->bucket('sys')->flush();
        }
    }

    /**
     * Sets cache in the system-bucket.
     *
     * @param String     $key
     * @param null       $value
     * @param null       $ttl
     *
     * @return null
     */
    private function cache_system($key, $value = NULL, $ttl = NULL) {
        return $this->__setCache('sys', $key, $value, $ttl);
    }

    /**
     * This is the generic setCache function. This uses the prefix to select
     * what bucket to use, then fetches the key for that bucket.
     *
     * @param  String    $prefix
     * @param  String    $key
     * @param null       $value
     * @param null       $ttl
     *
     * @return null|mixed
     */
    private function __setCache($prefix, $key, $value = NULL, $ttl = NULL) {
        $return = NULL;
        if ( $this->enabled('use_cache') ) {
            if ( isset( $value ) ) {
                $value = $value !== NULL && is_callable($value) ? $value : function () use ($value) {
                    return $value;
                };
            }
            $return = $this->cacheObject->bucket($prefix)
              ->get($key, $value, $ttl);
        }
        return $return;
    }

    /**
     * This will delete a key in a bucket.
     *
     * @param String $prefix
     * @param String $key
     *
     * @return mixed
     */
    private function __deleteCache($prefix, $key) {
        return $this->cacheObject->bucket($prefix)->delete($key);
    }

    /**
     * Gets cacheStorage, inits it and sets the cacheObject of!
     */
    public function setupCache() {
        if ( $this->enabled('use_cache') ) {
            $this->cacheObject = $this->DI->get('DHP_FW\cache\Cache');
            $this->cacheObject->bucket('app');
            $this->cacheObject->bucket('data');
            $this->cacheObject->bucket('sys');
        }
    }
}

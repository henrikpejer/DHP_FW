<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\cache;

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-01-28 16:16
 *
 */
class Memcached implements CacheStorageInterface {

    private $defaultTtl, $store;

    public function __construct(array $servers, $defaultTtl = 2592000) { #30 days in seconds
        $this->defaultTtl = $defaultTtl;
        $this->store      = new \Memcached();
        $this->store->addServers($servers);
    }

    /**
     * Used to set a value in the cacheObject.
     *
     * @param           $key   cache key for the value
     * @param mixed     $value the value
     * @param null      $ttl   optional TimeToLive
     *
     * @return mixed
     */
    public function set($key, $value, $ttl = NULL) {
        $ttl = $ttl == NULL ? $ttl : $this->defaultTtl;
        $this->store->set($key, $value, $ttl);
    }

    /**
     * Used to get a value associated with a key from the
     * cacheObject storage.
     *
     * If it isn't set and a closure is provided, the closure
     * will be called and the return of that output will be used
     * as a value for that key, with the optional TimeToLive if set.
     *
     * @param          $key     cache key for the value to get
     * @param callable $closure return set when key is not present
     * @param null     $ttl     optional TTL for the key
     *
     * @return mixed
     */
    public function get($key, callable $closure = NULL, $ttl = NULL) {
        $__success__ = NULL;
        $return      = $this->store->get($key);
        if ( $this->store->getResultCode() == \Memcached::RES_NOTFOUND && $closure !== NULL && is_callable($closure) ) { # something went bad, right?
            ob_start();
            $__return__ = $closure();
            $__echo__   = ob_get_clean();
            $return     =
              empty( $__return__ ) ? $__echo__ : $__return__;
            $this->set($key, $return, $ttl);
        }
        return $return;
    }

    /**
     * Deletes the key, and the value
     *
     * @param $key key to remove
     *
     * @return mixed
     */
    public function delete($key) {
        $this->store->delete(key);
    }

    /**
     * This will flush the whole cacheObject so nothing will be served
     * from the cacheObject.
     *
     * @return mixed
     */
    public function flush() {
        $this->store->flush();
    }
}

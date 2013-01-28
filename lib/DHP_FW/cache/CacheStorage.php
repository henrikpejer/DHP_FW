<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\cache;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 20:38
 */
abstract class CacheStorage {

    protected $defaultTtl;

    public function __construct($defaultTtl = 2592000) { #30 days in seconds
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * Used to set a value in the cacheObject.
     *
     * @param      $key   cache key for the value
     * @param      $value the value
     * @param null $ttl   optional TimeToLive
     *
     * @return mixed
     */
    public function set($key, $value, $ttl = NULL) {
        $ttl = isset($ttl) ? $ttl : $this->defaultTtl;
        return $this->_set($key, $value, $ttl);
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
        list($return, $__success__) = $this->_get($key);
        if ($__success__ == FALSE && $closure !== NULL && is_callable($closure)) { # something went bad, right?
            ob_start();
            $__return__ = $closure();
            $__echo__   = ob_get_clean();
            $return     = empty($__return__) ? $__echo__ : $__return__;
            $this->set($key, $return, (isset($ttl) ? $ttl : $this->defaultTtl));
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
        return $this->_delete($key);
    }

    /**
     * This will flush the whole cacheObject so nothing will be served
     * from the cacheObject.
     *
     * @return mixed
     */
    public function flush() {
        $this->_flush();
        return $this;
    }

    /**
     * This sets the value.
     *
     * Internal method, should be overridden in sub class.
     *
     * @param String        $key
     * @param Mixed         $value
     * @param Integer       $ttl
     */
    protected function _set($key, $value, $ttl) {
    }

    /**
     * This gets a value for a key and returns it.
     *
     * The return is special since it should be an array with two values
     * first is the value of the key
     * the second is TRUE / FALSE stating if the get was successful or not
     * true is successful, false is unsuccesful.
     *
     * Internal method, should be overridden in sub class.
     *
     * @param String $key
     *
     * @return array
     */
    protected function _get($key) {
    }

    /**
     * Deletes the key in the cache store
     *
     * Internal method, should be overridden in sub class.
     *
     * @param $key
     * @return bool|\string[]
     */
    protected function _delete($key) {
    }

    /**
     * Flushes the cache
     *
     * Internal method, should be overridden in sub class.
     *
     * @return bool
     */
    protected function _flush() {
    }
}
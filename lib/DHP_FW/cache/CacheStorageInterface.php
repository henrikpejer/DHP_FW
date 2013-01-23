<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\cache;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 20:38
 */
interface CacheStorageInterface {

    /**
     * Used to set a value in the cache.
     *
     * @param      $key cache key for the value
     * @param      $value the value
     * @param null $ttl optional TimeToLive
     * @return mixed
     */
    public function set($key, $value, $ttl = NULL);

    /**
     * Used to get a value associated with a key from the
     * cache storage.
     *
     * If it isn't set and a closure is provided, the closure
     * will be called and the return of that output will be used
     * as a value for that key, with the optional TimeToLive if set.
     *
     * @param          $key cache key for the value to get
     * @param callable $closure return set when key is not present
     * @param null     $ttl optional TTL for the key
     * @return mixed
     */
    public function get($key,callable $closure = NULL, $ttl = NULL);

    /**
     * Deletes the key, and the value
     *
     * @param $key key to remove
     * @return mixed
     */
    public function delete($key);

    /**
     * This will flush the whole cache so nothing will be served
     * from the cache.
     *
     * @return mixed
     */
    public function flush();
}
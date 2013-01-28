<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\cache;
/**
 * Proxy interface between cache and a cache plugin.
 *
 * Probably more of a facade...
 *
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 22:08
 */
interface CacheBucketProxyInterface{

    /**
     * Supply the storage this proxy will use. Prefix is the prefix or the
     * key. So the final key will be
     *
     * prefix.key.
     *
     *
     * @param String                      $prefix
     * @param CacheStorageInterface $storage
     */
    function __construct($prefix, CacheStorage $storage);

    /**
     * Sets a value in the bucket
     *
     * @param String $key
     * @param mixed  $value
     * @param int|null  $ttl
     * @return mixed
     */
    function set($key, $value, $ttl = NULL);

    /**
     * Gets a value associated with the key, in the current bucket.
     *
     * Also, if it does not exist and closure is provided, this function
     * will act as a write through cache.
     *
     * @param string $key
     * @param callable  $closure
     * @param null|int  $ttl
     * @return mixed
     */
    function get($key, callable $closure = NULL, $ttl = NULL);

    /**
     * Removes a key and that value from the bucket
     *
     * @param String $key
     * @return mixed
     */
    function delete($key);

    /**
     * Flushes the cache.
     *
     * @return mixed
     */
    function flush();
}

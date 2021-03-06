<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\cache;

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-01-28 16:16
 *
 */
class Memcached extends CacheStorage {

    protected $defaultTtl;
    private $store;

    public function __construct(array $servers = array(array('localhost', 11211)), $defaultTtl = 2592000) { #30 days in seconds
        $this->defaultTtl = $defaultTtl;
        $this->store      = new \Memcached();
        $this->store->addServers($servers);
    }

    /**
     * Returns Memcached::getVersion
     *
     * @return array
     */
    public function getVersion() {
        return $this->store->getVersion();
    }

    /**
     * Checks if there are any memache-servers that we can use or not
     *
     * @return bool
     */
    public function checkIsUp() {
        $return = FALSE;
        foreach ($this->getVersion() as $version) {
            /*
             * 255.255.255 seems to be the version when no memcached server
             * can be found on that host,port, combination
             */
            if ($version != '255.255.255') {
                $return = TRUE;
                break;
            }
        }
        return $return;
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
    protected function _set($key, $value, $ttl) {
        return $this->store->set($key, $value, $ttl);
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
    protected function _get($key, callable $closure = NULL, $ttl = NULL) {
        $return      = $this->store->get($key);
        $__success__ = ($this->store->getResultCode() == \Memcached::RES_NOTFOUND) ? FALSE : TRUE;
        return array($return, $__success__);
    }

    /**
     * Deletes the key, and the value
     *
     * @param string $key key to remove
     *
     * @return mixed
     */
    protected function _delete($key) {
        $this->store->delete($key);
    }

    /**
     * This will flush the whole cacheObject so nothing will be served
     * from the cacheObject.
     *
     * @return mixed
     */
    protected function _flush() {
        $this->store->flush();
    }
}

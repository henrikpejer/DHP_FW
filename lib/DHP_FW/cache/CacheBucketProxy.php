<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\cache;
use \DHP_FW\cache\CacheStorage;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 22:08
 */
class CacheBucketProxy implements CacheStorage{
    private $prefix = NULL;
    private $storage = NULL;

    public function __construct($prefix, CacheStorage $storage) {
        $this->prefix = $prefix;
        $this->storage = $storage;
    }

    public function set($key, $value, $ttl = NULL) {
        $key = $this->prefix.'_'.$key;
        return $this->storage->set($key,$value,$ttl);
    }

    public function get($key, \closure $closure = NULL, $ttl = NULL) {
        $key = $this->prefix.'_'.$key;
        return $this->storage->get($key,$closure,$ttl);
    }

    public function delete($key) {
        $key = $this->prefix.'_'.$key;
        return $this->storage->delete($key);
    }

    public function flush() {
        return $this->storage->flush();
    }
}

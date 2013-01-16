<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\cache;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 20:38
 */
interface CacheStorage {

    public function set($key, $value, $ttl = NULL);
    public function get($key,\closure $closure = NULL, $ttl = NULL);
    public function delete($key);
    public function flush();
}
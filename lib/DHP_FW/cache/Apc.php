<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\cache;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 20:41
 */
class Apc implements CacheStorageInterface{

    private $defaultTtl = NULL;

    public function __construct($defaultTtl = 2592000){ #30 days in seconds
        $this->defaultTtl = $defaultTtl;
    }

    public function set($key, $value, $ttl = NULL) {
        $ttl = isset($ttl)?$ttl:$this->defaultTtl;
        return apc_store($key,$value, $ttl);
    }

    public function get($key, callable $closure = NULL, $ttl = NULL) {
        $__success__ = NULL;
        $return = apc_fetch($key,$__success__);
        if($return === FALSE && $closure !== NULL && is_callable($closure)){  # something went bad, right?
            ob_start();
            $__return__ = $closure();
            $__echo__ = ob_get_clean();
            $return = empty($__return__)?$__echo__:$__return__;
            $this->set($key,$return, (isset($ttl)?$ttl:$this->defaultTtl));
        }
        return $return;
    }

    public function delete($key) {
        apc_delete($key);
        return $this;
    }

    public function flush() {
        apc_clear_cache('user');
        return $this;
    }
}
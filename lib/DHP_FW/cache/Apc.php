<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\cache;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 20:41
 */
class Apc extends CacheStorage {

    protected $defaultTtl = NULL;

    protected function _set($key,$value,$ttl){
        # echo "SET : ";
        # var_dump(func_get_args());
        return apc_store($key,$value,$ttl);
    }

    protected function _get($key){
        $return      = apc_fetch($key, $__success__);
        # echo "GET : ";
        # var_dump(array($key,$return));
        return array($return,$__success__);
    }

    protected function _delete($key){
        return apc_delete($key);
    }

    protected function _flush(){
        return apc_clear_cache('user');
    }
}
<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\cache;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 22:08
 */
interface CacheBucketProxyInterface extends CacheStorageInterface{

    function __construct($prefix, CacheStorageInterface $storage);
}

<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\cache;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-16 20:29
 */
class Cache {

    private $buckets = array();

    public function __construct(CacheStorageInterface $storage) {
        $this->storage = $storage;
    }

    # this returns, what, an object with reference to this storage
    # but with a prefix for the key, right?
    public function bucket($prefix) {
        if ( !isset( $this->buckets[$prefix] ) ) {
            $bucket                 =
              new CacheBucketProxy( $prefix, $this->storage );
            $this->buckets[$prefix] = $bucket;
        }
        return $this->buckets[$prefix];
    }
}
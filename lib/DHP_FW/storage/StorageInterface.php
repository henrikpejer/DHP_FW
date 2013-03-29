<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\storage;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-02-03 19:21
 */
interface StorageInterface {

    public function truncate();

    public function amend($data);

    public function read($len = NULL);

    public function close();

    public function rewind();

    public function delete();
}

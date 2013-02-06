<?php
declare(encoding="UTF8");
namespace DHP_FW\storage;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-02-03 19:21
 */
interface StorageInterface {

    /**
     * Opens storage
     *
     * @return bool
     */
    function open();

    /**
     * Saves data to storage
     *
     * @param mixed $data
     *
     * @return bool
     */
    function save($data);

    /**
     * Reads data from storage. Optionally with an offset and length
     *
     * @param int $offset
     * @param int $length
     *
     * @return mixed
     */
    function read($offset = NULL, $length = NULL);

    /**
     * Truncates to zero
     *
     * @return bool
     */
    function truncate();

    /**
     * Removes the data and the storage
     *
     * @return bool
     */
    function delete();

    /**
     * Sets read-only: not possible to alter NOR save,truncate, delete or save the
     * object
     *
     * @return bool
     */
    function setReadOnly();

    /**
     * Sets read-and-write : simply making it possible to update and save the object
     *
     * @return bool
     */
    function setReadAndWrite();
}

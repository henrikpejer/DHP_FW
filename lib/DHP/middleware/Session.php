<?php
declare(encoding = "UTF8");
namespace DHP\middleware;

/**
 * A very basic session implementation
 *
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-04-02 22:34
 */
class Session
{

    private $id, $sessionData, $flashData = null;

    public function __construct()
    {
        $this->sessionData = array();
        $this->flashData   = array();
        $this->getId();
    }

    /**
     * This method will figure out what session-id we have OR generate a new one
     * for us
     */
    private function getId()
    {

    }
}

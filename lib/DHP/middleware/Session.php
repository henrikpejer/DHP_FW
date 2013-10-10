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

    private $sessionId   = null;
    private $sessionData = null;
    private $flashData   = null;

    /**
     * Initiates the session library
     */
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
        return $this->sessionId;
    }
}

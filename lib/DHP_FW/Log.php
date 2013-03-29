<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
use DHP_FW\storage\StorageInterface;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-05 19:43
 */
class Log {

    private $event = NULL;
    public function __construct(StorageInterface $storage, EventInterface $event ) {
        $this->event = $event;
        $this->logStorage = $storage;
        if( isset($this->event) ){
            $this->event->register('log', array($this, 'message'));
        }
    }

    public function message($message) {
        $this->logStorage->amend($this->makeMessage(func_get_args()));
    }

    private function makeMessage($args) {
        $t       = microtime(TRUE);
        $micro   = sprintf("%06d", ($t - floor($t)) * 1000000);
        $d       = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
        $message = array_shift($args);
        $args    = implode('|', $args);
        if($args == ''){
            $args = 'INFO';
        }
        return sprintf('[%1$s][%2$s]::%3$s'."\n",
            $d->format("Y-m-d H:i:s.u"),
            $args,
            $message
        );
    }
}
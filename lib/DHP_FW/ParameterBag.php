<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-01-17 16:44
 *
 */ 
class ParameterBag implements ParameterBagInterface {
    private $_values,$event;
    public function __construct(array $values, \DHP_FW\EventInterface $event){ # $this->storage->{$constructor}
        $this->_values = $values;
        $this->event = $event;
    }
    
    public function __get($name){
        return isset($this->_values[$name])?$this->_values[$name]:NULL;
    }

    public function __set($name,$value){
        # run delegation, if any, to notice that we DO have changed data
        $this->event->triggerSubscribe($this, 'dataChanged',$name,$value);
        return $this->_values[$name] = $value;
    }
}

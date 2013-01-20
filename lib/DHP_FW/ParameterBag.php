<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;
use DHP_FW\Event;
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-01-17 16:44
 *
 */ 
class ParameterBag {
    protected $_values;
    public function __construct(array $values){
        $this->_values = $values;
    }
    
    public function __get($name){
        return isset($this->_values[$name])?$this->_values[$name]:NULL;
    }

    public function __set($name,$value){
        #echo "Set called!";
        # run delegation, if any, to notice that we DO have changed data
        \app\DI()->get('\\DHP_FW\\Event')->triggerSubscribe($this, 'dataChanged',$name,$value);
        #var_dump(\app\DI()->get('\\DHP_FW\\Event'));
        return $this->_values[$name] = $value;
    }

}

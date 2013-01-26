<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-01-17 16:44
 *
 */
class ParameterBag implements ParameterBagInterface {
    private $_values, $event;

    /**
     * A bag with values. or, parameters
     *
     * @param array          $values initial values
     * @param EventInterface $event
     */
    public function __construct(array $values, \DHP_FW\EventInterface $event) { # $this->storage->{$constructor}
        $this->_values = $values;
        $this->event   = $event;
    }

    /**
     * Use this to get the value
     *
     * @param $name String The name of the parameter we want
     *
     * @return mixed Returns value or NULL if not found
     */
    public function __get($name) {
        return isset($this->_values[$name]) ? $this->_values[$name] : NULL;
    }

    /**
     * We use this to set values in the parameter bag
     *
     * @param $name  String name of the parameter to set
     * @param $value Mixed new value of the parameter
     *
     * @return mixed returns the value set
     */
    public function __set($name, $value) {
        # run delegation, if any, to notice that we DO have changed data
        $this->event->triggerSubscribe($this, 'dataChanged', $name, $value);
        return $this->_values[$name] = $value;
    }
}

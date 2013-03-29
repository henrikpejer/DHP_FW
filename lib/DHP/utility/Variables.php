<?php
declare(encoding = "UTF8");
namespace DHP\utility;
/**
 * Used to automatically set properties on an instantiated class
 *
 *
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 22:44
 */
class Variables extends Constants {

    /**
     * Used to set a public property on an object
     * @param $name
     * @param $value
     * @return $this
     */
    public function __set($name, $value) {
        $this->values[$this->globalEnvironment][$name] = $value;
        return $this;
    }

    /**
     * Used to get a public property of an object
     * @param $name
     * @return null
     */
    public function __get($name) {
        switch (TRUE) {
            case isset($this->values[$this->environment][$name]):
                $return = $this->values[$this->environment][$name];
                break;
            case isset($this->values[$this->globalEnvironment][$name]):
                $return = $this->values[$this->globalEnvironment][$name];
                break;
            default:
                $return = NULL;
        }
        return $return;
    }
}

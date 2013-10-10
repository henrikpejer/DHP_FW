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
class Variables extends Constants
{

    /**
     * Used to get a public property of an object
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        switch (true) {
            case isset($this->values[$this->environment][$name]):
                $return = $this->values[$this->environment][$name];
                break;
            case isset($this->values[self::DEFAULT_ENVIRONMENT][$name]):
                $return = $this->values[self::DEFAULT_ENVIRONMENT][$name];
                break;
            default:
                $return = null;
        }
        return $return;
    }

    /**
     * Used to set a public property on an object
     * @param $name
     * @param $value
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->values[self::DEFAULT_ENVIRONMENT][$name] = $value;
        return $this;
    }
}

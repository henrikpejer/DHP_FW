<?php
declare(encoding = "UTF8");
namespace DHP\utility;

/**
 * Sets constant properties on an object. Once set, you cannot change them.
 *
 * This will also make it so that you can set different values depending on environment.
 *
 * Example:
 * $constant->databaseIp = '192.168.0.6';
 * $constant->databaseIp('dev','127.0.0.1');
 *
 * This way, we have two values for databaseIp, the first is "global" and the other is for the
 * dev environment.
 *
 * By setting what environment we are in, we'd get different values.
 *
 * Continuing the example above:
 *
 * $constant->databaseIp; // = '192.168.0.6';
 * $constant->__setEnvironment('dev');
 * $constant->databaseIp; // = '127.0.0.1';
 *
 * If a value does not exist in the selected environment it will fallback to the global. If there
 * isn't one set there either, NULL will be returned.
 *
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 23:08
 */
class Constants
{

    const DEFAULT_ENVIRONMENT = 'GLOBAL';
    protected $values = array();
    protected $environment = self::DEFAULT_ENVIRONMENT;

    /**
     * Sets up object and if supplied with settings and defaultEnvironment,
     * that will be set too.
     *
     * @param array $values
     * @param null  $defaultEnvironment
     * @internal param array $settings
     */
    public function __construct(array $values = null, $defaultEnvironment = null)
    {
        if ($defaultEnvironment !== null) {
            $this->environment = $defaultEnvironment;
        }
        if ($values !== null) {
            $this->values                            = $values;
            $this->values[self::DEFAULT_ENVIRONMENT] = $this->values['values'];
            unset($this->values['values']);
        }
    }

    /**
     * Sets environment, this is for separating different settings depending on environment.
     * @param $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

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
     * @throws \RuntimeException
     * @return $this
     */
    public function __set($name, $value)
    {
        if (isset($this->values[self::DEFAULT_ENVIRONMENT][$name])) {
            throw new \RuntimeException("Can not update value of existing constant");
        }
        $this->values[self::DEFAULT_ENVIRONMENT][$name] = $value;
        return $this;
    }

    /**
     * This is to set a value depending on what environment we are in.
     *
     * @param $name
     * @param $arguments
     * @return $this
     * @throws \RuntimeException
     */
    public function __call($name, $arguments)
    {
        list($environment, $value) = $arguments;
        if (isset($this->values[$environment][$name])) {
            throw new \RuntimeException("Can not update value of existing constant");
        }
        $this->values[$environment][$name] = $value;
        return $this;
    }
}

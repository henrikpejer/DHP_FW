<?php
declare(encoding = "UTF8");
namespace DHP\utility;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-30 16:48
 */
class Util
{
    /**
     * Returns constructor arguments. Returns NULL when unable to load/find class
     * @param $class
     *
     * @return NULL|array
     * @throws \Exception
     */
    static public function classConstructorArguments($class)
    {
        $args = array();
        try {
            $refClass    = new \ReflectionClass($class);
            $constructor = $refClass->getConstructor();
            if ($constructor) {
                $params = $constructor->getParameters();
                if ($params) {
                    foreach ($params as $param) {
                        $arg =
                                array(
                                    'name'     => $param->getName(),
                                    'required' => true,
                                    'class'    => null
                                );
                        if ($param->getClass()) {
                            $arg['class'] =
                                    $param->getClass()->getName();
                        }
                        if ($param->isOptional()) {
                            $arg['required'] = false;
                            $arg['default']  =
                                    $param->getDefaultValue();
                        }
                        $args[] = $arg;
                    }
                }
            }
        } catch (\Exception $e) { # exception thrown, return null
            throw $e;
        }
        return $args;
    }

    static public function methodDocComments($reflectionMethod)
    {
        $__comments__ = array();
        # get the docs
        $__comment__ = $reflectionMethod->getDocComment();
        if (false !== $__comment__) {
            $lines = explode("\n", $__comment__);
            foreach ($lines as $line) {
                $line = trim($line, ' *');
                if (preg_match('/^@([a-z]+) (.*)$/i', $line, $matches)) {
                    $__comments__[$matches[1]] = $matches[2];
                }
            }
        }
        return $__comments__;
    }

    static public function parseArgv($name)
    {
        static $parsedValues = null;
        if ($parsedValues === null) {
            $parsedValues = array();
            if (isset($_SERVER['argv'])) {
                $ln = count($_SERVER['argv']);
                for ($i = 0; $i < $ln; $i++) {
                    $value     = $_SERVER['argv'][$i];
                    $nextValue = isset($_SERVER['argv'][($i + 1)]) ?
                            $_SERVER['argv'][($i + 1)] : null;
                    if (substr($value, 0, 2) === '--') {
                        if (substr($nextValue, 0, 2) !== '--') {
                            $parsedValues[substr($value, 2)] = $nextValue;
                            ++$i;
                        } else {
                            $parsedValues[substr($value, 2)] = true;
                        }
                    } else {
                        $parsedValues[$value] = true;
                    }
                }
            }
        }
        return isset($parsedValues[$name]) ? $parsedValues[$name] : null;
    }
}

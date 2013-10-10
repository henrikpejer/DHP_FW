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
    public static function classConstructorArguments($class)
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

    /**
     * @param $reflectionMethod
     * @return array
     */
    public static function methodDocComments($reflectionMethod)
    {
        $comments = array();
        # get the docs
        /** @noinspection PhpUndefinedMethodInspection */
        $comment = $reflectionMethod->getDocComment();
        if (false !== $comment) {
            $lines = explode("\n", $comment);
            foreach ($lines as $line) {
                $line = trim($line, ' *');
                if (preg_match('/^@([a-z]+) (.*)$/i', $line, $matches)) {
                    $comments[$matches[1]] = $matches[2];
                }
            }
        }
        return $comments;
    }

    /**
     * @param $name
     * @return null
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function parseArgv($name)
    {
        $parsedValues = array();
        if (isset($_SERVER['argv'])) {
            $length = count($_SERVER['argv']);
            for ($i = 0; $i < $length; $i++) {
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
        return isset($parsedValues[$name]) ? $parsedValues[$name] : null;
    }
}

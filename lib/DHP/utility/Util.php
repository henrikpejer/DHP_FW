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
                                'required' => TRUE,
                                'class'    => NULL
                            );
                        if ($param->getClass()) {
                            $arg['class'] =
                                $param->getClass()->getName();
                        }
                        if ($param->isOptional()) {
                            $arg['required'] = FALSE;
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
    static public function methodDocComments($reflectionMethod)
    {
        $comments = array();
        # get the docs
        /** @noinspection PhpUndefinedMethodInspection */
        $comment = $reflectionMethod->getDocComment();
        if (FALSE !== $comment) {
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
     */
    static public function parseArgv($name)
    {
        $parsedValues = array();
        if (isset($_SERVER['argv'])) {
            $ln = count($_SERVER['argv']);
            for ($i = 0; $i < $ln; $i++) {
                $value     = $_SERVER['argv'][$i];
                $nextValue = isset($_SERVER['argv'][($i + 1)]) ?
                    $_SERVER['argv'][($i + 1)] : NULL;
                if (substr($value, 0, 2) === '--') {
                    if (substr($nextValue, 0, 2) !== '--') {
                        $parsedValues[substr($value, 2)] = $nextValue;
                        ++$i;
                    } else {
                        $parsedValues[substr($value, 2)] = TRUE;
                    }
                } else {
                    $parsedValues[$value] = TRUE;
                }
            }
        }
        return isset($parsedValues[$name]) ? $parsedValues[$name] : NULL;
    }
}

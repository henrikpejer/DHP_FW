<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 21:23
 */
class Utils {
    # returns NULL when unable to load/find class
    static public function classConstructorArguments($class) {
        $args = array();
        try {
            $refClass    = new \ReflectionClass($class);
            $constructor = $refClass->getConstructor();
            if ($constructor) {
                $params = $constructor->getParameters();
                if ($params) {
                    foreach ($params as $param) {
                        $arg = array(
                            'name'     => $param->getName(),
                            'required' => TRUE,
                            'class'    => NULL
                        );
                        if ($param->getClass()) {
                            $arg['class'] = $param->getClass()->getName();
                        }
                        if ($param->isOptional()) {
                            $arg['required'] = FALSE;
                            $arg['default']  = $param->getDefaultValue();
                        }
                        $args[] = $arg;
                    }
                }
            }
        }catch(\Exception $e){  # exception thrown, return null
            throw $e;
        }
        return $args;
    }
}

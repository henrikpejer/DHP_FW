<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-01-17 16:37
 *
 */
class ParameterBagReadOnly extends ParameterBag implements ParameterBagReadOnlyInterface {

    protected $_values;

    public function __set($name, $value) {
        return NULL;
    }
}

<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-01-17 16:37
 *
 */ 
interface ParameterBagReadOnlyInterface extends \DHP_FW\ParameterBagInterface{
    /**
     * This will _not_ update the values in the parameterbag since it is
     * read only.
     *
     * @param $name Name of value to set
     * @param $value Value of name
     * @return NULL
     */
    function __set($name,$value);
}

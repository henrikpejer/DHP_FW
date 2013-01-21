<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-21 19:38
 */
interface EventInterface {
    function trigger($eventName, &$one = NULL, &$two = NULL, &$three = NULL, &$four = NULL, &$five = NULL, &$six = NULL, &$seven = NULL);
    function register($eventName, $callable);
    function subscribe($objectToSubscribeTo, &$subscriber);
    function triggerSubscribe($delegate,$method, &$one = NULL, &$two = NULL, &$three = NULL, &$four = NULL);

}
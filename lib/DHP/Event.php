<?php
declare(encoding = "UTF8");
namespace DHP;
/**
 *
 * This little piggy will handle events throughout the system.
 * Objects can subscribe to events happening in the system and
 * functions will be called, with or without arguments.
 */
if (!defined('DHP\\EVENT_ABORT')) {
    define('DHP\\EVENT_ABORT', NULL);
}

class Event implements EventInterface
{

    public $delegates = array();
    protected $events = array('*' => array(),
        '__controller__' => array());

    /**
     * This triggers an event. All registered events are looped through in the order
     * they were registered. All parameters are called by reference so the registered
     * event methods can change the values, if necessary.
     *
     * If a registered method returns FALSE, the loop will break and further events
     * will not be processed.
     *
     *
     * @param String $eventName
     * @param null $one
     * @param null $two
     * @param null $three
     * @param null $four
     * @param null $five
     * @param null $six
     * @param null $seven
     *
     * @return mixed
     */
    public function trigger($eventName,
                            &$one = NULL,
                            &$two = NULL,
                            &$three = NULL,
                            &$four = NULL,
                            &$five = NULL,
                            &$six = NULL,
                            &$seven = NULL)
    {
        $args = func_get_args();
        $__return__ = NULL;
        switch (count($args)) {
            case 1:
                $__return__ = $this->call($eventName);
                break;
            case 2:
                $__return__ = $this->call($eventName, $one);
                break;
            case 3:
                $__return__ = $this->call($eventName, $one, $two);
                break;
            case 4:
                $__return__ = $this->call($eventName, $one, $two, $three);
                break;
            case 5:
                $__return__ = $this->call($eventName, $one, $two, $three, $four);
                break;
            case 6:
                $__return__ = $this->call($eventName, $one, $two, $three, $four,
                    $five);
                break;
            case 7:
                $__return__ = $this->call($eventName, $one, $two, $three, $four,
                    $five, $six);
                break;
            case 8:
                $__return__ = $this->call($eventName, $one, $two, $three, $four,
                    $five, $six, $seven);
                break;
        }
        return $__return__;
    }

    /**
     * Internal function to actually trigger the event
     *
     * @param      $eventName
     * @param null $one
     * @param null $two
     * @param null $three
     * @param null $four
     * @param null $five
     * @param null $six
     * @param null $seven
     *
     * @return mixed|null
     */
    private function call($eventName,
                          &$one = NULL,
                          &$two = NULL,
                          &$three = NULL,
                          &$four = NULL,
                          &$five = NULL,
                          &$six = NULL,
                          &$seven = NULL)
    {
        $return = NULL;
        $numArgs = (func_num_args() - 1);
        $callArgs = NULL;
        foreach ($this->mergeEventToCall($eventName) as $event) {
            $__return__ = NULL;
            switch ($numArgs) {
                case 0:
                    if (!isset($callArgs)) {
                        $callArgs = array();
                    }
                    if (is_array($event)) {
                        $__return__ = call_user_func($event);
                    } else {
                        $__return__ = $event();
                    }
                    break;
                case 1:
                    if (!isset($callArgs)) {
                        $callArgs = array(&$one);
                    }
                    if (is_array($event)) {
                        $__return__ = call_user_func_array($event, $callArgs);
                    } else {
                        $__return__ = $event($one);
                    }
                    break;
                case 2:
                    if (!isset($callArgs)) {
                        $callArgs = array(&$one, &$two);
                    }
                    if (is_array($event)) {
                        $__return__ = call_user_func_array($event, $callArgs);
                    } else {
                        $__return__ = $event($one, $two);
                    }
                    break;
                case 3:
                    if (!isset($callArgs)) {
                        $callArgs = array(&$one, &$two, &$three);
                    }
                    if (is_array($event)) {
                        $__return__ = call_user_func_array($event, $callArgs);
                    } else {
                        $__return__ = $event($one, $two, $three);
                    }
                    break;
                case 4:
                    if (!isset($callArgs)) {
                        $callArgs = array(&$one, &$two, &$three, &$four);
                    }
                    if (is_array($event)) {
                        $__return__ = call_user_func_array($event, $callArgs);
                    } else {
                        $__return__ = $event($one, $two, $three, $four);
                    }
                    break;
                case 5:
                    if (!isset($callArgs)) {
                        $callArgs = array(&$one, &$two, &$three, &$four, &$five);
                    }
                    if (is_array($event)) {
                        $__return__ = call_user_func_array($event, $callArgs);
                    } else {
                        $__return__ = $event($one, $two, $three, $four, $five);
                    }
                    break;
                case 6:
                    if (!isset($callArgs)) {
                        $callArgs = array(&$one, &$two, &$three, &$four, &$five,
                            &$six);
                    }
                    if (is_array($event)) {
                        $__return__ = call_user_func_array($event, $callArgs);
                    } else {
                        $__return__ = $event($one, $two, $three, $four, $five, $six);
                    }
                    break;
                case 7:
                    if (!isset($callArgs)) {
                        $callArgs = array(&$one, &$two, &$three, &$four, &$five,
                            &$six, &$seven);
                    }
                    if (is_array($event)) {
                        $__return__ = call_user_func_array($event, $callArgs);
                    } else {
                        $__return__ = $event($one, $two, $three, $four, $five, $six,
                            $seven);
                    }
                    break;
            }
            if ($__return__ === EVENT_ABORT) {
                break;
            }
            $return = $__return__;
        }
        return $return;
    }

    /**
     * This function will get events that match the current
     * event as well as events with wildcard, *.
     *
     *
     *
     * @param $eventName : name of event to be called
     * @return array
     */
    private function mergeEventToCall($eventName)
    {
        $eventKeys = array($eventName);
        if (strpos($eventName, '.')) {
            $eventParts = explode('.', $eventName);
            $eventBase = '';
            foreach ($eventParts as $part) {
                $eventBase .= $part;
                $eventKeys[] = $eventBase . '*';
                $eventKeys[] = $eventBase . '.*';
            }
        }
        $eventKeys[] = '*';
        $eventKeys[] = '__controller__';
        $eventsToReturn = array();
        foreach ($eventKeys as $event) {
            if (isset($this->events[$event])) {
                $eventsToReturn =
                    array_merge($eventsToReturn, $this->events[$event]);
            }
        }
        return $eventsToReturn;
    }

    /**
     * This is used to register a callable with a certain event.
     *
     * @param String $eventName
     * @param Callable $callable
     * @return mixed
     */
    public function register($eventName, callable $callable)
    {
        if (!isset($this->events[$eventName])) {
            $this->events[$eventName] = array();
        }
        $this->events[$eventName][] = $callable;
        return TRUE;
    }

    /**
     * This is used when there are events that should not be publicly called but only
     * called on a observer, sort of.
     *
     * This way an object can tell it's observer when a certain event happened and
     * delegate some of its functionality to the observer.
     *
     * @param mixed $objectToSubscribeTo object to subscribe to
     * @param mixed $subscriber observer
     * @return mixed
     */
    public function subscribe($objectToSubscribeTo, &$subscriber)
    {
        $objectToSubscribeTo = spl_object_hash($objectToSubscribeTo);
        if (!isset($this->delegates[$objectToSubscribeTo])) {
            $this->delegates[$objectToSubscribeTo] = array();
        }
        $this->delegates[$objectToSubscribeTo][spl_object_hash($subscriber)] = & $subscriber;
    }

    /**
     * This will call $method on all the observers to the delegate, usually an object
     * calls this with $this :
     *
     * triggerSubscriber($this, 'observerNeedsToReactToThis')
     *
     * @param Object $delegate
     * @param String $method
     * @param null $one
     * @param null $two
     * @param null $three
     * @param null $four
     * @return mixed
     */
    public function triggerSubscribe($delegate,
                                     $method,
                                     &$one = NULL,
                                     &$two = NULL,
                                     &$three = NULL,
                                     &$four = NULL)
    {
        $__objectHash__ = spl_object_hash($delegate);
        $return = NULL;
        if (isset($this->delegates[$__objectHash__])) {
            foreach ($this->delegates[$__objectHash__] as $target) {
                $return = $target->$method($one, $two, $three, $four);
                if ($return === FALSE) {
                    break;
                }
            }
        }
        return $return;
    }
}
<?php
declare(encoding = "UTF8");
namespace DHP;

interface EventInterface {
    /**
     * This triggers an event. All registered events are looped through in the order
     * they were registered. All parameters are called by reference so the registered
     * event methods can change the values, if necessary.
     *
     * If a registered method returns FALSE, the loop will break and further events
     * will not be processed.
     *
     * @param String     $eventName
     * @param null       $one
     * @param null       $two
     * @param null       $three
     * @param null       $four
     * @param null       $five
     * @param null       $six
     * @param null       $seven
     * @return mixed
     */
    function trigger($eventName,
                     &$one = NULL,
                     &$two = NULL,
                     &$three = NULL,
                     &$four = NULL,
                     &$five = NULL,
                     &$six = NULL,
                     &$seven = NULL);

    /**
     * This is used to register a callable with a certain event.
     *
     * @param String $eventName
     * @param Callable $callable
     * @return mixed
     */
    function register($eventName, callable $callable);

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
    function subscribe($objectToSubscribeTo, &$subscriber);

    /**
     * This will call $method on all the observers to the delegate, usually an object
     * calls this with $this :
     *
     * triggerSubscriber($this, 'observerNeedsToReactToThis')
     *
     * @param Object     $delegate
     * @param String     $method
     * @param null $one
     * @param null $two
     * @param null $three
     * @param null $four
     * @return mixed
     */
    function triggerSubscribe($delegate,
                              $method,
                              &$one = NULL,
                              &$two = NULL,
                              &$three = NULL,
                              &$four = NULL);

}
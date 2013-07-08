<?php

class EventTest extends PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = new \DHP\Event();
    }

    public function testRegisterFunction()
    {
        $global = false;
        $anonMethod = function ($val = null) use (&$global) {
            $global = $val;
        };
        $this->object->register('test', $anonMethod);
        $val1 = 'new value';
        $val2 = 'new value2';
        $val3 = 'new value3';
        $val4 = 'new value4';
        $val5 = 'new value5';
        $val6 = 'new value6';
        $val7 = 'new value7';
        $val8 = 'new value8';
        $this->object->trigger('test');
        $this->assertEquals(null, $global);
        $this->object->trigger('test', $val1);
        $this->object->trigger('test', $val1, $val2);
        $this->object->trigger('test', $val1, $val2, $val3);
        $this->object->trigger('test', $val1, $val2, $val3, $val4);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6, $val7);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

        $this->assertEquals('new value', $global);
    }

    public function testRegisterMethod()
    {
        eval("class ttt{
    public \$global;
    public function erun(\$val = null){
        \$this->global = \$val;
       }
}");
        $o = new \ttt();
        $this->object->register('test', array($o, 'erun'));
        $val1 = 'new value';
        $val2 = 'new value2';
        $val3 = 'new value3';
        $val4 = 'new value4';
        $val5 = 'new value5';
        $val6 = 'new value6';
        $val7 = 'new value7';
        $val8 = 'new value8';
        $this->object->trigger('test');
        $this->assertEquals(null, $o->global);
        $this->object->trigger('test', $val1);
        $this->object->trigger('test', $val1, $val2);
        $this->object->trigger('test', $val1, $val2, $val3);
        $this->object->trigger('test', $val1, $val2, $val3, $val4);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6, $val7);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

        $this->assertEquals('new value', $o->global);
    }

    public function testSubscriber()
    {
        eval('class observer{
        public $value,$event;
        public function __construct($event){$this->event = $event;}
        public function run(){
            $v = "new value";
            $this->value = $this->event->triggerSubscribe($this,"observerCalls",$v);
        }
        }');

        $obs = new observer($this->object);
        $this->assertEquals(null,$obs->value);
        $obs->run();
        $this->assertEquals(null,$obs->value);
        eval('class sub{
            public function observerCalls($val){
                return false;
            }
        }');
        $sub = new \sub;
        $this->object->subscribe($obs,$sub);
        $obs->run();
        $this->assertFalse($obs->value);
    }

}

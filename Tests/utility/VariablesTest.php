<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 22:58
 */

class VariablesTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->object = new \DHP\utility\Variables();
    }

    public function testSetVariable() {
        $this->object->henrik = 'pejer';
        $this->assertEquals('pejer', $this->object->henrik);
    }

    public function testSetEnvironment() {
        $this->object->henrik    = 'pejer';
        $this->object->thirdRock = 'fromTheSun';
        $this->object->henrik('test', 'pejer test value');
        $this->assertEquals('pejer', $this->object->henrik);
        $this->object->__setEnvironment('test');
        $this->assertEquals('pejer test value', $this->object->henrik);
        $this->assertEquals('fromTheSun', $this->object->thirdRock);
        $this->assertNull($this->object->notSetYet);
    }
}

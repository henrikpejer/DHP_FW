<?php

class UuidTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->object = new \DHP\component\Uuid(12);
    }

    public function testGeneration()
    {
        $val = (string)$this->object;
        $this->assertNotEmpty($val);
    }

    public function testInvoking()
    {
        $val = $this->object;
        $this->assertNotEmpty($val());
    }

    public function testSetUuid()
    {
        $this->object->setId('id');
        $this->assertEquals('id', (string)$this->object);
    }

    public function testDecode()
    {
        $d = $this->object->decode();
        $this->assertEquals('127.0.0.1', $d['ip']);
    }

    public function testCheckHostname()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->object = new \DHP\component\Uuid(12);
        $d = $this->object->decode();
        $this->assertEquals('127.0.0.1', $d['ip']);
    }
}

<?php

class RequestTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->object = new \DHP\Request('GET','blog/this-is-the-title','{"big":"bad"}',array('postVar'=>'postValue'),array('getVar'=>'getValue'),array(),array('some-header'=>'header-value'));
    }

    public function testGet(){
        $this->assertEquals(array('getVar'=>'getValue'),$this->object->get);
    }

    public function testPost(){
        $this->assertEquals(array('postVar'=>'postValue'),$this->object->post);
    }

    public function testFiles(){
        $this->assertEquals(array(),$this->object->files);
    }

    public function testHeaders(){
        $this->assertEquals(array('some-header'=>'header-value'),$this->object->headers);
    }

    public function testSetupWithEnvironment(){
        $_SERVER['HTTP_Content-Type'] = 'HTML';
        $_SERVER['REQUEST_URI'] = 'blog/what-the';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->object->setupWithEnvironment();
        $this->assertEmpty($this->object->get);
        $this->assertEmpty($this->object->post);
        $this->assertEmpty($this->object->files);
        $this->assertEmpty($this->object->uri);
        $this->assertEquals(array('Content-type'=>'HTML'),$this->object->headers);
    }
}

<?php

class ModuleTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->routing = new \DHP\Routing();
        $this->object = $this->getMockForAbstractClass('\DHP\blueprint\Module',array($this->routing));
    }
    public function testGet()
    {
        $this->object->get('uri/get/test', function () {
            return 'This is necessary';
        });
        $this->assertNotEmpty($this->routing->match('GET','uri/get/test'));
    }
    public function testGetNameSpace()
    {
        $this->object->uriNamespace('blog');
        $this->object->get('uri/get/test', function () {
            return 'This is necessary';
        });
        $this->assertEmpty($this->routing->match('GET','uri/get/test'));
        $this->assertNotEmpty($this->routing->match('GET','blog/uri/get/test'));
    }
    public function testPost()
    {
        $this->object->post('uri/get/test', function () {
            return 'This is necessary';
        });
        $this->assertNotEmpty($this->routing->match('POST','uri/get/test'));
    }
    public function testPut()
    {
        $this->object->put('uri/get/test', function () {
            return 'This is necessary';
        });
        $this->assertNotEmpty($this->routing->match('PUT','uri/get/test'));
    }
    public function testDelete()
    {
        $this->object->delete('uri/get/test', function () {
            return 'This is necessary';
        });
        $this->assertNotEmpty($this->routing->match('DELETE','uri/get/test'));
    }

}

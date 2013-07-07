<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-04-30 21:07
 */

class BodyParserTest extends PHPUnit_Framework_TestCase
{
    protected $request;
    protected $object;
    public function setup()
    {
        $this->request = new \DHP\Request('HEADER','/','{"name":"Henrik","test":true}');
        $this->object  = new \DHP\middleware\BodyParser($this->request);
    }

    public function testJsonData()
    {
        $this->object->run();
        $this->assertEquals(array('name' => "Henrik", 'test' => true), $this->request->body);
    }
}

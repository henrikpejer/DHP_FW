<?php
use \DHP\Response;
use \DHP\Request;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    private $object;

    /**
     * @runInSeparateProcess
     */
    public function testContent()
    {
        $this->expectOutputString('This is the new content');
        $this->object->setContent('This is the new content');
        $this->object->send();
    }

    /**
     * @runInSeparateProcess
     */
    public function testAppend()
    {
        $this->expectOutputString('This is the new content. And this is new.');
        $this->object->setContent('This is the new content');
        $this->object->appendContent('. And this is new.');
        $this->object->send();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAppendException()
    {
        $this->object->setContent('This is the new content');
        $this->object->appendContent(array('something' => 'else'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testAppendToobjectData()
    {
        $this->object->setContent(array('something' => 'something else'));
        $this->object->appendContent('else');
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonExport()
    {
        $this->object->setContent(array('thiswillbe' => 'an object'));
        $this->expectOutputString('{"meta":{"status":null,"messages":[]},"thiswillbe":"an object"}');
        $this->object->send();
    }

    /**
     * @runInSeparateProcess
     */
    public function testStatusHeader()
    {
        $headerTestAgainst = array(
            'status' => array('value' => 'HTTP/1.1 200 OK', 'statusCode' => 200),
            'content-type' => array('value' => 'Content-Type: text/html', 'statusCode' => null)
        );
        $this->object->setStatus(200);
        $this->object->addHeader("content-type", "text/html");
        $this->object->send();
        $this->assertAttributeEquals($headerTestAgainst, 'headers', $this->object);
    }

    /**
     * @runInSeparateProcess
     */
    public function testStatusHeaderWithExtraHeader()
    {
        $headerTestAgainst = array(
            'Location' => array('value' => 'Location: /blog/title', 'statusCode' => 201),
            'content-type' => array('value' => 'Content-Type: text/html', 'statusCode' => null)
        );
        $this->object->setStatus(201, 'Location', '/blog/title');
        $this->object->addHeader("content-type", "text/html");
        $this->object->send();
        $this->assertAttributeEquals($headerTestAgainst, 'headers', $this->object);

    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSendingHeadersWhenHeadersAlreadySent()
    {
        $this->object->send();
    }

    protected function setUp()
    {
        $request = new \DHP\Request();
        $this->object = new \DHP\Response($request);
    }
}

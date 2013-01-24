<?php
namespace DHP_FW\cache;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2013-01-16 at 21:46:48.
 */
class ApcTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Apc
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Apc;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object->flush();
    }

    /**
     */
    public function testSet() {
        \PHPUnit_Framework_Assert::assertFalse($this->object->get('nonExisting'));
        $this->object->set('nonExisting', 'something');
        \PHPUnit_Framework_Assert::assertEquals('something', $this->object->get('nonExisting'));
    }

    /**
     */
    public function testGet() {
        $this->object->set('nonExisting', 'something');
        \PHPUnit_Framework_Assert::assertEquals('something', $this->object->get('nonExisting'));
    }

    /**
     */
    public function testDelete() {
        $this->object->set('nonExisting', 'something');
        \PHPUnit_Framework_Assert::assertEquals('something', $this->object->get('nonExisting'));
        $this->object->delete('nonExisting');
        \PHPUnit_Framework_Assert::assertFalse($this->object->get('nonExisting'));
    }


    public function testFlush() {
        $this->object->set('nonExisting', 'something');
        \PHPUnit_Framework_Assert::assertEquals('something', $this->object->get('nonExisting'));
        $this->object->flush();
        \PHPUnit_Framework_Assert::assertFalse($this->object->get('nonExisting'));
        $this->object->set('nonExisting', 'something');
        \PHPUnit_Framework_Assert::assertEquals('something', $this->object->get('nonExisting'));
    }

    public function testClosure() {
        \PHPUnit_Framework_Assert::assertTrue($this->object->get('nonExisting',function(){
            return TRUE;
        },300));
    }
}

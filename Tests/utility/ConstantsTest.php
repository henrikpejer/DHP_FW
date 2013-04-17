<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-29 23:11
 */

class ConstantsTest extends PHPUnit_Framework_TestCase {

    protected $object, $test;

    public function setUp() {
        $this->object = new \DHP\utility\Constants();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can not update value of existing constant
     */
    public function testSettingVariable() {
        $this->object->henrik = 'pejer';
        $this->assertEquals('pejer', $this->object->henrik);
        $this->object->henrik = 'shouldNotBeSet';
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can not update value of existing constant
     */
    public function testSetEnvironment() {
        $this->object->henrik    = 'pejer';
        $this->object->thirdRock = 'fromTheSun';
        $this->object->henrik('test', 'pejer test value');
        $this->assertEquals('pejer', $this->object->henrik);
        $this->object->setEnvironment('test');
        $this->assertEquals('pejer test value', $this->object->henrik);
        $this->assertEquals('fromTheSun', $this->object->thirdRock);
        $this->assertNull($this->object->notSetYet);
        $this->object->henrik('test','shouldThrowException');
    }

    public function testSetup() {
        $initialValues = array(
            'values' => array(
                'henrik' => 'pejer',
                'db'     => '192.168.0.1'
            ),
            'dev' => array(
                'db' => '127.0.0.1'
            )
        );
        $environment = 'dev';
        $this->object = new \DHP\utility\Constants($initialValues,$environment);
        $this->assertEquals('pejer',$this->object->henrik);
        $this->assertEquals('127.0.0.1',$this->object->db);

    }
}

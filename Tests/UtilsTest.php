<?php
namespace DHP_FW;
require_once __DIR__ . '/../lib/DHP_FW/Utils.php';
require_once __DIR__ . '/../lib/DHP_FW/dependencyInjection/DIProxy.php';

/**
 * Generated by PHPUnit_SkeletonGenerator on 2013-01-01 at 21:31:06.
 */
class UtilsTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Utils
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        if (!class_exists('testUtilsClass')) {
            eval('class testUtilsClass{
                function __construct( \\DHP_FW\\Utils $something, $nothing = FALSE){


            }}');
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     */
    public function testClassConstructorArguments() {
        $args =
          Utils::classConstructorArguments('DHP_FW\dependencyInjection\DIProxy');
        \PHPUnit_Framework_Assert::assertEquals(1, count($args));
        \PHPUnit_Framework_Assert::assertEquals(array(
            'name'     => 'class',
            'required' => TRUE,
            'class'    => NULL
        ), $args[0]);
        $args = Utils::classConstructorArguments('testUtilsClass');
        \PHPUnit_Framework_Assert::assertEquals(2, count($args));
        \PHPUnit_Framework_Assert::assertEquals(array(
            array(
                'name'     => 'something',
                'required' => TRUE,
                'class'    => 'DHP_FW\\Utils'
            ),
            array(
                'name'     => 'nothing',
                'required' => FALSE,
                'class'    => NULL,
                'default'  => FALSE
            )
        ), $args);
    }

    /**
     * @expectedException \Exception
     */
    public function testLoadNonexistingClass() {
        \PHPUnit_Framework_Assert::assertNull(
         Utils::classConstructorArguments('\\DHP_FWW\\dependencyInjection\\DIflex'));
    }
}

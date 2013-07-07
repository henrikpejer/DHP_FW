<?php
/**
 * Created by JetBrains PhpStorm.
 * User: henrikpejer
 * Date: 7/6/13
 * Time: 09:27
 * To change this template use File | Settings | File Templates.
 */

class UtilTest extends PHPUnit_Framework_TestCase {
    public function setUp()
    {
        $_SERVER['argv'] = array(
            'index.php',
            '--method',
            'GET',
            '--uri',
            'blog/test',
            'enableCookies',
            '--testSomethingElse',
            '--tryityoulikeit'
        );
    }

    public function testparseArgv()
    {
        $this->assertEquals('GET',\DHP\utility\Util::parseArgv('method'));
        $this->assertEquals(true,\DHP\utility\Util::parseArgv('enableCookies'));
        $this->assertEquals(true,\DHP\utility\Util::parseArgv('testSomethingElse'));
    }

    /**
     * @expectedException \ReflectionException
     */
    public function testClassConstructorArguments(){
        \DHP\utility\Util::classConstructorArguments('\DHP\nonexistingClass');
    }
}

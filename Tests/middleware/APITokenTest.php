<?php
/**
 * Created by JetBrains PhpStorm.
 * User: henrikpejer
 * Date: 7/4/13
 * Time: 09:44
 * To change this template use File | Settings | File Templates.
 */

class APITokenTest extends PHPUnit_Framework_TestCase
{

    private $event;
    public function setup()
    {
        $this->event = new \DHP\Event();
    }

    public function testAuthToken()
    {
        $request = new \DHP\Request('GET',array(),array(),array(),array(),array(),array('X-Auth-Token'=>'123'));
        $object = new \DHP\middleware\APIToken($request,$this->event);
        $this->event->register('APIToken.XAuthToken',function($token){
            return $token == 123?TRUE:FALSE;
        });
        $this->assertNull($object->run());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWrongAuthToken()
    {
        $request = new \DHP\Request('GET',array(),array(),array(),array(),array(),array('X-Auth-Token'=>'124'));
        $object = new \DHP\middleware\APIToken($request,$this->event);
        $this->event->register('APIToken.XAuthToken',function($token){
            return $token == 123?TRUE:FALSE;
        });
        $object->run();
    }

    public function testAuthAuthentication()
    {
        $request = new \DHP\Request('GET',array(),array(),array(),array(),array(),array('X-Auth-User'=>'Henrik','X-Auth-Password'=>'Pejer'));
        $object = new \DHP\middleware\APIToken($request,$this->event);
        $this->event->register('APIToken.XAuthToken',function($user,$pass){
            if ($user == 'Henrik' && $pass == 'Pejer' ){
                return true;
            }
            return false;
        });
        $this->assertNull($object->run());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWrongAuthAuthentication()
    {
        $request = new \DHP\Request('GET',array(),array(),array(),array(),array(),array('X-Auth-User'=>'Henrik','X-Auth-Password'=>'pejer'));
        $object = new \DHP\middleware\APIToken($request,$this->event);
        $this->event->register('APIToken.XAuthToken',function($user,$pass){
            if ($user == 'Henrik' && $pass == 'Pejer' ){
                return true;
            }
            return false;
        });
        $object->run();
    }

}

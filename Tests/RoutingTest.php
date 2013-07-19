<?php
declare(encoding = "UTF8");
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2013-07-18 17:32
 */

class RoutingTest extends PHPUnit_Framework_TestCase
{

    private $object;
    public function setUp()
    {
        $this->object = new \DHP\Routing();
    }

    public function testAlias()
    {
        $this->object->registerRoute(array('GET'), 'blog/:title',function(){},'blog.page');
        $this->assertEquals('blog/this-is-the-title',$this->object->generateUrlFromRoute('blog.page',array(':title'=>'this-is-the-title')));
    }
}

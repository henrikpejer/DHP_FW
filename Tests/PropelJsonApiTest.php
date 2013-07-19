<?php
declare(encoding = "UTF8");
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2013-07-18 20:57
 */

class PropelJsonApiTest extends PHPUnit_Framework_TestCase
{
    public function testCommands()
    {
        $dataMap = array(
            'blog' => array(),
            'comments' => array(),
            'author'=>array()
        );
        $request = new \DHP\Request('GET','blog/2,3/comments');
        $response = new \DHP\Response($request);
        $api = $this->getMock('DHP\modules\propelJson\API',array('run'),array($response,$request,$dataMap));
        $api->expects($this->any())->method('run')->will($this->returnValue(false));
        $this->assertEquals(array('blog' => '2,3','comments'=> null),$api->returnDataCommands());
    }
}

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
    public function testSingleAliasCommands()
    {
        $dataMap = array(
            '_blog' => array('_model'=>'blog'),
            '_comments' => array('_model'=>'comments'),
            'comments' => array('_model'=>'comments'),
            'author'=>array()
        );
        $request = new \DHP\Request('GET','_blog/2,3/comments');
        $response = new \DHP\Response($request);
        $api = $this->getMock('DHP\modules\propelJson\API',array('run'),array($response,$request,$dataMap));
        $api->expects($this->any())->method('run')->will($this->returnValue(false));
        $this->assertEquals(array('blog' => '2,3','comments'=> null),$api->returnDataCommands());
    }
    public function testSeveralAliasCommands()
    {
        $dataMap = array(
            '_blog' => array('_model'=>'blog'),
            '_comments' => array('_model'=>'comments'),
            'author'=>array()
        );
        $request = new \DHP\Request('GET','_blog/2,3/_comments');
        $response = new \DHP\Response($request);
        $api = $this->getMock('DHP\modules\propelJson\API',array('run'),array($response,$request,$dataMap));
        $api->expects($this->any())->method('run')->will($this->returnValue(false));
        $this->assertEquals(array('blog' => '2,3','comments'=> null),$api->returnDataCommands());
    }

    public function testMixingAliasesAndNoAliasesCommands()
    {
        $dataMap = array(
            '_blog' => array('_model'=>'blog'),
            '_comments' => array('_model'=>'comments'),
            'author'=>array()
        );
        $request = new \DHP\Request('GET','_blog/2,3/author');
        $response = new \DHP\Response($request);
        $api = $this->getMock('DHP\modules\propelJson\API',array('run'),array($response,$request,$dataMap));
        $api->expects($this->any())->method('run')->will($this->returnValue(false));
        $this->assertEquals(array('blog' => '2,3','author'=> null),$api->returnDataCommands());
    }
    public function testAliasesCommands()
    {
        $dataMap = array(
            '_blog' => array('_model'=>'blog'),
            '_comments' => array('_model'=>'comments'),
            'blog' => array(),
            'comments' => array(),
            'author'=>array()
        );
        $request = new \DHP\Request('GET','_blog/2,3/author');
        $response = new \DHP\Response($request);
        $api = $this->getMock('DHP\modules\propelJson\API',array('run'),array($response,$request,$dataMap));
        $api->expects($this->any())->method('run')->will($this->returnValue(false));
        $this->assertEquals(array('blog' => '2,3','author'=> null),$api->returnDataCommands());
        $request = new \DHP\Request('GET','blog/2,3/comments');
        $response = new \DHP\Response($request);
        $api = $this->getMock('DHP\modules\propelJson\API',array('run'),array($response,$request,$dataMap));
        $api->expects($this->any())->method('run')->will($this->returnValue(false));
        $this->assertEquals(array('blog' => '2,3','comments'=> null),$api->returnDataCommands());
    }
}

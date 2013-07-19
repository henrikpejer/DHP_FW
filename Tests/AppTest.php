<?php
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-04-03 20:59
 */

class AppTest extends PHPUnit_Framework_TestCase
{

    public function setup()
    {
        $DI = new \DHP\dependencyInjection\DI();
        $DI->set('DHP\Request')->setArguments(
            array(
                'GET',
                '/blog/title/title-of-blog-post'
            )
        );
        $this->DI = $DI;
        $this->object = $DI->get('DHP\App');
    }

    public function testAddController()
    {
        $this->expectOutputString("this is the title : title of blog post");
        eval('class AppController extends \DHP\blueprint\Controller{
/**
* @method GET
* @uri /title/:title
* @routeAlias blog.page
*/
public function title($title){
 echo "this is the title : {$title}";
}

        };');

        $this->object->addController('AppController', 'blog');
        $this->object->start();
        $this->assertEquals('blog/title/the-title',$this->DI->get('DHP\Routing')->generateUrlFromRoute('blog.page',array(':title'=>'the-title')));
    }

    public function testExtendingApp()
    {
        $this->expectOutputString('Generic Method, only on /blog/*this is the title : title of blog post');
        eval("class App extends \\DHP\\App{
            function start(\$routesFile = null, \$appConfig = null){
                \$this->routing->registerRoute(array('GET'), 'blog/*',
                    function (callable \$next) {
                        echo \"Generic Method, only on /blog/*\";
                        \$next();
                    });
                \$this->addController('AppController','blog');

                \$this->apply(\$this->DependencyInjector->get('DHP\\middleware\\BodyParser'));
                parent::start(\$routesFile, \$appConfig);
            }
        }");

        $app = $this->DI->get('App');
        $app->start();
    }

    public function testApply()
    {
        $this->DI->get('DHP\Request')->setBody('{"Henrik":"Pejer"}');
        $this->object->apply('DHP\middleware\BodyParser');
        $this->assertEquals(array('Henrik' => 'Pejer'), $this->DI->get('DHP\Request')->body);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAppWithConfigAndRoutes()
    {
        # write config file to disk
        $this->expectOutputString("DONE");
        $this->object->start(__DIR__ . '/AppTestRoutes.txt', __DIR__ . '/AppTestConfig.txt');
        $this->DI->get('DHP\Response')->send();
    }

    /**
     */
    public function testAppWithCustomParamType()
    {
        # write config file to disk
        $global = null;
        $this->DI->get('DHP\Routing')->addCustomParameter('blogTitle',function($title)use(&$global){
            $global = $title;
            return true;
        });
        $this->object->start(__DIR__ . '/AppTestRoutesCustomParams.txt', __DIR__ . '/AppTestConfig.txt');
        $this->assertEquals('title of blog post',$global);
        $routeMatch = array(
            'GET' => array(
                'blog/title/:blogTitle' =>
                function ($title, callable $next, $di) {
                    $di->get('DHP\Response')->appendContent("DONE");
                    $next();
                }
            ),
            'POST' => array(),
            'DELETE' => array(),
            'PUT' => array(),
            'HEAD' => array(),
            'ANY' => array()
        );
        $this->assertEquals($routeMatch,$this->DI->get('DHP\Routing')->getRoutes());

    }
}

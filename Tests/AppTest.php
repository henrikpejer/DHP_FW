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
        $this->DI     = $DI;
        $this->object = $DI->get('DHP\App');
    }

    public function testAddController()
    {
        $this->expectOutputString("this is the title : title of blog post");
        eval('class AppController extends \DHP\blueprint\Controller{
/**
* @method GET
* @uri /title/:title
*/
public function title($title){
 echo "this is the title : {$title}";
}

        };');

        $this->object->addController('AppController', 'blog');
        $this->object->start();
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


}

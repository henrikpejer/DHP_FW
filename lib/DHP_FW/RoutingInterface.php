<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-31 18:58
 */
interface RoutingInterface {
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_ANY    = 'ANY';
    const ROUTE_CONTINUE     = 'YES';

    /**
     * Sets up routing.
     *
     * Event is not necessary, but preferable.
     *
     * @param dependencyInjection\DIInterface $DependencyInjector
     * @param EventInterface                  $Event
     */

    public function __construct(
        \DHP_FW\dependencyInjection\DIInterface $DependencyInjector,
        EventInterface $Event = NULL);

    /**
     * This will set up routing for a GET request. $closure will be called when a
     * route is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: get('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in closure above to "hello world blog post"
     *
     * @param string   $uri
     * @param callable $closure
     *
     * @return mixed
     */
    public function get($uri, callable $closure);

    /**
     * This will set up routing for a POST request. $closure will be called when a
     * route is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: post('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in closure above to "hello world blog post"
     * @param string   $uri
     * @param callable $closure
     * @return mixed
     */
    public function post($uri, callable $closure);

    /**
     * This will set up routing for a DELETE request. $closure will be called when a
     * route is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: delete('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in closure above to "hello world blog post"
     * @param string   $uri
     * @param callable $closure
     * @return mixed
     */
    public function delete($uri, callable $closure);

    /**
     * This will set up routing for a PUT request. $closure will be called when a
     * route is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: put('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in closure above to "hello world blog post"
     * @param string   $uri
     * @param callable $closure
     * @return mixed
     */
    public function put($uri, callable $closure);

    /**
     * This will set up routing for a HEAD request. $closure will be called when a
     * route is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: head('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in closure above to "hello world blog post"
     * @param string   $uri
     * @param callable $closure
     * @return mixed
     */
    public function head($uri, callable $closure);

    /**
     * This will set up routing for any type of request. $closure will be called when
     * a route is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: any('blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in closure above to "hello world blog post"
     *
     * @param string   $uri
     * @param callable $closure
     * @return mixed
     */
    public function any($uri, callable $closure);


    /**
     * This will set up routing for several types of requests at once. So if you want
     * the same route to get triggered for GET and POST-requests, the $methods
     * parameter is an array with POST and GET as values.
     *
     * $closure will be called when a route
     * is matched.
     *
     * Also, values in the URI will be sent as parameters to the method, if there are
     * parameters in the uri.
     *
     * Example:
     * route: verb(array('POST','GET'),'blog/:title',function($title){})
     * uri: blog/hello-world-blog-post
     *
     * This will set the $title-prameter in closure above to "hello world blog post"
     *
     * @param array $methods
     * @param       $uri
     * @param       $closure
     * @return mixed
     */
    public function verb(array $methods, $uri, callable $closure);

    /**
     * Returns the current set routes
     * @return array
     */
    public function routes();


    /**
     * Adds possibility to execute a closure when a certain parameter type
     * is matched for route. When closure is called, whatever is returned
     * will be passed to the route-method as a parameter.
     *
     * Lets say you want to have a user-object populated when a user-id
     * exists in a uri, then with this method you can add that functionality.
     *
     * Example:
     * url: http://example.com/admin/user/4/edit
     * route uri: admin/user/:userId/edit
     *
     * param('userId',function($userId){return loadUserWithID($userId);});
     *
     * The above code will execute the route code and whatever :userId was
     * will be switched for the value that the loadUserWithID-returns
     * @param string      $paramName
     * @param callable    $closure
     * @return mixed
     */
    public function param($paramName, callable $closure);

    /**
     * Adds the possibility to continue matching routes even though
     * we already found a match.
     *
     * This is usefull if we want to add a check if the user is logged
     * in and have admin rights for all uris that start with admin.
     *
     * Calling continueWithNextRoute would make app continue to look for
     * the next match.
     *
     * This must be called in every route that is not the final one.
     *
     * @return mixed|void
     */
    public function continueWithNextRoute();

    /**
     * Will start to match a route and return the matching route, or false.
     *
     * @param String $method the http-method used, ie GET, POST etc
     * @param String $uri the uri of the request
     * @return array||FALSE
     */
    public function match($method,$uri);
}

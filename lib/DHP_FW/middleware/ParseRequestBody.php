<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW\middleware;

/**
 * This will parse the data in the body of the request. Since this is not always
 * needed, we broke this functionality into a middleware.
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2013-02-01 14:58
 *
 */
class ParseRequestBody {
    private $request = NULL;

    /**
     * Gets the request object, proceeds to start the body parsing
     *
     * @param \DHP_FW\RequestInterface $request
     */
    public function __construct(\DHP_FW\RequestInterface $request){
        $this->request = $request;
        $this->request->body = $this->parseBodyData($this->request->body);
    }

    /**
     * This will parse request body. For the moment only used
     * when requests content-type is json, will json_decode
     * the body.
     *
     * @param String $body the contents of the request body
     *
     * @return mixed|null
     */
    protected function parseBodyData($body) {
        $requestContentType = strtolower($this->request->header('Content-Type'));
        switch (TRUE) {
            case ( strpos($requestContentType, 'json') !== FALSE ):
                $__body__ = new \DHP_FW\ParameterBagReadOnly( json_decode($body,
                                                                          TRUE) );
                break;
            default:
                $__body__ = $body;
                break;
        }
        return $__body__;
    }
}

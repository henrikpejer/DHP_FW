<?php
declare(encoding = "UTF8");
namespace DHP;

use DHP\Request;


/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:35
 */

class Response
{
    private $headerStatusCodes = array(
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        409 => 'Conflict',
        500 => 'Internal Server Error'
    );

    private $headers = array('status' => array('200', NULL));
    private $body = NULL;
    private $request = NULL;

    /**
     * Sets up the request
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * This will send headers and data
     */
    public function __toString()
    {
        $this->sendHeaders();
        $this->sendBody();
        return '';
    }

    /**
     * The body can contain either a string or object / array. IF it is an object or array
     * it will, later on, get json-serialized and sent as json.
     *
     * @param mixed $bodyData set the data to be sent via the request
     */
    public function setBody($bodyData)
    {
        $this->body = $bodyData;
    }

    /**
     * A simply way of setting a status header
     *
     * @param $int
     * @param null $headerData extra, string to use in conjuction with header, such as 201 location: xxxx
     */
    public function setStatus($int, $headerData = NULL)
    {
        $this->headers['status'] = array($int, $headerData);
    }

    /**
     * Here we send the headers of the application
     * @return bool
     * @throws \RuntimeException
     */
    private function sendHeaders()
    {
        if (headers_sent() === TRUE) {
            throw new \RuntimeException("Headers already sent");
        }
        # todo : walk through headers, send them of one after another
        return true;
    }

    /**
     * Here we format and send the body
     */
    private function sendBody()
    {
        if (is_string($this->body)) {
            echo $this->body;
        } else {
            echo json_encode($this->body,JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT);
        }
    }
}

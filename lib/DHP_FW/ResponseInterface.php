<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-01 05:35
 */

interface ResponseInterface {

    /**
     * @param EventInterface $event
     */
    function __construct(\DHP_FW\EventInterface $event);


    /**
     * @return boolean if cacheObject already have been sent or not
     */
    function cacheSent();

    /**
     * This will send headers and echo whatever is in the $data-variable
     *
     * @param int  $dataOrStatus the status, an int, http status
     * @param null $data         Data to be sent
     *
     * @return null
     */
    function send($dataOrStatus, $data = NULL);

    /**
     * This sets a header. Since not all headers have a :, you do not need to
     * include the second parameter, '$value'.
     *
     * @param string     $name  Name of the header, whatever is before :
     * @param null       $value the value of the header, whatever is after :
     * @param bool       $processHeaderName
     *
     * @return $this
     */
    function header($name, $value = NULL, $processHeaderName = TRUE);

    /**
     * This sets the status of the response. This method will
     * generate a statu-header.
     *
     * @param int  $httpNumber  the status number, an int
     * @param null $httpMessage the message accompaning the http-status number
     *
     * @return null
     */
    function status($httpNumber, $httpMessage = NULL);

    /**
     * This will send a file, together with headers for mimetype, filename.
     * Setting downloadfile to true should force the browser to download the file.
     *
     * If no mimetype is specified, finfo will be used to try to figure out the
     * mimetype
     *
     * @param String     $filePath     Path to the file that should be sent
     * @param null       $mimeType     string, mimetype
     * @param null       $fileName     optional filename
     * @param bool       $downLoadFile If the file should be downloaded or not
     *
     * @return null
     * @throws \Exception
     */
    function sendFile($filePath, $mimeType = NULL, $fileName = NULL,
        $downLoadFile = FALSE);

    /**
     * A wrapper function for sendFile, with $downloadFile set to true
     *
     * @param string     $filePath Path to the file that should be sent
     * @param null       $mimeType string, mimetype
     * @param null       $fileName optional filename
     *
     * @return null
     * @throws \Exception
     */
    function downloadFile($filePath, $mimeType = NULL, $fileName = NULL);

    /**
     * Will send redirect headers.
     *
     * @param  string    $url          the url to redirect to, must be a complete URL
     * @param int        $httpStatus   , together with status int
     * @param null       $httpMessage  optional http-status message
     *
     * @return null
     */
    function redirect($url, $httpStatus = 301, $httpMessage = NULL);

    /**
     * If we should skip sending the headers. IF set to true, no headers will
     * be sent.
     *
     * @param bool $doSurpress if headers should be surpressed or not
     *
     * @return null
     */
    function supressHeaders($doSurpress = TRUE);

    /**
     * This method will check if a cacheObject of the response exists in cacheObject
     * and will send that immediately.
     *
     * @return boolean true if cacheObject was used, false if not
     */
    function checkCache();
}

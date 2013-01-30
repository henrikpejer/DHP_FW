<?php
declare( encoding = "UTF8" ) ;
namespace DHP_FW;
/**
 * This class handles errors and exceptions encountered throught the app
 *
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-29 22:06
 */
class ErrorHandler {
    protected $phpErrorCodes = array(1     => 'E_ERROR',
                                     2     => 'E_WARNING',
                                     4     => 'E_PARSE',
                                     8     => 'E_NOTICE',
                                     16    => 'E_CORE_ERROR',
                                     32    => 'E_CORE_WARNING',
                                     64    => 'E_COMPILE_ERROR',
                                     128   => 'E_COMPILE_WARNING',
                                     256   => 'E_USER_ERROR',
                                     512   => 'E_USER_WARNING',
                                     1024  => 'E_USER_NOTICE',
                                     2048  => 'E_STRICT',
                                     4096  => 'E_RECOVERABLE_ERROR',
                                     8192  => 'E_DEPRECATED',
                                     16384 => 'E_USER_DEPRECATED',
                                     30719 => 'E_ALL');

    /**
     * This sets up the error library. We want the event-object
     * to be populated so we can send events when errors and exceptions
     * are caught.
     * 
     * If event is null, we do not send any events
     * 
     * @param EventInterface $event
     */
    public function __construct(\DHP_FW\EventInterface $event = NULL) {
        $this->event = $event;
        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'phpErrorHandler'));
    }

    /**
     * Handles uncaught exceptions.
     *
     * @param \Exception $exception
     *
     * @return null
     */
    public function exceptionHandler(\Exception $exception) {
        # is this a PHP-error....?
        $code    = $exception->getCode();
        $message = $exception->getMessage();
        if ( get_class($exception) === "ErrorException" ) {
            $code = isset( $this->phpErrorCodes[$exception->getCode()] ) ?
                    $this->phpErrorCodes[$exception->getCode()] : 'E_UNKNOWN';
        }
        // todo: make this handle exception outputs somewhat better
        echo "Uncaught exception\n{$code} : '{$message}'";
        return NULL;
    }

    /**
     * This catches PHP-errors, then routes them to an exception
     * making them catch-able...
     *
     * @param Int    $errno
     * @param String $errstr
     * @param String $errfile
     * @param int    $errline
     *
     * @throws \ErrorException
     */
    public function phpErrorHandler($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException( $errstr, $errno, 0, $errfile, $errline );
    }
}

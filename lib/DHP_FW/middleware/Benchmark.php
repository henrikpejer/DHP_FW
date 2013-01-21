<?php
declare(encoding = "UTF8") ;
namespace DHP_FW\middleware;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-01-19 00:04
 */
class Benchmark implements MiddlewareInterface{
    private $timeStart, $memStart = NULL;
    function __construct(\DHP_FW\Event $event){
        $this->timeStart = microtime(TRUE);
        $this->memStart = 0;
        $self = $this;
        $event->register('DHP_FW.Response.send',function($status,&$data)use($self){
            $data .= "\n<!-- ";
            $data .= sprintf('time: %.4F s, memory: %.4F MB',(microtime(TRUE) - $this->timeStart),(((memory_get_peak_usage(TRUE) - $this->memStart)/1024)/1024));
            $data .= " -->";
        });
    }
}

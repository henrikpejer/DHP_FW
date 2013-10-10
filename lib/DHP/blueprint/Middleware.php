<?php
declare(encoding = "UTF8");
namespace DHP\blueprint;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-03-30 23:44
 */
class Middleware
{
    // @codeCoverageIgnoreStart
    public function run()
    {
        throw new \RuntimeException("Run-method in middleware must be implemented");
    }
    // @codeCoverageIgnoreEnd
}

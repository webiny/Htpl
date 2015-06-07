<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Processor;


use Webiny\Htpl\Processor\Layout;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $layout = new Layout('source string', ['fileA.htpl', 'fileB.htpl']);
        $this->assertInstanceOf('\Webiny\Htpl\Processor\Layout', $layout);

        $this->assertSame('source string', $layout->getSource());
        $layout->setSource('new source');
        $this->assertSame('new source', $layout->getSource());

        $this->assertSame(['fileA.htpl', 'fileB.htpl'], $layout->getIncludedFiles());

        $this->assertSame(time(), $layout->getLastTouched());
        $layout->setLastTouched('100');
        $this->assertSame('100', $layout->getLastTouched());
    }
}
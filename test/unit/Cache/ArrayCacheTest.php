<?php
namespace Webiny\Htpl\UnitTests\Cache;

use Webiny\Htpl\Cache\ArrayCache;

class ArrayCacheTest extends \PHPUnit_Framework_TestCase
{

    public function testBasic()
    {
        $arrayCache = new ArrayCache();
        $arrayCache->write('file1', 'content');
        $this->assertSame('content', $arrayCache->read('file1'));
        $arrayCache->write('file2', 'content 2');
        $this->assertSame('content 2', $arrayCache->read('file2'));
        $this->assertSame('content', $arrayCache->read('file1'));
        $this->assertSame(2, count($arrayCache->dumpCache()));
        $arrayCache->delete('file2');
        $this->assertFalse($arrayCache->read('file2'));
    }
}
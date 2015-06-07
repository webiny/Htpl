<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\TemplateProviders;

class ArrayProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $ap = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $this->assertInstanceOf('\Webiny\Htpl\TemplateProviders\TemplateProviderInterface', $ap);
    }

    public function testBasics()
    {
        $ap = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>'bar']);
        $this->assertSame('bar', $ap->getSource('test'));
        $this->assertSame('test', $ap->getCacheKey('test'));
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage Template foo not found
     */
    public function testGetSourceException()
    {
        $ap = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $ap->getSource('foo');
    }


}
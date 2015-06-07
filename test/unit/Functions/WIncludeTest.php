<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Functions;

class WIncludeTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTag()
    {
        $instance = new \Webiny\Htpl\Functions\WInclude();
        $tag = $instance->getTag();

        $this->assertSame('w-include', $tag);
    }

    public function testLexerTagParsing()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>'<w-include file="someVar"/>']);
        $htpl = new \Webiny\Htpl\Htpl($provider);
        $htpl->assign('someVar', 'someTemplate.htpl');

        $result = $htpl->build('test')->getSource();
        $this->assertSame('<?php Webiny\Htpl\Functions\WInclude::htpl('.\Webiny\Htpl\Processor\OutputWrapper::getVar('someVar').', $this->getHtplInstance()) ?>', $result);
    }

    public function testLexerTagParsing2()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([
            'test'          =>  '<w-include file="include.htpl"/>',
            'include.htpl'  =>  'Hello World'
        ]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $result = $htpl->fetch('test');
        $this->assertSame('Hello World', $result);
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage w-include must have a "file" attribute defined
     */
    public function testParseTagException()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WInclude();
        $instance->parseTag('', null, $htpl);
    }
}
<?php
namespace Webiny\Htpl\UnitTests\Functions;

class WIncludeTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTag()
    {
        $instance = new \Webiny\Htpl\Functions\WInclude();
        $tag = $instance->getTag();

        $this->assertSame('w-include', $tag);
    }

    public function testParseTag()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WInclude();
        $result = $instance->parseTag('', ['file'=>'include.htpl'], $htpl);

        $this->assertSame('', $result['openingTag']);
        $this->assertSame('<?php Webiny\Htpl\Functions\WInclude::htpl("include.htpl", $this->getHtplInstance()) ?>', $result['content']);
        $this->assertSame('', $result['closingTag']);
    }

    public function testLexerTagParsing()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>'<w-include file="include.htpl"/>']);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $result = $htpl->build('test')->getSource();
        $this->assertSame('<?php Webiny\Htpl\Functions\WInclude::htpl("include.htpl", $this->getHtplInstance()) ?>', $result);
    }

    public function testLexerTagParsing2()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>'<w-include file="someVar"/>']);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $result = $htpl->build('test')->getSource();
        $this->assertSame('<?php Webiny\Htpl\Functions\WInclude::htpl('.\Webiny\Htpl\Processor\OutputWrapper::getVar('someVar').', $this->getHtplInstance()) ?>', $result);
    }

    public function testLexerTagParsing3()
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
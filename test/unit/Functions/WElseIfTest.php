<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Functions;

class WElseIfTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTag()
    {
        $instance = new \Webiny\Htpl\Functions\WElseIf();
        $tag = $instance->getTag();

        $this->assertSame('w-elseif', $tag);
    }

    public function testParseTag()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WElseIf();
        $result = $instance->parseTag('', ['cond'=>'1>2'], $htpl);

        $this->assertSame('<?php } elseif (1>2) { ?>', $result['openingTag']);
        $this->assertSame('', $result['closingTag']);
    }

    public function testLexerTagParsing()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>'<w-elseif cond="1>2"/>']);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $result = $htpl->build('test')->getSource();
        $this->assertSame('<?php } elseif (1>2) { ?>', $result);
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage w-elseif must have a logical condition
     */
    public function testParseTagException()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WElseIf();
        $instance->parseTag('', null, $htpl);
    }
}
<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Functions;

class WElseTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTag()
    {
        $instance = new \Webiny\Htpl\Functions\WElse();
        $tag = $instance->getTag();

        $this->assertSame('w-else', $tag);
    }

    public function testParseTag()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WElse();
        $result = $instance->parseTag('', null, $htpl);

        $this->assertSame('', $result['openingTag']);
        $this->assertSame('<?php } else { ?>', $result['content']);
        $this->assertSame('', $result['closingTag']);
    }

    public function testLexerTagParsing()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>'<w-else/>']);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $result = $htpl->build('test')->getSource();
        $this->assertSame('<?php } else { ?>', $result);
    }

    public function testLexerTagParsing2()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>'<w-else></w-else>']);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $result = $htpl->build('test')->getSource();
        $this->assertSame('<?php } else { ?>', $result);
    }
}
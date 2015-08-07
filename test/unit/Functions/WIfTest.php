<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Functions;

class WIfTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTag()
    {
        $instance = new \Webiny\Htpl\Functions\WIf();
        $tag = $instance->getTag();

        $this->assertSame('w-if', $tag);
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @dataProvider parseTagProvider
     */
    public function testParseTag($cond, $expectedResult)
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WIf();
        $result = $instance->parseTag('', ['cond'=>$cond], $htpl);

        $this->assertSame($expectedResult, $result['openingTag']);
        $this->assertSame('<?php } ?>', $result['closingTag']);
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage w-if must have a logical condition
     */
    public function testParseTagException()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WIf();
        $instance->parseTag('', null, $htpl);
    }

    /**
     * @dataProvider parseTagProvider
     */
    public function testLexerTagParsing($cond, $expectedResult)
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>'<w-if cond="'.$cond.'"></w-if>']);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $result = $htpl->build('test')->getSource();

        $this->assertSame($expectedResult.'<?php } ?>', $result);
    }

    public function parseTagProvider()
    {
        return[
            ["1>2", '<?php if (1>2) { ?>'],
            ["'a'!='b'", '<?php if (\'a\'!=\'b\') { ?>'],
            ["var=='test'", '<?php if (htmlspecialchars($this->getVar(\'var\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')==\'test\') { ?>'],
            ["a!='b'", '<?php if (htmlspecialchars($this->getVar(\'a\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')!=\'b\') { ?>'],
            ["a!=b", '<?php if (htmlspecialchars($this->getVar(\'a\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')!=htmlspecialchars($this->getVar(\'b\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')) { ?>'],
            ["someVar>'10'", '<?php if (htmlspecialchars($this->getVar(\'someVar\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')>\'10\') { ?>'],
            ["someVar>10", '<?php if (htmlspecialchars($this->getVar(\'someVar\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')>10) { ?>'],
            ["some.var!='test.var'", '<?php if (htmlspecialchars($this->getVar(\'some.var\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')!=\'test.var\') { ?>'],
            ["var12!='test'", '<?php if (htmlspecialchars($this->getVar(\'var12\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')!=\'test\') { ?>'],
        ];
    }
}
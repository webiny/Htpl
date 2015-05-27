<?php
namespace Webiny\Htpl\UnitTests\Functions;

class WLoopTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTag()
    {
        $instance = new \Webiny\Htpl\Functions\WLoop();
        $tag = $instance->getTag();

        $this->assertSame('w-loop', $tag);
    }

    public function testParseTag()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WLoop();
        $result = $instance->parseTag('TEST', ['items' => 'items', 'var' => 'var'], $htpl);

        $this->assertSame('<?php foreach (' . \Webiny\Htpl\Processor\OutputWrapper::getVar('items') . ' as $var){ ?>',
            $result['openingTag']);
        $this->assertSame('TEST', $result['content']);
        $this->assertSame('<?php } ?>', $result['closingTag']);
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage w-loop function requires `items` attribute to be defined
     */
    public function testParseTagItemsException()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WLoop();
        $instance->parseTag('TEST', ['var' => 'var'], $htpl);
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage w-loop function requires `var` attribute to be defined
     */
    public function testParseTagVarException()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WLoop();
        $instance->parseTag('TEST', ['items' => 'items'], $htpl);
    }

    public function testLexerTagParsing()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([
            'test' => '<w-loop items="items" var="var"><li>{var}</li></w-loop>'
        ]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        // source check
        $result = $htpl->build('test')->getSource();
        $expectedResult = '<?php foreach (' . \Webiny\Htpl\Processor\OutputWrapper::getVar('items') . ' as $var){ ?><li><?= $this->escape($var);?></li><?php } ?>';
        $this->assertSame(trim($expectedResult), trim($result));

        // output check
        $result = $htpl->build('test', ['items' => ['ItemA', 'ItemB']])->fetch();
        $this->assertSame('<li>ItemA</li><li>ItemB</li>', $result);
    }

    public function testLexerTagParsingWithKey()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([
            'test' => '<w-loop items="items" var="var" key="key"><li>{key}=>{var}</li></w-loop>'
        ]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        // source check
        $result = $htpl->build('test')->getSource();
        $expectedResult = '<?php foreach (' . \Webiny\Htpl\Processor\OutputWrapper::getVar('items') . ' as $key => $var)';
        $expectedResult .= '{ ?><li><?= $this->escape($key);?>=><?= $this->escape($var);?></li><?php } ?>';
        $this->assertSame(trim($expectedResult), trim($result));

        // output check
        $result = $htpl->build('test', [
            'items' => [
                'A' => 'ItemA',
                'B' => 'ItemB'
            ]
        ])->fetch();
        $this->assertSame('<li>A=>ItemA</li><li>B=>ItemB</li>', trim($result));
    }

    public function testLexerTagParsingWithKeyNested()
    {
        $tpl = '<w-loop items="items" var="var" key="key">';
        $tpl .='<li>';
        $tpl .='{key}=>{var.val}';
        $tpl .='<w-loop items="innerItems" var="iEntry" key="iKey">';
        $tpl .='<span>{key} => {iEntry.name} => {iKey}</span>';
        $tpl .='</w-loop>';
        $tpl .='</li>';
        $tpl .='</w-loop>';

        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test' => $tpl]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        // source check
        $data = [
            'items'      => [
                'A' => ['val' => 'ItemA'],
                'B' => ['val' => 'ItemB'],
            ],
            'innerItems' => [
                'C' => ['name' => 'ItemC'],
                'D' => ['name' => 'ItemD']
            ]
        ];
        $expectedResult = '<li>A=>ItemA
<span>A => ItemC => C</span><span>A => ItemD => D</span>
</li><li>B=>ItemB
<span>B => ItemC => C</span><span>B => ItemD => D</span>
</li>';
        $result = $htpl->build('test', $data)->fetch();
        $this->assertSame($expectedResult, trim($result));
    }
}
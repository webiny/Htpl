<?php

namespace Webiny\Htpl\UnitTests\Processor\Lexers;

use Webiny\Htpl\Processor\Lexers\TagLexer;

class TagLexerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param $tpl
     * @param $name
     * @param $attrs
     * @param $outerHtml
     * @param $content
     *
     * @throws \Webiny\Htpl\HtplException
     * @dataProvider parseProvider
     */
    public function testParse($tpl, $name, $attrs, $outerHtml, $content)
    {
        $result = TagLexer::parse($tpl)->getLexedTags();

        $this->assertSame(1, count($result));
        $this->assertSame($name, $result[0]['name']);
        $this->assertSame($attrs, $result[0]['attributes']);
        $this->assertSame($outerHtml, $result[0]['outerHtml']);
        $this->assertSame($content, trim($result[0]['content']));
    }

    public function parseProvider()
    {
        return [
            [
                '<w-include file="file.htpl"/>',
                'w-include',
                ['file'=>'file.htpl'],
                '<w-include file="file.htpl"/>',
                ''
            ],
            [
                '<w-include file="file.htpl" />',
                'w-include',
                ['file'=>'file.htpl'],
                '<w-include file="file.htpl" />',
                ''
            ],
            [
                '<w-include file="file.htpl " />',
                'w-include',
                ['file'=>'file.htpl '],
                '<w-include file="file.htpl " />',
                ''
            ],
            [
                '<w-foo/>',
                'w-foo',
                [],
                '<w-foo/>',
                ''
            ],
            [
                '<w-foo-bar/>',
                'w-foo-bar',
                [],
                '<w-foo-bar/>',
                ''
            ],
            [
                '<w-foo-bar a="b" b="c"/>',
                'w-foo-bar',
                ['a'=>'b', 'b'=>'c'],
                '<w-foo-bar a="b" b="c"/>',
                ''
            ],
            [
                '<w-foo-bar a=""/>',
                'w-foo-bar',
                ['a'=>''],
                '<w-foo-bar a=""/>',
                ''
            ],
            [
                '<w-foo-bar a="1"/>',
                'w-foo-bar',
                ['a'=>'1'],
                '<w-foo-bar a="1"/>',
                ''
            ],
            [
                '<w-foo>
                    some content
                </w-foo>',
                'w-foo',
                [],
                '<w-foo>
                    some content
                </w-foo>',
                'some content'
            ],
            [
                '<w-foo a="foo bar">
                    some content<br/>
                </w-foo>',
                'w-foo',
                ['a'=>'foo bar'],
                '<w-foo a="foo bar">
                    some content<br/>
                </w-foo>',
                'some content<br/>'
            ],
            [
                '<br/><br/><br/>
                <w-foo a="foo bar">
                    some content<br/>
                </w-foo>',
                'w-foo',
                ['a'=>'foo bar'],
                '<w-foo a="foo bar">
                    some content<br/>
                </w-foo>',
                'some content<br/>'
            ],
            [
                '<br/><br/><br/>
                <w-foo a="foo bar">
                    some content<br/>
                </w-foo>
                <br/><br/><br/>',
                'w-foo',
                ['a'=>'foo bar'],
                '<w-foo a="foo bar">
                    some content<br/>
                </w-foo>',
                'some content<br/>'
            ],
        ];
    }

    public function testParse2()
    {
        $tpl = '<br/>';
        $result = TagLexer::parse($tpl)->getLexedTags();

        $this->assertSame(0, count($result));
    }

    public function testParse3()
    {
        $tpl = '
        <ul>
            <w-loop items="entry" var="v" key="k">
                <w-if cond="k==\'name\' || k==\'id\' || k==\'item_order\'">
                    <li><strong>{k}:</strong> {v}</li>
                </w-if>
            </w-loop>
        </ul>
        ';

        $result = TagLexer::parse($tpl)->getLexedTags();

        $this->assertSame(2, count($result));

        // w-if check
        $this->assertSame('w-if', $result[0]['name']);
        $this->assertSame(['cond'=>'k==\'name\' || k==\'id\' || k==\'item_order\''], $result[0]['attributes']);
        $outerHtml ='<w-if cond="k==\'name\' || k==\'id\' || k==\'item_order\'">
                    <li><strong>{k}:</strong> {v}</li>
                </w-if>';
        $this->assertSame($outerHtml, $result[0]['outerHtml']);
        $content = '<li><strong>{k}:</strong> {v}</li>';
        $this->assertSame($content, trim($result[0]['content']));

        // w-loop check
        $this->assertSame('w-loop', $result[1]['name']);
        $this->assertSame(['items'=>'entry', 'var'=>'v', 'key'=>'k'], $result[1]['attributes']);
        $outerHtml ='<w-loop items="entry" var="v" key="k">
                <w-if cond="k==\'name\' || k==\'id\' || k==\'item_order\'">
                    <li><strong>{k}:</strong> {v}</li>
                </w-if>
            </w-loop>';
        $this->assertSame($outerHtml, $result[1]['outerHtml']);
        $content = '<w-if cond="k==\'name\' || k==\'id\' || k==\'item_order\'">
                    <li><strong>{k}:</strong> {v}</li>
                </w-if>';
        $this->assertSame($content, trim($result[1]['content']));
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage Unable to parse the template
     */
    public function testParseException()
    {
        $tpl = '<w-foo>
                    some content
                </w-bar>';
        $result = TagLexer::parse($tpl)->getLexedTags();

        $this->assertSame(0, count($result));
    }


}
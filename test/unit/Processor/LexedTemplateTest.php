<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Processor;

use Webiny\Htpl\Processor\LexedTemplate;

class LexedTemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $instance = new LexedTemplate([], 'source');
        $this->assertInstanceOf('\Webiny\Htpl\Processor\LexedTemplate', $instance);
    }

    public function testGetLexedTags()
    {
        $instance = new LexedTemplate(['tag1', 'tag2'], 'source');
        $this->assertSame(['tag1', 'tag2'], $instance->getLexedTags());
    }

    public function testGetTemplate()
    {
        $instance = new LexedTemplate(['tag1', 'tag2'], 'source');
        $this->assertSame('source', $instance->getTemplate());
    }

    public function testSelect()
    {
        $tags = [
            [
                'name' => 'w-if'
            ],
            [
                'name'       => 'w-if',
                'attributes' => ['cond' => 'true']
            ],
            [
                'name'       => 'w-if',
                'attributes' => ['cond' => 'false']
            ],
            [
                'name' => 'w-foo'
            ]
        ];

        $instance = new LexedTemplate($tags, 'source');
        $result = $instance->select('w-if');
        $this->assertSame(3, count($result));

        $result = $instance->select('w-foo');
        $this->assertSame('w-foo', $result[0]['name']);

        $result = $instance->select('w-if', ['cond'=>'false']);
        $this->assertSame(1, count($result));
        $this->assertSame('w-if', $result[0]['name']);
    }

}
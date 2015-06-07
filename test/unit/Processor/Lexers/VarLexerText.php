<?php

namespace Webiny\Htpl\UnitTests\Processor\Lexers;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\Processor\Lexers\VarLexer;
use Webiny\Htpl\TemplateProviders\ArrayProvider;

class VarLexerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param $str
     * @param $expectedResult
     *
     * @throws \Webiny\Htpl\HtplException
     * @dataProvider parseProvider
     */
    public function testParse($str, $expectedResult)
    {
        $htpl = new Htpl(new ArrayProvider([]));
        $result = VarLexer::parse($str, $htpl);

        $this->assertSame($expectedResult, $result);
    }

    public function parseProvider()
    {
        return[
            [
                '{var}',
                '<?= $this->escape($this->getVar(\'var\', $this->vars));?>'
            ],
            [
                '{varA} {varB}',
                '<?= $this->escape($this->getVar(\'varA\', $this->vars));?> <?= $this->escape($this->getVar(\'varB\', $this->vars));?>'
            ],
            [
                '{var.name}',
                '<?= $this->escape($this->getVar(\'var.name\', $this->vars));?>'
            ],
            [
                '{var.name|upper}',
                '<?= $this->escape(\Webiny\Htpl\Modifiers\CorePack::upper($this->getVar(\'var.name\', $this->vars)));?>'
            ],
            [
                '{var.name|nl2br}',
                '<?= \Webiny\Htpl\Modifiers\CorePack::nl2br($this->escape($this->getVar(\'var.name\', $this->vars)));?>'
            ],
            [
                '{var.name|nl2br|upper}',
                '<?= \Webiny\Htpl\Modifiers\CorePack::nl2br($this->escape(\Webiny\Htpl\Modifiers\CorePack::upper($this->getVar(\'var.name\', $this->vars))));?>'
            ],
            [
                '{var.name|upper|nl2br}',
                '<?= \Webiny\Htpl\Modifiers\CorePack::nl2br($this->escape(\Webiny\Htpl\Modifiers\CorePack::upper($this->getVar(\'var.name\', $this->vars))));?>'
            ],
            [
                '{var.name|upper|lower}',
                '<?= $this->escape(\Webiny\Htpl\Modifiers\CorePack::lower(\Webiny\Htpl\Modifiers\CorePack::upper($this->getVar(\'var.name\', $this->vars))));?>'
            ],
            [
                '{var.name|lower|upper}',
                '<?= $this->escape(\Webiny\Htpl\Modifiers\CorePack::upper(\Webiny\Htpl\Modifiers\CorePack::lower($this->getVar(\'var.name\', $this->vars))));?>'
            ],
            [
                '{"var.name"|upper}',
                '<?= $this->escape(\Webiny\Htpl\Modifiers\CorePack::upper("var.name"));?>'
            ],
            [
                '{{"one", "two"}|first}',
                '<?= $this->escape(\Webiny\Htpl\Modifiers\CorePack::first(["one","two"]));?>'
            ],
            [
                '{var.name|replace({"name":"john snow"})}',
                '<?= $this->escape(\Webiny\Htpl\Modifiers\CorePack::replace($this->getVar(\'var.name\', $this->vars), ["name"=>"john snow"]));?>'
            ],
        ];
    }
}
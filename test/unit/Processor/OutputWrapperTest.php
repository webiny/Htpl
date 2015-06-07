<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Processor;


use Webiny\Htpl\Processor\OutputWrapper;

class OutputWrapperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetVar()
    {
        $this->assertSame('$this->getVar(\'var\', $this->vars)', OutputWrapper::getVar('var'));
        $this->assertSame('$this->getVar(\'var\', $context)', OutputWrapper::getVar('var', '$context'));
        $this->assertSame('$this->getVar(\'var.a\', $context)', OutputWrapper::getVar('var.a', '$context'));
        $this->assertSame('$this->getVar(\'context.test\', $context)', OutputWrapper::getVar('context.test', '$context'));
        $this->assertSame('$this->getVar(\'test\', $context)', OutputWrapper::getVar('context.test', '$context', true));
    }

    public function testOutputVar()
    {
        $this->assertSame('<?php echo $var;?>', trim(OutputWrapper::outputVar('$var')));
    }

    public function testOutputFunction()
    {
        $this->assertSame('<?php someFunction() ?>', trim(OutputWrapper::outputFunction('someFunction()')));
    }

    public function testEscape()
    {
        $this->assertSame('htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')', trim(OutputWrapper::escape('$var')));
    }
}
<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Processor;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\Processor\Compiler;
use Webiny\Htpl\TemplateProviders\ArrayProvider;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCompiledTemplate()
    {
        $provider = new ArrayProvider(['test.htpl'=>'{var}']);
        $htpl = new Htpl($provider);
        
        $compiler = new Compiler($htpl);
        $result = $compiler->getCompiledTemplate('test.htpl');

        $this->assertInstanceOf('Webiny\Htpl\Processor\Template', $result);

        $this->assertSame('<?php echo htmlspecialchars($this->getVar(\'var\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\');?>', trim($result->getSource()));
    }

    public function testGetCompiledTemplateLiteral()
    {
        $provider = new ArrayProvider(['test.htpl' => '<w-literal>{var}</w-literal>']);
        $htpl = new Htpl($provider);

        $compiler = new Compiler($htpl);
        $result = $compiler->getCompiledTemplate('test.htpl');

        $this->assertInstanceOf('Webiny\Htpl\Processor\Template', $result);

        $this->assertSame('{var}', trim($result->getSource()));
    }

}
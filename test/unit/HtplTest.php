<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests;

use Webiny\Htpl\Cache\ArrayCache;
use Webiny\Htpl\Htpl;
use Webiny\Htpl\TemplateProviders\ArrayProvider;
use Webiny\Htpl\UnitTests\Mocks\MockModifierPack;
use Webiny\Htpl\UnitTests\Mocks\WMockFunction;

class HtplTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);

        $htpl = new Htpl($provider);
        $this->assertInstanceOf('Webiny\Htpl\Htpl', $htpl);
    }

    public function testGetTemplateProvider()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);

        $htpl = new Htpl($provider);
        $this->assertSame($provider, $htpl->getTemplateProvider());
    }

    public function testGetCache()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);
        $cache = new ArrayCache();

        $htpl = new Htpl($provider, $cache);
        $this->assertSame($cache, $htpl->getCache());
    }

    public function testSetGetOptions()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);

        $htpl = new Htpl($provider);
        $this->assertArrayHasKey('forceCompile', $htpl->getOptions());
        $this->assertArrayHasKey('lexer', $htpl->getOptions());
        $this->assertArrayHasKey('minify', $htpl->getOptions());
        $this->assertArrayHasKey('cacheValidationTTL', $htpl->getOptions());

        $htpl->setOptions(['foo' => 'bar']);
        $htpl->setOptions(['one' => 'two']);
        $this->assertSame('bar', $htpl->getOptions()['foo']);
        $this->assertSame('two', $htpl->getOptions()['one']);
    }

    public function testSetGetForceCompile()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);

        $htpl = new Htpl($provider);
        $this->assertSame(false, $htpl->getOptions()['forceCompile']);
        $this->assertSame(false, $htpl->getForceCompile());

        $htpl->setForceCompile(true);
        $this->assertSame(true, $htpl->getOptions()['forceCompile']);
        $this->assertSame(true, $htpl->getForceCompile());
    }

    public function testBuild()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);

        $htpl = new Htpl($provider);
        $this->assertInstanceOf('Webiny\Htpl\Processor\Template', $htpl->build('test.htpl'));
    }

    /**
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage Template test.htpl not found
     */
    public function testBuildException()
    {
        $provider = new ArrayProvider(['fooBar' => '{var}']);

        $htpl = new Htpl($provider);
        $this->assertInstanceOf('Webiny\Htpl\Processor\Template', $htpl->build('test.htpl'));
    }

    public function testFetch()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);
        $htpl = new Htpl($provider);
        $htpl->assign('var', 'FooBar');

        $this->assertSame('FooBar', $htpl->build('test.htpl')->fetch());
    }

    public function testDisplay()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);
        $htpl = new Htpl($provider);
        $htpl->assign('var', 'FooBar');

        ob_start();
        $htpl->build('test.htpl')->display();
        $result = ob_get_clean();

        $this->assertSame('FooBar', $result);
    }

    public function testAssign()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);
        $htpl = new Htpl($provider);
        $this->assertSame([], $htpl->getVars());

        $htpl->assign('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $htpl->getVars());

        $htpl->assignArray(['test' => 'test']);
        $this->assertSame(['foo' => 'bar', 'test' => 'test'], $htpl->getVars());
    }

    public function testRegisterFunction()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);
        $htpl = new Htpl($provider);
        $functions = $htpl->getFunctions();

        $this->assertArrayHasKey('w-if', $functions);
        $this->assertArrayHasKey('w-else', $functions);
        $this->assertArrayHasKey('w-elseif', $functions);
        $this->assertArrayHasKey('w-include', $functions);
        $this->assertArrayHasKey('w-loop', $functions);
        $this->assertArrayHasKey('w-minify', $functions);

        require_once __DIR__ . '/Mocks/WMockFunction.php';
        $mockFunction = new WMockFunction();
        $htpl->registerFunction($mockFunction);
        $functions = $htpl->getFunctions();

        $this->assertArrayHasKey('w-mock', $functions);
        $this->assertArrayHasKey('w-if', $functions);
        $this->assertArrayHasKey('w-else', $functions);
        $this->assertArrayHasKey('w-elseif', $functions);
        $this->assertArrayHasKey('w-include', $functions);
        $this->assertArrayHasKey('w-loop', $functions);
        $this->assertArrayHasKey('w-minify', $functions);
        $this->assertInstanceOf('Webiny\Htpl\UnitTests\Mocks\WMockFunction', $functions['w-mock']);
    }

    public function testRegisterModifierPack()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);
        $htpl = new Htpl($provider);
        $modifiers = $htpl->getModifiers();

        $this->assertArrayHasKey('abs', $modifiers);
        $this->assertArrayHasKey('firstUpper', $modifiers);
        $this->assertArrayHasKey('nl2br', $modifiers);

        require_once __DIR__ . '/Mocks/MockModifierPack.php';
        $mockMods = new MockModifierPack();
        $htpl->registerModifierPack($mockMods);
        $modifiers = $htpl->getModifiers();

        $this->assertArrayHasKey('mock-mod', $modifiers);
        $this->assertArrayHasKey('abs', $modifiers);
        $this->assertArrayHasKey('firstUpper', $modifiers);
        $this->assertArrayHasKey('nl2br', $modifiers);
    }

    public function testSetGetLexerTags()
    {
        $provider = new ArrayProvider(['test.htpl' => '{var}']);
        $htpl = new Htpl($provider);

        $this->assertSame(['varStartFlag' => '{', 'varEndFlag' => '}'], $htpl->getLexerFlags());

        $htpl->setLexerFlags('{{', '}}');
        $this->assertSame(['varStartFlag' => '{{', 'varEndFlag' => '}}'], $htpl->getLexerFlags());
    }

}
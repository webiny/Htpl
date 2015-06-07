<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Processor;


use Webiny\Htpl\Htpl;
use Webiny\Htpl\Processor\Template;
use Webiny\Htpl\TemplateProviders\ArrayProvider;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $provider = new ArrayProvider(['test.htpl'=>'{var}']);
        $htpl = new Htpl($provider);

        $template = new Template($htpl, 'some template string');
        $this->assertInstanceOf('Webiny\Htpl\Processor\Template', $template);

        $provider = new ArrayProvider(['test.htpl'=>'{var}']);
        $htpl = new Htpl($provider);
        $this->assertInstanceOf('Webiny\Htpl\Processor\Template', $htpl->build('test.htpl'));
    }

    public function testGetVar()
    {
        $provider = new ArrayProvider(['test.htpl'=>'{var}']);
        $htpl = new Htpl($provider);
        $htpl->assignArray(['level1'=>['level2' => ['foo'=>'bar']]]);
        $htpl->assign('testFoo', 'testBar');

        $template = new Template($htpl, 'some template string');
        $this->assertSame('bar', $template->getVar('level1.level2.foo', $htpl->getVars()));
        $this->assertSame('testBar', $template->getVar('testFoo', $htpl->getVars()));
    }

    public function testGetHtplInstance()
    {
        $provider = new ArrayProvider(['test.htpl'=>'{var}']);
        $htpl = new Htpl($provider);

        $template = new Template($htpl, 'some template string');
        $this->assertInstanceOf('Webiny\Htpl\Htpl', $template->getHtplInstance());
    }

    public function testFetch()
    {
        $provider = new ArrayProvider(['test.htpl'=>'{var}']);
        $htpl = new Htpl($provider);
        $htpl->assign('var', 'FooBar');

        $this->assertSame('FooBar', $htpl->build('test.htpl')->fetch());
    }

    public function testDisplay()
    {
        $provider = new ArrayProvider(['test.htpl'=>'{var}']);
        $htpl = new Htpl($provider);
        $htpl->assign('var', 'FooBar');

        ob_start();
        $htpl->build('test.htpl')->display();
        $result = ob_get_clean();

        $this->assertSame('FooBar', $result);
    }

    public function testGetSource()
    {
        $provider = new ArrayProvider(['test.htpl'=>'{var}']);
        $htpl = new Htpl($provider);
        $htpl->assign('var', 'FooBar');

        $expectedResult = '<?php echo htmlspecialchars($this->getVar(\'var\', $this->vars), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\');?>';
        $this->assertSame($expectedResult, trim($htpl->build('test.htpl')->getSource()));
    }
}
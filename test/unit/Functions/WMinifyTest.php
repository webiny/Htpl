<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Functions;

use Webiny\Htpl\UnitTests\Mocks\WMinifyMock;

require __DIR__.'/Mocks/WMinifyMock.php';

class WMinifyTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTag()
    {
        $instance = new \Webiny\Htpl\Functions\WMinify();
        $tag = $instance->getTag();

        $this->assertSame('w-minify', $tag);
    }

    public function testParseTag()
    {
        // htpl instance
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        // do the test
        $instance = new \Webiny\Htpl\Functions\WMinify();
        $content = '<link rel="stylesheet" href="test.css"/>';
        $result = $instance->parseTag($content, null, $htpl);

        $this->assertSame('', $result['openingTag']);
        $this->assertContains('\Webiny\Htpl\Functions\WMinify::minifyCallback', $result['content']);
        $this->assertSame('', $result['closingTag']);
    }

    public function testLexerTagParsingCss()
    {
        // htpl instance
        $tpl = '
        <w-minify>
            <link rel="stylesheet" href="assets/css/style.css"/>
        </w-minify>
        ';
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>$tpl]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $htpl->setOptions([
            'minify' => [
                'driver'    => 'Webiny\Htpl\UnitTests\Mocks\WMinifyMock'
            ]
        ]);

        $result = $htpl->build('test')->fetch();
        $this->assertSame('<link rel="stylesheet" href="/mock/min.css"/>', $result);
        $this->assertSame(['assets/css/style.css'], WMinifyMock::$cssFiles);
    }

    public function testLexerTagParsingJs()
    {
        // htpl instance
        $tpl = '
        <w-minify>
            <script src="assets/js/skel.min.js"></script>
            <script src="assets/js/init.js"></script>
        </w-minify>
        ';
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test'=>$tpl]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $htpl->setOptions([
            'minify' => [
                'driver'    => 'Webiny\Htpl\UnitTests\Mocks\WMinifyMock'
            ]
        ]);

        $result = $htpl->build('test')->fetch();
        $this->assertSame('<script type="text/javascript" src="/mock/min.js"/>', $result);
        $this->assertSame(['assets/js/skel.min.js', 'assets/js/init.js'], WMinifyMock::$jsFiles);
    }

    /**
     * @throws \Webiny\Htpl\HtplException
     * @expectedException \Webiny\Htpl\HtplException
     * @expectedExceptionMessage w-minify content cannot be empty
     */
    public function testEmptyContentException()
    {
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        $instance = new \Webiny\Htpl\Functions\WMinify();
        $instance->parseTag('', null, $htpl);
    }
}
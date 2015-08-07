<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Processor;

use Webiny\Htpl\Processor\LayoutTree;
use Webiny\Htpl\TemplateProviders\ArrayProvider;

class LayoutTreeTest extends \PHPUnit_Framework_TestCase
{

    public function testGetLayout()
    {
        $tpl = '<test/>';
        $provider = new ArrayProvider(['test.htpl' => $tpl]);

        $result = LayoutTree::getLayout($provider, 'test.htpl');
        $this->assertSame($tpl, $result->getSource());
    }

    public function testGetLayout2()
    {
        $tpl = '<w-layout template="master.htpl">';
        $tpl .= '<w-block name="content">Test content</w-block>';
        $tpl .= '</w-layout>';

        $layout = '<html><body><w-block name="content"></w-block></body>';

        $provider = new ArrayProvider([
            'test.htpl'   => $tpl,
            'master.htpl' => $layout
        ]);

        $result = LayoutTree::getLayout($provider, 'test.htpl');
        $this->assertSame('<html><body>Test content</body>', $result->getSource());
    }

    public function testGetLayout3()
    {
        $tpl = '<w-layout template="master.htpl">';
        $tpl .= '<w-block name="content">';
        $tpl .= '<w-include file="includedFile.htpl"/>';
        $tpl .= 'Test content</w-block>';
        $tpl .= '</w-layout>';

        $layout = '<html><body><w-block name="content"></w-block></body>';
        $includedFile = '{var}';

        $provider = new ArrayProvider([
            'test.htpl'         => $tpl,
            'master.htpl'       => $layout,
            'includedFile.htpl' => $includedFile
        ]);

        $result = LayoutTree::getLayout($provider, 'test.htpl');
        $this->assertSame('<html><body>{var}Test content</body>', $result->getSource());
    }

    public function testGetLayout4HtmlInclude()
    {
        $tpl = '<w-layout template="master.htpl">';
        $tpl .= '<w-block name="content">';
        $tpl .= '<w-include file="HTMLInclude.html"/>';
        $tpl .= 'Test content</w-block>';
        $tpl .= '</w-layout>';

        $layout = '<html><body><w-block name="content"></w-block></body>';
        $includedFile = 'This is <div>{var}</div> HTML. ';

        $provider = new ArrayProvider([
            'test.htpl'         => $tpl,
            'master.htpl'       => $layout,
            'HTMLInclude.html' => $includedFile
        ]);

        $result = LayoutTree::getLayout($provider, 'test.htpl');
        $this->assertSame('<html><body>This is <div>{var}</div> HTML. Test content</body>', $result->getSource());
    }
}

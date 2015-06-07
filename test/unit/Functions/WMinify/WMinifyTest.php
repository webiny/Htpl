<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Functions\WMinify;


class WMinify extends \PHPUnit_Framework_TestCase
{
    public function testMinifyCss()
    {
        // htpl instance
        $tpl = '
        <w-minify>
            <link rel="stylesheet" href="style.css"/>
            <link rel="stylesheet" href="style2.css"/>
        </w-minify>
        ';
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test' => $tpl]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        // register minify providers
        $minProvider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([
            'style.css'  => 'html{color:red}

            div{border:none}
            ',
            'style2.css' => 'body{color:white}'
        ]);
        $minCache = new \Webiny\Htpl\Cache\ArrayCache();
        $htpl->setOptions([
            'minify' => [
                'webRoot'  => '/minified/',
                'provider' => $minProvider,
                'cache'    => $minCache
            ]
        ]);

        // do the minify
        $minDriver = new \Webiny\Htpl\Functions\WMinify\WMinify($htpl);
        $result = $minDriver->minifyCss(['style.css', 'style2.css']);
        
        // verify the cache
        $cache = current($minCache->dumpCache());
        $expectedResult = 'html{color:red}div{border:none}
body{color:white}';

        $this->assertSame(trim($expectedResult), trim($cache['content']));
    }

    public function testMinifyJs()
    {
        // htpl instance
        $tpl = '
        <w-minify>
            <link rel="stylesheet" href="js1.js"/>
            <link rel="stylesheet" href="js2.js"/>
        </w-minify>
        ';
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test' => $tpl]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        // register minify providers
        $minProvider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([
            'js1.js'  => 'var = "foo";

            alert(var);
            ',
            'js2.js' => 'var2 = "bar";'
        ]);
        $minCache = new \Webiny\Htpl\Cache\ArrayCache();
        $htpl->setOptions([
            'minify' => [
                'webRoot'  => '/minified/',
                'provider' => $minProvider,
                'cache'    => $minCache
            ]
        ]);

        // do the minify
        $minDriver = new \Webiny\Htpl\Functions\WMinify\WMinify($htpl);
        $result = $minDriver->minifyCss(['js1.js', 'js2.js']);

        // verify the cache
        $cache = current($minCache->dumpCache());
        $expectedResult = 'var = "foo";alert(var);
var2 = "bar";';

        $this->assertSame(trim($expectedResult), trim($cache['content']));
    }

    public function testWMinifyAbstract()
    {
        $tpl = '';
        $provider = new \Webiny\Htpl\TemplateProviders\ArrayProvider(['test' => $tpl]);
        $htpl = new \Webiny\Htpl\Htpl($provider);

        // register minify providers
        $minProvider = new \Webiny\Htpl\TemplateProviders\ArrayProvider([
            'js1.js'  => 'var = "foo";

            alert(var);
            ',
            'js2.js' => 'var2 = "bar";'
        ]);
        $minCache = new \Webiny\Htpl\Cache\ArrayCache();
        $htpl->setOptions([
            'minify' => [
                'webRoot'  => '/minified/',
                'provider' => $minProvider,
                'cache'    => $minCache
            ]
        ]);

        $minDriver = new \Webiny\Htpl\Functions\WMinify\WMinify($htpl);

        $this->assertInstanceOf('\Webiny\Htpl\Htpl', $minDriver->getHtpl());
        $this->assertInstanceOf('\Webiny\Htpl\TemplateProviders\TemplateProviderInterface', $minDriver->getProvider());
        $this->assertInstanceOf('\Webiny\Htpl\Cache\CacheInterface', $minDriver->getCache());
        $this->assertSame('/minified/', $minDriver->getWebRoot());
    }
}
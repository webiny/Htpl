<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Mocks;

class WMinifyMock extends \Webiny\Htpl\Functions\WMinify\WMinifyAbstract
{

    static $jsFiles;
    static $cssFiles;

    /**
     * Callback that receives a list of absolute paths to one or more css files.
     * As a result it should return an http absolute path to the minified css file.
     * NOTE: Method is called on every page refresh, so it should internally handle the caching and checking of freshness.
     *
     * @param array $files List of absolute paths to one or more css files.
     *
     * @return string http absolute path to the minified css file.
     */
    public function minifyCss(array $files)
    {
        self::$cssFiles = $files;

        return '/mock/min.css';
    }

    public function getCssFiles()
    {
        return self::$cssFiles;
    }

    /**
     * Callback that receives a list of absolute paths to one or more javascript files.
     * As a result it should return an http absolute path to the minified javascript file.
     * NOTE: Method is called on every page refresh, so it should internally handdle the caching and checking of freshness.
     *
     * @param array $files List of absolute paths to one or more javascript files.
     *
     * @return string http absolute path to the minified javascript file.
     */
    public function minifyJavaScript(array $files)
    {
        self::$jsFiles = $files;

        return '/mock/min.js';
    }

    public function getJavascriptFiles()
    {
        return self::$jsFiles;
    }
}

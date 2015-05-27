<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Functions\WMinify;

/**
 * Class WMinify.
 *
 * This is the default minify driver that uses simple parsers to minify and concatenate your javascript and css files.
 *
 * @package Webiny\Htpl\Functions\WMinify
 */
class WMinify extends WMinifyAbstract
{
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
        $minifiedFileContent = '';
        $cacheKeyParts = [];

        // check the cache
        foreach ($files as $f) {
            $modTime = $this->getProvider()->createdOn($f);
            $cacheKeyParts[] = $f . '-' . $modTime;
        }

        $cacheKey = 'htpl.' . md5(implode('', $cacheKeyParts)) . '.min.css';

        $minifiedFile = $this->getCache()->getFilePath($cacheKey);
        if ($minifiedFile != false) {
            $minifiedFileData = explode(DIRECTORY_SEPARATOR, $minifiedFile);
            return $this->getWebRoot() . end($minifiedFileData);
        }

        // agregate and parse the files
        foreach ($files as $f) {
            $content = $this->getProvider()->getSource($f);

            if (substr($f, -7) != 'min.css') {
                // parse "import tags"
                preg_match_all('/@import ([\s\S]+?)\((["\']?)([\s\S]+?)\.css(.?)\)/', $content, $imports);
                if (count($imports[3]) > 0) {
                    // import those scripts
                    $importLoop = 0;
                    foreach ($imports[3] as $importCss) {
                        $path = $this->relativeUrlToAbsolute($importCss . '.css', $f);
                        $importContent = $this->getProvider()->getSource($path);

                        $content = str_replace($imports[0][$importLoop], $importContent, $content);

                        $importLoop++;
                    }
                }

                // minify
                $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
                $content = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '     '], '', $content);
                $content = preg_replace(['(( )+{)', '({( )+)'], '{', $content);
                $content = preg_replace(['(( )+})', '(}( )+)', '(;( )*})'], '}', $content);
                $content = preg_replace(['(;( )+)', '(( )+;)'], ';', $content);
            }

            // sort out the relative paths
            preg_match_all('/url\((.*?)\)/', $content, $matches);
            if (count($matches[0]) > 0) {
                // clean out the quotes
                $mIndex = 0;
                foreach ($matches[0] as $m) {
                    $path = str_replace(['"', "'"], '', $matches[1][$mIndex]);
                    if (strpos($path, 'https:') === false && strpos($path, 'http:') === false) {
                        // convert the path
                        $path = $this->relativeUrlToAbsolute($path, $f);

                        // replace all the paths inside the content
                        $content = str_replace($m, 'url("/' . $path . '")', $content);
                    }

                    $mIndex++;
                }
            }

            $minifiedFileContent .= "\n" . $content;
        }

        // write the minified file and return the path
        $minifiedFile = $this->getCache()->write($cacheKey, $minifiedFileContent);

        $minifiedFileData = explode(DIRECTORY_SEPARATOR, $minifiedFile);
        return $this->getWebRoot() . end($minifiedFileData);
    }

    /**
     * Callback that receives a list of absolute paths to one or more javascript files.
     * As a result it should return an http absolute path to the minified javascript file.
     * NOTE: Method is called on every page refresh, so it should internally handle the caching and checking of freshness.
     *
     * @param array $files List of absolute paths to one or more javascript files.
     *
     * @return string http absolute path to the minified javascript file.
     */
    public function minifyJavaScript(array $files)
    {
        $minifiedFileContent = '';
        $cacheKeyParts = [];

        // check the cache
        foreach ($files as $f) {
            $modTime = $this->getProvider()->createdOn($f);
            $cacheKeyParts[] = $f . '-' . $modTime;
        }
        $cacheKey = 'htpl.' . md5(implode('', $cacheKeyParts)) . '.min.js';

        $minifiedFile = $this->getCache()->getFilePath($cacheKey);
        if ($minifiedFile != false) {
            $minifiedFileData = explode(DIRECTORY_SEPARATOR, $minifiedFile);
            return $this->getWebRoot() . end($minifiedFileData);
        }

        // aggregate and parse the files
        foreach ($files as $f) {
            $content = $this->getProvider()->getSource($f);
            // check if we need to minify the file
            if (substr($f, -6) != 'min.js') {
                // minify
                $content = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $content);
                $content = str_replace(["\r\n", "\r", "\t", "\n", '  ', '    ', '     '], '', $content);
                $content = preg_replace(['(( )+\))', '(\)( )+)'], ')', $content);

            }

            $minifiedFileContent .= "\n" . $content;
        }

        // write the minified file and return the path
        $minifiedFile = $this->getCache()->write($cacheKey, $minifiedFileContent);

        $minifiedFileData = explode(DIRECTORY_SEPARATOR, $minifiedFile);
        return $this->getWebRoot() . end($minifiedFileData);
    }

    /**
     * Transforms the relative url to absolute.
     *
     * @param string $rel  The relative url.
     * @param string $base Base for the transformation.
     *
     * @return string
     */
    protected function relativeUrlToAbsolute($rel, $base)
    {
        // extract the base path without the filename
        $base = dirname($base);

        // combine relative path and the base path
        $path = rtrim($base, '/') . '/' . ltrim($rel, '/');

        $filename = str_replace('//', '/', $path);
        $parts = explode('/', $filename);
        $out = [];
        foreach ($parts as $part) {
            if ($part == '.') {
                continue;
            }
            if ($part == '..') {
                array_pop($out);
                continue;
            }
            $out[] = $part;
        }

        return implode('/', $out);
    }
}
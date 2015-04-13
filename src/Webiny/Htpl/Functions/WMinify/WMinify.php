<?php

namespace Webiny\Htpl\Functions\WMinify;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;

/**
 * Class WMinify.
 *
 * This is the default minify class that uses simple parsers to minify and concatenate your javascript and css files.
 * The class uses the filesystem to read and write the minified files.
 *
 * @package Webiny\Htpl\Functions\WMinify
 */
class WMinify implements WMinifyInterface
{

    protected $htpl;

    /**
     * Base constructor.
     *
     * @param Htpl $htpl Current Htpl instance.
     */
    public function __construct(Htpl $htpl)
    {
        $this->htpl = $htpl;
    }

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
            $modTime = $this->getFileModTime($f);
            $cacheKeyParts[] = $f . '-' . $modTime;
        }
        $cacheKey = 'htpl.' . md5(implode('', $files)) . '.min.css';
        $minifiedFile = $this->htpl->getOptions()['minify']['minifyDir'] . '/' . $cacheKey;

        if ($this->minifiedFileExists($cacheKey)) {
            return $minifiedFile;
        }

        // agregate and parse the files
        foreach ($files as $f) {
            $content = $this->getFileContent($f);

            if (substr($f, -7) != 'min.css') {
                // parse "import tags"
                preg_match_all('/@import ([\s\S]+?)\((["\']?)([\s\S]+?)\.css(.?)\)/', $content, $imports);
                if (count($imports[3]) > 0) {
                    // import those scripts
                    $importLoop = 0;
                    foreach ($imports[3] as $importCss) {
                        $path = $this->relativeUrlToAbsolute($importCss . '.css', $f);
                        $importContent = $this->getFileContent($path);

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
                    // convert the path
                    $path = $this->relativeUrlToAbsolute($path, $f);

                    // replace all the paths inside the content
                    $content = str_replace($m, 'url("' . $path . '")', $content);

                    $mIndex++;
                }
            }

            $minifiedFileContent .= "\n" . $content;
        }

        // write the file
        $this->writeMinifiedFile($cacheKey, $minifiedFileContent);

        // return the html tag
        return $minifiedFile;
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
            $modTime = $this->getFileModTime($f);
            $cacheKeyParts[] = $f . '-' . $modTime;
        }
        $cacheKey = 'htpl-minify-' . md5(implode('', $cacheKeyParts)) . '.min.js';
        $minifiedFile = $this->htpl->getOptions()['minify']['minifyDir'] . '/' . $cacheKey;

        if ($this->minifiedFileExists($cacheKey)) {
            return $minifiedFile;
        }

        // agregate and parse the files
        foreach ($files as $f) {
            $content = $this->getFileContent($f);
            // check if we need to minify the file
            if (substr($f, -6) != 'min.js') {
                // minify
                $content = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $content);
                $content = str_replace(["\r\n", "\r", "\t", "\n", '  ', '    ', '     '], '', $content);
                $content = preg_replace(['(( )+\))', '(\)( )+)'], ')', $content);

            }

            $minifiedFileContent .= "\n" . $content;
        }

        // write the file
        $this->writeMinifiedFile($cacheKey, $minifiedFileContent);

        return $minifiedFile;
    }

    protected function getFileContent($file)
    {
        if (strpos($file, '://') !== false) {
            throw new HtplException(sprintf('You can only minify your local files. %s cannot be minifed.', $file));
        }

        return file_get_contents($file);
    }

    protected function getFileModTime($file)
    {
        if (strpos($file, '://') !== false) {
            throw new HtplException(sprintf('You can only minify your local files. %s cannot be minifed.', $file));
        }

        $file = $this->htpl->getTemplateDir() . $file;
        return filemtime($file);
    }

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

    protected function writeMinifiedFile($filename, &$content)
    {
        $dir = $this->htpl->getTemplateDir() . $this->htpl->getOptions()['minify']['minifyDir'] . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($dir . $filename, $content);
    }

    protected function minifiedFileExists($filename)
    {
        $dir = $this->htpl->getTemplateDir() . $this->htpl->getOptions()['minify']['minifyDir'];
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        clearstatcache(true, $dir . $filename);
        if (file_exists($dir . $filename)) {
            return true;
        }

        return false;
    }
}
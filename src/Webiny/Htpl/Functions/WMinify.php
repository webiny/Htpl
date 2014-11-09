<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\Processor\Selector;

class WMinify extends FunctionAbstract
{

    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public static function getTag()
    {
        return 'w-minify';
    }

    /**
     * This is a callback method when we match the tag that the function is registered for.
     * The method will receive a list of attributes that the tag has associated.
     * The method should return a string that should replace the matching tag.
     * If the method returns false, no replacement will occur.
     *
     * @param string     $content
     * @param array|null $attributes
     *
     * @throws HtplException
     * @return string|bool
     */
    public static function parseTag($content, $attributes)
    {
        // content
        if (empty($content)) {
            throw new HtplException('w-minify content cannot be empty.');
        }

        // check if it's javascript
        $items = Selector::select($content, '//script');
        if (count($items) > 0) {
            $output = self::_parseJavascript($items);
        } else {
            $items = Selector::select($content, '//link');
            if (count($items) > 0) {
                $output = self::_parseCss($items);
            }
        }

        return [
            'openingTag' => '',
            'content'    => $output,
            'closingTag' => ''
        ];
    }

    private static function _parseJavascript($items)
    {
        $minifiedFileContent = '';
        $files = [];
        // agregate and parse the files
        foreach ($items as $i) {
            $files[] = $i['attributes']['src'];
            $content = self::_getFileContent($i['attributes']['src']);
            // check if we need to minify the file
            if (substr($i['attributes']['src'], -6
                ) != 'min.js' && (!isset($i['attributes']['minify']) || $i['attributes']['minify'] != 'false')
            ) {
                // @todo enable a way for others to do their own custom minify function
                $content = self::_minifyString($content);

            }
            $minifiedFileContent .= "\n" . $content;
        }

        $minifiedFileName = 'htpl.' . md5(implode('', $files)) . '.min.js';

        // write the file
        self::_writeMinifiedFile($minifiedFileName, $minifiedFileContent);

        return '<script type="text/javascript" src="/minified/' . $minifiedFileName . '"></script>';

        // @todo how to check the object freshness
    }

    private static function _parseCss($items)
    {
        $minifiedFileContent = '';
        $files = [];
        // agregate and parse the files
        foreach ($items as $i) {
            $files[] = $i['attributes']['href'];
            $content = self::_getFileContent($i['attributes']['href']);

            if (substr($i['attributes']['href'], -7
                ) != 'min.css' && (!isset($i['attributes']['minify']) || $i['attributes']['minify'] != 'false')
            ) {
                // @todo enable a way for others to do their own custom minify function
                //$content = self::_minifyString($content);
            }

            // sort out the relative paths
            preg_match_all('/url\((.*?)\)/', $content, $matches);
            if (count($matches[0]) > 0) {
                // clean out the quotes
                $mIndex = 0;
                foreach($matches[0] as $m){
                    $path = str_replace(['"', "'"], '', $matches[1][$mIndex]);
                    // convert the path
                    $path = self::_relativeUrltoAbsolute($path, $i['attributes']['href']);

                    // replace all the paths inside the content
                    $content = str_replace($m, 'url("'.$path.'")', $content);

                    $mIndex++;
                }
            }

            $minifiedFileContent .= "\n" . $content;
        }

        $minifiedFileName = 'htpl.' . md5(implode('', $files)) . '.min.js';

        // write the file
        self::_writeMinifiedFile($minifiedFileName, $minifiedFileContent);

        return '<link rel="stylesheet" href="/minified/' . $minifiedFileName . '"></link>';

        // @todo how to check the object freshness
    }

    private static function _getFileContent($file)
    {
        return file_get_contents(Htpl::getTemplateDir() . $file);
    }

    private static function _relativeUrltoAbsolute($rel, $base)
    {
        // $base = /css/minify.css
        // $rel = ../img/dark_wall.png
        // $result = http://localhost/img/dark_wall.png

        // extract the base path without the filename
        $base = dirname($base);

        // get host
        //$host = $_SERVER['HOST_NAME'];

        // combine relative path and the base path
        $path = rtrim($base, '/').'/'.ltrim($rel, '/');

        $filename = str_replace('//', '/', $path);
        $parts = explode('/', $filename);
        $out = array();
        foreach ($parts as $part){
            if ($part == '.') continue;
            if ($part == '..') {
                array_pop($out);
                continue;
            }
            $out[] = $part;
        }

        return implode('/', $out);
    }

    private static function _minifyString($string)
    {
        // reference http://codewiz.biz/article/post/minify+and+combining+of+css+and+js
        $lines = explode("\n", $string);
        $lines = array_map(function ($line) {
                return preg_replace("@\s*//.*$@", '', $line);
            }, $lines
        );
        $content = implode("\n", $lines);
        // remove tabs, spaces, newlines, etc.
        $content = str_replace([
                                   "\r\n",
                                   "\r",
                                   "\n",
                                   "\t",
                                   "  ",
                                   "    ",
                                   "     "
                               ], "", $content
        );
        // remove other spaces before/after )
        $content = preg_replace([
                                    '(( )+\))',
                                    '(\)( )+)'
                                ], ')', $content
        );

        return $content;
    }

    private static function _writeMinifiedFile($filename, &$content)
    {
        $dir = Htpl::getTemplateDir() . 'minified/'; // @todo this should be a configurable path
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($dir . $filename, $content);
    }
}
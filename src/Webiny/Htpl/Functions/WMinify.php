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
     * @param string $content
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

        /*if (!isset($attributes['type'])) {
            throw new HtplException('w-minify "type" attribute must be set.');
        }

        if ($attributes['type'] != 'javascript' && $attributes['type'] != 'css') {
            throw new HtplException('w-minify invalid "type" value "' . $attributes['type'] . '".');
        }

        if ($attributes['type'] == 'javascript') {
            $output = self::_parseJavascript($content);
        } else {
            if ($attributes['type'] == 'css') {
                $output = self::_parseCss($content);
            }
        }*/

        // check if it's javascript
        $items = Selector::select($content, '//script');
        if ($items > 0) {
            $output = self::_parseJavascript($items);
        } else {
            $items = Selector::select($content, '//link');
            if ($items > 0) {
                //$output = self::_parseCss($items);
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
                // very simple minify function
                // reference http://codewiz.biz/article/post/minify+and+combining+of+css+and+js
                $lines = explode("\n", $content);
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
            }
            $minifiedFileContent .= "\n" . $content;
        }

        $minifiedFileName = 'htpl.' . md5(implode('', $files)) . '.min.js';

        // write the file
        self::_writeMinifiedFile($minifiedFileName, $minifiedFileContent);

        return '<script type="text/javascript" src="/minified/'.$minifiedFileName.'"></script>';

        // @todo how to check the object freshness


    }

    private static function _getFileContent($file)
    {
        return file_get_contents(Htpl::getTemplateDir() . $file);
    }

    private static function _writeMinifiedFile($filename, &$content)
    {
        $dir = Htpl::getTemplateDir().'minified/'; // @todo this should be a configurable path?
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }

        return file_put_contents($dir.$filename, $content);
    }
}
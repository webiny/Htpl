<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Processor\OutputWrapper;
use Webiny\Htpl\Processor\Selector;

class WMinify implements FunctionInterface
{
    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public function getTag()
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
    public function parseTag($content, $attributes, Htpl $htpl)
    {
        // content
        if (empty($content)) {
            throw new HtplException('w-minify content cannot be empty.');
        }

        // check if it's javascript
        $items = Selector::select($content, '//script');
        if (count($items) > 0) {

            // extract items
            $files = [];
            foreach ($items as $i) {
                $files[] = $i['attributes']['src'];
            }

            $callback = '\Webiny\Htpl\Functions\WMinify::minifyCallback(' . var_export($files,
                    true) . ', "js", $this->getHtplInstance())';
        } else {
            // check if css
            $items = Selector::select($content, '//link');
            if (count($items) > 0) {
                // extract items
                $files = [];
                foreach ($items as $i) {
                    $files[] = $i['attributes']['href'];
                }

                $callback = '\Webiny\Htpl\Functions\WMinify::minifyCallback(' . var_export($files,
                        true) . ', "css", $this->getHtplInstance())';
            }
        }

        return [
            'openingTag' => '',
            'content'    => OutputWrapper::outputFunction($callback),
            'closingTag' => ''
        ];
    }

    public static function minifyCallback($files, $type, Htpl $htpl)
    {
        // get minify driver instance
        $driver = $htpl->getOptions()['minify']['driver'];
        $minifyDriver = new $driver($htpl);
        if (!($minifyDriver instanceof \Webiny\Htpl\Functions\WMinify\WMinifyInterface)) {
            throw new HtplException('Minify driver must implement \Webiny\Htpl\Functions\WMinify\WMinifyInterface.');
        }

        if ($type == 'js') {
            $minifiedFile = $minifyDriver->minifyJavaScript($files);
            echo '<script type="text/javascript" src="' . $minifiedFile . '"></script>';
        } else if ($type == 'css') {
            $minifiedFile = $minifyDriver->minifyCss($files);
            echo '<link rel="stylesheet" href="' . $minifiedFile . '"></link>';
        } else {
            throw new HtplException(sprintf('Unknown $type value for minify callback: %s', $type));
        }
    }
}
<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Processor\OutputWrapper;

/**
 * WMinify function.
 *
 * @package Webiny\Htpl\Functions
 */
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
        preg_match_all('/src=(\'|")([\W\w]+?)\1/', $content, $items);
        if (count($items[2]) > 0) {
            $callback = '\Webiny\Htpl\Functions\WMinify::minifyCallback(' . var_export($items[2],
                    true) . ', "js", $this->getHtplInstance())';
        } else {
            // check if css
            preg_match_all('/href=(\'|")([\W\w]+?)\1/', $content, $items);
            if (count($items[2]) > 0) {
                $callback = '\Webiny\Htpl\Functions\WMinify::minifyCallback(' . var_export($items[2],
                        true) . ', "css", $this->getHtplInstance())';
            }
        }

        if (isset($callback)) {
            return [
                'openingTag' => '',
                'content'    => OutputWrapper::outputFunction($callback),
                'closingTag' => ''
            ];
        } else {
            return false;
        }
    }

    /**
     * Static callback that does the minification.
     * The method is called from within the compiled template.
     *
     * @param array  $files List of files that need to minified.
     * @param string $type  Is it a js or a css minification in question.
     * @param Htpl   $htpl  Current htpl instance.
     *
     * @throws HtplException
     */
    public static function minifyCallback($files, $type, Htpl $htpl)
    {
        // get minify driver instance
        $options = $htpl->getOptions()['minify'];
        if (empty($options)) {
            throw new HtplException('Missing options for w-minify function.');
        }

        // get driver
        $driver = isset($options['driver']) ? $options['driver'] : '\Webiny\Htpl\Functions\WMinify\WMinify';
        if (!is_object($driver)) {
            $driver = new $driver($htpl);
        }
        if (!($driver instanceof \Webiny\Htpl\Functions\WMinify\WMinifyAbstract)) {
            throw new HtplException('Minify driver must implement \Webiny\Htpl\Functions\WMinify\WMinifyAbstract.');
        }

        if ($type == 'js') {
            $minifiedFile = $driver->minifyJavaScript($files);
            echo '<script type="text/javascript" src="' . $minifiedFile . '"/>';
        } else if ($type == 'css') {
            $minifiedFile = $driver->minifyCss($files);
            echo '<link rel="stylesheet" href="' . $minifiedFile . '"/>';
        } else {
            throw new HtplException(sprintf('Unknown $type value for minify callback: %s', $type));
        }
    }
}
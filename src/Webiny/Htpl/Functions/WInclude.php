<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Processor\OutputWrapper;

class WInclude implements FunctionInterface
{

    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public function getTag()
    {
        return 'w-include';
    }

    /**
     * This is a callback method when we match the tag that the function is registered for.
     * The method will receive a list of attributes that the tag has associated.
     * The method should return a string that should replace the matching tag.
     * If the method returns false, no replacement will occur.
     *
     * @param string     $content
     * @param array|null $attributes
     * @param Htpl       $htpl
     *
     * @throws HtplException
     * @return string|bool
     */
    public function parseTag($content, $attributes, Htpl $htpl)
    {
        // content
        if (!isset($attributes['file'])) {
            return false;
        }

        // get the include callback
        if (substr($attributes['file'], -5) == '.htpl') {
            $callback = 'Webiny\Htpl\Functions\WInclude::htpl';
        } else if (substr($attributes['file'], -5) == '.html') {
            $callback = 'Webiny\Htpl\Functions\WInclude::html';
        } else {
            // treat as variable
            $callback = 'Webiny\Htpl\Functions\WInclude::dynamic';
        }

        $callback .= '("' . $attributes['file'] . '", $this->getHtplInstance())';

        return [
            'openingTag' => '',
            'content'    => OutputWrapper::outputFunction($callback),
            'closingTag' => ''
        ];
    }

    public static function htpl($file, Htpl $htpl)
    {
        $path = self::resolveIncludePath($file, $htpl);

        $htpl->render(str_replace($htpl->getTemplateDir(), '', $path));
    }

    public static function html($file, Htpl $htpl)
    {
        $path = self::resolveIncludePath($file, $htpl);

        echo file_get_contents($path);
    }

    public static function dynamic($file, Htpl $htpl)
    {
        die('dynamic:' . $file);
    }

    private static function resolveIncludePath($file, Htpl $htpl)
    {
        // check if absolute or relative path
        if ($file[0] != DIRECTORY_SEPARATOR && $file[1] != ':') {
            $fullPath = $htpl->getTemplateDir() . $htpl->getTemplate();
            $path = realpath(dirname($fullPath) . DIRECTORY_SEPARATOR . dirname($file) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($file);
        } else {
            $path = realpath($htpl->getTemplateDir() . $file);
        }

        if (!$path) {
            throw new HtplException(sprintf('Unable to include "%s" since file doesn\'t exist', $file));
        }

        return $path;
    }
}
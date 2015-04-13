<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;

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

        // validate the file before including it
        if (substr($attributes['file'], -5) != '.htpl' && substr($attributes['file'], -5) != '.html') {
            throw new HtplException('Cannot include "' . $attributes['file'] . '", only html and htpl files are allowed.');
        }

        // get the contents // @todo enable variable support
        $content = file_get_contents($htpl->getTemplateDir() . $attributes['file']);

        return [
            'openingTag' => '',
            'content'    => $content,
            'closingTag' => ''
        ];
    }
}
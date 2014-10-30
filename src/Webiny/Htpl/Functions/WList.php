<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\HtplException;

class WList extends FunctionAbstract
{

    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public static function getTag()
    {
        return 'w-list';
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
        // items attributes
        if(!isset($attributes['items']) || empty($attributes['items'])){
            throw new HtplException('w-list function requires `items` attribute to be defined.');
        }

        // item attribute
        if(!isset($attributes['var']) || empty($attributes['var'])){
            throw new HtplException('w-list function requires `var` attribute to be defined.');
        }

        $items = self::_getVarName($attributes['items']);
        $var = self::_getVarName($attributes['var']);

        $func = 'foreach ('.$items.' as '.$var.'){ ';

        // insert meta ?

        return [
            'openingTag' => self::_outputFunction($func),
            'closingTag' => self::_outputFunction('}')
        ];
    }
}
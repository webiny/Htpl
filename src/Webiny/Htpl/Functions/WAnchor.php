<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\HtplException;

class WAnchor extends FunctionAbstract
{

    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public static function getTag()
    {
        return 'a';
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
        if (!isset($attributes['w-href'])) {
            return false;
        }

        $tag = '<a href="' . self::_outputVar(self::_getVarName($attributes['w-href'])) . '"';
        foreach ($attributes as $aName => $aVal) {
            if ($aName != 'href' && $aName != 'w-href') {
                $tag.=' '.$aName.'= "'.$aVal.'"';
            }
        }
        $tag.='>';

        return [
            'openingTag' => $tag
        ];
    }
}
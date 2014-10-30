<?php

namespace Webiny\Htpl\Functions;

class WVar extends FunctionAbstract
{

    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public static function getTag()
    {
        return 'w-var';
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
            throw new HtplException('w-var content cannot be empty.');
        }

        // the content matches the internal variable
        $var = self::_getVarName($content);

        if (is_array($attributes)) {
            // default attribute
            if (isset($attributes['default'])) {
                $var = '(!empty(' . $var . ') ? ' . $var . ' : "' . $attributes['default'] . '")';
            }
        }

        // apply modifiers
        if (isset($attributes['mod'])) {
            $var = self::_applyModifiers($var, $attributes['mod']);
        }

        // wrap for output
        $var = self::_outputVar($var);

        return [
            'openingTag' => '',
            'content'    => $var,
            'closingTag' => ''
        ];
    }
}
<?php

namespace Webiny\Htpl\Modifiers;

use Webiny\Htpl\HtplException;

class CorePack implements ModifierPackInterface
{

    public static function getModifiers()
    {

        // @todo define how to issue pre-escape and post-escape modifiers
        return [
            // pre-escape stage
            'abs'          => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::abs',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'capitalize'   => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::capitalize',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'lower'        => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::lower',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'upper'        => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::upper',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'firstUpper'   => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::firstUpper',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'date'         => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::date',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'timeAgo'      => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::timeAgo',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'default'      => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::defaultValue',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'first'        => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::first',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'format'       => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::format',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'last'         => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::last',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'join'         => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::join',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'keys'         => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::keys',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'values'       => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::values',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'jsonEncode'   => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::jsonEncode',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'length'       => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::length',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'numberFormat' => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::numberFormat',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'replace'      => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::replace',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'round'        => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::round',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'stripTags'    => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::stripTags',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            'trim'         => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::trim',
                'stage'    => self::STAGE_PRE_ESCAPE
            ],
            // post-escape stage
            'nl2br'        => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::nl2br',
                'stage'    => self::STAGE_POST_ESCAPE
            ],
            'raw'          => [
                'callback' => '\Webiny\Htpl\Modifiers\CorePack::raw',
                'stage'    => self::STAGE_POST_ESCAPE
            ]
        ];
    }

    public static function abs($num)
    {
        return abs($num);
    }

    public static function capitalize($str)
    {
        return mb_convert_case(mb_strtolower($str), MB_CASE_TITLE);
    }

    public static function firstUpper($str)
    {
        return mb_strtoupper($str[0]) . mb_strtolower(substr($str, 1));
    }

    public static function lower($str)
    {
        return mb_strtolower($str);
    }

    public static function upper($str)
    {
        return mb_strtoupper($str);
    }

    public static function date($timestamp, $format = 'Y-m-d H:i:s', $timezone = null)
    {
        if (!is_numeric($timestamp) || (int)$timestamp != $timestamp) {
            $timestamp = strtotime($timestamp);
        }

        $dt = new \DateTime();
        $dt->setTimestamp($timestamp);
        if (!is_null($timezone)) {
            $dt->setTimezone($timezone);
        }

        return $dt->format($format);
    }

    public static function timeAgo($timestamp)
    {
        if (!is_numeric($timestamp) || (int)$timestamp != $timestamp) {
            $timestamp = strtotime($timestamp);
        }

        $etime = time() - $timestamp;

        if ($etime < 1) {
            return '0 seconds';
        }

        $a = [
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60      => 'month',
            24 * 60 * 60           => 'day',
            60 * 60                => 'hour',
            60                     => 'minute',
            1                      => 'second'
        ];

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
            }
        }
    }

    public static function defaultValue($str, $default)
    {
        if (empty($str)) {
            return $default;
        }

        return $str;
    }

    public static function first($arr)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "first" modifier, takes only arrays.'));
        }

        reset($arr);
        return array_shift($arr);
    }

    public static function format($str, $parts)
    {
        if (!is_array($parts)) {
            throw new HtplException(sprintf('The "format" modifier, takes only arrays.'));
        }

        return vsprintf($str, $parts);
    }

    public static function last($arr)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "last" modifier, takes only arrays.'));
        }

        reset($arr);
        return end($arr);
    }

    public static function join($arr, $glue)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "join" modifier, takes only arrays.'));
        }

        return join($glue, $arr);
    }

    public static function keys($arr)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "keys" modifier, takes only arrays.'));
        }

        return array_keys($arr);
    }

    public static function values($arr)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "values" modifier, takes only arrays.'));
        }

        return array_values($arr);
    }

    public static function jsonEncode($val)
    {
        return json_encode($val);
    }


    public static function length($val)
    {
        if (is_array($val) || is_object($val)) {
            return count($val);
        } else {
            return strlen($val);
        }
    }

    public static function nl2br($str)
    {
        return nl2br($str);
    }

    public static function numberFormat($num, $dec = 0, $decPoint = '.', $thousandsSep = ',')
    {
        return number_format($num, $dec, $decPoint, $thousandsSep);
    }

    public static function raw($str)
    {
        return html_entity_decode($str, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
    }

    public static function replace($str, $replacements)
    {
        if (!is_array($replacements)) {
            throw new HtplException('Modifier "replace" requires that the first param is an array.');
        }

        return str_replace(array_keys($replacements), array_values($replacements), $str);
    }

    public static function round($float, $precision = 0, $mode = 'up')
    {
        $modes = [
            'up'   => PHP_ROUND_HALF_UP,
            'down' => PHP_ROUND_HALF_DOWN
        ];

        if (!isset($modes[$mode])) {
            throw new HtplException(sprintf('Unknown round mode "%s".', $mode));
        }

        return round($float, $precision, $modes[$mode]);
    }

    public static function stripTags($str, $allow = '')
    {
        return strip_tags($str, $allow);
    }

    public static function trim($str, $direction = 'both', $charMask = " \t\n\r\0\x0B")
    {
        if ($direction == 'both') {
            return trim($str, $charMask);
        } else if ($direction == 'left') {
            return ltrim($str, $charMask);
        } else if ($direction == 'right') {
            return rtrim($str, $charMask);
        } else {
            throw new HtplException(sprintf('Unknown trim direction "%s".'), $direction);
        }
    }
}
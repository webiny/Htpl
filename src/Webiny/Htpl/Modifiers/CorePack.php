<?php

namespace Webiny\Htpl\Modifiers;

use Webiny\Htpl\HtplException;

class CorePack implements ModifierPackInterface
{

    public static function getModifiers()
    {

        // @todo define how to issue pre-escape and post-escape modifiers
        return [
            'abs'          => '\Webiny\Htpl\Modifiers\CorePack::abs',
            'capitalize'   => '\Webiny\Htpl\Modifiers\CorePack::capitalize',
            'lower'        => '\Webiny\Htpl\Modifiers\CorePack::lower',
            'upper'        => '\Webiny\Htpl\Modifiers\CorePack::upper',
            'firstUpper'   => '\Webiny\Htpl\Modifiers\CorePack::firstUpper',
            'date'         => '\Webiny\Htpl\Modifiers\CorePack::date',
            'timeAgo'      => '\Webiny\Htpl\Modifiers\CorePack::timeAgo',
            'default'      => '\Webiny\Htpl\Modifiers\CorePack::defaultValue',
            'first'        => '\Webiny\Htpl\Modifiers\CorePack::first',
            'last'         => '\Webiny\Htpl\Modifiers\CorePack::last',
            'join'         => '\Webiny\Htpl\Modifiers\CorePack::join',
            'keys'         => '\Webiny\Htpl\Modifiers\CorePack::keys',
            'values'       => '\Webiny\Htpl\Modifiers\CorePack::values',
            'jsonEncode'   => '\Webiny\Htpl\Modifiers\CorePack::jsonEncode',
            'length'       => '\Webiny\Htpl\Modifiers\CorePack::length',
            'nl2br'        => '\Webiny\Htpl\Modifiers\CorePack::nl2br', // post-escape
            'numberFormat' => '\Webiny\Htpl\Modifiers\CorePack::numberFormat',
            'raw'          => '\Webiny\Htpl\Modifiers\CorePack::raw', // post-escape
            'replace'      => '\Webiny\Htpl\Modifiers\CorePack::replace'
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

    public static function replace($str, $replacements)
    {
        //@todo smisli kako array definirati kao parametar

        die(json_decode($replacements));
    }

}
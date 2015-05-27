<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Modifiers;

use Webiny\Htpl\HtplException;

/**
 * CorePack - the core modifier pack.
 *
 * @package Webiny\Htpl\Modifiers
 */
class CorePack implements ModifierPackInterface
{

    /**
     * Get the list of registered modifiers inside this pack.
     *
     * @return array
     */
    public static function getModifiers()
    {
        return [
            //
            // pre-escape stage
            //
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
            //
            // post-escape stage
            //
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

    /**
     * Math absolute modifier.
     *
     * @param integer $num
     *
     * @return number
     */
    public static function abs($num)
    {
        return abs($num);
    }

    /**
     * Round the number.
     *
     * @param float  $float     Number to round.
     * @param int    $precision What's the round precision.
     * @param string $mode      Round 'up' or 'down'.
     *
     * @return float
     * @throws HtplException
     */
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

    /**
     * Capitalize string.
     *
     * @param string $str
     *
     * @return string
     */
    public static function capitalize($str)
    {
        return mb_convert_case(mb_strtolower($str), MB_CASE_TITLE);
    }

    /**
     * Make the first char, of a string, uppercase.
     *
     * @param string $str
     *
     * @return string
     */
    public static function firstUpper($str)
    {
        return mb_strtoupper($str[0]) . mb_strtolower(substr($str, 1));
    }

    /**
     * Make the string lowercase.
     *
     * @param string $str
     *
     * @return string
     */
    public static function lower($str)
    {
        return mb_strtolower($str);
    }

    /**
     * Make the string uppercase.
     *
     * @param $str
     *
     * @return string
     */
    public static function upper($str)
    {
        return mb_strtoupper($str);
    }

    /**
     * Date format modifier.
     *
     * @param string|int $date     Date that should be formatted.
     * @param string     $format   Date format.
     * @param null       $timezone Timezone.
     *
     * @return string
     */
    public static function date($date, $format = 'Y-m-d H:i:s', $timezone = null)
    {
        if (!is_numeric($date) || (int)$date != $date) {
            $timestamp = strtotime($date);
        } else {
            $timestamp = $date;
        }

        $dt = new \DateTime();
        $dt->setTimestamp($timestamp);
        if (!is_null($timezone)) {
            $dt->setTimezone($timezone);
        }

        return $dt->format($format);
    }

    /**
     * Outputs the give date in a 'time ago' format, for example "5 seconds ago", "2 days ago"
     *
     * @param string|int $date Date that should be formatted.
     *
     * @return string
     */
    public static function timeAgo($date)
    {
        if (!is_numeric($date) || (int)$date != $date) {
            $timestamp = strtotime($date);
        } else {
            $timestamp = $date;
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

    /**
     * If $val is empty, return $default.
     *
     * @param mixed $val     Initial value to check.
     * @param mixed $default Value to return if $val is empty.
     *
     * @return mixed
     */
    public static function defaultValue($val, $default)
    {
        if (empty($val)) {
            return $default;
        }

        return $val;
    }

    /**
     * String format, using the internal sprintf function.
     *
     * @param string $str
     * @param array  $parts
     *
     * @return string
     * @throws HtplException
     */
    public static function format($str, $parts)
    {
        if (!is_array($parts)) {
            throw new HtplException(sprintf('The "format" modifier, takes only arrays.'));
        }

        return vsprintf($str, $parts);
    }

    /**
     * Returns the first member of the given array.
     *
     * @param array $arr
     *
     * @return mixed
     * @throws HtplException
     */
    public static function first($arr)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "first" modifier, takes only arrays.'));
        }

        reset($arr);
        return array_shift($arr);
    }

    /**
     * Returns the last array member.
     *
     * @param array $arr
     *
     * @return mixed
     * @throws HtplException
     */
    public static function last($arr)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "last" modifier, takes only arrays.'));
        }

        reset($arr);
        return end($arr);
    }

    /**
     * Joins array members.
     *
     * @param array  $arr
     * @param string $glue
     *
     * @return string
     * @throws HtplException
     */
    public static function join($arr, $glue)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "join" modifier, takes only arrays.'));
        }

        return join($glue, $arr);
    }

    /**
     * Get array keys.
     *
     * @param string $arr
     *
     * @return array
     * @throws HtplException
     */
    public static function keys($arr)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "keys" modifier, takes only arrays.'));
        }

        return array_keys($arr);
    }

    /**
     * Get array values.
     *
     * @param array $arr
     *
     * @return array
     * @throws HtplException
     */
    public static function values($arr)
    {
        if (!is_array($arr)) {
            throw new HtplException(sprintf('The "values" modifier, takes only arrays.'));
        }

        return array_values($arr);
    }

    /**
     * In case of a string, returns the string length. In case of an array, returns the element count.
     *
     * @param mixed $val
     *
     * @return int
     */
    public static function length($val)
    {
        if (is_array($val) || is_object($val)) {
            return count($val);
        } else {
            return strlen($val);
        }
    }

    /**
     * Json encode the given array.
     *
     * @param array $val
     *
     * @return string
     */
    public static function jsonEncode($val)
    {
        return json_encode($val);
    }

    /**
     * Replaces the new lines, in the string, with html <br/> tags.
     *
     * @param string $str
     *
     * @return string
     */
    public static function nl2br($str)
    {
        return nl2br($str);
    }

    /**
     * Formats the number using given number format options.
     *
     * @param number $num          Number to format.
     * @param int    $dec          How many decimal points should the number have.
     * @param string $decPoint     What char should be used for the decimal point.
     * @param string $thousandsSep What char should be used for thousand step.
     *
     * @return string
     */
    public static function numberFormat($num, $dec = 0, $decPoint = '.', $thousandsSep = ',')
    {
        return number_format($num, $dec, $decPoint, $thousandsSep);
    }

    /**
     * Converts the escaped string to it's raw format.
     *
     * @param string $str
     *
     * @return string
     */
    public static function raw($str)
    {
        return html_entity_decode($str, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
    }

    /**
     * Find an replace characters inside the given string.
     *
     * @param string $str
     * @param array  $replacements
     *
     * @return string
     * @throws HtplException
     */
    public static function replace($str, $replacements)
    {
        if (!is_array($replacements)) {
            throw new HtplException('Modifier "replace" requires that the first param is an array.');
        }

        return str_replace(array_keys($replacements), array_values($replacements), $str);
    }

    /**
     * Remove HTML tags from the given string.
     *
     * @param string $str   String from which to remove the tags.
     * @param string $allow A comma separated list of tags that should not be removed.
     *
     * @return string
     */
    public static function stripTags($str, $allow = '')
    {
        return strip_tags($str, $allow);
    }

    /**
     * Trim the string.
     *
     * @param string $str       String to trim
     * @param string $direction Trim direction, can be 'left', 'right' or 'both'.
     * @param string $charMask  Which characters should be trimmed.
     *
     * @return string
     * @throws HtplException
     */
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
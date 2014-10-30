<?php
namespace Webiny\Htpl\Modifiers;

class DatePack implements ModifierPackInterface
{
    public static function getModifiers()
    {
        return [
            'wordTrim' => '\Webiny\Htpl\Modifiers\DatePack::wordTrim',
            'date'     => '\Webiny\Htpl\Modifiers\DatePack::date',
            'case'     => '\Webiny\Htpl\Modifiers\DatePack::caseMod',
            'timeAgo'  => '\Webiny\Htpl\Modifiers\DatePack::timeAgo',
        ];
    }

    public static function wordTrim($content, $length, $ending = '...')
    {

    }

    public static function date($date = 'now', $format = 'Y-m-d H:i:s')
    {

    }

    public static function timeAgo($date)
    {
        // return now
    }

    public static function caseMod($val)
    {
        // bla
    }
}
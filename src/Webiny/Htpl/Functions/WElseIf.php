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
 * WElseIf function.
 *
 * @package Webiny\Htpl\Functions
 */
class WElseIf extends WIf
{
    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public function getTag()
    {
        return 'w-elseif';
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
        if (empty($attributes) || empty($attributes['cond']) || trim($attributes['cond']) == '') {
            throw new HtplException('w-elseif must have a logical condition.');
        }

        $conditions = $this->parseConditions($attributes['cond']);
        $openingTag = '} elseif (' . $conditions . ') {';

        return [
            'openingTag' => OutputWrapper::outputFunction($openingTag),
            'closingTag' => ''
        ];
    }
}
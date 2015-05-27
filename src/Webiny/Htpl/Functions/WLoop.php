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
 * WLoop function.
 *
 * @package Webiny\Htpl\Functions
 */
class WLoop implements FunctionInterface
{
    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public function getTag()
    {
        return 'w-loop';
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
        // items attributes
        if (!isset($attributes['items']) || empty($attributes['items'])) {
            throw new HtplException($this->getTag() . ' function requires `items` attribute to be defined.');
        }
        $items = OutputWrapper::getVar($attributes['items']);

        // var attribute
        if (!isset($attributes['var']) || empty($attributes['var'])) {
            throw new HtplException($this->getTag() . ' function requires `var` attribute to be defined.');
        }

        // key attribute
        $contexts = [$attributes['var']];
        $var = '$' . $attributes['var'];
        $key = null;
        if (isset($attributes['key']) && !empty($attributes['key'])) {
            $contexts[] = $attributes['key'];
            $key = '$' . $attributes['key'];
            $func = 'foreach (' . $items . ' as ' . $key . ' => ' . $var . '){';
        } else {
            $func = 'foreach (' . $items . ' as ' . $var . '){';
        }


        return [
            'openingTag' => OutputWrapper::outputFunction($func),
            'content'    => $content,
            'closingTag' => OutputWrapper::outputFunction('}'),
            'contexts'   => $contexts
        ];
    }
}
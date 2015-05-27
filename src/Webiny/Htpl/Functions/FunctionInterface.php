<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;

/**
 * Function Interface -> all functions must implement this interface.
 *
 * @package Webiny\Htpl\Functions
 */
interface FunctionInterface
{
    /**
     * Return the html tag that the function is attached to.
     * Note: the tag **must** start with 'w-', otherwise it won't get parsed.
     *
     * @return string
     */
    public function getTag();

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
     * @return array|bool
     */
    public function parseTag($content, $attributes, Htpl $htpl);
}
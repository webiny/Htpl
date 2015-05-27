<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Modifiers;

/**
 * ModifierPackInterface -> all modifier packs must implement this interface.
 *
 * @package Webiny\Htpl\Modifiers
 */
interface ModifierPackInterface
{
    const STAGE_POST_ESCAPE = 'post-escape';
    const STAGE_PRE_ESCAPE = 'pre-escape';

    /**
     * Get the list of registered modifiers inside this pack.
     *
     * @return array
     */
    public static function getModifiers();
}
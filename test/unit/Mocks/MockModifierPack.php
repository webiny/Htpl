<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Mocks;

use Webiny\Htpl\Modifiers\ModifierPackInterface;

class MockModifierPack implements ModifierPackInterface
{

    /**
     * Get the list of registered modifiers inside this pack.
     *
     * @return array
     */
    public static function getModifiers()
    {
        return [
            'mock-mod' => []
        ];
    }
}
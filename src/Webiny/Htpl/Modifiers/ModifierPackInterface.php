<?php
namespace Webiny\Htpl\Modifiers;

interface ModifierPackInterface
{
    const STAGE_POST_ESCAPE = 'post-escape';
    const STAGE_PRE_ESCAPE = 'pre-escape';

    public static function getModifiers();
}
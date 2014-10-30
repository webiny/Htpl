<?php

namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Htpl;

class TemplateLoader
{
    public static function getSource($template, $currentPath = '')
    {
        return file_get_contents(self::resolvePath($template, $currentPath));
    }

    public static function resolvePath($path, $currentPath = '')
    {
        if ($path[0] == '/') {
            return Htpl::getTemplateDir() . substr($path, 1);
        } elseif ($path[0] == '.' && $path[1] == '/') {
            // relative path todo
            return 'pero';
        } else {
            return Htpl::getTemplateDir() . $path;
        }
    }


}
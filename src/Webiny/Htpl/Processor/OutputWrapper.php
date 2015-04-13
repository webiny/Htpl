<?php

namespace Webiny\Htpl\Processor;

class OutputWrapper
{

    public static function getVar($name, $context = null)
    {

        if (is_null($context)) {
            $context = '$this->vars';
        }

        return '$this->getVar(\'' . $name . '\', ' . $context . ')';
    }

    public static function outputVar($var)
    {
        return '<?php echo ' . $var . ';?>';
    }

    public static function outputFunction($func)
    {
        return "\n" . '<?php ' . $func . ' ?>' . "\n";
    }

    public static function setContext()
    {
        //todo
    }
}
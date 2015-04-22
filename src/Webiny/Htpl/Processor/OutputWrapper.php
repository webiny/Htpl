<?php

namespace Webiny\Htpl\Processor;

class OutputWrapper
{

    public static function getVar($name, $context = null, $mergeContext = false)
    {
        if (is_null($context)) {
            $context = '$this->vars';
        }

        if ($mergeContext && strpos($name, str_replace('$', '', $context ). '.') === 0) {
            $name = str_replace(str_replace('$', '', $context ) . '.', '', $name);
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

    public static function escape($val)
    {
        return '$this->escape(' . $val . ')';
    }
}
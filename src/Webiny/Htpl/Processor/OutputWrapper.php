<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Processor;

/**
 * OutputWrapper is a class providing helper methods for wrapping compiled variables and function into PHP callbacks.
 *
 * @package Webiny\Htpl\Processor
 */
class OutputWrapper
{

    /**
     * Variable wrapper.
     *
     * @param string      $name         Variable name
     * @param null|string $context      Variable lookup context. If it's null, root context is used.
     * @param bool        $mergeContext Sometimes we change the context, the merge context is used to detect on which
     *                                  variables the context actually needs to change.
     *
     * @return string
     */
    public static function getVar($name, $context = null, $mergeContext = false)
    {
        if (is_null($context)) {
            $context = '$this->vars';
        }

        if ($mergeContext && strpos($name, str_replace('$', '', $context) . '.') === 0) {
            $name = str_replace(str_replace('$', '', $context) . '.', '', $name);
        }

        return '$this->getVar(\'' . $name . '\', ' . $context . ')';
    }

    /**
     * Output a variable wrapper.
     *
     * @param string $var Variable to output.
     *
     * @return string
     */
    public static function outputVar($var)
    {
        return '<?php echo ' . $var . ';?>'."\n";
    }

    /**
     * Output a function wrapper.
     *
     * @param string $func Function to output.
     *
     * @return string
     */
    public static function outputFunction($func)
    {
        return '<?php ' . $func . ' ?>';
    }

    /**
     * Escape callback wrapper.
     *
     * @param string $val Value to escape.
     *
     * @return string
     */
    public static function escape($val)
    {
        return 'htmlspecialchars('.$val.', ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\')';
    }
}
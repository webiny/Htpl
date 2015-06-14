<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Processor\Lexers\VarLexer;
use Webiny\Htpl\Processor\OutputWrapper;

/**
 * WIf function.
 *
 * @package Webiny\Htpl\Functions
 */
class WIf implements FunctionInterface
{
    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public function getTag()
    {
        return 'w-if';
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
            throw new HtplException('w-if must have a logical condition.');
        }

        $conditions = $this->parseConditions($attributes['cond'], $htpl);
        $openingTag = 'if (' . $conditions . ') {';

        return [
            'openingTag' => OutputWrapper::outputFunction($openingTag),
            'closingTag' => OutputWrapper::outputFunction('}')
        ];
    }

    /**
     * Parses the conditions and returns a compiled PHP string to execute the conditions.
     *
     * @param string $conditions A string of conditions.
     *
     * @return string
     */
    protected function parseConditions($conditions, $htpl)
    {
        // extract the strings
        preg_match_all('/([\'])([A-z]?[A-z\.0-9]+)\1/', $conditions, $matches, PREG_OFFSET_CAPTURE);

        $vars = [];
        if (count($matches[0]) > 0) {
            $countOffset = 0;
            foreach ($matches[0] as $m) {
                $varName = 'htpl_' . uniqid();
                $conditions = substr_replace($conditions, $varName, $m[1] + $countOffset, strlen($m[0]));
                $countOffset += strlen($varName) - strlen($m[0]);
                $vars[$varName] = $m[0];
            }
        }

        $testFunctions = [
            'isset'  => 'isset',
            'isnull' => 'isnull',
            'empty'  => 'empty'
        ];

        // protected var names
        $protectedVarNames = ['false', 'true', 'null'];

        // extract the variables
        preg_match_all('/([A-z][\w\.|]+|[A-z]+)/', $conditions, $matches, PREG_OFFSET_CAPTURE);

        if (count($matches[0]) > 0) {
            $countOffset = 0;
            foreach ($matches[0] as $m) {
                if (!in_array($m[0], $testFunctions) && !in_array($m[0], $protectedVarNames)) {
                    if (isset($vars[$m[0]])) {
                        $var = $vars[$m[0]];
                    } else {
                        $var = VarLexer::parse('{'.$m[0].'}', $htpl, false);
                    }

                    $conditions = substr_replace($conditions, $var, $m[1] + $countOffset, strlen($m[0]));

                    $countOffset += strlen($var) - strlen($m[0]);
                }

            }
        }

        return $conditions;
    }
}
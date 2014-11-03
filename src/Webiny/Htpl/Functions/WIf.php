<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\HtplException;

class WIf extends FunctionAbstract
{

    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public static function getTag()
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
     *
     * @throws HtplException
     * @return string|bool
     */
    public static function parseTag($content, $attributes)
    {
        // content
        if (empty($attributes)) {
            throw new HtplException('w-if must have a logical condition.');
        }

        $conditionGroups = self::_extractConditions($attributes);
       // die(print_r($conditionGroups));
        $conditions = '';
        foreach ($conditionGroups as $cg) {
            if($cg[0]=='or'){
                $conditions = substr($conditions, 0, -4).' || ';
            }else{
                $conditions .= '(' . implode(' && ', $cg) . ') && ';
            }
        }
        $openingTag = 'if (' . substr($conditions, 0, -4) . ') {';

        return [
            'openingTag' => self::_outputFunction($openingTag),
            'closingTag' => self::_outputFunction('}')
        ];
    }

    private static function _extractConditions($conditions)
    {
        $conditionGroups = [];
        foreach ($conditions as $var => $c) {
            $innerConditions = explode(';', $c);
            if (count($innerConditions) < 1) {
                $innerConditions = [$c];
            }

            if ($c == 'or' && $var == 'operator') {
                $conditionGroups[] = ['or'];
                continue;
            }

            $conditionList = [];
            foreach ($innerConditions as $i) {
                if ($i != '') {
                    $iData = explode(':', $i);
                    // term
                    $term = self::_getOperand(trim($iData[0]));
                    if (!$term) {
                        throw new HtplException('w-if encountered an unknown operand "' . $iData[0] . '".');
                    }
                    // term value
                    if (is_numeric($iData[1])) {
                        $termValue = $iData[1];
                    } else {
                        if (strpos(trim($iData[1]), '"') === 0 || strpos(trim($iData[1]), "'") === 0) {
                            $termValue = $iData[1];
                        } else {
                            $termValue = self::_getVarName($iData[1]);
                        }
                    }

                    $conditionList[] = self::_getVarName($var) . $term . $termValue;
                }
            }
            $conditionGroups[] = $conditionList;
        }

        return $conditionGroups;
    }

    private static function _getOperand($operandName)
    {
        switch ($operandName) {
            case 'eq':
                return '==';
                break;
            case 'neq':
                return '!=';
                break;
            case 'gt':
                return '>';
                break;
            case 'gte':
                return '>=';
                break;
            case 'lt':
                return '<';
                break;
            case 'lte':
                return '<=';
                break;
            case 'in':
                return 'in_array($var, $value)';
                break;
            case 'nin':
                return '!in_array($var, $value)';
                break;
            default:
                return false;
                break;

        }

    }
}
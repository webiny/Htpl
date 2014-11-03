<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\HtplException;

abstract class FunctionAbstract implements FunctionInterface
{
    protected function _getVarName($name)
    {
        if (strpos($name, '.') !== false) {
            $nameData = explode('.', $name);
            $name = '$_htpl';
            foreach ($nameData as $n) {
                $name .= '[\'' . $n . '\']';
            }

            return $name;
        } else {
            return '$_htpl[\'' . $name . '\']';
        }
    }

    protected function _applyModifiers($var, $modifiers)
    {
        ###############################################################################################
        ### this part here should be improved
        ### current implementation can cause problems is somebody is using "," and ":" as a string,
        ### and not as a delimiter
        ###############################################################################################
        // first parse the modifiers
        $modList = explode(';', $modifiers);
        $passedResult = $var;
        foreach ($modList as $m) {
            if (strpos($m, ':') !== false) {
                $mData = explode(':', $m);
                $mName = trim($mData[0]);

                // check if there are multiple parameters
                $parameters = self::_quotifyParameters($mData[1]);
            } else {
                $mName = trim($m);
                $parameters = '';
            }

            // create the modifier callback
            $modifier = self::_getModifierCallback($mName);

            $passedResult = $modifier . '(' . $passedResult . (!empty($parameters) ? ', ' . $parameters : '') . ')';
        }

        return $passedResult;
    }

    protected function _outputVar($var)
    {
        return '<?php echo ' . $var . ';?>';
    }

    protected function _outputFunction($func)
    {
        return "\n" . '<?php ' . $func . ' ?>' . "\n";
    }

    protected function _parseProperties($string)
    {
        $properties = [];
        $stringData = explode(';', $string);
        if (count($stringData) < 1) {
            $stringData = [$string];
        }

        foreach ($stringData as $sd) {
            if (trim($sd) != '') {
                $sdData = explode(':', $sd);
                if (count($sdData) == 2) {
                    $properties[trim($sdData[0])] = trim($sdData[1]);
                }
            }
        }

        return $properties;
    }

    private function _quotifyParameters($parameters)
    {
        $paramList = explode(',', $parameters);
        if (count($paramList) < 1) {
            $paramList = [$parameters];
        }

        $parameters = [];
        foreach ($paramList as $p) {
            $firstChar = $p[0];
            $lastChar = substr($p, -1);

            if (($firstChar == '"' && $lastChar == '"') || ($firstChar == "'" && $lastChar == "'")) {
                $parameters[] = $p;
            } else {
                if ($firstChar == '"') {
                    $parameters[] = "'" . $p . "'";
                } else {
                    $parameters[] = '\'' . $p . '\'';
                }
            }
        }

        return implode(', ', $parameters);
    }

    private function _getModifierCallback($name)
    {
        $modifiers = [
            'wordTrim' => '\Webiny\Htpl\Modifiers\DatePack::wordTrim',
            'date'     => '\Webiny\Htpl\Modifiers\DatePack::date',
            'case'     => '\Webiny\Htpl\Modifiers\DatePack::caseMod',
            'timeAgo'  => '\Webiny\Htpl\Modifiers\DatePack::timeAgo',
        ];

        if (!isset($modifiers[$name])) {
            if (empty($modifier)) {
                throw new HtplException('Unknown modifier "' . $name . '";');
            }
        }

        return $modifiers[$name];
    }
}
<?php

namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;

class VarParser
{
    private $htpl;

    static public function parseTemplate($tpl, Htpl $htpl)
    {
        preg_match_all('/{(.*?)[^\\\\]}/', $tpl, $matches);

        if (count($matches[0]) < 1) {
            return $tpl;
        }

        $vParser = new self($htpl);
        foreach ($matches[0] as $m) {
            $var = $vParser->parseVar($m);

            // update the template
            $tpl = str_replace($m, OutputWrapper::outputVar($var), $tpl);
        }

        return $tpl;
    }

    public function __construct(Htpl $htpl)
    {
        $this->htpl = $htpl;
    }

    private function parseVar($var)
    {
        $condition = trim(substr($var, 1, -1)); // trim and remove curly brackets

        // check if we have a modifier
        preg_match('/\|(.*?)$/', $condition, $modifiers);

        if (count($modifiers) > 0) { // has modifier
            // if we matched a modifier, the first match is a variable, since we don't allow multiple variables with modifiers
            // get the variable
            preg_match('/([A-z][A-z0-9.]+)/', $condition, $varMatch);
            $var = trim($varMatch[1]);
            $var = OutputWrapper::getVar($var);
            $condition = $this->parseModifier($var, $modifiers[0]);
        } else { // no modifier
            // extract the vars from condition
            // sometimes we have multiple vars inside the same brackets, eg {var1+var2/var3}
            preg_match_all('/([A-z][A-z0-9.]+)/', $condition, $vars);

            // replace vars with php variables
            if (count($vars[0]) > 0) {
                foreach ($vars[0] as $name) {
                    $var = OutputWrapper::getVar($name);
                    $condition = str_replace($name, $var, $condition);
                }
            }
        }

        return $condition;
    }

    private function parseModifier($var, $modifiers)
    {
        $passedResult = $var;

        // parse modifiers
        $modifiers = explode('|W:NL|',
            preg_replace('/(\|.)([A-z0-9]+)(|\(([\s\S]+?)\)((\||\}| \|)))+/', '|W:NL|$0', $modifiers));

        foreach ($modifiers as $mod) {
            $mod = trim(substr($mod, 1));
            if ($mod == '') {
                continue;
            }

            // get modifier name
            preg_match('/([A-z][A-z0-9.]+)/', $mod, $modNameMatch);
            $modName = trim($modNameMatch[0]);

            // parse modifier parameters
            $modParams = substr($mod, strlen($modName));

            # first: replace all quoted strings with a custom value
            $replacements = [];
            preg_match_all('/("|\')([\S\s]+?)\1/', $modParams, $modParamMatches);

            $parameters = [];
            if (count($modParamMatches[0]) > 0) {
                foreach ($modParamMatches[0] as $mp) {
                    $id = uniqid('htpl_');
                    $replacements[$id] = substr($mp, 1, -1); // to remove the outer quotes

                    $modParams = str_replace($mp, $id, $modParams);
                }
            }

            # second: explode the string on commas
            $modParams = explode(',', substr($modParams, 1, -1));

            foreach ($modParams as $p) {
                $p = trim($p);
                if (isset($replacements[$p])) {
                    # revert the replacements inside the array
                    # quote the param
                    if (strpos($replacements[$p], '"') !== false) {
                        $parameters[] = "'" . $replacements[$p] . "'";
                    } else {
                        $parameters[] = '"' . $replacements[$p] . '"';
                    }
                } else {
                    # if not in array, it's probably a number
                    $parameters[] = $p;
                }
            }

            // get the modifier callback
            if (!isset($this->htpl->getModifiers()[$modName])) {
                throw new HtplException(sprintf('Unknown modifier "%s".', $modName));
            }

            // build modifier callback
            $passedResult = $this->htpl->getModifiers()[$modName] . '(' . $passedResult . (count($parameters) > 0 ? ', ' . implode(',',
                        $parameters) : '') . ')';
        }

        // always escape modifier result
        $passedResult = '$this->escape(' . $passedResult . ')';

        return $passedResult;
    }
}
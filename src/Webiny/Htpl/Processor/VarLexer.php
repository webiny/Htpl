<?php
namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;

class VarLexer
{
    // general types
    const T_STRING = 'T_STRING';
    const T_DOUBLE_QUOTE = 'T_DOUBLE_QUOTE';
    const T_SINGLE_QUOTE = 'T_SINGLE_QUOTE';
    const T_COMMA = 'T_COMMA';
    const T_WHITESPACE = 'T_WHITESPACE';
    const T_MATH = 'T_MATH';

    // array
    const ARRAY_START = 'ARRAY_START';
    const ARRAY_END = 'ARRAY_END';
    const ARRAY_SEPARATOR = 'ARRAY_SEPARATOR';

    // modifier
    const MOD_SEPARATOR = 'MOD_SEPARATOR';
    const MOD_PARAM_START = 'MOD_PARAM_START';
    const MOD_PARAM_END = 'MOD_PARAM_END';

    // other
    const T_OTHER = 'T_OTHER';

    protected static $_terminals = array(
        // general types
        '/^([\w\.]+)/'  => self::T_STRING,
        '/^(\")/'       => self::T_DOUBLE_QUOTE,
        '/^(\')/'       => self::T_SINGLE_QUOTE,
        '/^(\,)/'       => self::T_COMMA,
        '/^(\s+)/'      => self::T_WHITESPACE,
        '/^([+-\/*%])/' => self::T_MATH,
        // array
        '/^({)/'        => self::ARRAY_START,
        '/^(})/'        => self::ARRAY_END,
        '/^(\:)/'       => self::ARRAY_SEPARATOR,
        // modifier
        '/^(\|)/'       => self::MOD_SEPARATOR,
        '/^(\()/'       => self::MOD_PARAM_START,
        '/^(\))/'       => self::MOD_PARAM_END,
        // other
        '/^(\S)/'       => self::T_OTHER
    );

    /**
     * @var array Tokens.
     */
    private $parts = [];

    /**
     * @var array Current var data.
     */
    private $currentVar = [];

    /**
     * @var string Given input that will be parsed.
     */
    private $input;

    /**
     * @var Htpl Current Htpl instance.
     */
    private $htpl;


    /**
     * Parses the given input
     *
     * @param string $input
     *
     * @return string Parse result.
     * @throws HtplException
     */
    static public function parse($input, Htpl $htpl)
    {
        // break template into lines
        $lines = explode("\n", $input);

        foreach ($lines as $l => $line) {
            try {
                $instance = new self($line, $htpl);
                $lines[$l] = $instance->getOutput();
            } catch (HtplException $e) {
                throw new HtplException(sprintf('Unable to parse the template at line %s. ', $l) . $e->getMessage());
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param string $input Input that should be parsed into a callback.
     */
    public function __construct($input, Htpl $htpl)
    {
        $this->input = $input;
        $this->htpl = $htpl;
    }

    /**
     * Returns the result of parsing the input.
     *
     * @return string
     * @throws HtplException
     */
    public function getOutput()
    {
        // get lexer flags from Htpl options
        $lexerVarFlags = $this->htpl->getLexerFlags();

        // check if we need to parse the string at all
        if (strpos($this->input, $lexerVarFlags['varStartFlag']) === false || strpos($this->input,
                $lexerVarFlags['varEndFlag']) === false
        ) {
            return $this->input;
        }

        // tokenize
        $offset = 0;
        $number = 0;
        $matchedEntries = 0;
        $input = $this->input;
        while ($offset < strlen($this->input)) {
            $result = $this->tokenize($this->input, $number, $offset);
            if ($result === false) {
                throw new HtplException(sprintf('Unable to parse template near %s', substr($this->input, $offset)));
            }

            $offset += strlen($result['match']);

            if ($result['match'] == $lexerVarFlags['varStartFlag']) {
                $matchedEntries++;
                $this->parts[] = $result;
            } else if (($matchedEntries > 0 && $result['match'] != $lexerVarFlags['varEndFlag'])) {
                $this->parts[] = $result;
            } else if ($matchedEntries > 0 && $result['match'] == $lexerVarFlags['varEndFlag']) {
                $matchedEntries--;
                $this->parts[] = $result;
                if ($matchedEntries == 0) {
                    $entryString = $this->joinParts();

                    // remove the variable start flag
                    array_shift($this->parts);
                    // remove the variable end flag
                    array_pop($this->parts);

                    // parse variable (variable name + attached modifiers)
                    $input = str_replace($entryString, OutputWrapper::outputVar($this->lexVariables()), $input);

                    // reset the arrays
                    $this->parts = [];
                    $this->currentVar = [];
                }
            }
        }

        return $input;
    }

    /**
     * Tokenizes the given line.
     *
     * @param string $line   Current line.
     * @param int    $number Line number.
     * @param int    $offset Token offset.
     *
     * @return array|bool
     */
    private function tokenize($line, $number, $offset)
    {
        $string = substr($line, $offset);

        foreach (static::$_terminals as $pattern => $name) {
            if (preg_match($pattern, $string, $matches)) {
                return array(
                    'match' => $matches[1],
                    'token' => $name,
                    'line'  => $number + 1
                );
            }
        }

        return false;
    }

    /**
     * Moves the cursor to the next token.
     */
    private function moveCursor()
    {
        array_shift($this->parts);
    }

    /**
     * Get the current token name.
     *
     * @return string|bool Current token name, or false if there are no tokens.
     */
    private function currentToken()
    {
        return isset($this->parts[0]) ? $this->parts[0]['token'] : false;
    }

    /**
     * Get the value of current token.
     *
     * @return string|bool Current token value, or false if there no tokens.
     */
    private function currentValue()
    {
        return isset($this->parts[0]) ? $this->parts[0]['match'] : false;
    }

    /**
     * Moves the cursor so it skips all the whitespace tokens.
     */
    private function skipWhitespace()
    {
        while ($this->currentToken() == self::T_WHITESPACE) {
            $this->moveCursor();
        }
    }

    /**
     * Counts the number of remaining tokens.
     *
     * @return int Current token count.
     */
    private function countParts()
    {
        return count($this->parts);
    }

    /**
     * Joins the remaining parts into a string.
     *
     * @return string
     */
    private function joinParts()
    {
        $str = '';
        foreach ($this->parts as $p) {
            $str .= $p['match'];
        }

        return $str;
    }

    /**
     * Builds the variables for the current parts.
     *
     * @return string
     * @throws HtplException
     */
    private function lexVariables()
    {
        $variables = [];
        $inputString = $this->joinParts();
        do {
            $this->lexVariable();
            $this->lexMathOperator();
            $this->lexModifiers();

            $variables[] = $this->currentVar;
            $this->currentVar = [];
        } while ($this->countParts() > 0);

        $varComplete = '';
        foreach ($variables as $v) {
            foreach ($v as $type => $param) {
                // variable name
                if ($type == 'name') {
                    $var = $this->outputParameter($param);
                }

                // modifiers
                if ($type == 'modifiers') {

                    // pre-escape
                    if (isset($param['pre-escape'])) {
                        foreach ($param['pre-escape'] as $mod) {
                            $var = $this->outputModifier($mod, $var);
                        }
                    }

                    // escape
                    $var = OutputWrapper::escape($var);

                    // post-escape
                    if (isset($param['post-escape'])) {
                        foreach ($param['post-escape'] as $mod) {
                            $var = $this->outputModifier($mod, $var);
                        }
                    }
                }

                // math operations
                if ($type == 'mathOperators') {
                    foreach ($param as $operator) {
                        $var .= $operator;
                    }
                }
            }
            $varComplete .= isset($v['modifiers']) ? $var : OutputWrapper::escape($var);
        }

        return $varComplete;
    }

    /**
     * Parse var name.
     */
    private function lexVariable()
    {
        $this->skipWhitespace();
        $this->currentVar['name'] = $this->lexParameter();
    }

    private function lexMathOperator()
    {
        if ($this->currentToken() != self::T_MATH) {
            return false;
        }

        $this->currentVar['mathOperators'][] = $this->currentValue();
        $this->moveCursor();
    }


    /**
     * Parse modifiers.
     *
     * @return bool False is returned if there are no modifiers.
     * @throws HtplException
     */
    private function lexModifiers()
    {
        // check if we have modifiers
        $this->skipWhitespace();
        if ($this->currentToken() != self::MOD_SEPARATOR) {
            // check how many tokens we have left, either return the var, or issue a warning
            if ($this->countParts() > 0) {
                //throw new HtplException('Unable to parse variable near ' . $this->joinParts());
                return true;
            } else {
                return false;
            }
        }
        $this->moveCursor();

        # lets parse modifiers
        // get modifier name
        $modifiers = [];
        $loop = 0;
        do {
            $this->skipWhitespace();
            if ($this->currentToken() == self::MOD_SEPARATOR || $this->currentToken() == self::T_COMMA) {
                $this->moveCursor();
            }
            $mod = $this->lexModifier();
            $modifiers[$mod['stage']][] = $mod;
            $loop++;
            if ($loop > 100) {
                throw new HtplException(sprintf('Unable to find %s near %s', self::MOD_PARAM_END, $this->input));
            }
        } while ($this->countParts() > 0);

        $this->currentVar['modifiers'] = $modifiers;
    }

    /**
     * Parse a single modifier.
     *
     * @return array
     * @throws HtplException
     */
    private function lexModifier()
    {
        // expected entry is a T_STRING
        $this->skipWhitespace();
        if ($this->currentToken() != self::T_STRING) {
            throw new HtplException(sprintf('Unable to parse variable. Expected a %s, got a %s', self::T_STRING,
                $this->currentToken()));
        }

        $modName = $this->currentValue();
        $this->moveCursor();

        // get modifier parameters
        $params = [];
        $this->skipWhitespace();
        if ($this->currentToken() == self::MOD_PARAM_START) {
            $this->moveCursor();
            while (($param = $this->lexParameter()) != false) {
                $params[] = $param;
            }
        }

        // check that the modifier exist, and extract the callback and the stage to which we need to apply it
        if (!isset($this->htpl->getModifiers()[$modName])) {
            throw new HtplException(sprintf('Unknown modifier %s', $modName));
        }

        return [
            'name'     => $modName,
            'params'   => $params,
            'callback' => $this->htpl->getModifiers()[$modName]['callback'],
            'stage'    => $this->htpl->getModifiers()[$modName]['stage']
        ];
    }

    /**
     * Parses the current token as a parameter, base on the token type.
     *
     * @return array|bool
     * @throws HtplException
     */
    private function lexParameter()
    {
        // expected entry is a T_COMMA
        $this->skipWhitespace();
        if ($this->currentToken() == self::T_COMMA) {
            $this->moveCursor();
        }

        // detect the end of the modifier
        $this->skipWhitespace();
        if ($this->currentToken() == self::MOD_PARAM_END) {
            $this->moveCursor();
            return false;
        }

        // every param needs to start with a quote
        $this->skipWhitespace();

        if (ctype_digit($this->currentValue())) { // check if it's a number -> number params don't have quotes
            return [
                'type'  => 'number',
                'value' => $this->lexNumberParam()
            ];
        } else if ($this->currentToken() == self::T_DOUBLE_QUOTE || $this->currentToken() == self::T_SINGLE_QUOTE) { // check if string
            return [
                'type'  => 'string',
                'value' => $this->lexStringParam()
            ];
        } else if ($this->currentToken() == self::ARRAY_START) { // check if array
            return [
                'type'  => 'array',
                'value' => $this->lexArrayParam()
            ];
        } else if ($this->currentToken() == self::T_STRING) { // check if variable
            return [
                'type'  => 'variable',
                'value' => $this->lexVariableParam()
            ];
        } else if ($this->currentToken() == self::T_MATH) { // check if a math function
            return [
                'type'  => 'math',
                'value' => $this->currentValue()
            ];
        } else {
            throw new HtplException(sprintf('Unable to parse variable, unexpected %s near %s', $this->currentToken(),
                $this->joinParts()));
        }
    }

    /**
     * Parses number parameter.
     *
     * @return int
     */
    private function lexNumberParam()
    {
        $paramValue = $this->currentValue();
        $this->moveCursor();

        return $paramValue;
    }

    /**
     * Parses string parameter.
     *
     * @return string
     * @throws HtplException
     */
    private function lexStringParam()
    {
        $paramStartQuote = $this->currentToken();
        $this->moveCursor();

        // until we reach the ending quote, everything is considered a param value
        $paramValue = '';
        $loops = 0;
        while ($this->currentToken() != $paramStartQuote) {
            $paramValue .= $this->currentValue();
            $this->moveCursor();
            $loops++;
            if ($loops > 100) {
                throw new HtplException(sprintf('Unable to find ending %s for a parameter inside %s variable',
                    $paramStartQuote, $this->currentVar['name']));
            }
        }
        $this->moveCursor(); // ending quote

        return $paramValue;
    }

    /**
     * Parses array parameter.
     *
     * @return array
     * @throws HtplException
     */
    private function lexArrayParam()
    {
        $this->moveCursor();

        // an array can be key/value, or just values
        $array = [];
        while ($this->currentToken() != self::ARRAY_END) {
            $value = $this->lexParameter();
            if ($this->currentToken() == self::ARRAY_SEPARATOR) {
                $this->moveCursor();
                $this->skipWhitespace();
                $key = $value;
                $value = $this->lexParameter();
                $array[] = [
                    'key'   => $key,
                    'value' => $value
                ];
                $this->skipWhitespace();
                if ($this->currentToken() == self::T_COMMA) {
                    $this->moveCursor();
                    $this->skipWhitespace();
                }
            } else {
                $array[]['value'] = $value;
            }
        }

        $this->moveCursor();

        return $array;
    }

    /**
     * Parses variable parameter.
     *
     * @return string
     */
    private function lexVariableParam()
    {
        $paramValue = $this->currentValue();
        $this->moveCursor();

        return $paramValue;
    }

    /**
     * Creates an output parameter.
     *
     * @param array $param
     *
     * @return string
     * @throws HtplException
     */
    private function outputParameter($param)
    {
        if ($param['type'] == 'string') {
            return '"' . addcslashes($param['value'], '"') . '"';
        } else if ($param['type'] == 'variable') {
            return OutputWrapper::getVar($param['value']);
        } else if ($param['type'] == 'number') {
            return $param['value'];
        } else if ($param['type'] == 'array') {
            $arrayParams = [];
            foreach ($param['value'] as $a) {
                if(isset($a['key'])){
                    $arrayParams[] = $this->outputParameter($a['key']) . '=>' . $this->outputParameter($a['value']);
                }else{
                    $arrayParams[] = $this->outputParameter($a['value']);
                }
            }

            return '[' . implode(',', $arrayParams) . ']';
        } else if ($param['type'] == 'math') {
            return $param['value'];
        } else {
            throw new HtplException(sprintf('Unknown parameter: "%s".', $param['type']));
        }
    }

    /**
     * Creates callback for the given modifier.
     *
     * @param array  $mod Modifier data.
     * @param string $var Current variable callback.
     *
     * @return string
     * @throws HtplException
     */
    private function outputModifier($mod, $var)
    {
        // callback
        $result = $this->htpl->getModifiers()[$mod['name']]['callback'] . '(' . $var;

        // parameters
        if (count($mod['params']) > 0) {
            $params = [];

            foreach ($mod['params'] as $p) {
                $params[] = $this->outputParameter($p);
            }

            $result .= ', ' . implode(', ', $params);
        }

        $result .= ')';

        return $result;
    }
}
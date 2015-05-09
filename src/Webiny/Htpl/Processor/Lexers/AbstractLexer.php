<?php

namespace Webiny\Htpl\Processor\Lexers;

/**
 * Class AbstractLexer
 * @package Webiny\Htpl\Processor\Lexers
 */
abstract class AbstractLexer
{
    const T_WHITESPACE = 'T_WHITESPACE';

    /**
     * @var array List of current token parts.
     */
    protected $parts = [];

    /**
     * @var array List of token parts that the cursor has already passed.
     */
    protected $shiftedParts = [];

    /**
     * @var string Given input that will be parsed.
     */
    protected $input;


    /**
     * Tokenizes the given line.
     *
     * @param string $line   Current line.
     * @param int    $number Line number.
     * @param int    $offset Token offset.
     *
     * @return array|bool
     */
    protected function tokenize($line, $number, $offset)
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
    protected function moveCursor()
    {
        $this->shiftedParts[] = array_shift($this->parts);
    }

    protected function prevCursor()
    {
        $part = array_pop($this->shiftedParts);
        array_unshift($this->parts, $part);
    }

    /**
     * Get the current token name.
     *
     * @return string|bool Current token name, or false if there are no tokens.
     */
    protected function currentToken()
    {
        return isset($this->parts[0]) ? $this->parts[0]['token'] : false;
    }

    /**
     * Get the value of current token.
     *
     * @return string|bool Current token value, or false if there no tokens.
     */
    protected function currentValue()
    {
        return isset($this->parts[0]) ? $this->parts[0]['match'] : false;
    }

    /**
     * Moves the cursor so it skips all the whitespace tokens.
     */
    protected function skipWhitespace()
    {
        $whitespaces = '';
        while ($this->currentToken() == self::T_WHITESPACE) {
            $whitespaces .= $this->currentValue();
            $this->moveCursor();
        }

        return $whitespaces;
    }

    /**
     * Counts the number of remaining tokens.
     *
     * @return int Current token count.
     */
    protected function countParts()
    {
        return count($this->parts);
    }

    /**
     * Joins the remaining parts into a string.
     *
     * @return string
     */
    protected function joinParts()
    {
        $str = '';
        foreach ($this->parts as $p) {
            $str .= $p['match'];
        }

        return $str;
    }
}
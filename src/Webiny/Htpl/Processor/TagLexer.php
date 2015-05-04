<?php
namespace Webiny\Htpl\Processor;

use Webiny\Htpl\HtplException;

class TagLexer
{
    // general types
    const T_STRING = 'T_STRING';
    const T_DOUBLE_QUOTE = 'T_DOUBLE_QUOTE';
    const T_SINGLE_QUOTE = 'T_SINGLE_QUOTE';
    const T_COMMA = 'T_COMMA';
    const T_WHITESPACE = 'T_WHITESPACE';
    const T_EQUAL = 'T_EQUAL';


    // array
    const TAG_END = 'TAG_END';
    const TAG_OPEN = 'TAG_OPEN';
    const TAG_CLOSE = 'TAG_CLOSE';
    const TAG_SELF_CLOSE = 'TAG_SELF_CLOSE';

    // other
    const T_OTHER = 'T_OTHER';

    protected static $_terminals = array(
        // general types
        '/^([\w\.-]+)/' => self::T_STRING,
        '/^(\")/'       => self::T_DOUBLE_QUOTE,
        '/^(\')/'       => self::T_SINGLE_QUOTE,
        '/^(\,)/'       => self::T_COMMA,
        '/^(\s+)/'      => self::T_WHITESPACE,
        '/^(\=)/'       => self::T_EQUAL,
        // tags
        '/^(\<\/)/'     => self::TAG_END,
        '/^(\<)/'       => self::TAG_OPEN,
        '/^(\>)/'       => self::TAG_CLOSE,
        '/^(\<\/)/'     => self::TAG_SELF_CLOSE,
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

    private $tags = [];

    /**
     * Parses the given input
     *
     * @param string $input
     *
     * @return LexedTemplate
     * @throws HtplException
     */
    static public function parse($input)
    {
        try {
            $instance = new self($input);
            $lexedTags = $instance->getOutput();
        } catch (HtplException $e) {

            throw new HtplException(sprintf('Unable to parse the template.' . "\n") . $e->getMessage());
        }

        return new LexedTemplate($lexedTags, $input);
    }

    /**
     * @param string $input Input that should be parsed into a callback.
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Returns the result of parsing the input.
     *
     * @return string
     * @throws HtplException
     */
    public function getOutput()
    {
        // tokenize
        $offset = 0;
        $number = 0;
        while ($offset < strlen($this->input)) {
            $result = $this->tokenize($this->input, $number, $offset);
            if ($result === false) {
                throw new HtplException("Unable to parse line " . ($number + 1) . " near " . substr($this->input,
                        $offset));
            }
            $this->parts[] = $result;
            $offset += strlen($result['match']);
        }

        try {
            return $this->lexTags();
        } catch (HtplException $e) {
            $this->skipWhitespace();
            $currentLine = explode("\n", $this->joinParts());
            throw new HtplException(sprintf('Error near %s.' . "\n",
                    $currentLine[0] . "\n" . $currentLine[1] . "\n" . $currentLine[2]) . $e->getMessage());
        }

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
        $this->shiftedParts[] = array_shift($this->parts);
    }

    private function prevCursor()
    {
        $part = array_pop($this->shiftedParts);
        array_unshift($this->parts, $part);
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

    private function lexTags()
    {
        do {
            $tag = $this->lexTag();
            if ($tag) {
                $this->tags[] = $tag;
            }
        } while ($this->countParts() > 0);

        return $this->tags;
    }

    private function lexTag()
    {
        if ($this->currentToken() != self::TAG_OPEN) {
            $this->moveCursor();
            return false;
        }

        $openingTag = $this->currentValue();
        $this->moveCursor();
        if (substr($this->currentValue(), 0, 2) != 'w-') {
            $this->moveCursor();
            return false;
        }

        // tag name
        $name = $this->currentValue();
        $this->moveCursor();
        $openingTag .= $name;

        // parameters
        $openingTag .= $this->skipWhitespace();
        $parameters = [];
        if ($this->currentToken() == self::T_STRING) {
            do {
                // param name
                $paramName = $this->currentValue();
                $openingTag .= $paramName;
                // equal sign
                $this->moveCursor();
                $openingTag .= $this->skipWhitespace();
                if ($this->currentToken() != self::T_EQUAL) {
                    if($this->currentToken()==self::TAG_CLOSE){
                        break;
                    }

                    throw new HtplException(sprintf('Unexpected %s, expecting %s', $this->currentToken(),
                        self::T_EQUAL));
                }
                $openingTag .= $this->currentValue();
                // open quote for param value
                $this->moveCursor();
                $openingTag .= $this->skipWhitespace();
                if ($this->currentToken() != self::T_DOUBLE_QUOTE && $this->currentToken() != self::T_SINGLE_QUOTE) {
                    throw new HtplException(sprintf('Unexpected %s, expecting %s or %s', $this->currentToken(),
                        self::T_DOUBLE_QUOTE, self::T_SINGLE_QUOTE));
                }
                $openQuote = $this->currentToken();
                $openingTag .= $this->currentValue();
                // param value
                $this->moveCursor();
                $value = '';
                while ($this->currentToken() != $openQuote) {
                    $value .= $this->currentValue();
                    $openingTag .= $this->currentValue();
                    $this->moveCursor();
                }

                // end quote
                $openingTag .= $this->currentValue();
                $this->moveCursor();

                if ($this->currentToken() == self::T_WHITESPACE) {
                    $openingTag .= $this->currentValue();
                    $this->moveCursor();
                }

                // store param
                $parameters[$paramName] = $value;
            } while ($this->currentToken() != self::TAG_CLOSE && $this->currentToken() != self::TAG_SELF_CLOSE && $this->currentToken() != self::TAG_OPEN);
        }

        $openingTag .= $this->currentValue();
        $this->moveCursor();

        // content
        $content = '';
        $closingTag = '';
        while ($this->countParts() > 0) {
            $value = $this->currentValue();
            if ($this->currentToken() == self::TAG_END || $this->currentToken() == self::TAG_SELF_CLOSE) {
                $this->moveCursor();

                if ($value . $this->currentValue() == '</' . $name) {
                    $this->moveCursor();
                    $closingTag = '</' . $name . '>';
                    break;
                } else {
                    $content .= $value . $this->currentValue();
                }
            } elseif ($this->currentToken() == self::TAG_OPEN) {
                $tag = $this->lexTag();
                if ($tag) {
                    $this->tags[] = $tag;
                    $content .= $tag['outerHtml'];
                } else {
                    do {
                        $this->prevCursor();
                    } while ($this->currentToken() != self::TAG_OPEN);
                    $this->moveCursor();
                    $content .= $value . $this->currentValue();
                }
            } else {
                $content .= $value;
            }
            $this->moveCursor();
        }

        return [
            'name'       => $name,
            'content'    => $content,
            'attributes' => $parameters,
            'outerHtml'  => $openingTag . $content . $closingTag
        ];
    }

}
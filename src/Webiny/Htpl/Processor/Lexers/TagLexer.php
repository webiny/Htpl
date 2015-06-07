<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Processor\Lexers;

use Webiny\Htpl\HtplException;
use Webiny\Htpl\Processor\LexedTemplate;

/**
 * TagLexer parses the html tags.
 * Note: not all html tags are parsed, only the ones starting with 'w-'.
 *
 * @package Webiny\Htpl\Processor\Lexers
 */
class TagLexer extends AbstractLexer
{
    // general types
    const T_STRING = 'T_STRING';
    const T_DOUBLE_QUOTE = 'T_DOUBLE_QUOTE';
    const T_SINGLE_QUOTE = 'T_SINGLE_QUOTE';
    const T_COMMA = 'T_COMMA';
    const T_WHITESPACE = 'T_WHITESPACE';
    const T_EQUAL = 'T_EQUAL';

    // tag types
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
        '/^(\/\>)/'     => self::TAG_SELF_CLOSE,
        '/^(\<\/)/'     => self::TAG_END,
        '/^(\<)/'       => self::TAG_OPEN,
        '/^(\>)/'       => self::TAG_CLOSE,
        // other
        '/^(\S)/'       => self::T_OTHER
    );

    /**
     * @var array List of parsed tags.
     */
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

            throw new HtplException(sprintf('Unable to parse the template.' . "\n"), 0, $e);
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
            throw new HtplException(sprintf('Error near %s.', substr($this->joinParts(), 0, 255)), 0, $e);
        }

    }

    /**
     * Perform the tag parsing.
     *
     * @return array An array of parsed tags.
     * @throws HtplException
     */
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

    /**
     * Parse a single tag.
     *
     * @return array|bool Tag details, or false if it cannot be parsed.
     * @throws HtplException
     */
    private function lexTag()
    {
        $start = $this->countParts();

        // check if it's opening tag
        if ($this->currentToken() != self::TAG_OPEN) {
            $this->moveCursor();
            return false;
        }

        // we only parse the tags with the w- prefix
        $this->moveCursor();
        if (substr($this->currentValue(), 0, 2) != 'w-') {
            $this->moveCursor();
            return false;
        }

        // get tag name
        $name = $this->lexTagName();

        // check if the tag has parameters
        $parameters = [];
        if ($this->currentToken() != self::TAG_CLOSE && $this->currentToken() != self::TAG_SELF_CLOSE) {
            // parameters
            $this->skipWhitespace();
            $parameters = $this->lexTagParameters();
        }

        // get all the parts between when we stared parsing the opening tag, and when we stopped
        $openingTag = '';
        $partCount = count($this->shiftedParts);
        $dif = ($start - $this->countParts());
        for ($i = 0; $i < $dif; $i++) {
            $openingTag .= $this->shiftedParts[$partCount - $dif + $i]['match'];
        }
        $openingTag .= $this->currentValue();

        // tag close
        if ($this->currentToken() != self::TAG_CLOSE && $this->currentToken() != self::TAG_SELF_CLOSE) {
            throw new HtplException(sprintf('Expecting %s, got %s.', self::TAG_CLOSE, $this->currentToken()));
        }

        // content
        $content = '';
        if ($this->currentToken() != self::TAG_SELF_CLOSE) {
            $this->moveCursor();// move the TAG_CLOSE
            $content = $this->lexTagContent($name);
            $outerContent = $openingTag . $content . '</' . $name . '>';
        } else {
            $outerContent = $openingTag;
        }

        return [
            'name'       => $name,
            'content'    => $content,
            'attributes' => $parameters,
            'outerHtml'  => $outerContent
        ];
    }

    /**
     * Parse the tag name.
     *
     * @return bool|string
     * @throws HtplException
     */
    private function lexTagName()
    {
        if ($this->currentToken() != self::T_STRING) {
            throw new HtplException(sprintf('Expecting %s, got %s.', self::T_STRING, $this->currentToken()));
        }

        // tag name
        $name = $this->currentValue();
        $this->moveCursor();

        return $name;
    }

    /**
     * Parse the tag parameters (attributes).
     *
     * @return array
     * @throws HtplException
     */
    private function lexTagParameters()
    {
        $parameters = [];
        if ($this->currentToken() == self::T_STRING) {
            do {
                // param name
                $paramName = $this->currentValue();

                // equal sign
                $this->moveCursor();
                if ($this->currentToken() != self::T_EQUAL) {
                    if ($this->currentToken() == self::TAG_CLOSE || $this->currentToken() == self::TAG_SELF_CLOSE) {
                        break;
                    }

                    throw new HtplException(sprintf('Unexpected %s, expecting %s', $this->currentToken(),
                        self::T_EQUAL));
                }

                // open quote for param value
                $this->moveCursor();
                if ($this->currentToken() != self::T_DOUBLE_QUOTE && $this->currentToken() != self::T_SINGLE_QUOTE) {
                    throw new HtplException(sprintf('Unexpected %s, expecting %s or %s', $this->currentToken(),
                        self::T_DOUBLE_QUOTE, self::T_SINGLE_QUOTE));
                }
                $openQuote = $this->currentToken();

                // param value
                $this->moveCursor();
                $value = '';
                while ($this->currentToken() != $openQuote) {
                    $value .= $this->currentValue();
                    $this->moveCursor();
                }

                // end quote
                $this->moveCursor();

                // possible separator before the next whitespace
                if ($this->currentToken() == self::T_WHITESPACE) {
                    $this->skipWhitespace();
                }

                // store param
                $parameters[$paramName] = $value;
            } while ($this->currentToken() != self::TAG_CLOSE && $this->currentToken() != self::TAG_SELF_CLOSE && $this->currentToken() != self::TAG_OPEN);
        }

        return $parameters;
    }

    /**
     * Parse the tag internal content.
     *
     * @param string $tagName Tag name that is being parsed.
     *
     * @return string Internal content.
     * @throws HtplException
     */
    private function lexTagContent($tagName)
    {
        // quick check if we have a closing tag
        if (stripos($this->input, '</' . $tagName . '>') == false) {
            throw new HtplException(sprintf('Missing a closing tag for %s block.', $tagName));
        }

        $content = '';
        while ($this->countParts() > 0) {
            $value = $this->currentValue();
            // check if it's the tag end for the current tag
            if ($this->currentToken() == self::TAG_END || $this->currentToken() == self::TAG_SELF_CLOSE) {
                $this->moveCursor();

                if ($value . $this->currentValue() == '</' . $tagName) {
                    $this->moveCursor();
                    break;
                } else {
                    $content .= $value . $this->currentValue();
                }
                // check if a new tag is inside the current tag
            } elseif ($this->currentToken() == self::TAG_OPEN) {
                // try to parse the nested tag
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

        return $content;
    }
}
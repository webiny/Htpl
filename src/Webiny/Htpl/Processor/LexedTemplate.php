<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Processor;

/**
 * LexedTemplate is the result of TagLexer.
 *
 * @package Webiny\Htpl\Processor
 */
class LexedTemplate
{
    /**
     * @var array List of lexed tags.
     */
    private $lexedTags;

    /**
     * @var string The template source.
     */
    private $sourceTemplate;


    /**
     * Base constructor.
     *
     * @param array  $lexedTags
     * @param string $sourceTemplate
     */
    public function __construct($lexedTags, $sourceTemplate)
    {
        $this->lexedTags = $lexedTags;
        $this->sourceTemplate = $sourceTemplate;
    }

    /**
     * Get a list of lexed tags.
     *
     * @return array
     */
    public function getLexedTags()
    {
        return $this->lexedTags;
    }

    /**
     * Select a particular tag from the list of parsed tags.
     *
     * @param  string $tag        Tag name.
     * @param array   $attributes Optional list of attributes that the tag needs to match.
     *
     * @return array
     */
    public function select($tag, $attributes = [])
    {
        $result = [];

        foreach ($this->lexedTags as $t) {
            if ($t['name'] == $tag) {
                if (count($attributes) > 0) {
                    foreach ($attributes as $k => $v) {
                        if (isset($t['attributes'][$k]) && $t['attributes'][$k] == $v) {
                            $result[] = $t;
                        }
                    }
                } else {
                    $result[] = $t;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the source template.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->sourceTemplate;
    }
}
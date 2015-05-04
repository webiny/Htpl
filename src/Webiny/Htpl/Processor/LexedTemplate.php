<?php

namespace Webiny\Htpl\Processor;

class LexedTemplate
{
    private $lexedTags;
    private $sourceTemplate;

    public function __construct($lexedTags, $sourceTemplate)
    {
        $this->lexedTags = $lexedTags;
        $this->sourceTemplate = $sourceTemplate;
    }

    public function replace(array $query, $replacement)
    {

    }

    public function getLexedTags()
    {
        return $this->lexedTags;
    }

    public function getTemplate()
    {
        return $this->sourceTemplate;
    }

    public function selectTags($tag)
    {
        $result = [];
        foreach ($this->lexedTags as $t) {
            if ($t['name'] == $tag) {
                $result[] = $t;
            }
        }

        return $result;
    }
}
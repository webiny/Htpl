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

    public function getLexedTags()
    {
        return $this->lexedTags;
    }

    public function select($tag, $attributes = [])
    {
        $result = [];

        foreach ($this->lexedTags as $t) {
            if ($t['name'] == $tag) {
                if (count($attributes) > 0) {
                    foreach ($attributes as $k => $v) {
                        if (!isset($t['attributes'][$k]) || $t['attributes'][$k] != $v) {
                            continue;
                        }
                    }
                }

                $result[] = $t;
            }
        }

        return $result;
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
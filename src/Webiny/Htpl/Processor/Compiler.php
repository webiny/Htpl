<?php

namespace Webiny\Htpl\Processor;

use Webiny\Htpl\HtplException;

class Compiler
{
    private $_template;

    function _construct($template)
    {
        // before we can do the template compile, we need to solve the template inheritance
        $layoutTree = new LayoutTree($template);
        $this->_template = $layoutTree->getLayout();
        $this->compile();

        // not format the html
        $formatter = new OutputFormatter();
        $template = $formatter->clean_html_code($this->_template);
        die($template);
    }

    function compile()
    {
        // get a list of possible functions (tags) that we support
        $functions = $this->_getFunctions();

        foreach ($functions as $tag => $callback) {
            $matches = Selector::select($this->_template, '//' . $tag);
            if (count($matches) > 0) {
                foreach ($matches as $m) {
                    $content = $m['content'];
                    $attributes = isset($m['attributes']) ? $m['attributes'] : [];
                    // extract the opening and closing tag
                    $outerContent = str_replace($m['content'], '', $m['outerHtml']);
                    $closingTag = '</' . $tag . '>';
                    $openingTag = str_replace($closingTag, '', $outerContent);

                    // process the function callback
                    try {
                        $result = $callback::parseTag($content, $attributes);
                    } catch (HtplException $e) {
                        throw new HtplException('Htpl in unable to parse your template near: ' . $openingTag . "\n\n " . $e->getMessage(
                            )
                        );
                    }

                    if (!$result) {
                        continue;
                    }

                    // do the replacement
                    if (isset($result['content'])) {
                        $replacement = $result['openingTag'] . $result['content'] . $result['closingTag'];
                        $this->_template = str_replace($m['outerHtml'], $replacement, $this->_template);
                    } else {
                        $this->_template = str_replace($openingTag, $result['openingTag'], $this->_template);
                        if (isset($result['closingTag'])) {
                            $this->_template = str_replace($closingTag, $result['closingTag'], $this->_template);
                        }
                    }
                }
            }
        }
    }

    private function _parseAttributes($str)
    {
        preg_match_all('|(.*?)\="(.*?)"|', $str, $matches);
        $attributes = [];
        if (count($matches[0]) > 0) {
            $mIndex = 0;
            foreach ($matches[1] as $m) {
                $attributes[trim($m)] = trim($matches[2][$mIndex]);
                $mIndex++;
            }
        }

        return $attributes;
    }

    private function _getFunctions()
    {
        return [
            'w-minify' => 'Webiny\Htpl\Functions\WMinify',
            'w-img'    => 'Webiny\Htpl\Functions\WImage',
            'a'        => 'Webiny\Htpl\Functions\WAnchor',
            'w-if'     => 'Webiny\Htpl\Functions\WIf',
            'w-list'   => 'Webiny\Htpl\Functions\WList',
            'w-var'    => 'Webiny\Htpl\Functions\WVar'
        ];
    }
}
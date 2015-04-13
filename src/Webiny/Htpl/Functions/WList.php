<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Processor\OutputWrapper;
use Webiny\Htpl\Processor\Selector;

class WList implements FunctionInterface
{

    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public function getTag()
    {
        return 'w-list';
    }

    /**
     * This is a callback method when we match the tag that the function is registered for.
     * The method will receive a list of attributes that the tag has associated.
     * The method should return a string that should replace the matching tag.
     * If the method returns false, no replacement will occur.
     *
     * @param string     $content
     * @param array|null $attributes
     * @param Htpl       $htpl
     *
     * @throws HtplException
     * @return string|bool
     */
    public function parseTag($content, $attributes, Htpl $htpl)
    {
        // items attributes
        if (!isset($attributes['items']) || empty($attributes['items'])) {
            throw new HtplException($this->getTag() . ' function requires `items` attribute to be defined.');
        }

        // item attribute
        if (!isset($attributes['var']) || empty($attributes['var'])) {
            throw new HtplException($this->getTag() . ' function requires `var` attribute to be defined.');
        }

        $currentContext = isset($attributes['context']) ? $attributes['context'] : null;
        if (is_null($currentContext)) {
            $items = OutputWrapper::getVar($attributes['items']);
        } else {
            $items = OutputWrapper::getVar($attributes['items'], false); //stao
        }

        // key attribute
        $var = '$' . $attributes['var'];
        $key = null;
        if (isset($attributes['key']) && !empty($attributes['key'])) {
            $key = '$' . $attributes['key'];
            $func = 'foreach (' . $items . ' as ' . $key . ' => ' . $var . '){ ';
        } else {
            $func = 'foreach (' . $items . ' as ' . $var . '){ ';
        }

        // set the context
        $content = $this->updateContext($content, $var, $key);

        return [
            'openingTag' => OutputWrapper::outputFunction($func),
            'content'    => $content,
            'closingTag' => OutputWrapper::outputFunction('}')
        ];
    }

    private function updateContext($content, $newContext, $newKeyContext = null)
    {
        $content = html_entity_decode($content);

        #preg_match_all('/\$this->getVar\(\$this->vars\[\'([\w]+)\'\]([\S\s]+)\)/', $content, $matches);
        preg_match_all('/\$this->getVar\(\$this->vars\[\'([\w]+)\'\]/', $content, $matches);
        if (count($matches[1]) < 1) {
            return $content;
        }

        // update context on variables
        $newContext = str_replace('$', '', $newContext);
        $newKeyContext = str_replace('$', '', $newKeyContext);
        foreach ($matches[1] as $m) {
            if ($m == $newContext) {
                $content = str_replace('$this->getVar($this->vars[\'' . $m . '\']', '$this->getVar($' . $newContext,
                    $content);
            } elseif (!is_null($newKeyContext) && $m == $newKeyContext) {
                $content = str_replace('$this->getVar($this->vars[\'' . $m . '\']', '$this->getVar($' . $newKeyContext,
                    $content);
            }
        }

        // update context on nested loops
        $lists = Selector::select($content, '//' . $this->getTag());
        if (count($lists) > 0) {
            foreach ($lists as $l) {
                if (strpos($l['attributes']['items'], $newContext) === 0) {
                    // append the context attribute
                    $itemsAttr = 'items="' . $l['attributes']['items'] . '"';
                    $contextAttr = ' context="' . $newContext . '"';
                    $content = str_replace($itemsAttr, $itemsAttr . $contextAttr, $content);
                }
            }
        }

        return $content;
    }
}
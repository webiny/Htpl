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
            $items = OutputWrapper::getVar($attributes['items'], $currentContext, true);
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

        $newContext = str_replace('$', '', $newContext);
        $contexts = [$newContext];
        if (!empty($newKeyContext)) {
            $contexts[] = str_replace('$', '', $newKeyContext);
        }
        foreach ($contexts as $context) {
            // update the context when accessing object properties
            preg_match_all('/\$this->getVar\((\'|")' . $context . '\.([\w]+)(\1), (\$this->vars)\)/', $content,
                $matches);

            if (count($matches[0]) > 0) {
                foreach ($matches[0] as $mk => $mv) {
                    $content = str_replace($mv, OutputWrapper::getVar($matches[2][$mk], '$' . $context), $content);
                }
            }

            // update context on direct property access
            preg_match_all('/\$this->getVar\((\'|")' . $context . '(\1), (\$this->vars)\)/', $content, $matches);
            if (count($matches[0]) > 0) {
                foreach ($matches[0] as $mk => $mv) {
                    $content = str_replace($mv, '$' . $context, $content);
                }
            }
        }

        // update context on nested loops
        $lists = Selector::select($content, '//' . $this->getTag());
        if (count($lists) > 0) {
            foreach ($lists as $l) {
                if (strpos($l['attributes']['items'], $newContext) === 0) {
                    // append the context attribute
                    $itemsAttr = 'items="' . $l['attributes']['items'] . '"';
                    $contextAttr = ' context="$' . $newContext . '"';
                    $content = str_replace($itemsAttr, $itemsAttr . $contextAttr, $content);
                }
            }
        }

        return $content;
    }
}
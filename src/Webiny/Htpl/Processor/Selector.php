<?php

namespace Webiny\Htpl\Processor;

class Selector
{

    public static function select($source, $query)
    {
        // disable libxml errors because it doesn't support html5 tags
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = true;
        $doc->formatOutput = true;
        $doc->substituteEntities = false;
        $doc->loadHtml($source);
        libxml_clear_errors();

        $xpath = new \DOMXpath($doc);
        $xResult = $xpath->query($query);

        $result = [];
        foreach ($xResult as $r) {
            $entry = [];
            // extract blocks
            $entry['tag'] = $r->tagName;
            $innerHtml = '';
            $children = $r->childNodes;
            foreach ($children as $child) {
                $innerHtml .= urldecode($child->ownerDocument->saveHtml($child));
            }
            $entry['content'] = $innerHtml;
            $xAttributes = $r->attributes;
            foreach ($xAttributes as $a) {
                $entry['attributes'][$a->name] = $a->value;
            }
            $entry['outerHtml'] = urldecode($r->ownerDocument->saveHtml($r));
            $result[] = $entry;
        }

        return $result;
    }

    public static function replace($source, $query, $replacement)
    {
        $source = Selector::prepare($source);

        $results = self::select($source, $query);
        if (count($results) < 1) {
            return $source;
        }

        foreach ($results as $r) {
            $source = str_replace($r['outerHtml'], $replacement, $source);
        }

        return $source;
    }

    public static function prepare($tpl)
    {
        libxml_use_internal_errors(true);

        $tplDoc = new \DOMDocument();
        $tplDoc->preserveWhiteSpace = true;
        $tplDoc->formatOutput = true;
        $tplDoc->substituteEntities = true;

        // filter out things that can break loadHtml
        $tpl = str_replace(['<head>', '</head>'], ['head-start', 'head-end'], $tpl);
        $tpl = str_replace(['<body>', '</body>'], ['body-start', 'body-end'], $tpl);
        $tpl = str_replace(['<html>', '</html>'], ['html-start', 'html-end'], $tpl);
        $tpl = str_replace('+', '_htpl-plus-sign_', $tpl);

        $tplDoc->loadHtml('<w-fragment>' . $tpl . '</w-fragment>');

        // extract the content from the fragment
        $xpath = new \DOMXpath($tplDoc);
        $xResult = $xpath->query('//w-fragment');
        foreach ($xResult as $r) {
            $children = $r->childNodes;
            $tpl = '';
            foreach ($children as $child) {
                $tpl .= urldecode($child->ownerDocument->saveHtml($child));
            }
        }

        // replace back the components
        $tpl = str_replace(['head-start', 'head-end'], ['<head>', '</head>'], $tpl);
        $tpl = str_replace(['body-start', 'body-end'], ['<body>', '</body>'], $tpl);
        $tpl = str_replace(['html-start', 'html-end'], ['<html>', '</html>'], $tpl);
        $tpl = str_replace('_htpl-plus-sign_', '+', $tpl);

        return $tpl;
    }

    public static function outputCleanup($tpl)
    {
        $tpl = html_entity_decode($tpl);

        // some tags that we need to correct
        $tags = [
            '></link>' => '/>'
        ];
        $tpl = str_replace(array_keys($tags), array_values($tags), $tpl);

        // check if we the starting html tag
        if (stripos($tpl, '<html') !== true) {
            $tpl = '<html>' . "\n" . $tpl;
        }

        // check if we have the html definition
        if (stripos($tpl, '<!doctype') !== true) {
            $tpl = '<!doctype html>' . "\n" . $tpl;
        }

        return $tpl;
    }
}
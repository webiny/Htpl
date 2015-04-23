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
        preg_match('/\<!doctype([\S\s]+?)\>/i', $tpl, $docTypeMatch);
        if (count($docTypeMatch) > 0) {
            $tpl = str_replace($docTypeMatch[0], 'htpl-doctype-start', $tpl);
        }

        preg_match('/\<html([\S\s]+)?\>/i', $tpl, $htmlMatch);
        if (count($htmlMatch) > 0) {
            $tpl = str_replace($htmlMatch[0], 'htpl-html-start', $tpl);
        }

        $tpl = str_replace(['<head>', '</head>'], ['htpl-head-start', 'htpl-head-end'], $tpl);
        $tpl = str_replace(['<body>', '</body>'], ['htpl-body-start', 'htpl-body-end'], $tpl);
        $tpl = str_replace('</html>', 'htpl-html-end', $tpl);
        // since we are doing urldecode, the plus sign needs to be filtered out before the decode
        $tpl = str_replace('+', 'htpl-plus-sign', $tpl);

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
        if (count($docTypeMatch) > 0) {
            $tpl = str_replace('htpl-doctype-start', $docTypeMatch[0], $tpl);
        }
        if (count($htmlMatch) > 0) {
            $tpl = str_replace('htpl-html-start', $htmlMatch[0], $tpl);
        }
        $tpl = str_replace(['htpl-head-start', 'htpl-head-end'], ['<head>', '</head>'], $tpl);
        $tpl = str_replace(['htpl-body-start', 'htpl-body-end'], ['<body>', '</body>'], $tpl);
        $tpl = str_replace('htpl-html-end', '</html>', $tpl);
        $tpl = str_replace('htpl-plus-sign', '+', $tpl);

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

        return $tpl;
    }
}
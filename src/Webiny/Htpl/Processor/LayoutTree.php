<?php

namespace Webiny\Htpl\Processor;

class LayoutTree
{
    private $_layout;

    function __construct($template)
    {
        $this->_layout = self::processLayouts($template);
    }

    function getLayout()
    {
        return $this->_layout;
    }

    static function processLayouts($template)
    {
        $source = TemplateLoader::getSource($template);
        $layouts = Selector::select($source, '//w-layout');
        foreach ($layouts as $l) {
            $parentSource = TemplateLoader::getSource($l['attributes']['template']);
            $layoutSource = self::joinLayouts($l['content'], $parentSource, 0);
            $source = Selector::replace($source, "//w-layout[@template='" . $l['attributes']['template'] . "']", $layoutSource);
        }

        // cleanup the remaining blocks
        $blocks = Selector::select($source, '//w-block');
        foreach($blocks as $b){
            $source = Selector::replace($source, "//w-block[@name='" . $b['attributes']['name'] . "']", $b['content']);
        }

        return $source;
    }

    static function joinLayouts($childSource, $parentSource, $i)
    {
        // take the blocks from child
        $childBlocks = Selector::select($childSource, '//w-block');

        // replace the matching blocks
        foreach ($childBlocks as $cb) {
            $parentSource = Selector::replace($parentSource, "//w-block[@name='" . $cb['attributes']['name'] . "']", $cb['content']);
        }

        // get the parent layout and repeat the process
        $source = $parentSource;
        $layouts = Selector::select($source, '//w-layout');
        if(count($layouts)>0){
            foreach ($layouts as $l) {
                $parentSource = TemplateLoader::getSource($l['attributes']['template']);
                $layoutSource = self::joinLayouts($l['content'], $parentSource, 1);
                $source = Selector::replace($source, "//w-layout[@template='" . $l['attributes']['template'] . "']", $layoutSource);
            }
        }

        return $source;
    }
}
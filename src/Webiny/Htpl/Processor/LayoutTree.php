<?php

namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Loaders\LoaderInterface;

class LayoutTree
{
    public static function getLayout(LoaderInterface $loader, $templateName)
    {
        $layoutTree = new self;
        return $layoutTree->processLayouts($loader, $templateName);
    }

    private function processLayouts(LoaderInterface $loader, $templateName)
    {
        // prepare the main template
        $source = $loader->getSource($templateName);
        $source = Selector::prepare($source);
        $layouts = Selector::select($source, '//w-layout');
        foreach ($layouts as $l) {
            // get and prepare the parent
            $parentSource = $loader->getSource($l['attributes']['template']);
            $parentSource = Selector::prepare($parentSource);

            $layoutSource = $this->joinLayouts($loader, $l['content'], $parentSource, 0);
            $source = Selector::replace($source, "//w-layout[@template='" . $l['attributes']['template'] . "']",
                $layoutSource . "\n");
        }

        // cleanup the remaining blocks
        $blocks = Selector::select($source, '//w-block');
        foreach ($blocks as $b) {
            $source = Selector::replace($source, "//w-block[@name='" . $b['attributes']['name'] . "']",
                $b['content'] . "\n");
        }

        return $source;
    }

    private function joinLayouts(LoaderInterface $loader, $childSource, $parentSource, $i)
    {
        // take the blocks from child
        $childBlocks = Selector::select($childSource, '//w-block');

        // replace the matching blocks
        foreach ($childBlocks as $cb) {
            $parentSource = Selector::replace($parentSource, "//w-block[@name='" . $cb['attributes']['name'] . "']",
                $cb['content'] . "\n");
        }

        // get the parent layout and repeat the process
        $source = $parentSource;
        $layouts = Selector::select($source, '//w-layout');
        if (count($layouts) > 0) {
            foreach ($layouts as $l) {
                $parentSource = $loader->getSource($l['attributes']['template']);
                $parentSource = Selector::prepare($parentSource);

                $layoutSource = $this->joinLayouts($loader, $l['content'], $parentSource, 1);
                $source = Selector::replace($source, "//w-layout[@template='" . $l['attributes']['template'] . "']",
                    $layoutSource . "\n");
            }
        }

        return $source;
    }
}
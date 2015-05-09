<?php

namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Loaders\LoaderInterface;
use Webiny\Htpl\Processor\Lexers\TagLexer;

class LayoutTree
{
    public static function getLayout(LoaderInterface $loader, $templateName)
    {
        $layoutTree = new self;
        return $layoutTree->processLayouts($loader, $templateName);
    }

    private function processLayouts(LoaderInterface $loader, $templateName)
    {
        $source = $loader->getSource($templateName);
        $layouts = TagLexer::parse($source)->select('w-layout');

        foreach ($layouts as $l) {
            // get and prepare the parent
            $parentSource = $loader->getSource($l['attributes']['template']);

            $layoutSource = $this->joinLayouts($loader, $l['content'], $parentSource, 0);
            $source = str_replace($l['outerHtml'], $layoutSource, $source);
        }

        return $source;
    }

    private function joinLayouts(LoaderInterface $loader, $childSource, $parentSource)
    {
        // take the blocks from child
        $childBlocks = TagLexer::parse($childSource)->select('w-block');

        $parentSourceLexed = TagLexer::parse($parentSource);

        // replace the matching blocks
        foreach ($childBlocks as $cb) {
            $parentBlock = $parentSourceLexed->select('w-block', ['name' => $cb['attributes']['name']])[0]['outerHtml'];
            $parentSource = str_replace($parentBlock, $cb['content'], $parentSource);
        }

        // get the parent layout and repeat the process
        $source = $parentSource;
        $layouts = TagLexer::parse($source)->select('w-layout');
        foreach ($layouts as $l) {
            $parentSource = $loader->getSource($l['attributes']['template']);

            $layoutSource = $this->joinLayouts($loader, $l['content'], $parentSource, 1);
            $source = str_replace($l['outerHtml'], $layoutSource, $source);
        }

        return $source;
    }
}
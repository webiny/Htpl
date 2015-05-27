<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Processor;

use Webiny\Htpl\HtplException;
use Webiny\Htpl\TemplateProviders\TemplateProviderInterface;
use Webiny\Htpl\Processor\Lexers\TagLexer;

/**
 * LayoutTree handles the layout inheritance between multiple templates.
 * The class compiles the layout by flattening the defined layout and block scopes.
 *
 * @package Webiny\Htpl\Processor
 */
class LayoutTree
{
    /**
     * Processes and returns the flattened template.
     *
     * @param TemplateProviderInterface $loader
     * @param string          $templateName
     *
     * @return mixed|string
     */
    public static function getLayout(TemplateProviderInterface $loader, $templateName)
    {
        $layoutTree = new self;
        return $layoutTree->processLayouts($loader, $templateName);
    }

    /**
     * Reads and flattens the initial template.
     *
     * @param TemplateProviderInterface $loader
     * @param string          $templateName
     *
     * @return string
     * @throws \Webiny\Htpl\HtplException
     */
    private function processLayouts(TemplateProviderInterface $loader, $templateName)
    {
        $source = $loader->getSource($templateName);
        $layouts = TagLexer::parse($source)->select('w-layout');

        foreach ($layouts as $l) {
            // get and prepare the parent
            if (!isset($l['attributes']['template'])) {
                throw new HtplException('A "w-layout" tag is missing the "template" attribute.');
            }
            $parentSource = $loader->getSource($l['attributes']['template']);

            $layoutSource = $this->joinLayouts($loader, $l['content'], $parentSource, 0);
            //$source = str_replace($l['outerHtml'], $layoutSource, $source);
            $source = preg_replace('/(\s+|)' . preg_quote($l['outerHtml'], '/') . '(\s+|)/', $layoutSource, $source);
        }

        // cleanup remaining (empty) blocks
        $blocks = TagLexer::parse($source)->select('w-block');
        foreach ($blocks as $b) {
            $source = preg_replace('/(\s+|)' . preg_quote($b['outerHtml'], '/') . '(\s+|)/', '', $source);
        }

        return $source;
    }

    /**
     * Recursive method that walks the template layout hierarchy tree and flattens all the templates, until there
     * are no more depths to go.
     *
     * @param TemplateProviderInterface $loader
     * @param string          $childSource
     * @param string          $parentSource
     *
     * @return string
     * @throws \Webiny\Htpl\HtplException
     */
    private function joinLayouts(TemplateProviderInterface $loader, $childSource, $parentSource)
    {
        // take the blocks from child
        $childBlocks = TagLexer::parse($childSource)->select('w-block');

        $parentSourceLexed = TagLexer::parse($parentSource);

        // replace the matching blocks
        foreach ($childBlocks as $cb) {
            $parentBlock = $parentSourceLexed->select('w-block', ['name' => $cb['attributes']['name']])[0]['outerHtml'];
            //$parentSource = str_replace($parentBlock, $cb['content'], $parentSource);
            $parentSource = preg_replace('/(\s+|)' . preg_quote($parentBlock, '/') . '(\s+|)/', $cb['content'], $parentSource);
        }

        // get the parent layout and repeat the process
        $source = $parentSource;
        $layouts = TagLexer::parse($source)->select('w-layout');
        foreach ($layouts as $l) {
            if (!isset($l['attributes']['template'])) {
                throw new HtplException('A "w-layout" tag is missing the "template" attribute.');
            }
            $parentSource = $loader->getSource($l['attributes']['template']);

            $layoutSource = $this->joinLayouts($loader, $l['content'], $parentSource, 1);
            //$source = str_replace($l['outerHtml'], $layoutSource, $source);
            $source = preg_replace('/(\s+|)' . preg_quote($l['outerHtml'], '/') . '(\s+|)/', $layoutSource, $source);
        }

        return $source;
    }
}
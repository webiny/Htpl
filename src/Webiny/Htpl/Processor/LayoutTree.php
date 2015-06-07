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
    private $includedFiles = [];

    /**
     * Processes and returns the flattened template.
     *
     * @param TemplateProviderInterface $provider
     * @param string                    $templateName
     *
     * @return Layout
     */
    public static function getLayout(TemplateProviderInterface $provider, $templateName)
    {
        $layoutTree = new self;
        return $layoutTree->processLayouts($provider, $templateName);
    }

    /**
     * Reads and flattens the initial template.
     *
     * @param TemplateProviderInterface $provider
     * @param string                    $templateName
     *
     * @return Layout
     * @throws \Webiny\Htpl\HtplException
     */
    private function processLayouts(TemplateProviderInterface $provider, $templateName)
    {
        $source = $provider->getSource($templateName);
        $this->includedFiles[$templateName] = $provider->createdOn($templateName);
        $layouts = TagLexer::parse($source)->select('w-layout');

        foreach ($layouts as $l) {
            // get and prepare the parent
            if (!isset($l['attributes']['template'])) {
                throw new HtplException('A "w-layout" tag is missing the "template" attribute.');
            }
            $parentSource = $provider->getSource($l['attributes']['template']);
            $this->includedFiles[$l['attributes']['template']] = $provider->createdOn($l['attributes']['template']);

            $layoutSource = $this->joinLayouts($provider, $l['content'], $parentSource, 0);
            $source = preg_replace('/(\s+|)' . preg_quote($l['outerHtml'], '/') . '(\s+|)/', $layoutSource, $source);
        }

        // build the includes
        $layout = $this->handleIncludes($provider, $source);
        $source = $layout->getSource();

        // cleanup remaining (empty) blocks
        $blocks = TagLexer::parse($source)->select('w-block');

        foreach ($blocks as $b) {
            $source = preg_replace('/(\s+|)' . preg_quote($b['outerHtml'], '/') . '(\s+|)/', '', $source);
        }
        $layout->setSource($source);


        return $layout;
    }

    /**
     * Recursive method that walks the template layout hierarchy tree and flattens all the templates, until there
     * are no more depths to go.
     *
     * @param TemplateProviderInterface $provider
     * @param string                    $childSource
     * @param string                    $parentSource
     *
     * @return string
     * @throws \Webiny\Htpl\HtplException
     */
    private function joinLayouts(TemplateProviderInterface $provider, $childSource, $parentSource)
    {
        // take the blocks from child
        $childBlocks = TagLexer::parse($childSource)->select('w-block');

        $parentSourceLexed = TagLexer::parse($parentSource);

        // replace the matching blocks
        foreach ($childBlocks as $cb) {
            $parentBlock = $parentSourceLexed->select('w-block', ['name' => $cb['attributes']['name']])[0]['outerHtml'];
            $parentSource = preg_replace('/(\s+|)' . preg_quote($parentBlock, '/') . '(\s+|)/', $cb['content'],
                $parentSource);
        }

        // get the parent layout and repeat the process
        $source = $parentSource;
        $layouts = TagLexer::parse($source)->select('w-layout');
        foreach ($layouts as $l) {
            if (!isset($l['attributes']['template'])) {
                throw new HtplException('A "w-layout" tag is missing the "template" attribute.');
            }
            $parentSource = $provider->getSource($l['attributes']['template']);
            $this->includedFiles[$l['attributes']['template']] = $provider->createdOn($l['attributes']['template']);

            $layoutSource = $this->joinLayouts($provider, $l['content'], $parentSource, 1);
            $source = preg_replace('/(\s+|)' . preg_quote($l['outerHtml'], '/') . '(\s+|)/', $layoutSource, $source);
        }

        return $source;
    }

    /**
     * Recursively handles the included templates (just the non dynamic ones).
     *
     * @param TemplateProviderInterface $provider
     * @param                           $source
     *
     * @return Layout
     * @throws HtplException
     */
    private function handleIncludes(TemplateProviderInterface $provider, $source)
    {
        $includes = TagLexer::parse($source)->select('w-include');

        $parsedTemplates = 0;
        foreach ($includes as $i) {
            if (substr($i['attributes']['file'], -5) == '.htpl') {
                // join the includes with the main template
                $includedTemplate = $provider->getSource($i['attributes']['file']);
                $source = preg_replace('/(\s+|)' . preg_quote($i['outerHtml'], '/') . '(\s+|)/', $includedTemplate,
                    $source);
                // add the template to include list
                $this->includedFiles[$i['attributes']['file']] = $provider->createdOn($i['attributes']['file']);
                $parsedTemplates++;
            }
        }

        if (count($includes) < 1 || $parsedTemplates < 1) {
            return new Layout($source, $this->includedFiles);
        }

        return $this->handleIncludes($provider, $source);
    }
}
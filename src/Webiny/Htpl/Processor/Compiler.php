<?php

namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;

class Compiler
{
    private $_template;

    /**
     * @var Htpl
     */
    private $htpl;

    public function __construct(Htpl $htpl)
    {
        $this->htpl = $htpl;
    }

    public function getCompiledTemplate($templateName)
    {
        //@TODO: test cache HIT case

        // first, let's try to get it from cache
        $cachedTemplate = $this->getFromCache($templateName);
        if ($cachedTemplate) {
            return $cachedTemplate;
        }

        // do the compile
        $template = $this->compileTemplate($templateName);

        // cache the result
        $compiledTemplatePath = $this->htpl->getWriter()->write(md5($templateName).'.php', $template);

        // create Template instance
        $template = new Template($this->htpl, $compiledTemplatePath);

        return $template;
    }

    private function getFromCache($templateName)
    {
        if ($this->htpl->getForceCompile()) {
            return false;
        }

        // try to get it from cache
        $cachedTemplate = $this->htpl->getWriter()->read($templateName);
        if (!$cachedTemplate) {
            return false;
        }

        // verify if cache is still fresh
        $templateModTime = $this->htpl->getLoader()->getFreshness($templateName);

        // cache creation/mod time
        $cacheModTime = $this->htpl->getWriter()->createdOn($templateName);

        if ($cacheModTime >= $templateModTime) {
            return $cachedTemplate;
        }

        $this->htpl->getWriter()->delete($templateName);

        return false;
    }

    private function compileTemplate($templateName)
    {
        // before we can do the template compile, we need to solve the template inheritance
        $template = LayoutTree::getLayout($this->htpl->getLoader(), $templateName);

        // now parse the variables
        $template = VarParser::parseTemplate($template, $this->htpl);

        // get a list of possible functions (tags) that we support
        $functions = $this->htpl->getFunctions();

        // parse functions
        foreach ($functions as $tag => $callback) {
            $matches = Selector::select($template, '//' . $tag);
            if (count($matches) > 0) {
                foreach ($matches as $m) {

                    // do a fresh match, since some of the tags (eg. w-list) can modify the attributes
                    $currentMatch = Selector::select($template, '(//' . $tag . ')[1]')[0];

                    $content = $currentMatch['content'];
                    $attributes = isset($currentMatch['attributes']) ? $currentMatch['attributes'] : [];

                    // extract the opening and closing tag
                    $outerContent = str_replace($currentMatch['content'], '', $currentMatch['outerHtml']);
                    $closingTag = '</' . $tag . '>';
                    $openingTag = str_replace($closingTag, '', $outerContent);

                    // process the function callback
                    try {
                        $instance = new $callback;
                        $result = $instance->parseTag($content, $attributes, $this->htpl);
                    } catch (HtplException $e) {
                        throw new HtplException('Htpl in unable to parse your template near: ' . $openingTag . "\n\n " . $e->getMessage());
                    }

                    if (!$result) {
                        continue;
                    }

                    // do the replacement
                    if (isset($result['content'])) {
                        $replacement = $result['openingTag'] . $result['content'] . $result['closingTag'];
                        // we replace with offset 1 cause, we always do the replacement on the current template instance
                        $template = Selector::replace($template, '(//' . $tag . ')[1]', $replacement);
                    } else {
                        $template = str_replace($openingTag, $result['openingTag'], $template);
                        if (isset($result['closingTag'])) {
                            $template = str_replace($closingTag, $result['closingTag'], $template);
                        }
                    }
                }
            }
        }

        // tidy the output
        return Selector::outputCleanup($template);
    }
}
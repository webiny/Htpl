<?php

namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;

class Compiler
{
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
        // first, let's try to get it from cache
        $template = $this->getFromCache($templateName);
        if (!$template) {
            // do the compile
            $template = $this->compileTemplate($templateName);

            // cache the result
            $this->htpl->getWriter()->write($templateName, $template);
        }

        // create Template instance
        $template = new Template($this->htpl, $template);

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
        $templateModTime = $this->htpl->getLoader()->createdOn($templateName);

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

        // validate that the raw template doesn't contain any PHP code
        if (strpos($template, '<?') !== false) {
            throw new HtplException(sprintf('Template "%s" contains PHP tags which are not allowed.', $templateName));
        }

        // now parse the variables
        $template = VarLexer::parse($template, $this->htpl);

        // get a list of possible functions (tags) that we support
        $functions = $this->htpl->getFunctions();
        $lexedTemplate = TagLexer::parse($template);
        foreach ($functions as $tag => $callback) {

            $tags = $lexedTemplate->selectTags($tag);
            foreach ($tags as $t) {
                $lexedTemplate = TagLexer::parse($template);
                $currentMatch = $lexedTemplate->selectTags($tag)[0];
                $content = $currentMatch['content'];
                $attributes = isset($currentMatch['attributes']) ? $currentMatch['attributes'] : [];

                try {
                    // extract the opening and closing tag
                    $outerContent = str_replace($currentMatch['content'], '', $currentMatch['outerHtml']);
                    $closingTag = '</' . $tag . '>';
                    $openingTag = str_replace($closingTag, '', $outerContent);


                    $instance = new $callback;
                    $result = $instance->parseTag($content, $attributes, $this->htpl);

                    if (!$result) {
                        continue;
                    }

                    // check if we have context defined
                    $contextStart = '';
                    $contextEnd = '';
                    if (isset($result['contexts'])) {
                        foreach($result['contexts'] as $c){
                            $contextStart.= '<!-- htpl-context-start:'.$c.' -->'."\n";
                            $contextEnd.= '<!-- htpl-context-end:'.$c.' -->'."\n";
                        }
                    }

                    // do the replacement
                    if (isset($result['content'])) {
                        $replacement = $contextStart.$result['openingTag'] . $result['content'] . $result['closingTag'].$contextEnd;

                        // we replace with offset 1 cause, we always do the replacement on the current template instance
                        $template = str_replace($currentMatch['outerHtml'], $replacement, $template);
                    } else {
                        $template = str_replace($openingTag, $result['openingTag'], $template);
                        if (isset($result['closingTag'])) {
                            $template = str_replace($closingTag, $result['closingTag'], $template);
                        }
                    }
                } catch (HtplException $e) {
                    throw new HtplException('Htpl in unable to parse your template near: ' . $openingTag . "\n\n " . $e->getMessage());
                }
            }
        }

        $template = $this->adjustContexts($template);

        //@todo parse the contexts -> done
        //@todo prepisi layout tree na novi lexer
        //@todo rename current lexer to VarLexer, so we can distiguish between the two lexers -> done

        /*
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
        */

        // tidy the output
        return Selector::outputCleanup($template);
    }

    private function adjustContexts($template)
    {
        $pattern = '/\<\!\-\- htpl\-context\-start\:([\W\w\s\.]+?)-->/';
        preg_match_all($pattern, $template, $matches);
        if (count($matches[0]) > 0) {
            $contexts = $matches[1];

            foreach ($contexts as $c) {

                // get the context borders
                $pattern = '/\<\!\-\- htpl\-context\-start\:'.$c.'-->([\S\s]+?)\<\!\-\- htpl\-context\-end\:'.$c.'-->/';
                preg_match($pattern, $template, $matches);

                if(count($matches)>0){
                    $contextTpl = $matches[1];
                    // match a get var function and adjust the context
                    // $this->getVar('postId', $this->vars)
                    preg_match_all('/\$this->getVar\(\'' . trim($c) . '(\'|\.[\s\S]+?)\, \$this->vars\)/', $contextTpl, $varMatches);

                    if (count($varMatches[0]) > 0) {
                        foreach ($varMatches[0] as $offset => $m) {
                            if($varMatches[1][$offset]=="'"){
                                // if the context var is accessed directly, without the inner context (the dot)
                                $newContext = '$'.trim($c);
                            }else{
                                // adjust the context
                                $newContext = str_replace('$this->vars', '$' . trim($c), $m);
                                $newContext = str_replace("'".trim($c).'.', "'", $newContext);
                            }

                            $contextTpl = str_replace($m, $newContext, $contextTpl);
                        }

                        $template = str_replace($matches[0], $contextTpl, $template);
                    }
                }
            }
        }

        return $template;
    }
}
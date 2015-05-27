<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Processor\Lexers\TagLexer;
use Webiny\Htpl\Processor\Lexers\VarLexer;

/**
 * Compiler is the main class that does the template compiling, writes the cache and returns the compiled template.
 *
 * @package Webiny\Htpl\Processor
 */
class Compiler
{
    /**
     * @var Htpl
     */
    private $htpl;

    /**
     * Base constructor.
     *
     * @param Htpl $htpl Htpl instance.
     */
    public function __construct(Htpl $htpl)
    {
        $this->htpl = $htpl;
    }

    /**
     * Compiles and returns the compiled template for the given template name.
     *
     * @param string $templateName Template that should be compiled.
     *
     * @return Template
     * @throws HtplException
     */
    public function getCompiledTemplate($templateName)
    {
        // first, let's try to get it from cache
        $template = false;
        if (!$this->htpl->getForceCompile()) {
            $template = $this->getFromCache($templateName);
        }

        if (!$template) {
            // do the compile
            $template = $this->compileTemplate($templateName);

            // cache the result
            if (!$this->htpl->getForceCompile()) {
                $this->htpl->getCache()->write($templateName, $template);
            }
        }

        // create Template instance
        $template = new Template($this->htpl, $template);

        return $template;
    }

    /**
     * Try to retrieve the compiled template from the cache.
     *
     * @param string $templateName Template that should be retrieved.
     *
     * @return bool|string Compiled template in form of a string, or bool false if the template is not in cache.
     */
    private function getFromCache($templateName)
    {
        // try to get it from cache
        $cachedTemplate = $this->htpl->getCache()->read($templateName);
        if (!$cachedTemplate) {
            return false;
        }

        // verify if cache is still fresh
        $templateModTime = $this->htpl->getTemplateProvider()->createdOn($templateName);

        // cache creation/mod time
        $cacheModTime = $this->htpl->getCache()->createdOn($templateName);

        if ($cacheModTime >= $templateModTime) {
            return $cachedTemplate;
        }

        $this->htpl->getCache()->delete($templateName);

        return false;
    }

    /**
     * Method that does the actual compiling.
     *
     * @param string $templateName Template name that should be compiled.
     *
     * @return string Compiled template in form of a string.
     * @throws HtplException
     */
    private function compileTemplate($templateName)
    {
        // before we can do the template compile, we need to solve the template inheritance
        $template = LayoutTree::getLayout($this->htpl->getTemplateProvider(), $templateName);

        // validate that the raw template doesn't contain any PHP code
        if (strpos($template, '<?') !== false) {
            throw new HtplException(sprintf('Template "%s" contains PHP tags which are not allowed.', $templateName));
        }

        // parse the variables
        $template = VarLexer::parse($template, $this->htpl);

        // get a list of possible functions (tags) that we support
        $functions = $this->htpl->getFunctions();
        $lexedTemplate = TagLexer::parse($template);
        foreach ($functions as $tag => $callback) {

            $tags = $lexedTemplate->select($tag);
            foreach ($tags as $t) {
                $lexedTemplate = TagLexer::parse($template);
                $currentMatch = $lexedTemplate->select($tag);
                if (count($currentMatch) < 1) {
                    continue;
                } else {
                    $currentMatch = $currentMatch[0];
                }
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
                        foreach ($result['contexts'] as $c) {
                            $contextStart .= '<!-- htpl-context-start:' . $c . ' -->' . "\n";
                            $contextEnd = '<!-- htpl-context-end:' . $c . ' -->' . "\n".$contextEnd;
                        }
                    }

                    // do the replacement
                    if (isset($result['content'])) {
                        $replacement = $contextStart . $result['openingTag'] . $result['content'] . $result['closingTag'] . $contextEnd;

                        // we replace with offset 1 cause, we always do the replacement on the current template instance
                        //$template = str_replace($currentMatch['outerHtml'], $replacement, $template);
                        $template = preg_replace('/(\s+|)' . preg_quote($currentMatch['outerHtml'], '/') . '(\s+|)/',
                            $replacement, $template);
                    } else {
                        //$template = str_replace($openingTag, $result['openingTag'], $template);
                        $template = preg_replace('/(\s+|)' . preg_quote($openingTag, '/') . '(\s+|)/',
                            $result['openingTag'], $template);
                        if (isset($result['closingTag'])) {
                            //$template = str_replace($closingTag, $result['closingTag'], $template);
                            $template = preg_replace('/(\s+|)' . preg_quote($closingTag, '/') . '(\s+|)/',
                                $result['closingTag'], $template);
                        }
                    }
                } catch (HtplException $e) {
                    throw new HtplException('Htpl in unable to parse your template near: ' . $openingTag . "\n\n " . $e->getMessage());
                }
            }
        }

        // adjust contexts
        $template = $this->adjustContexts($template);

        return $template;
    }

    /**
     * Certain registered functions can declare context aware variables. In these case we need to somehow make sure that
     * these variables are available only within that context. This method handles that by simply modifying the compiled
     * PHP code so that the lookup for context variables is one only inside the context scope.
     *
     * @param string $template Compiled template in the form of a string.
     *
     * @return string Compiled template in the form of a string.
     */
    private function adjustContexts($template)
    {
        $pattern = '/\<\!\-\- htpl\-context\-start\:([\W\w\s\.]+?)-->/';
        preg_match_all($pattern, $template, $matches);

        if (count($matches[0]) > 0) {
            $contexts = $matches[1];

            foreach ($contexts as $c) {

                // get the context borders
                $pattern = '/\<\!\-\- htpl\-context\-start\:' . $c . '-->([\S\s]+?)\<\!\-\- htpl\-context\-end\:' . $c . '-->/';
                preg_match($pattern, $template, $matches);

                if (count($matches) > 0) {
                    $contextTpl = $matches[1];
                    // match a get var function and adjust the context
                    // $this->getVar('postId', $this->vars)
                    preg_match_all('/\$this->getVar\(\'' . trim($c) . '(\'|\.[\s\S]+?)\, \$this->vars\)/', $contextTpl,
                        $varMatches);

                    if (count($varMatches[0]) > 0) {
                        foreach ($varMatches[0] as $offset => $m) {
                            if ($varMatches[1][$offset] == "'") {
                                // if the context var is accessed directly, without the inner context (the dot)
                                $newContext = '$' . trim($c);
                            } else {
                                // adjust the context
                                $newContext = str_replace('$this->vars', '$' . trim($c), $m);
                                $newContext = str_replace("'" . trim($c) . '.', "'", $newContext);
                            }

                            $contextTpl = str_replace($m, $newContext, $contextTpl);
                        }

                        $template = str_replace($matches[0], $contextTpl, $template);
                    }
                }

                // clean up in case we didn't match any of the upper conditions
                $template = str_replace('<!-- htpl-context-start:' . $c . '-->', '', $template);
                $template = str_replace('<!-- htpl-context-end:' . $c . '-->', '', $template);
            }
        }

        // cleanup remaining
        return $template;
    }
}
<?php

namespace Webiny\Htpl;

use Webiny\Htpl\Functions\FunctionInterface;
use Webiny\Htpl\Loaders\Filesystem;
use Webiny\Htpl\Loaders\LoaderInterface;
use Webiny\Htpl\Modifiers\ModifierPackInterface;
use Webiny\Htpl\Processor\Compiler;
use Webiny\Htpl\Processor\Processor;
use Webiny\Htpl\Processor\Template;
use Webiny\Htpl\Writer\WriterInterface;

class Htpl
{
    /**
     * @var string Name of the current template.
     */
    private $template = '';

    private $options = [
        'forceCompile' => false,
        'lexer'        => [
            'varStartFlag' => '{',
            'varEndFlag'   => '}'
        ],
        'minify'       => []
    ];

    private $assignedVars = [];

    private $internalFunctions = [
        /*'\Webiny\Htpl\Functions\WIf',
        '\Webiny\Htpl\Functions\WElse',
        '\Webiny\Htpl\Functions\WElseIf',
        '\Webiny\Htpl\Functions\WInclude',*/
        '\Webiny\Htpl\Functions\WList',
        '\Webiny\Htpl\Functions\WMinify'
    ];

    private $internalModifiers = [
        '\Webiny\Htpl\Modifiers\CorePack'
    ];

    private $initializedFunctions = [];
    private $initializedModifiers = [];

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var WriterInterface
     */
    private $writer;


    public function __construct(LoaderInterface $loader, WriterInterface $writer)
    {
        // @todo: update the options here

        // initialize functions
        $this->initializeFunctions();

        // initialize modifiers
        $this->initializeModifiers();

        // set loader (default: filesystem loader)
        $this->loader = $loader;

        // set writer
        $this->writer = $writer;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getWriter()
    {
        return $this->writer;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = array_merge($this->options,$options);
    }

    public function setForceCompile($forceCompile)
    {
        $this->options['forceCompile'] = (bool)$forceCompile;
    }

    public function getForceCompile()
    {
        return $this->options['forceCompile'];
    }

    /**
     * Fetch the template from the given location, parse it and return the output.
     *
     * @param string $template   Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     *
     * @return Template
     */
    function fetch($template, $parameters = [])
    {
        $this->template = $template;
        $this->assignArray($parameters);

        // compile the template
        $compiler = new Compiler($this);
        $compiledTemplate = $compiler->getCompiledTemplate($template);

        // return
        return $compiledTemplate;
    }

    /**
     * Fetch the template from the given location, parse it and output the result to the browser.
     *
     * @param string $template   Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     *
     * @return void
     */
    function render($template, $parameters = [])
    {
        echo $this->fetch($template, $parameters)->display();
    }

    /**
     * Assign a variable and its value into the template engine.
     *
     * @param string $var   Variable name.
     * @param mixed  $value Variable value.
     *
     * @return void
     */
    function assign($var, $value)
    {
        $this->assignedVars[$var] = $value;
    }

    function assignArray(array $parameters)
    {
        foreach ($parameters as $k => $v) {
            $this->assign($k, $v);
        }
    }

    function getVars()
    {
        return $this->assignedVars;
    }

    /**
     * @return string Returns the current template name.
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Register a function.
     *
     * @param FunctionInterface $function
     *
     * @return void
     */
    function registerFunction(FunctionInterface $function)
    {
        $this->initializedFunctions[$function->getTag()] = $function;
    }

    function registerModifierPack(ModifierPackInterface $mod)
    {
        $this->initializedModifiers = array_merge($this->initializedModifiers, $mod::getModifiers());
    }

    public function getFunctions()
    {
        return $this->initializedFunctions;
    }

    public function getModifiers()
    {
        return $this->initializedModifiers;
    }

    public function setLexerFlags($startFlag, $endFlag)
    {
        $this->options['lexer']['varStartFlag'] = $startFlag;
        $this->options['lexer']['varEndFlag'] = $endFlag;
    }

    public function getLexerFlags()
    {
        return [
            'varStartFlag' => $this->options['lexer']['varStartFlag'],
            'varEndFlag'   => $this->options['lexer']['varEndFlag']
        ];
    }

    private function initializeFunctions()
    {
        foreach ($this->internalFunctions as $funcClass) {
            $instance = new $funcClass;
            $tag = $instance->getTag();
            if (!isset($this->initializedFunctions[$tag])) {
                $this->initializedFunctions[$tag] = $instance;
            }
        }
    }

    private function initializeModifiers()
    {
        foreach ($this->internalModifiers as $mod) {
            $this->initializedModifiers = array_merge($this->initializedModifiers, $mod::getModifiers());
        }
    }
}
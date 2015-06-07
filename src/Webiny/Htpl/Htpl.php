<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl;

use Webiny\Htpl\Cache\NullCache;
use Webiny\Htpl\Functions\FunctionInterface;
use Webiny\Htpl\Modifiers\ModifierPackInterface;
use Webiny\Htpl\Processor\Compiler;
use Webiny\Htpl\Processor\Template;
use Webiny\Htpl\TemplateProviders\TemplateProviderInterface;
use Webiny\Htpl\Cache\CacheInterface;

/**
 * Htpl template engine.
 * See the readme file for details.
 *
 * @package Webiny\Htpl
 */
class Htpl
{
    /**
     * @var string Name of the current template.
     */
    private $template = '';

    /**
     * @var array Internal options
     */
    private $options = [
        'forceCompile' => false,
        'lexer'        => [
            'varStartFlag' => '{',
            'varEndFlag'   => '}'
        ],
        'minify'       => [],
        'cacheValidationTTL'    => 60 // in seconds, how often should we check when cache was last validated
    ];

    /**
     * @var array List of assigned variables.
     */
    private $assignedVars = [];

    /**
     * @var array List of registered internal functions.
     */
    private $internalFunctions = [
        '\Webiny\Htpl\Functions\WIf',
        '\Webiny\Htpl\Functions\WElse',
        '\Webiny\Htpl\Functions\WElseIf',
        '\Webiny\Htpl\Functions\WInclude',
        '\Webiny\Htpl\Functions\WLoop',
        '\Webiny\Htpl\Functions\WMinify'
    ];

    /**
     * @var array List of registered internal modifiers
     */
    private $internalModifiers = [
        '\Webiny\Htpl\Modifiers\CorePack'
    ];

    /**
     * @var array List of function instances
     */
    private $initializedFunctions = [];

    /**
     * @var array List of initialized modifiers.
     */
    private $initializedModifiers = [];

    /**
     * @var TemplateProviderInterface
     */
    private $templateProvider;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var array Internal cache, only valid for the current instance.
     */
    private $compiledTemplates = [];


    /**
     * Base constructor.
     *
     * @param TemplateProviderInterface $provider Template provider instance.
     * @param CacheInterface|null       $cache    Cache instance.
     */
    public function __construct(TemplateProviderInterface $provider, CacheInterface $cache = null)
    {
        // initialize functions
        $this->initializeFunctions();

        // initialize modifiers
        $this->initializeModifiers();

        // set provider
        $this->templateProvider = $provider;

        // set cache
        $this->cache = !empty($cache) ? $cache : new NullCache();
    }

    /**
     * Get the current template provider instance.
     *
     * @return TemplateProviderInterface
     */
    public function getTemplateProvider()
    {
        return $this->templateProvider;
    }

    /**
     * Get the current cache instance.
     *
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Get current options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set one or more options.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set the force compile flag.
     *
     * @param bool $forceCompile Force compile flag (default: false).
     */
    public function setForceCompile($forceCompile)
    {
        $this->options['forceCompile'] = (bool)$forceCompile;
    }

    /**
     * Get the current state of force compile flag.
     *
     * @return bool
     */
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
    public function build($template, $parameters = [])
    {
        $this->assignArray($parameters);
        $this->template = $template;

        if (!isset($this->compiledTemplates[$template])) {
            // compile the template
            $compiler = new Compiler($this);
            $this->compiledTemplates[$template] = $compiler->getCompiledTemplate($template);
        }

        return $this->compiledTemplates[$template];
    }

    /**
     * Helper method that sends the template to the browser.
     *
     * @param string $template   Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     */
    public function display($template, $parameters = [])
    {
        $this->build($template, $parameters)->display();
    }

    /**
     * Helper method that returns the template result in form of a string.
     *
     * @param string $template   Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     *
     * @return string
     */
    public function fetch($template, $parameters = [])
    {
        return $this->build($template, $parameters)->fetch();
    }

    /**
     * Assign a variable and its value into the template engine.
     *
     * @param string $var   Variable name.
     * @param mixed  $value Variable value.
     *
     * @return void
     */
    public function assign($var, $value)
    {
        $this->assignedVars[$var] = $value;
    }

    /**
     * Assign multiple variables.
     *
     * @param array $parameters List of key=>value variables.
     */
    public function assignArray(array $parameters)
    {
        foreach ($parameters as $k => $v) {
            $this->assign($k, $v);
        }
    }

    /**
     * Get current assigned variables.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->assignedVars;
    }

    /**
     * Register a function.
     *
     * @param FunctionInterface $function
     *
     * @return void
     */
    public function registerFunction(FunctionInterface $function)
    {
        $this->initializedFunctions[$function->getTag()] = $function;
    }

    /**
     * Register a modifier pack.
     *
     * @param ModifierPackInterface $modPack
     */
    function registerModifierPack(ModifierPackInterface $modPack)
    {
        $this->initializedModifiers = array_merge($this->initializedModifiers, $modPack::getModifiers());
    }

    /**
     * Get the list of initialized functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->initializedFunctions;
    }

    /**
     * Get the list of initialized modifiers.
     *
     * @return array
     */
    public function getModifiers()
    {
        return $this->initializedModifiers;
    }

    /**
     * Set the start and end flag for marking variables.
     * Default flags are { }.
     * NOTE: This is an EXPERIMENTAL feature.
     *
     * @param string $startFlag
     * @param string $endFlag
     */
    public function setLexerFlags($startFlag, $endFlag)
    {
        $this->options['lexer']['varStartFlag'] = $startFlag;
        $this->options['lexer']['varEndFlag'] = $endFlag;
    }

    /**
     * Get the current value of start and end flag.
     *
     * @return array
     */
    public function getLexerFlags()
    {
        return [
            'varStartFlag' => $this->options['lexer']['varStartFlag'],
            'varEndFlag'   => $this->options['lexer']['varEndFlag']
        ];
    }

    /**
     * Initializes the registered functions.
     */
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

    /**
     * Initializes the registered modifiers.
     */
    private function initializeModifiers()
    {
        foreach ($this->internalModifiers as $mod) {
            $this->initializedModifiers = array_merge($this->initializedModifiers, $mod::getModifiers());
        }
    }
}
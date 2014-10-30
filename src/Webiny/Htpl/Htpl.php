<?php

namespace Webiny\Htpl;

use Webiny\Htpl\Processor\Processor;

class Htpl
{
    static private $_templateDir = '';

    /**
     * Fetch the template from the given location, parse it and return the output.
     *
     * @param string $template   Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     *
     * @return string Parsed template.
     */
    function fetch($template, $parameters = [])
    {
        Processor::processTemplate($template, $parameters);
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
        echo $this->fetch($template, $parameters);
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

    }

    /**
     * Root dir where the templates are stored.
     *
     * @param string $dir Absolute path to the directory that holds the templates.
     *
     * @throws HtplException
     * @return void
     */
    public static function setTemplateDir($dir)
    {
        if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
            $dir .= '/';
        }

        if ($dir[0] != '/' && $dir[1] != ':') {
            throw new HtplException('Template dir path must be an absolute path.');
        }

        self::$_templateDir = $dir;
    }

    /**
     * Returns the root dir where the templates are stored.
     *
     * @return string
     */
    public static function getTemplateDir()
    {
        return self::$_templateDir;
    }

    /**
     * Register a plugin for the template engine.
     *
     * @param Plugin $plugin
     *
     * @return void
     */
    function registerPlugin(Plugin $plugin)
    {

    }
}
<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\TemplateProviders;

use Webiny\Htpl\HtplException;
use Webiny\Htpl\TemplateProviders\LoaderInterface;

/**
 * ArrayProvider -> a simple template provider that uses an array to store and retrieve the templates.
 *
 * @package Webiny\Htpl\TemplateProviders
 */
class ArrayProvider implements TemplateProviderInterface
{

    /**
     * @var array List of assigned templates.
     */
    private $templates;


    /**
     * Base constructor.
     *
     * @param array $templates List of templates.
     */
    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * Add the given template to the list of templates.
     *
     * @param string $name   Template name.
     * @param string $source Template source.
     */
    public function addTemplate($name, $source)
    {
        $this->templates[$name] = $source;
    }

    /**
     * Removes the given template.
     *
     * @param string $name Template name that should be removed.
     *
     * @return bool True if template was removed, false if template was not found.
     */
    public function removeTemplate($name)
    {
        if (isset($this->templates[$name])) {
            unset($this->templates[$name]);
            return true;
        }

        return false;
    }

    /**
     * Get template source based on the provided $name.
     *
     * @param string $name Template name.
     *
     * @return string The template source.
     *
     * @throws HtplException Template not found.
     */
    public function getSource($name)
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }

        throw new HtplException(sprintf('Template %s not found', $name));
    }

    /**
     * Get the cache key which will be used to cache the compiled template.
     *
     * @param string $name Template name.
     *
     * @return string Cache key.
     */
    public function getCacheKey($name)
    {
        return $name;
    }

    /**
     * Returns the last modified time of the template.
     *
     * @param string $name Template name.
     *
     * @return bool Is the template still fresh.
     */
    public function createdOn($name)
    {
        return time();
    }
}
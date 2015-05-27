<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\TemplateProviders;

/**
 * Provider retrieves the template source file.
 *
 * Interface TemplateProviderInterface
 * @package Webiny\Htpl\TemplateProviders
 */
interface TemplateProviderInterface
{
    /**
     * Get template source based on the provided $name.
     *
     * @param string $name Template name.
     *
     * @return string The template source.
     *
     * @throws HtplException Template not found.
     */
    public function getSource($name);

    /**
     * Get the cache key which will be used to cache the compiled template.
     *
     * @param string $name Template name.
     *
     * @return string Cache key.
     */
    public function getCacheKey($name);

    /**
     * Returns the last modified time of the template.
     *
     * @param string $name Template name.
     *
     * @return bool Is the template still fresh.
     */
    public function createdOn($name);
}
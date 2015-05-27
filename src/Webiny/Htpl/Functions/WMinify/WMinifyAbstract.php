<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Functions\WMinify;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Cache\CacheInterface;
use Webiny\Htpl\TemplateProviders\TemplateProviderInterface;

/**
 * WMinify abstract class -> all WMinify drivers must extend this class.
 *
 * @package Webiny\Htpl\Functions\WMinify
 */
abstract class WMinifyAbstract
{

    /**
     * @var Htpl
     */
    private $htpl;

    /**
     * @var TemplateProviderInterface
     */
    private $provider;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $webRoot;


    /**
     * Callback that receives a list of absolute paths to one or more css files.
     * As a result it should return an http absolute path to the minified css file.
     * NOTE: Method is called on every page refresh, so it should internally handle the caching and checking of freshness.
     *
     * @param array $files List of absolute paths to one or more css files.
     *
     * @return string http absolute path to the minified css file.
     */
    public abstract function minifyCss(array $files);

    /**
     * Callback that receives a list of absolute paths to one or more javascript files.
     * As a result it should return an http absolute path to the minified javascript file.
     * NOTE: Method is called on every page refresh, so it should internally handdle the caching and checking of freshness.
     *
     * @param array $files List of absolute paths to one or more javascript files.
     *
     * @return string http absolute path to the minified javascript file.
     */
    public abstract function minifyJavaScript(array $files);

    /**
     * Base constructor.
     *
     * @param Htpl $htpl Current Htpl instance.
     */
    public function __construct(Htpl $htpl)
    {
        $this->htpl = $htpl;
    }

    /**
     * Get the current Htpl instance.
     *
     * @return Htpl
     */
    public function getHtpl()
    {
        return $this->htpl;
    }

    /**
     * @return TemplateProviderInterface
     * @throws HtplException
     */
    public function getProvider()
    {

        if (is_object($this->provider)) {
            return $this->provider;
        }

        $options = $this->getHtpl()->getOptions()['minify'];
        if (empty($options)) {
            throw new HtplException('Missing options for w-minify function.');
        }

        $provider = $options['provider'];
        if (empty($provider)) {
            throw new HtplException('The provider is not defined for w-minify function.');
        }

        if (!is_object($provider)) {
            throw new HtplException('w-minify provider should be an instance of Webiny\Htpl\TemplateProviders\TemplateProvidersInterface.');
        }

        return $this->provider = $provider;
    }

    /**
     * @return CacheInterface
     * @throws HtplException
     */
    public function getCache()
    {
        if (is_object($this->cache)) {
            return $this->cache;
        }

        $options = $this->getHtpl()->getOptions()['minify'];
        if (empty($options)) {
            throw new HtplException('Missing options for w-minify function.');
        }

        $cache = $options['cache'];
        if (empty($cache)) {
            throw new HtplException('Cache is not defined for w-minify function.');
        }

        if (!is_object($cache)) {
            throw new HtplException('w-minify cache should be an instance of Webiny\Htpl\Cache\CacheInterface.');
        }

        return $this->cache = $cache;
    }

    /**
     * Get the defined web root.
     *
     * @return string
     * @throws HtplException
     */
    public function getWebRoot()
    {
        if (!empty($this->webRoot)) {
            return $this->webRoot;
        }

        $options = $this->getHtpl()->getOptions()['minify'];
        if (empty($options)) {
            throw new HtplException('Missing options for w-minify function.');
        }

        $webRoot = $options['webRoot'];
        if (is_null($webRoot)) {
            $webRoot = '/';
        }

        $this->webRoot = $webRoot;

        return $webRoot;
    }
}

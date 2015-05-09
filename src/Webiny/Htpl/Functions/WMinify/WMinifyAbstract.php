<?php

namespace Webiny\Htpl\Functions\WMinify;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Loaders\LoaderInterface;
use Webiny\Htpl\Writer\WriterInterface;

abstract class WMinifyAbstract
{

    /**
     * @var Htpl
     */
    private $htpl;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var WriterInterface
     */
    private $writer;

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
     * @return Htpl
     */
    public function getHtpl()
    {
        return $this->htpl;
    }

    /**
     * @return LoaderInterface
     * @throws HtplException
     */
    public function getLoader()
    {

        if (is_object($this->loader)) {
            return $this->loader;
        }

        $options = $this->getHtpl()->getOptions()['minify'];
        if (empty($options)) {
            throw new HtplException('Missing options for w-minify function.');
        }

        $loader = $options['loader'];
        if (empty($loader)) {
            throw new HtplException('The loader is not defined for w-minify function.');
        }

        if (!is_object($loader)) {
            throw new HtplException('w-minify loader should be an instance of Webiny\Htpl\Loaders\LoaderInterface.');
        }

        return $this->loader = $loader;
    }

    /**
     * @return WriterInterface
     * @throws HtplException
     */
    public function getWriter()
    {
        if (is_object($this->writer)) {
            return $this->writer;
        }

        $options = $this->getHtpl()->getOptions()['minify'];
        if (empty($options)) {
            throw new HtplException('Missing options for w-minify function.');
        }

        $writer = $options['writer'];
        if (empty($writer)) {
            throw new HtplException('The writer is not defined for w-minify function.');
        }

        if (!is_object($writer)) {
            throw new HtplException('w-minify writer should be an instance of Webiny\Htpl\Loaders\LoaderInterface.');
        }

        return $this->writer = $writer;
    }

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

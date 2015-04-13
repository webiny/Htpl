<?php

namespace Webiny\Htpl\Functions\WMinify;

use Webiny\Htpl\Htpl;

interface WMinifyInterface
{
    /**
     * Base constructor.
     *
     * @param Htpl $htpl Current Htpl instance.
     */
    public function __construct(Htpl $htpl);

    /**
     * Callback that receives a list of absolute paths to one or more css files.
     * As a result it should return an http absolute path to the minified css file.
     * NOTE: Method is called on every page refresh, so it should internally handle the caching and checking of freshness.
     *
     * @param array $files List of absolute paths to one or more css files.
     *
     * @return string http absolute path to the minified css file.
     */
    public function minifyCss(array $files);

    /**
     * Callback that receives a list of absolute paths to one or more javascript files.
     * As a result it should return an http absolute path to the minified javascript file.
     * NOTE: Method is called on every page refresh, so it should internally handdle the caching and checking of freshness.
     *
     * @param array $files List of absolute paths to one or more javascript files.
     *
     * @return string http absolute path to the minified javascript file.
     */
    public function minifyJavaScript(array $files);
}
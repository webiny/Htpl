<?php

namespace Webiny\Htpl\Loaders;

use Webiny\Htpl\HtplException;

class Filesystem implements LoaderInterface
{
    private $paths = [];
    private $cache = [];

    public function __construct(array $paths)
    {
        $this->setPaths($paths);
    }

    public function appendPath($path)
    {
        $this->paths[] = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function prependPath($path)
    {
        array_unshift($this->paths, rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
    }

    public function setPaths(array $paths)
    {
        foreach ($paths as $p) {
            $this->paths[] = rtrim($p, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
    }

    public function getPaths()
    {
        return $this->paths;
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
        // first check the cache
        if (isset($this->cache[$name]) && isset($this->cache[$name]['source'])) {
            return $this->cache[$name]['source'];
        }
        $this->cache[$name]['source'] = null;


        // get the template
        $templatePath = isset($this->cache[$name]['source']) ? $this->cache[$name]['source'] : $this->locateTemplate($name);
        $source = file_get_contents($templatePath);

        // cache it
        $this->cache[$name] = [
            'source' => $source
        ];

        // return the source
        return $source;
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
        // get the template path
        return $this->locateTemplate($name);
    }

    /**
     * @param $name
     *
     * @return string
     * @throws HtplException
     */
    private function locateTemplate($name)
    {
        // check the cache
        if (isset($this->cache[$name]) && isset($this->cache[$name]['path'])) {
            return $this->cache[$name]['path'];
        }

        // loop the paths and try to find the template
        foreach ($this->paths as $p) {
            $filename = $p . $name;
            if (file_exists($filename)) {
                $this->cache[$name]['path'] = $filename;
                return $filename;
            }
        }

        throw new HtplException(sprintf('Template "%s" not found.', $name));
    }

    /**
     * Returns the last modified time of the template.
     *
     * @param string $name Template name.
     *
     * @return bool Is the template still fresh.
     */
    public function getFreshness($name)
    {
        return filemtime($this->locateTemplate($name));
    }
}
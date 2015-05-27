<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Cache;

/**
 * Stores the cache in an array - valid only for the current request.
 *
 * @package Webiny\Htpl\Cache
 */
class ArrayCache implements CacheInterface
{

    private $_cache;

    /**
     * Writes the given content.
     *
     * @param string $file    Can be a filename, or a path.
     * @param string $content Content that should be written.
     *
     * @return string Absolute path to the written file.
     */
    public function write($file, $content)
    {
        $this->_cache[$file]['content'] = $content;
        $this->_cache[$file]['createdOn'] = time();

        return $file;
    }

    /**
     * Reads the file source.
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return string|false Returns either the source, or false if the file is not found.
     */
    public function read($file)
    {
        if (isset($this->_cache[$file])) {
            return $this->_cache[$file]['content'];
        }

        return false;
    }

    /**
     * Deletes the given file
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return void
     */
    public function delete($file)
    {
        if (isset($this->_cache[$file])) {
            unset($this->_cache[$file]);
        }
    }

    /**
     * Gets the unix timestamp when the file was created.
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return int|bool Unix timestamp, or false if file doesn't exist.
     */
    public function createdOn($file)
    {
        if (isset($this->_cache[$file])) {
            return $this->_cache[$file]['createdOn'];
        }

        return false;
    }

    /**
     * If the file exists, returns the full file path, otherwise false.
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return string|false
     */
    public function getFilePath($file)
    {
        if (isset($this->_cache[$file])) {
            return $file;
        }

        return false;
    }

    /**
     * Returns all cache entries.
     *
     * @return array
     */
    public function dumpCache()
    {
        return $this->_cache;
    }
}

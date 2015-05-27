<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Cache;

/**
 * Cache is used to write different files to the disk, or the cloud.
 * Typically used for saving compiled template files.
 *
 * Interface CacheInterface
 * @package Webiny\Htpl\Writer
 */
interface CacheInterface
{
    /**
     * Writes the given content.
     *
     * @param string $file    Can be a filename, or a path.
     * @param string $content Content that should be written.
     *
     * @return string Absolute path to the written file.
     */
    public function write($file, $content);

    /**
     * Reads the file source.
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return string|false Returns either the source, or false if the file is not found.
     */
    public function read($file);

    /**
     * Deletes the given file
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return void
     */
    public function delete($file);

    /**
     * Gets the unix timestamp when the file was created.
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return int|bool Unix timestamp, or false if file doesn't exist.
     */
    public function createdOn($file);

    /**
     * If the file exists, returns the full file path, otherwise false.
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return string|false
     */
    public function getFilePath($file);
}
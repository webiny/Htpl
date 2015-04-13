<?php

namespace Webiny\Htpl\Writer;

/**
 * Writer is used to write different files to the disk, or cloud.
 * Typically used for writing the compiled template files and minify files.
 *
 * Interface WriterInterface
 * @package Webiny\Htpl\Writer
 */
interface WriterInterface
{
    /**
     * Writes the given content.
     *
     * @param string $file Can be a filename, or a path.
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
}
<?php

namespace Webiny\Htpl\Writer;

class Filesystem implements WriterInterface
{

    private $writerDir;


    public function __construct($writerDir)
    {
        $this->writerDir = rtrim($writerDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!is_dir($this->writerDir)) {
            mkdir($this->writerDir, 0755);
        }
    }

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
        file_put_contents($this->getFullPath($file), $content);

        return $this->getFullPath($file);
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
        $file = $this->getFullPath($file);
        clearstatcache(true, $file);
        if (file_exists($file)) {
            return file_get_contents($file);
        }
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
        $file = $this->getFullPath($file);
        clearstatcache(true, $file);
        if (file_exists($file)) {
            unlink($file);
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
        $file = $this->getFullPath($file);
        clearstatcache(true, $file);
        if (file_exists($file)) {
            return filemtime($file);
        }
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
        $file = $this->getFilename($file);
        $path = $this->writerDir . $file;
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }

    /**
     * Returns the full path to the given file.
     * If the root folder, in which the file is placed, doesn't exist, it will be created.
     *
     * @param string $file Can be a filename, or a path.
     *
     * @return string
     */
    private function getFullPath($file)
    {

        $file = $this->getFilename($file);
        $path = explode(DIRECTORY_SEPARATOR, $this->writerDir . $file);
        $file = array_pop($path);
        $path = implode(DIRECTORY_SEPARATOR, $path);

        if (!is_dir($path)) {
            mkdir($path, 0755);
        }

        return rtrim($path) . DIRECTORY_SEPARATOR . $file;
    }

    private function getFilename($file)
    {
        $extData = explode('.', $file);
        return md5($file) . '.' . array_pop($extData);
    }
}
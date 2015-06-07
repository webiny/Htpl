<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Processor;

/**
 * Layout class holds the result from LayoutTree processor.
 *
 * @package Webiny\Htpl\Processor
 */
class Layout
{
    /**
     * @var string Layout source
     */
    private $source;

    /**
     * @var array List of included files that compose the source.
     */
    private $includedFiles;

    /**
     * @var int Timestamp when the last cache check was done.
     */
    private $lastTouched = 0;


    /**
     * Base constructor.
     *
     * @param string $source        Layout source.
     * @param array  $includedFiles List of included files that compose the source.
     */
    public function __construct($source, $includedFiles)
    {
        $this->source = $source;
        $this->includedFiles = $includedFiles;
        $this->lastTouched = time();
    }

    /**
     * @param string $source Set the template source.
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string Current source.
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return array List of included files that compose the source.
     */
    public function getIncludedFiles()
    {
        return $this->includedFiles;
    }

    /**
     * @param int $lt Set the last touch timestamp.
     */
    public function setLastTouched($lt)
    {
        $this->lastTouched = $lt;
    }

    /**
     * @return int Get the last touch timestamp.
     */
    public function getLastTouched()
    {
        return $this->lastTouched;
    }
}
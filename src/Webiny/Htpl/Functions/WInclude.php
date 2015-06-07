<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Htpl;
use Webiny\Htpl\HtplException;
use Webiny\Htpl\Processor\OutputWrapper;

/**
 * WInclude function.
 *
 * @package Webiny\Htpl\Functions
 */
class WInclude implements FunctionInterface
{
    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public function getTag()
    {
        return 'w-include';
    }

    /**
     * This is a callback method when we match the tag that the function is registered for.
     * The method will receive a list of attributes that the tag has associated.
     * The method should return a string that should replace the matching tag.
     * If the method returns false, no replacement will occur.
     *
     * @param string     $content
     * @param array|null $attributes
     * @param Htpl       $htpl
     *
     * @throws HtplException
     * @return string|bool
     */
    public function parseTag($content, $attributes, Htpl $htpl)
    {
        if (!isset($attributes['file'])) {
            throw new HtplException('w-include must have a "file" attribute defined.');
        }

        $callback = 'Webiny\Htpl\Functions\WInclude::htpl';

        // check if variable is set
        if (empty($htpl->getVars()[$attributes['file']])) {
            throw new HtplException(sprintf('Cannot include a template file, variable "%s" is not defined.',
                $attributes['file']));
        }

        // treat as variable
        // (direct file includes are processed in the layout tree)
        $attributes['file'] = OutputWrapper::getVar($attributes['file']);
        $callback .= '(' . $attributes['file'] . ', $this->getHtplInstance())';

        return [
            'openingTag' => '',
            'content'    => OutputWrapper::outputFunction($callback),
            'closingTag' => ''
        ];
    }

    /**
     * Static callback that includes a Htpl template.
     *
     * @param string $file Path to the Htpl template.
     * @param Htpl   $htpl Current Htpl instance.
     *
     * @throws HtplException
     */
    public static function htpl($file, Htpl $htpl)
    {
        // validate the variable value
        // only htpl templates are allowed
        if (substr($file, -5) != '.htpl') {
            throw new HtplException(sprintf('Failed to include %s. Only .htpl templates can be included.', $file));
        }

        // use the same htpl instance so we benefit from the internal cache and already initialized provider and so we
        // also pass the current assigned variables
        $htpl->display($file);
    }
}
<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Htpl;

/**
 * Template class is the result of the Compiler class.
 *
 * @package Webiny\Htpl\Processor
 */
class Template
{
    /**
     * @var Htpl Current htpl instance
     */
    private $htpl;

    /**
     * @var string Compiled template string.
     */
    private $template;

    /**
     * @param Htpl   $htpl     Current Htpl instance.
     * @param string $template Compiled template string.
     */
    public function __construct(Htpl $htpl, $template)
    {
        $this->htpl = $htpl;
        $this->template = $template;
    }

    /**
     * Callback method used from within the compiled template to retrieve the variable value.
     *
     * @param string $key     Variable name.
     * @param array  $context Context from where the variable should be retrieved.
     *
     * @return null|mixed
     */
    public function getVar($key, $context)
    {
        if (strpos($key, '.') !== false) {
            $keyData = explode('.', $key);
            $value = $context;
            foreach ($keyData as $kd) {
                if (!empty($value[$kd])) {
                    $value = $value[$kd];
                } else {
                    return null;
                }
            }
        } else {
            if (!empty($context[$key])) {
                $value = $context[$key];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Escape callback.
     *
     * @param string $value Value that should be escaped.
     *
     * @return string
     */
    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
    }

    /**
     * Get the current Htpl instance.
     *
     * @return Htpl
     */
    public function getHtplInstance()
    {
        return $this->htpl;
    }

    /**
     * Display the template.
     * Note: template scope is set to $this as it's executed within the scope of this class.
     */
    public function display()
    {
        // we need to assign the latest variables from within the htpl instance, just before we build the output
        $this->vars = $this->getHtplInstance()->getVars();

        // build the output
        eval('?>' . $this->template);
    }

    /**
     * Returns the template output as a string.
     *
     * @return string
     */
    public function fetch()
    {
        ob_start();
        $this->display();

        return ob_get_clean();
    }

    public function getSource()
    {
        return $this->template;
    }
}
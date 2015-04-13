<?php

namespace Webiny\Htpl\Processor;

use Webiny\Htpl\Htpl;

class Template
{
    private $htpl;
    private $template;

    function __construct(Htpl $htpl, $template)
    {
        $this->htpl = $htpl;
        $this->vars = $htpl->getVars();
        $this->template = $template;
    }

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

        if (is_array($value) || is_object($value)) {
            return $value;
        }

        return $this->escape($value);
    }

    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
    }

    public function getHtplInstance()
    {
        return $this->htpl;
    }


    public function display()
    {
        include $this->template;
    }
}
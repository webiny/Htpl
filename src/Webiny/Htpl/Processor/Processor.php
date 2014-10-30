<?php

namespace Webiny\Htpl\Processor;

class Processor
{
    static function processTemplate($template, $assignments=[])
    {
        // check if templates are already compiled

        // compile the template
        $compiler = new Compiler();
        $compiler->_construct($template);
    }
}


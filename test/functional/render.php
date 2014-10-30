<?php

require_once '/var/www/projects/composer/vendor/autoload.php';

\Webiny\Component\ClassLoader\ClassLoader::getInstance()->registerMap([
                                                           'Webiny\Htpl\\' => '/var/www/projects/htpl/src/Webiny/Htpl'
                                                       ]
);

$htpl = new \Webiny\Htpl\Htpl();
$htpl->setTemplateDir(__DIR__.'/templates/');
$htpl->fetch('validDoc.htpl');
//$htpl->fetch('validDoc.htpl');
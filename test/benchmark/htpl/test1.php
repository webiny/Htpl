<?php
$loader = require __DIR__.'/../../../vendor/autoload.php';
$loader->add('Webiny\Htpl\\', __DIR__ . '/../../../src/');

// display the template
$provider = new \Webiny\Htpl\TemplateProviders\FilesystemProvider([__DIR__ . '/template']);
$cache = new \Webiny\Htpl\Cache\FilesystemCache(__DIR__ . '/temp/compiled');

$htpl = new \Webiny\Htpl\Htpl($provider, $cache);
$htpl->setForceCompile(false);

// assign variables
$htpl->assign('entries', include(__DIR__.'/../entries.php'));

echo $htpl->fetch('template.htpl');
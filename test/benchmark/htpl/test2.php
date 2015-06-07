<?php
$loader = require __DIR__.'/../../../vendor/autoload.php';
$loader->add('Webiny\Htpl\\', __DIR__ . '/../../../src/');

// setup
$loader = new \Webiny\Htpl\TemplateProviders\FilesystemProvider([__DIR__ . '/template']);
$writer = new \Webiny\Htpl\Cache\FilesystemCache(__DIR__ . '/temp/compiled');

$htpl = new \Webiny\Htpl\Htpl($loader, $writer);
$htpl->setForceCompile(true);

// assign variables
$htpl->assign('entries', include(__DIR__.'/../entries.php'));

$result = $htpl->fetch('template.htpl');
<?php
$loader = require __DIR__.'/../../../vendor/autoload.php';
$loader->add('Webiny\Htpl\\', __DIR__ . '/../../../src/');

for($i=0;$i<1000; $i++){
    // display the template
    $provider = new \Webiny\Htpl\TemplateProviders\FilesystemProvider([__DIR__ . '/template']);
    $cache = new \Webiny\Htpl\Cache\FilesystemCache(__DIR__ . '/temp/compiled');

    $htpl = new \Webiny\Htpl\Htpl($provider, $cache);
    $htpl->setForceCompile(false);

    // assign variables
    $htpl->assign('entries', include(__DIR__.'/../entries.php'));

    $htpl->fetch('template.htpl');
}
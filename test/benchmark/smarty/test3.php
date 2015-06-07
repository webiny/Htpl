<?php
require __DIR__ . '/vendor/autoload.php';

// setup
for ($i = 0; $i < 1000; $i++) {
    $smarty = new Smarty();
    $smarty->setTemplateDir(__DIR__ . '/template');
    $smarty->setCompileDir(__DIR__ . '/temp/compile');
    $smarty->setForceCompile(false);

    // assign variables
    $smarty->assign('entries', include(__DIR__ . '/../entries.php'));

    $smarty->fetch('template.tpl');
}
<?php
require __DIR__.'/vendor/autoload.php';

// setup
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__.'/template');
$smarty->setCompileDir(__DIR__.'/temp/compile');
$smarty->setForceCompile(true);

// assign variables
$smarty->assign('entries', include(__DIR__.'/../entries.php'));

$smarty->fetch('template.tpl');
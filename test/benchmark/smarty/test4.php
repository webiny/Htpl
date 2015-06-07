<?php
require __DIR__.'/vendor/autoload.php';

//for($i=0;$i<1000; $i++){
    // setup
    $smarty = new Smarty();
    $smarty->setTemplateDir(__DIR__.'/template');
    $smarty->setCompileDir(__DIR__.'/temp/compile');
    $smarty->setForceCompile(false);
    $smarty->setEscapeHtml(true);

    // assign variables
    $smarty->assign('arr', include(__DIR__.'/../entries.php'));
    $smarty->assign('var', 'John Snow');

    $smarty->fetch('varTest.tpl');
//}
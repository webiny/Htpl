<?php
require __DIR__ . '/vendor/autoload.php';

for ($i = 0; $i < 1000; $i++) {
    // setup
    $loader = new Twig_Loader_Filesystem([__DIR__ . '/template/']);
    $twig = new Twig_Environment($loader, ['cache' => __DIR__ . '/temp/compile']);

    // assign variables
    $twig->addGlobal('entries', include(__DIR__ . '/../entries.php'));

    $twig->render('template.html');
}
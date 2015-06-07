<?php
/**
 * Test 1: Built the template 1000 times by each time creating a new template engine instance.
 * Cache: on
 */

require 'vendor/autoload.php';

// timer start
$start = microtime(true);

// display the template
for ($i = 0; $i < 1000; $i++) {
    // setup
    $views = __DIR__ . '/template';
    $cache = __DIR__ . '/temp';
    $blade = new \Philo\Blade\Blade($views, $cache);

    // assign variables
    $tpl = $blade->view()->make('template', ['entries' => include('../entries.php')]);

    echo $tpl->render();
}

// timer stop
echo 'end time: ' . (microtime(true) - $start);
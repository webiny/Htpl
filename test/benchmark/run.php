<?php

// we have to execute tests in separate processes to get valid results, eg Twig is dynamically creating classes,
// which act like a cache when we are conducting tests that should not use any cache

$engines = [
    'smarty' => 'smarty',
    'twig  ' => 'twig',
    'htpl  ' => 'htpl',
];

$tests = [
    //'test1.php' => 100,     // one template render with cache
    //'test2.php' => 50,      // template render without cache
    //'test3.php' => 1        // render a template 1000 times with cache
    'test4.php'   => 25        // output 100 variables with some modifiers and loop it 1000 times
];

foreach ($engines as $name => $location) {
    foreach ($tests as $test => $loops) {
        echo sprintf("Testing %s (test: %s) => ", $name, $test);
        $start = microtime(true);
        for ($i = 0; $i < $loops; $i++) {
            system('php ' . $location . DIRECTORY_SEPARATOR . $test);
        }
        $end = microtime(true);
        echo "time taken: " . ($end - $start) . "\n";
    }
}
<?php
if (PHP_SAPI === 'cli') {
    require __DIR__ . '/vendor/autoload.php';
    new sqlLoader();

    print_r($argv);

    $version      = $argv[1];
    $api          = $argv[2];
    $method       = $argv[3];
    $action_name  = "\api\\$version\\$api";
    $action_class = new $action_name();
    $action_class->$method();
}
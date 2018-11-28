<?php
if (PHP_SAPI === 'cli') {
    require __DIR__ . '/vendor/autoload.php';
    new sqlLoader();

    $class        = $argv[1];
    $method       = $argv[2];
    $action_name  = "\script\\$class";
    $action_class = new $action_name();
    $action_class->$method();
}
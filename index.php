<?php

require __DIR__.'/vendor/autoload.php';
new SQLLoader();
$version = $_GET['ginV_version'];
$api = $_GET['ginV_api'];
$method = $_GET['ginV_method'];

if ($api=='rpc') {
    $action_name  = "\api\\$version\\$method";
    $action_class = new $action_name();
    $service = new Yar_Server($action_class);
    $service->handle();
    return null;
}

if (config('app.model') == 'debug') {
    include_once(__DIR__.'/test.php');
    test();
}


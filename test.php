<?php

function test() {
    $version = $_GET['ginV_version'];
    $api     = $_GET['ginV_api'];
    $method  = $_GET['ginV_method'];

    $action_name  = "\api\\$version\\$api";
    $action_class = new $action_name();
    $data         = $action_class->$method();
    dd($data);
    header('Content-type: application/json;charset=utf-8');
    echo json_encode($data);
}

test();
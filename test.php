<?php

function test() {
    header('Content-type: application/json');
    $version = $_GET['ginV_version'];
    $api     = $_GET['ginV_api'];
    $method  = $_GET['ginV_method'];

    $action_name  = "\api\\$version\\$api";
    $action_class = new $action_name();
    $page         = $_GET['page'];
    $data         = $action_class->$method($page);
    echo json_encode($data);
}
<?php

return [
    // 默认数据库连接名称
    'default' => 'mysql',


    // mysql数据库连接
    'mysql' => [
        'driver' => 'mysql',
        'host' => '10.10.20.207',
        'port' => '23306',
        'username' => 'root',
        'password' => '123456',
        'database' => 'ginv_mysql',
        'charset' => 'utf8mb4',
    ],

    'redis' => [
        'host' => '10.10.20.207',
        'port' => 6379,
        'timeout' => 2.5
    ]
];
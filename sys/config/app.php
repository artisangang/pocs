<?php

return [
    'ip' => '127.0.0.1',
    'port' => 9000,
    'mode' => 'prod',
    'debug' => false,
    'connection' => 'simpleChat',
    'connections' => [
        'simpleChat' => [
            'adapter' => 'pdo',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => '',
            'dbname' => 'pocs'
        ]
    ],
    'env' => [
        'test' => [
            'connection' => 'simpleChatTest',
            'port' => 9200,
            'debug' => false,
        ],
        'dev' => [
            'connection' => 'simpleChatDev',
            'port' => 9100,
            'debug' => true,
        ],
        'prod' => [
            'connection' => 'simpleChat',
            'port' => 9000,
            'debug' => false,
        ]
    ],
    'providers' => [
        \POCS\Lib\PDO\ServiceProvider::class
    ]

];
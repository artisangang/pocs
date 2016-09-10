<?php

return [
    'ip' => '127.0.0.1',
    'port' => 9100,
    'mode' => 'dev',
    'debug' => false,
    'connection' => 'simpleChat',
    'connections' => [
        'simpleChat' => [
            'adapter' => 'pdo',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => '',
            'dbname' => 'simpleChat'
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
            'debug' => true,
            'port' => 9100,
        ],
        'prod' => [
            'connection' => 'simpleChat',
            'port' => 9000,
            'debug' => false,
        ]
    ],


];
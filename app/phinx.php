<?php

return [
    'paths' => [
        'migrations' => '/app/db/migrations',
        'seeds' => '/app/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => 'mysql',
            'name' => 'HaarlemFestivaldb',
            'user' => 'developer',
            'pass' => 'secret123',
            'port' => '3306',
            'charset' => 'utf8mb4',
        ],
    ],
];
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => Environment::get('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Environment::get('DB_HOST', '127.0.0.1'),
            'port' => Environment::get('DB_PORT', '3306'),
            'database' => Environment::get('DB_DATABASE', 'forge'),
            'username' => Environment::get('DB_USERNAME', 'forge'),
            'password' => Environment::get('DB_PASSWORD', ''),
            'unix_socket' => Environment::get('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

    ],





];

<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | This application uses 3 database connections:
    | 1. mysql      - Main App DB (users, batches, workflow) - MySQL
    | 2. policy_db  - Policy Source DB (renewal data) - SQL Server
    | 3. customer_db - Customer DB (contact info) - SQL Server
    |
    */

    'connections' => [

        /*
        |--------------------------------------------------------------------------
        | Main Application Database (MySQL)
        |--------------------------------------------------------------------------
        | Used for: users, batches, workflow, settings, logs
        */
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'insurance_system'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Policy Source Database (SQL Server)
        |--------------------------------------------------------------------------
        | Used for: querying renewal/policy data
        | This is READ-ONLY - we only query data from here
        */
        'policy_db' => [
            'driver' => 'sqlsrv',
            'url' => env('POLICY_DB_URL'),
            'host' => env('POLICY_DB_HOST', 'localhost'),
            'port' => env('POLICY_DB_PORT', '1433'),
            'database' => env('POLICY_DB_DATABASE', 'policy_master'),
            'username' => env('POLICY_DB_USERNAME', 'sa'),
            'password' => env('POLICY_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('POLICY_DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('POLICY_DB_TRUST_CERT', 'false'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Customer Database (SQL Server)
        |--------------------------------------------------------------------------
        | Used for: customer contact info, phone numbers, emails
        | This is READ-ONLY - we only query data from here
        */
        'customer_db' => [
            'driver' => 'sqlsrv',
            'url' => env('CUSTOMER_DB_URL'),
            'host' => env('CUSTOMER_DB_HOST', 'localhost'),
            'port' => env('CUSTOMER_DB_PORT', '1433'),
            'database' => env('CUSTOMER_DB_DATABASE', 'customer_data'),
            'username' => env('CUSTOMER_DB_USERNAME', 'sa'),
            'password' => env('CUSTOMER_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('CUSTOMER_DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('CUSTOMER_DB_TRUST_CERT', 'false'),
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];

<?php

global $table_prefix;

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/app/Database/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/app/Database/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_environment' => 'development',
            'production' => [
                'adapter' => 'mysql',
                'host' => DB_HOST,
                'name' => DB_NAME,
                'user' => DB_USER,
                'pass' => DB_PASSWORD,
                'port' => '3306',
                'charset' => DB_CHARSET,
                'table_prefix' => 'wp_',
            ],
            'development' => [
                'adapter' => 'mysql',
                'host' => DB_HOST,
                'name' => DB_NAME,
                'user' => DB_USER,
                'pass' => DB_PASSWORD,
                'port' => '3306',
                'charset' => DB_CHARSET,
                'table_prefix' => 'wp_',
            ],
        ],
        'version_order' => 'creation'
    ];

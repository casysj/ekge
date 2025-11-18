<?php

declare(strict_types=1);

use Doctrine\DBAL\Driver\PDO\MySQL\Driver as PDOMySQLDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => PDOMySQLDriver::class,
                'params' => [
                    'host'     => getenv('DB_HOST') ?: 'mariadb',
                    'port'     => getenv('DB_PORT') ?: '3306',
                    'user'     => getenv('DB_USER') ?: 'ekge_user',
                    'password' => getenv('DB_PASSWORD') ?: 'ekge_password_change_this',
                    'dbname'   => getenv('DB_NAME') ?: 'ekge_church',
                    'charset'  => 'utf8mb4',
                    'driverOptions' => [
                        1002 => 'SET NAMES utf8mb4',
                    ],
                ],
            ],
        ],
        'driver' => [
            'Application_driver' => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../../module/Application/src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'Application\Entity' => 'Application_driver',
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'metadata_cache'    => 'array',
                'query_cache'       => 'array',
                'result_cache'      => 'array',
                'hydration_cache'   => 'array',
                'generate_proxies'  => true,
                'proxy_dir'         => 'data/DoctrineORMModule/Proxy',
                'proxy_namespace'   => 'DoctrineORMModule\Proxy',
                'filters'           => [],
                'datetime_functions' => [],
                'string_functions'  => [],
                'numeric_functions' => [],
            ],
        ],
    ],
];

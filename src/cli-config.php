<?php

declare(strict_types=1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

// 환경 변수 로드 (.env 파일이 있다면)
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

// 데이터베이스 연결 정보
$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => getenv('DB_HOST') ?: 'mariadb',
    'port'     => getenv('DB_PORT') ?: '3306',
    'user'     => getenv('DB_USER') ?: 'ekge_user',
    'password' => getenv('DB_PASSWORD') ?: 'ekge_password_change_this',
    'dbname'   => getenv('DB_NAME') ?: 'ekge_church',
    'charset'  => 'utf8mb4',
    'driverOptions' => [
        1002 => 'SET NAMES utf8mb4',
    ],
];

// Doctrine 설정
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

$paths = [__DIR__ . "/module/Application/src/Entity"];

$config = new Configuration();
$config->setMetadataDriverImpl(new AttributeDriver($paths));

// 캐시 설정 (개발 환경용 - ArrayAdapter 사용)
$cache = new ArrayAdapter();
$config->setMetadataCache($cache);
$config->setQueryCache($cache);
$config->setResultCache($cache);

$config->setProxyDir(__DIR__ . '/data/DoctrineORMModule/Proxy');
$config->setProxyNamespace('DoctrineORMModule\Proxy');
$config->setAutoGenerateProxyClasses(true);

// EntityManager 생성
$entityManager = EntityManager::create($dbParams, $config);

return ConsoleRunner::createHelperSet($entityManager);

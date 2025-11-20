#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * 데이터 마이그레이션 실행 스크립트
 *
 * 사용법:
 *   php migrate.php
 *   php migrate.php --dry-run
 *   php migrate.php --clear
 */

// Autoloader
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/DataMigration.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

// 설정 파일 로드
$configFile = __DIR__ . '/migration.config.php';

if (!file_exists($configFile)) {
    echo "❌ 오류: migration.config.php 파일이 없습니다.\n";
    echo "migration.config.example.php를 복사하여 migration.config.php로 만들고 설정을 수정하세요.\n";
    exit(1);
}

$config = require $configFile;

// 명령줄 인자 처리
$options = getopt('', ['dry-run', 'clear', 'help']);

if (isset($options['help'])) {
    echo "EKGE 데이터 마이그레이션 도구\n\n";
    echo "사용법:\n";
    echo "  php migrate.php              - 마이그레이션 실행\n";
    echo "  php migrate.php --dry-run    - 테스트 모드 (실제 저장 안 함)\n";
    echo "  php migrate.php --clear      - 타겟 DB 초기화 후 실행\n";
    echo "  php migrate.php --help       - 도움말 표시\n\n";
    exit(0);
}

if (isset($options['dry-run'])) {
    $config['options']['dry_run'] = true;
    echo "⚠️  DRY-RUN 모드: 실제 저장하지 않습니다.\n\n";
}

if (isset($options['clear'])) {
    $config['options']['clear_target'] = true;
    echo "⚠️  타겟 DB를 초기화합니다.\n";
    echo "계속하려면 'yes'를 입력하세요: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) !== 'yes') {
        echo "취소되었습니다.\n";
        exit(0);
    }
    fclose($handle);
    echo "\n";
}

// Doctrine EntityManager 설정
try {
    $target = $config['target_db'];

    $dbParams = [
        'driver'   => 'pdo_mysql',
        'host'     => $target['host'],
        'port'     => $target['port'],
        'user'     => $target['user'],
        'password' => $target['password'],
        'dbname'   => $target['dbname'],
        'charset'  => $target['charset'],
    ];

    $paths = [__DIR__ . '/../../module/Application/src/Entity'];
    $isDevMode = true;

    $doctrineConfig = Setup::createAttributeMetadataConfiguration($paths, $isDevMode, null, null, false);
    $entityManager = EntityManager::create($dbParams, $doctrineConfig);

    echo "✓ Doctrine EntityManager 초기화 완료\n";

} catch (Exception $e) {
    echo "❌ EntityManager 초기화 실패: " . $e->getMessage() . "\n";
    exit(1);
}

// 마이그레이션 실행
try {
    $migration = new DataMigration($entityManager, $config);
    $migration->run();

    echo "\n✅ 마이그레이션이 성공적으로 완료되었습니다!\n";

} catch (Exception $e) {
    echo "\n❌ 마이그레이션 실패: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

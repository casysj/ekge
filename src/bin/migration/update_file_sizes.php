<?php

/**
 * 첨부파일 fileSize 업데이트 스크립트
 *
 * data/upfile에 실제 파일이 있는 attachment 레코드의
 * fileSize, imageWidth, imageHeight를 업데이트합니다.
 *
 * 실행: docker-compose exec php php bin/migration/update_file_sizes.php
 */

declare(strict_types=1);

$basePath = '/var/www/html/data/uploads';

// DB 연결
$dbHost = getenv('DB_HOST') ?: 'mariadb';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'ekge_church';
$dbUser = getenv('DB_USER') ?: 'ekge_user';
$dbPass = getenv('DB_PASSWORD') ?: '';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== 첨부파일 fileSize 업데이트 시작 ===\n\n";

// fileSize=0인 레코드 조회
$stmt = $pdo->query("SELECT id, filePath, fileType, imageWidth, imageHeight FROM attachments WHERE fileSize = 0");
$attachments = $stmt->fetchAll();

echo "대상: " . count($attachments) . "개 레코드 (fileSize=0)\n\n";

$updated = 0;
$missing = 0;
$errors = 0;

$updateStmt = $pdo->prepare(
    "UPDATE attachments SET fileSize = :fileSize, imageWidth = :width, imageHeight = :height WHERE id = :id"
);

foreach ($attachments as $att) {
    $fullPath = $basePath . '/' . $att['filePath'];

    if (!file_exists($fullPath)) {
        echo "  ✗ [{$att['id']}] 파일 없음: {$att['filePath']}\n";
        $missing++;
        continue;
    }

    $fileSize = filesize($fullPath);
    $width = $att['imageWidth'];
    $height = $att['imageHeight'];

    // 이미지이고 width/height가 없으면 업데이트
    if ($att['fileType'] === 'image' && (!$width || !$height)) {
        $info = @getimagesize($fullPath);
        if ($info) {
            $width = $info[0];
            $height = $info[1];
        }
    }

    $updateStmt->execute([
        'fileSize' => $fileSize,
        'width' => $width,
        'height' => $height,
        'id' => $att['id'],
    ]);

    $updated++;

    if ($updated % 200 === 0) {
        echo "  ... {$updated}개 업데이트됨\n";
    }
}

echo "\n=== 완료 ===\n";
echo "업데이트: {$updated}개\n";
echo "파일 없음: {$missing}개\n";
echo "오류: {$errors}개\n";

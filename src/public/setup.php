<?php
/**
 * EKGE 프로덕션 설치 스크립트
 *
 * 사용법: 브라우저에서 https://yoursite.com/setup.php?key=YOUR_SECRET_KEY
 *
 * 중요: 설치 완료 후 반드시 이 파일을 삭제하세요!
 */

// ============================================
// 설정 - 배포 전 수정하세요
// ============================================
define('SECRET_KEY', 'ekge-setup-2026'); // 접근 비밀키 (변경 필수!)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'ekge_church');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

// SQL 파일 경로 (public 기준 상대경로)
define('SCHEMA_FILE', __DIR__ . '/../data/setup/schema.sql');
define('DATA_FILE', __DIR__ . '/../data/setup/data.sql');

// 첨부파일 경로
define('UPLOADS_PATH', __DIR__ . '/../data/uploads');
// ============================================

// 비밀키 확인
if (($_GET['key'] ?? '') !== SECRET_KEY) {
    http_response_code(403);
    die('Access denied');
}

// 타임아웃 확장 (대용량 SQL)
set_time_limit(300);
ini_set('memory_limit', '256M');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>EKGE Setup</title>
    <style>
        body { font-family: monospace; max-width: 800px; margin: 40px auto; padding: 0 20px; background: #1a1a2e; color: #e0e0e0; }
        h1 { color: #00d2ff; }
        .step { margin: 20px 0; padding: 15px; background: #16213e; border-radius: 8px; }
        .ok { color: #00e676; }
        .err { color: #ff5252; }
        .warn { color: #ffab40; }
        .info { color: #82b1ff; }
        pre { background: #0f0f23; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .danger { background: #ff5252; color: white; padding: 15px; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>
<h1>EKGE Setup</h1>
<?php
flush();

$results = [];

// ============================================
// Step 1: DB 연결 테스트
// ============================================
echo '<div class="step"><strong>Step 1: DB 연결</strong><br>';
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo '<span class="ok">DB 연결 성공</span>';
    $results['db'] = true;
} catch (PDOException $e) {
    echo '<span class="err">DB 연결 실패: ' . htmlspecialchars($e->getMessage()) . '</span>';
    $results['db'] = false;
}
echo '</div>';
flush();

if (!$results['db']) {
    echo '<div class="danger">DB 연결 실패. 상단의 DB 설정을 확인하세요.</div></body></html>';
    exit;
}

// DB 생성 (없으면)
$pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `" . DB_NAME . "`");

// ============================================
// Step 2: 스키마 실행
// ============================================
echo '<div class="step"><strong>Step 2: 테이블 생성</strong><br>';
if (!file_exists(SCHEMA_FILE)) {
    echo '<span class="err">schema.sql 파일 없음: ' . htmlspecialchars(SCHEMA_FILE) . '</span>';
    $results['schema'] = false;
} else {
    try {
        $sql = file_get_contents(SCHEMA_FILE);
        $pdo->exec($sql);

        // 테이블 확인
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo '<span class="ok">테이블 생성 완료 (' . count($tables) . '개)</span><br>';
        echo '<span class="info">' . implode(', ', $tables) . '</span>';
        $results['schema'] = true;
    } catch (PDOException $e) {
        echo '<span class="err">스키마 실행 오류: ' . htmlspecialchars($e->getMessage()) . '</span>';
        $results['schema'] = false;
    }
}
echo '</div>';
flush();

// ============================================
// Step 3: 데이터 import
// ============================================
echo '<div class="step"><strong>Step 3: 데이터 import</strong><br>';
if (!file_exists(DATA_FILE)) {
    echo '<span class="err">data.sql 파일 없음: ' . htmlspecialchars(DATA_FILE) . '</span>';
    $results['data'] = false;
} else {
    try {
        $sql = file_get_contents(DATA_FILE);

        // 외래키 체크 임시 비활성화
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec($sql);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // 레코드 수 확인
        $counts = [];
        $checkTables = ['users', 'boards', 'posts', 'attachments', 'menus', 'banners', 'popups'];
        foreach ($checkTables as $t) {
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
                $counts[] = "{$t}: {$count}";
            } catch (PDOException $e) {
                // 테이블이 없을 수 있음
            }
        }
        echo '<span class="ok">데이터 import 완료</span><br>';
        echo '<span class="info">' . implode(' | ', $counts) . '</span>';
        $results['data'] = true;
    } catch (PDOException $e) {
        echo '<span class="err">데이터 import 오류: ' . htmlspecialchars($e->getMessage()) . '</span>';
        $results['data'] = false;
    }
}
echo '</div>';
flush();

// ============================================
// Step 4: 첨부파일 fileSize 업데이트
// ============================================
echo '<div class="step"><strong>Step 4: 첨부파일 fileSize 업데이트</strong><br>';

$upfilePath = UPLOADS_PATH . '/upfile';
if (!is_dir($upfilePath)) {
    echo '<span class="warn">upfile 폴더 없음 (' . htmlspecialchars($upfilePath) . '). 사진 파일을 업로드한 후 다시 실행하세요.</span>';
    $results['files'] = 'skipped';
} else {
    try {
        $stmt = $pdo->query("SELECT id, filePath, fileType, imageWidth, imageHeight FROM attachments WHERE fileSize = 0");
        $attachments = $stmt->fetchAll();

        $updated = 0;
        $missing = 0;
        $updateStmt = $pdo->prepare("UPDATE attachments SET fileSize = :size, imageWidth = :w, imageHeight = :h WHERE id = :id");

        foreach ($attachments as $att) {
            $fullPath = UPLOADS_PATH . '/' . $att['filePath'];
            if (!file_exists($fullPath)) {
                $missing++;
                continue;
            }

            $size = filesize($fullPath);
            $w = $att['imageWidth'];
            $h = $att['imageHeight'];

            if ($att['fileType'] === 'image' && (!$w || !$h)) {
                $info = @getimagesize($fullPath);
                if ($info) { $w = $info[0]; $h = $info[1]; }
            }

            $updateStmt->execute(['size' => $size, 'w' => $w, 'h' => $h, 'id' => $att['id']]);
            $updated++;
        }

        echo "<span class=\"ok\">업데이트: {$updated}개</span>";
        if ($missing > 0) {
            echo " | <span class=\"warn\">파일 없음: {$missing}개</span>";
        }
        $results['files'] = true;
    } catch (PDOException $e) {
        echo '<span class="err">오류: ' . htmlspecialchars($e->getMessage()) . '</span>';
        $results['files'] = false;
    }
}
echo '</div>';
flush();

// ============================================
// Step 5: 결과 요약
// ============================================
echo '<div class="step"><strong>결과 요약</strong><br><pre>';
foreach ($results as $key => $val) {
    $status = $val === true ? 'OK' : ($val === 'skipped' ? 'SKIPPED' : 'FAIL');
    echo str_pad($key, 10) . ": {$status}\n";
}
echo '</pre></div>';

echo '<div class="danger">';
echo '<strong>중요: 이 파일(setup.php)을 반드시 삭제하세요!</strong><br>';
echo '보안을 위해 설치 완료 후 FTP에서 setup.php를 삭제해야 합니다.';
echo '</div>';
?>
</body>
</html>

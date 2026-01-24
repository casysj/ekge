#!/usr/bin/env php
<?php
/**
 * HTML Entity 디코딩 마이그레이션 스크립트
 *
 * 레거시 데이터의 &lt; &gt; &amp; &nbsp; 등을 실제 HTML로 변환
 *
 * 사용법:
 *   docker compose exec php php /var/www/html/bin/migration/decode-html-entities.php [--dry-run]
 */

$config = require __DIR__ . '/migration.config.php';

class HtmlEntityDecoder
{
    private PDO $pdo;
    private bool $dryRun;
    private int $updatedCount = 0;
    private int $skippedCount = 0;

    public function __construct(array $dbConfig, bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
        $this->connectDatabase($dbConfig);
    }

    private function connectDatabase(array $dbConfig): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['dbname'],
            $dbConfig['charset']
        );

        $this->pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        echo "✓ 데이터베이스 연결 성공\n";
    }

    public function run(): void
    {
        echo "\n=== HTML Entity 디코딩 마이그레이션 ===\n";
        echo $this->dryRun ? "모드: DRY RUN (실제 변경 없음)\n\n" : "모드: 실제 실행\n\n";

        // 인코딩된 데이터가 있는 게시글 조회
        $stmt = $this->pdo->query("
            SELECT id, title, content
            FROM posts
            WHERE content LIKE '%&lt;%'
               OR content LIKE '%&gt;%'
               OR content LIKE '%&amp;%'
               OR title LIKE '%&lt;%'
               OR title LIKE '%&gt;%'
               OR title LIKE '%&amp;%'
        ");

        $posts = $stmt->fetchAll();
        $totalCount = count($posts);

        echo "처리 대상: {$totalCount}개 게시글\n\n";

        if ($totalCount === 0) {
            echo "디코딩이 필요한 게시글이 없습니다.\n";
            return;
        }

        foreach ($posts as $post) {
            $this->processPost($post);
        }

        echo "\n=== 완료 ===\n";
        echo "업데이트: {$this->updatedCount}개\n";
        echo "스킵: {$this->skippedCount}개\n";
    }

    private function processPost(array $post): void
    {
        $id = $post['id'];
        $originalTitle = $post['title'];
        $originalContent = $post['content'];

        // HTML entity 디코딩
        $decodedTitle = html_entity_decode($originalTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decodedContent = html_entity_decode($originalContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 변경사항 확인
        $titleChanged = $decodedTitle !== $originalTitle;
        $contentChanged = $decodedContent !== $originalContent;

        if (!$titleChanged && !$contentChanged) {
            $this->skippedCount++;
            return;
        }

        // 변경 내용 미리보기 (처음 5개만)
        if ($this->updatedCount < 5) {
            echo "---\n";
            echo "ID: {$id}\n";
            if ($titleChanged) {
                echo "제목 (전): " . mb_substr($originalTitle, 0, 50) . "\n";
                echo "제목 (후): " . mb_substr($decodedTitle, 0, 50) . "\n";
            }
            if ($contentChanged) {
                echo "내용 (전): " . mb_substr($originalContent, 0, 80) . "...\n";
                echo "내용 (후): " . mb_substr($decodedContent, 0, 80) . "...\n";
            }
        }

        if (!$this->dryRun) {
            $updateStmt = $this->pdo->prepare("
                UPDATE posts SET title = ?, content = ? WHERE id = ?
            ");
            $updateStmt->execute([$decodedTitle, $decodedContent, $id]);
        }

        $this->updatedCount++;
    }

    public function backup(): void
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = __DIR__ . "/backup_posts_{$timestamp}.sql";

        echo "백업 생성 중: {$backupFile}\n";

        // 백업 쿼리 생성
        $stmt = $this->pdo->query("SELECT id, title, content FROM posts");
        $posts = $stmt->fetchAll();

        $sql = "-- Posts backup before HTML entity decoding\n";
        $sql .= "-- Created: {$timestamp}\n\n";

        foreach ($posts as $post) {
            $id = $post['id'];
            $title = addslashes($post['title']);
            $content = addslashes($post['content']);
            $sql .= "UPDATE posts SET title='{$title}', content='{$content}' WHERE id={$id};\n";
        }

        file_put_contents($backupFile, $sql);
        echo "✓ 백업 완료: " . count($posts) . "개 레코드\n";
    }
}

// 실행
$dryRun = in_array('--dry-run', $argv);
$backup = in_array('--backup', $argv);

$decoder = new HtmlEntityDecoder($config['target_db'], $dryRun);

if ($backup || !$dryRun) {
    $decoder->backup();
}

$decoder->run();

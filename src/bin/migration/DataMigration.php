<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Application\Entity\Board;
use Application\Entity\Post;
use Application\Entity\Attachment;
use Application\Entity\Menu;
use Application\Entity\Banner;

/**
 * 데이터 마이그레이션 클래스
 *
 * 구 DB에서 새 DB로 데이터를 마이그레이션합니다.
 * 언제든지 재실행 가능하도록 설계되었습니다.
 */
class DataMigration
{
    private EntityManager $em;
    private PDO $sourceDb;
    private array $config;
    private array $stats = [];

    public function __construct(EntityManager $em, array $config)
    {
        $this->em = $em;
        $this->config = $config;
        $this->connectSourceDb();
    }

    /**
     * 구 DB 연결
     */
    private function connectSourceDb(): void
    {
        $source = $this->config['source_db'];
        $dsn = "mysql:host={$source['host']};port={$source['port']};dbname={$source['dbname']};charset={$source['charset']}";

        $this->sourceDb = new PDO($dsn, $source['user'], $source['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        echo "✓ 구 DB 연결 성공\n";
    }

    /**
     * 전체 마이그레이션 실행
     */
    public function run(): void
    {
        echo "========================================\n";
        echo "  EKGE 데이터 마이그레이션 시작\n";
        echo "========================================\n\n";

        $startTime = microtime(true);

        try {
            // 타겟 DB 초기화 (옵션)
            if ($this->config['options']['clear_target']) {
                $this->clearTargetDatabase();
            }

            // 게시판 마이그레이션
            if ($this->config['options']['migrate_boards']) {
                $this->migrateBoards();
            }

            // 게시글 마이그레이션
            if ($this->config['options']['migrate_posts']) {
                $this->migratePosts();
            }

            // 첨부파일 마이그레이션
            if ($this->config['options']['migrate_attachments']) {
                $this->migrateAttachments();
            }

            // 메뉴 마이그레이션
            if ($this->config['options']['migrate_menus']) {
                $this->migrateMenus();
            }

            // 배너 마이그레이션
            if ($this->config['options']['migrate_banners']) {
                $this->migrateBanners();
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            echo "\n========================================\n";
            echo "  마이그레이션 완료! (소요시간: {$duration}초)\n";
            echo "========================================\n\n";

            $this->printStats();

        } catch (Exception $e) {
            echo "\n❌ 오류 발생: " . $e->getMessage() . "\n";
            echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
        }
    }

    /**
     * 타겟 DB 초기화
     */
    private function clearTargetDatabase(): void
    {
        echo "⚠️  타겟 DB 초기화 중...\n";

        $tables = ['attachments', 'posts', 'boards', 'menuContents', 'menus', 'banners'];

        foreach ($tables as $table) {
            $this->em->getConnection()->executeStatement("SET FOREIGN_KEY_CHECKS = 0");
            $this->em->getConnection()->executeStatement("TRUNCATE TABLE {$table}");
            $this->em->getConnection()->executeStatement("SET FOREIGN_KEY_CHECKS = 1");
        }

        echo "✓ 타겟 DB 초기화 완료\n\n";
    }

    /**
     * 게시판 마이그레이션
     */
    private function migrateBoards(): void
    {
        echo "📋 게시판 마이그레이션 중...\n";

        $siteCode = $this->config['site_code'];
        $sql = "SELECT * FROM BOARD_MST WHERE site_code = :site_code ORDER BY B_ID";
        $stmt = $this->sourceDb->prepare($sql);
        $stmt->execute(['site_code' => $siteCode]);
        $oldBoards = $stmt->fetchAll();

        $count = 0;

        foreach ($oldBoards as $oldBoard) {
            $bId = $oldBoard['B_ID'];
            $boardCode = $this->config['board_mapping'][$bId] ?? null;

            if (!$boardCode) {
                echo "  ⊘ B_ID={$bId} ({$oldBoard['B_TITLE']}): 매핑 없음, 건너뜀\n";
                continue;
            }

            // 이미 존재하는지 확인
            $existing = $this->em->getRepository(Board::class)->findOneBy(['boardCode' => $boardCode]);

            if ($existing) {
                echo "  → {$boardCode}: 이미 존재함, 건너뜀\n";
                continue;
            }

            // 게시판 타입 변환
            $boardType = $this->convertBoardType($oldBoard['B_TYPE']);

            $board = new Board();
            $board->setBoardCode($boardCode)
                  ->setBoardName($oldBoard['B_TITLE'])
                  ->setBoardType($boardType)
                  ->setDisplayOrder((int) $oldBoard['SORT_SEQ'])
                  ->setIsVisible(true)
                  ->setPostsPerPage(20)
                  ->setAllowAttachment(true)
                  ->setRequireAuth(false);

            if (!$this->config['options']['dry_run']) {
                $this->em->persist($board);
                $this->em->flush();
            }

            echo "  ✓ {$boardCode}: {$oldBoard['B_TITLE']}\n";
            $count++;
        }

        $this->stats['boards'] = $count;
        echo "  완료: {$count}개 게시판\n\n";
    }

    /**
     * 게시글 마이그레이션
     */
    private function migratePosts(): void
    {
        echo "📝 게시글 마이그레이션 중...\n";

        $siteCode = $this->config['site_code'];
        $sql = "SELECT bd.*, bm.B_ID
                FROM BOARD_DTL bd
                JOIN BOARD_MST bm ON bd.B_ID = bm.B_ID AND bd.SITE_CODE = bm.SITE_CODE
                WHERE bm.site_code = :site_code
                ORDER BY bd.REG_DATE";
        $stmt = $this->sourceDb->prepare($sql);
        $stmt->execute(['site_code' => $siteCode]);
        $oldPosts = $stmt->fetchAll();

        $count = 0;
        $skipped = 0;

        foreach ($oldPosts as $oldPost) {
            $bId = $oldPost['B_ID'];
            $boardCode = $this->config['board_mapping'][$bId] ?? null;

            if (!$boardCode) {
                $skipped++;
                continue;
            }

            $board = $this->em->getRepository(Board::class)->findOneBy(['boardCode' => $boardCode]);

            if (!$board) {
                echo "  ⚠️  게시판 없음: {$boardCode}\n";
                $skipped++;
                continue;
            }

            $post = new Post();
            $post->setBoard($board)
                 ->setTitle($oldPost['TITLE'])
                 ->setContent($oldPost['CONT'])
                 ->setAuthorName($oldPost['REG_USER'] ?? '관리자')
                 ->setViewCount((int) ($oldPost['HIT'] ?? 0))
                 ->setIsNotice(false)
                 ->setIsPublished(true)
                 ->setPublishedAt(new DateTime($oldPost['REG_DATE']));

            if (!$this->config['options']['dry_run']) {
                $this->em->persist($post);

                // 메모리 관리
                if ($count % 100 === 0) {
                    $this->em->flush();
                    $this->em->clear();
                    echo "  ... {$count}개 처리됨\n";
                }
            }

            $count++;
        }

        if (!$this->config['options']['dry_run']) {
            $this->em->flush();
            $this->em->clear();
        }

        $this->stats['posts'] = $count;
        $this->stats['posts_skipped'] = $skipped;
        echo "  완료: {$count}개 게시글 ({$skipped}개 건너뜀)\n\n";
    }

    /**
     * 첨부파일 마이그레이션
     */
    private function migrateAttachments(): void
    {
        echo "📎 첨부파일 마이그레이션 중...\n";

        // 구현 예시 (실제 파일 경로 확인 필요)
        echo "  ℹ️  첨부파일 마이그레이션은 수동으로 진행하세요.\n";
        echo "  → 구 파일 경로: {$this->config['source_upload_path']}\n";
        echo "  → 새 파일 경로: {$this->config['target_upload_path']}\n\n";

        $this->stats['attachments'] = 0;
    }

    /**
     * 메뉴 마이그레이션
     */
    private function migrateMenus(): void
    {
        echo "🗂️  메뉴 마이그레이션 중...\n";
        echo "  ℹ️  메뉴는 관리자 페이지에서 수동으로 설정하세요.\n\n";

        $this->stats['menus'] = 0;
    }

    /**
     * 배너 마이그레이션
     */
    private function migrateBanners(): void
    {
        echo "🖼️  배너 마이그레이션 중...\n";

        $siteCode = $this->config['site_code'];
        $sql = "SELECT * FROM MAIN_BANNER WHERE SITE_CODE = :site_code ORDER BY ORDER_NUM";
        $stmt = $this->sourceDb->prepare($sql);
        $stmt->execute(['site_code' => $siteCode]);
        $oldBanners = $stmt->fetchAll();

        $count = 0;

        foreach ($oldBanners as $oldBanner) {
            $banner = new Banner();
            $banner->setTitle($oldBanner['TITLE'] ?? '배너')
                   ->setImagePath($oldBanner['IMG_PATH'])
                   ->setLinkUrl(null)  // 구 DB에 LINK_URL 없음
                   ->setDisplayOrder((int) ($oldBanner['ORDER_NUM'] ?? 0))
                   ->setIsActive($oldBanner['USE_YN'] === 'Y');

            if (!$this->config['options']['dry_run']) {
                $this->em->persist($banner);
            }

            $count++;
        }

        if (!$this->config['options']['dry_run']) {
            $this->em->flush();
        }

        $this->stats['banners'] = $count;
        echo "  완료: {$count}개 배너\n\n";
    }

    /**
     * 게시판 타입 변환
     */
    private function convertBoardType(string $oldType): string
    {
        $typeMap = [
            'N' => 'notice',    // 공지형
            'G' => 'gallery',   // 갤러리형
            'P' => 'general',   // 일반형
            'F' => 'qna',       // Q&A
            'C' => 'category',  // 카테고리형
        ];

        return $typeMap[$oldType] ?? 'general';
    }

    /**
     * 통계 출력
     */
    private function printStats(): void
    {
        echo "통계:\n";
        foreach ($this->stats as $key => $value) {
            echo "  - {$key}: {$value}\n";
        }
    }
}

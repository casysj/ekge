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

        $siteCode = $this->config['site_code'];

        // 구 첨부파일 조회 (게시판 매핑된 것만)
        $boardIds = array_keys($this->config['board_mapping']);
        $boardIdList = "'" . implode("','", $boardIds) . "'";

        $sql = "SELECT attc.*, dtl.TITLE, dtl.REG_DATE, dtl.SEQ as POST_SEQ
                FROM BOARD_ATTC attc
                JOIN BOARD_DTL dtl ON attc.B_SEQ = dtl.SEQ
                    AND attc.B_ID = dtl.B_ID
                    AND attc.SITE_CODE = dtl.SITE_CODE
                WHERE attc.SITE_CODE = :site_code
                AND attc.B_ID IN ({$boardIdList})
                ORDER BY attc.ATTC_SEQ";

        $stmt = $this->sourceDb->prepare($sql);
        $stmt->execute(['site_code' => $siteCode]);
        $oldAttachments = $stmt->fetchAll();

        $count = 0;
        $skipped = 0;

        echo "  → 총 " . count($oldAttachments) . "개 첨부파일 처리 시작\n";

        foreach ($oldAttachments as $oldAttc) {
            $bId = $oldAttc['B_ID'];
            $boardCode = $this->config['board_mapping'][$bId] ?? null;

            if (!$boardCode) {
                $skipped++;
                continue;
            }

            // 게시글 찾기 (제목 + 날짜로 매칭)
            $title = $oldAttc['TITLE'];
            $regDate = $oldAttc['REG_DATE'];

            $dql = "SELECT p FROM Application\Entity\Post p
                    JOIN p.board b
                    WHERE b.boardCode = :boardCode
                    AND p.title = :title
                    AND p.publishedAt = :publishedAt";

            $query = $this->em->createQuery($dql);
            $query->setParameter('boardCode', $boardCode);
            $query->setParameter('title', $title);
            $query->setParameter('publishedAt', new \DateTime($regDate));

            $posts = $query->getResult();

            if (count($posts) === 0) {
                echo "  ⚠️  게시글 못찾음: {$title} ({$regDate})\n";
                $skipped++;
                continue;
            }

            $post = $posts[0]; // 첫 번째 결과 사용

            // 파일명에서 날짜 추출 (20160211_025354_2.jpg → 2016/20160211)
            $fileName = $oldAttc['TRS_NM'];
            $dateStr = substr($fileName, 0, 8); // YYYYMMDD
            $year = substr($dateStr, 0, 4);

            // 레거시 파일 경로 생성
            $legacyPath = "upfile/{$year}/{$dateStr}/{$fileName}";

            // 파일 확장자에서 타입 및 MIME 유추
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileType = $this->getFileType($ext);
            $mimeType = $this->getMimeType($ext);

            // 첨부파일 엔티티 생성
            $attachment = new Attachment();
            $attachment->setPost($post)
                       ->setOriginalName($oldAttc['ORG_NM'])
                       ->setSavedName($fileName)
                       ->setFilePath($legacyPath)
                       ->setFileSize(0) // 실제 파일 없으므로 0
                       ->setMimeType($mimeType)
                       ->setFileType($fileType)
                       ->setImageWidth($oldAttc['IMG_WD'] ? (int)$oldAttc['IMG_WD'] : null)
                       ->setImageHeight($oldAttc['IMG_HT'] ? (int)$oldAttc['IMG_HT'] : null)
                       ->setDisplayOrder($count);

            if (!$this->config['options']['dry_run']) {
                $this->em->persist($attachment);

                // 메모리 관리
                if ($count % 100 === 0 && $count > 0) {
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

        $this->stats['attachments'] = $count;
        $this->stats['attachments_skipped'] = $skipped;
        echo "  완료: {$count}개 첨부파일 ({$skipped}개 건너뜀)\n\n";

        if (!$this->config['options']['dry_run']) {
            echo "  ℹ️  첨부파일 메타데이터 마이그레이션 완료\n";
            echo "  ℹ️  실제 파일은 아직 복사되지 않았습니다 (fileSize=0)\n";
            echo "  ℹ️  파일 경로: upfile/YYYY/YYYYMMDD/*.jpg 형식으로 저장됨\n\n";
        }
    }

    /**
     * 파일 확장자로 파일 타입 결정
     */
    private function getFileType(string $ext): string
    {
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $docExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        $videoExts = ['mp4', 'avi', 'mov', 'wmv', 'flv'];
        $audioExts = ['mp3', 'wav', 'ogg', 'flac'];

        if (in_array($ext, $imageExts)) return 'image';
        if (in_array($ext, $docExts)) return 'document';
        if (in_array($ext, $videoExts)) return 'video';
        if (in_array($ext, $audioExts)) return 'audio';

        return 'other';
    }

    /**
     * 파일 확장자로 MIME 타입 결정
     */
    private function getMimeType(string $ext): string
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
        ];

        return $mimeTypes[$ext] ?? 'application/octet-stream';
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

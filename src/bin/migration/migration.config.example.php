<?php

declare(strict_types=1);

/**
 * 데이터 마이그레이션 설정
 *
 * 이 파일을 복사하여 migration.config.php로 저장하고
 * 실제 값으로 수정하세요.
 */

return [
    // 구 DB 연결 정보 (마이그레이션 소스)
    'source_db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'dbname'   => 'Backup_ekge',  // 구 DB 이름
        'user'     => 'root',
        'password' => 'your_password',
        'charset'  => 'utf8mb4',
    ],

    // 새 DB 연결 정보 (마이그레이션 대상)
    'target_db' => [
        'host'     => 'mariadb',  // Docker 컨테이너명 또는 localhost
        'port'     => 3306,
        'dbname'   => 'ekge_church',
        'user'     => 'ekge_user',
        'password' => 'ekge_password_change_this',
        'charset'  => 'utf8mb4',
    ],

    // 구 파일 저장 경로
    'source_upload_path' => '/path/to/old/files',  // 구 홈페이지 파일 경로

    // 새 파일 저장 경로
    'target_upload_path' => __DIR__ . '/../../public/uploads',

    // 사이트 코드 (구 DB에서 필터링용)
    'site_code' => 'essen',

    // 마이그레이션 옵션
    'options' => [
        'dry_run' => false,  // true면 실제 저장하지 않고 테스트만
        'clear_target' => false,  // true면 타겟 DB를 먼저 비움
        'migrate_files' => true,  // 파일 복사 여부
        'migrate_boards' => true,
        'migrate_posts' => true,
        'migrate_attachments' => true,
        'migrate_menus' => true,
        'migrate_banners' => true,
        'migrate_users' => false,  // 관리자는 수동 생성 권장
    ],

    // 게시판 매핑 (구 B_ID -> 새 boardCode)
    'board_mapping' => [
        '20' => 'sermon',      // 설교말씀 및 듣기
        '23' => 'notice',      // 교회소식
        '24' => 'weekly',      // 주보
        '25' => 'free',        // 자유게시판
        '26' => 'gallery',     // 교회앨범
        // 필요시 추가...
    ],

    // 기본 관리자 ID (게시글 user_id 매핑용)
    'default_admin_id' => 1,
];

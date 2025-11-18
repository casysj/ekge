-- ============================================
-- EKGE 에센 한인교회 데이터베이스 스키마
-- ============================================
-- 생성일: 2024-11-18
-- 인코딩: UTF8MB4
-- 엔진: InnoDB
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET time_zone = '+01:00';

-- ============================================
-- 1. users (관리자 사용자)
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '사용자명',
  `password` VARCHAR(255) NOT NULL COMMENT '비밀번호 (해시)',
  `email` VARCHAR(100) NOT NULL UNIQUE COMMENT '이메일',
  `displayName` VARCHAR(100) NOT NULL COMMENT '표시 이름',
  `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'editor' COMMENT '권한',
  `isActive` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '활성화 여부',
  `lastLoginAt` DATETIME NULL COMMENT '마지막 로그인',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='관리자 사용자';

-- ============================================
-- 2. boards (게시판 종류)
-- ============================================
CREATE TABLE IF NOT EXISTS `boards` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `boardCode` VARCHAR(50) NOT NULL UNIQUE COMMENT '게시판 코드 (sermon, weekly, album 등)',
  `boardName` VARCHAR(100) NOT NULL COMMENT '게시판 이름',
  `boardType` ENUM('notice', 'gallery', 'general', 'qna', 'category') NOT NULL DEFAULT 'general' COMMENT '게시판 타입',
  `description` TEXT NULL COMMENT '게시판 설명',
  `displayOrder` INT NOT NULL DEFAULT 0 COMMENT '표시 순서',
  `isVisible` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '노출 여부',
  `postsPerPage` INT NOT NULL DEFAULT 15 COMMENT '페이지당 게시글 수',
  `allowAttachment` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '첨부파일 허용',
  `requireAuth` BOOLEAN NOT NULL DEFAULT FALSE COMMENT '인증 필요 여부',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  INDEX `idx_boardCode` (`boardCode`),
  INDEX `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 종류';

-- ============================================
-- 3. posts (게시글)
-- ============================================
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `board_id` INT UNSIGNED NOT NULL COMMENT '게시판 ID',
  `title` VARCHAR(200) NOT NULL COMMENT '제목',
  `content` LONGTEXT NOT NULL COMMENT '내용',
  `authorName` VARCHAR(100) NOT NULL DEFAULT '관리자' COMMENT '작성자명',
  `user_id` INT UNSIGNED NULL COMMENT '작성자 ID (관리자)',
  `viewCount` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '조회수',
  `isNotice` BOOLEAN NOT NULL DEFAULT FALSE COMMENT '공지사항 여부',
  `isPublished` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '게시 여부',
  `publishedAt` DATETIME NULL COMMENT '게시 시작일',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  FOREIGN KEY (`board_id`) REFERENCES `boards`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_board_id` (`board_id`),
  INDEX `idx_isNotice` (`isNotice`),
  INDEX `idx_isPublished` (`isPublished`),
  INDEX `idx_publishedAt` (`publishedAt`),
  INDEX `idx_createdAt` (`createdAt`),
  FULLTEXT INDEX `ft_title_content` (`title`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시글';

-- ============================================
-- 4. attachments (첨부파일)
-- ============================================
CREATE TABLE IF NOT EXISTS `attachments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT UNSIGNED NOT NULL COMMENT '게시글 ID',
  `originalName` VARCHAR(255) NOT NULL COMMENT '원본 파일명',
  `savedName` VARCHAR(255) NOT NULL COMMENT '저장된 파일명',
  `filePath` VARCHAR(500) NOT NULL COMMENT '파일 경로',
  `fileSize` INT UNSIGNED NOT NULL COMMENT '파일 크기 (bytes)',
  `mimeType` VARCHAR(100) NOT NULL COMMENT 'MIME 타입',
  `fileType` ENUM('image', 'audio', 'video', 'document', 'other') NOT NULL DEFAULT 'other' COMMENT '파일 타입',
  `imageWidth` INT UNSIGNED NULL COMMENT '이미지 가로 (이미지인 경우)',
  `imageHeight` INT UNSIGNED NULL COMMENT '이미지 세로 (이미지인 경우)',
  `downloadCount` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '다운로드 수',
  `displayOrder` INT NOT NULL DEFAULT 0 COMMENT '표시 순서',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  INDEX `idx_post_id` (`post_id`),
  INDEX `idx_fileType` (`fileType`),
  INDEX `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='첨부파일';

-- ============================================
-- 5. menus (메뉴 구조)
-- ============================================
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_id` INT UNSIGNED NULL COMMENT '부모 메뉴 ID',
  `menuName` VARCHAR(100) NOT NULL COMMENT '메뉴명',
  `menuType` ENUM('board', 'html', 'external') NOT NULL DEFAULT 'html' COMMENT '메뉴 타입',
  `board_id` INT UNSIGNED NULL COMMENT '게시판 ID (board 타입인 경우)',
  `externalUrl` VARCHAR(500) NULL COMMENT '외부 링크 (external 타입인 경우)',
  `displayOrder` INT NOT NULL DEFAULT 0 COMMENT '표시 순서',
  `depth` TINYINT NOT NULL DEFAULT 1 COMMENT '깊이 (1: 대메뉴, 2: 중메뉴, 3: 소메뉴)',
  `isVisible` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '노출 여부',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  FOREIGN KEY (`parent_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`board_id`) REFERENCES `boards`(`id`) ON DELETE SET NULL,
  INDEX `idx_parent_id` (`parent_id`),
  INDEX `idx_displayOrder` (`displayOrder`),
  INDEX `idx_depth` (`depth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='메뉴 구조';

-- ============================================
-- 6. menuContents (메뉴 HTML 컨텐츠)
-- ============================================
CREATE TABLE IF NOT EXISTS `menuContents` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `menu_id` INT UNSIGNED NOT NULL UNIQUE COMMENT '메뉴 ID',
  `content` LONGTEXT NOT NULL COMMENT 'HTML 컨텐츠',
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  FOREIGN KEY (`menu_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE,
  INDEX `idx_menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='메뉴 HTML 컨텐츠';

-- ============================================
-- 7. banners (메인 배너)
-- ============================================
CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL COMMENT '배너 제목',
  `description` TEXT NULL COMMENT '배너 설명',
  `imagePath` VARCHAR(500) NOT NULL COMMENT '이미지 경로',
  `linkUrl` VARCHAR(500) NULL COMMENT '링크 URL',
  `displayOrder` INT NOT NULL DEFAULT 0 COMMENT '표시 순서',
  `isActive` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '활성화 여부',
  `startDate` DATE NULL COMMENT '시작일',
  `endDate` DATE NULL COMMENT '종료일',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  INDEX `idx_displayOrder` (`displayOrder`),
  INDEX `idx_isActive` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='메인 배너';

-- ============================================
-- 8. settings (사이트 설정)
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `settingKey` VARCHAR(100) NOT NULL UNIQUE COMMENT '설정 키',
  `settingValue` TEXT NULL COMMENT '설정 값',
  `description` VARCHAR(255) NULL COMMENT '설명',
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  INDEX `idx_settingKey` (`settingKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사이트 설정';

-- ============================================
-- 초기 데이터 삽입
-- ============================================

-- 기본 관리자 계정 (비밀번호: admin123 - 실제 사용시 변경 필요)
INSERT INTO `users` (`username`, `password`, `email`, `displayName`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ekge-church.de', '관리자', 'admin');

-- 기본 게시판
INSERT INTO `boards` (`boardCode`, `boardName`, `boardType`, `description`, `displayOrder`, `allowAttachment`) VALUES
('sermon', '설교말씀', 'category', '주일 설교 말씀과 음성 파일', 1, TRUE),
('weekly', '주보', 'notice', '주간 교회 소식지', 2, TRUE),
('notice', '교회소식', 'notice', '교회 공지사항', 3, TRUE),
('gallery', '교회앨범', 'gallery', '교회 행사 사진', 4, TRUE),
('free', '자유게시판', 'general', '자유로운 소통 공간', 5, TRUE);

-- 기본 사이트 설정
INSERT INTO `settings` (`settingKey`, `settingValue`, `description`) VALUES
('site_name', '에센 한인교회', '사이트 이름'),
('site_description', '독일 에센 소재 한인교회', '사이트 설명'),
('contact_email', 'info@ekge-church.de', '연락처 이메일'),
('address', 'Essen, Germany', '교회 주소'),
('service_time', '매주 일요일 오후 2시', '예배 시간'),
('timezone', 'Europe/Berlin', '타임존');

-- ============================================
-- 마이그레이션 로그 (이미 존재하는 테이블)
-- ============================================
-- migration_log 테이블은 init.sql에서 이미 생성됨

-- 마이그레이션 기록
INSERT INTO `migration_log` (`migration_name`) VALUES
('001_create_initial_schema');
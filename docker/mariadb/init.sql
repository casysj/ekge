-- 데이터베이스 설정
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 타임존 설정
SET time_zone = '+01:00';

-- 초기 테이블 생성은 추후 마이그레이션에서 진행
CREATE TABLE IF NOT EXISTS `migration_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `migration_name` VARCHAR(255) NOT NULL,
  `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `migration_name` (`migration_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

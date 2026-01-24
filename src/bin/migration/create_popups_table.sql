-- 팝업 테이블 생성
-- 실행: docker-compose exec mariadb mysql -u root -p ekge_church < src/bin/migration/create_popups_table.sql

CREATE TABLE IF NOT EXISTS popups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    startDate DATETIME NULL,
    endDate DATETIME NULL,
    isActive TINYINT(1) NOT NULL DEFAULT 0,
    createdAt DATETIME NOT NULL,
    updatedAt DATETIME NOT NULL,
    INDEX idx_active (isActive),
    INDEX idx_date_range (startDate, endDate, isActive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 샘플 데이터 (옵션)
-- INSERT INTO popups (title, content, startDate, endDate, isActive, createdAt, updatedAt)
-- VALUES (
--     '2026년 새해 예배 안내',
--     '<p>새해 첫 예배를 드립니다.</p><p>1월 5일 오전 11시, 본당에서 만나요!</p>',
--     '2026-01-01 00:00:00',
--     '2026-01-05 23:59:59',
--     1,
--     NOW(),
--     NOW()
-- );

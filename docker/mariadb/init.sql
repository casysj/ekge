-- 에센 한인교회 데이터베이스 초기 설정
-- UTF-8 문자셋으로 설정하여 한글 처리 지원

-- 데이터베이스가 존재하지 않을 경우 생성
CREATE DATABASE IF NOT EXISTS essen_church 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- 사용할 데이터베이스 선택
USE essen_church;

-- 사용자 권한 설정 (이미 docker-compose에서 생성됨)
-- GRANT ALL PRIVILEGES ON essen_church.* TO 'church_user'@'%';
-- FLUSH PRIVILEGES;

-- 테스트용 테이블 생성 (나중에 Laravel 마이그레이션으로 대체될 예정)
CREATE TABLE IF NOT EXISTS test_connection (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 테스트 데이터 삽입
INSERT INTO test_connection (message) VALUES 
('에센 한인교회 데이터베이스 연결 테스트'),
('Docker 환경 구축 완료'),
('한글 문자셋 테스트: 안녕하세요!');

-- 한글 테스트를 위한 샘플 교회 정보 테이블
CREATE TABLE IF NOT EXISTS church_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL COMMENT '제목',
    content TEXT NOT NULL COMMENT '내용',
    category ENUM('소개', '인사말', '연혁', '예배안내') NOT NULL COMMENT '카테고리',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='교회 기본 정보';

-- 샘플 데이터
INSERT INTO church_info (title, content, category) VALUES 
('에센 한인교회', '독일 에센 지역의 한인들을 위한 교회입니다.', '소개'),
('담임목사 인사말', '에센 한인교회에 오신 것을 환영합니다.', '인사말'),
('교회 연혁', '2020년 설립되어 현재에 이르고 있습니다.', '연혁'),
('주일예배', '매주 일요일 오후 2시 예배를 드립니다.', '예배안내');
# 데이터 마이그레이션 실행 가이드

## 📋 개요

이 가이드는 구 홈페이지 데이터를 새 홈페이지로 마이그레이션하는 방법을 설명합니다.
마이그레이션 도구는 **언제든지 재실행 가능**하도록 설계되었습니다.

---

## 🎯 마이그레이션 대상

### 데이터
- ✅ 게시판 설정 (BOARD_MST → boards)
- ✅ 게시글 (BOARD_DTL → posts)
- ✅ 첨부파일 메타데이터 (BOARD_ATTC → attachments)
- ⚠️ 메뉴 (수동 설정 권장)
- ⚠️ 배너 (필요시)
- ❌ 회원 정보 (새로 시작 권장)
- ❌ 댓글 (현재 미구현)

### 파일
- 🖼️ 이미지 (주보, 갤러리) - 약 1,800개
- 🎵 오디오 (설교 MP3) - 약 50개
- 📄 문서 (PDF, DOCX) - 약 50개
- **총 용량**: 약 5.5GB

---

## 📝 사전 준비

### 1. 구 DB 백업 확인

```bash
# 백업 파일 위치
ls -lh /mnt/user-data/uploads/20250727_ekge_DB_Backup.sql

# 또는 최신 백업 준비
mysqldump -u root -p Backup_ekge > backup_$(date +%Y%m%d).sql
```

### 2. 구 파일 경로 확인

```bash
# 구 홈페이지 파일 경로 확인
ls -lh /path/to/old/ekge/uploads/

# 파일 개수 확인
find /path/to/old/ekge/uploads/ -type f | wc -l
```

### 3. 디스크 용량 확인

```bash
# 새 서버 여유 공간 확인 (최소 10GB 권장)
df -h

# 업로드 디렉토리 확인
mkdir -p ~/webapps/ekge/src/public/uploads
df -h ~/webapps/ekge/src/public/uploads
```

---

## 🔧 설정

### 1. 설정 파일 생성

```bash
cd ~/webapps/ekge/src/bin/migration

# 예제 파일 복사
cp migration.config.example.php migration.config.php

# 설정 파일 수정
nano migration.config.php
```

### 2. 설정 내용 수정

```php
<?php
return [
    // 구 DB 연결 정보
    'source_db' => [
        'host'     => 'localhost',      # 구 DB 호스트
        'port'     => 3306,
        'dbname'   => 'Backup_ekge',    # 구 DB 이름
        'user'     => 'root',
        'password' => 'your_password',  # 👈 수정 필요!
        'charset'  => 'utf8mb4',
    ],

    // 새 DB 연결 정보
    'target_db' => [
        'host'     => 'mariadb',        # Docker: mariadb, Local: localhost
        'port'     => 3306,
        'dbname'   => 'ekge_church',
        'user'     => 'ekge_user',
        'password' => 'ekge_password_change_this',  # 👈 수정 필요!
        'charset'  => 'utf8mb4',
    ],

    // 파일 경로
    'source_upload_path' => '/path/to/old/files',  # 👈 수정 필요!
    'target_upload_path' => __DIR__ . '/../../public/uploads',

    // 게시판 매핑
    'board_mapping' => [
        '20' => 'sermon',   // 설교말씀
        '23' => 'notice',   // 교회소식
        '24' => 'weekly',   // 주보
        '25' => 'free',     // 자유게시판
        '26' => 'gallery',  // 교회앨범
        // 필요한 게시판 추가...
    ],
];
```

### 3. 구 DB 임포트 (필요시)

```bash
# 구 DB가 현재 서버에 없는 경우
mysql -u root -p

CREATE DATABASE IF NOT EXISTS Backup_ekge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# SQL 파일 임포트
mysql -u root -p Backup_ekge < 20250727_ekge_DB_Backup.sql
```

---

## 🚀 실행

### 테스트 실행 (Dry-run)

```bash
cd ~/webapps/ekge/src/bin/migration

# 실제 저장하지 않고 테스트만
php migrate.php --dry-run
```

**출력 예시:**
```
========================================
  EKGE 데이터 마이그레이션 시작
========================================

✓ 구 DB 연결 성공
✓ Doctrine EntityManager 초기화 완료

📋 게시판 마이그레이션 중...
  ✓ sermon: 설교말씀 및 듣기
  ✓ notice: 교회소식
  ✓ weekly: 주보
  ✓ free: 자유게시판
  ✓ gallery: 교회앨범
  완료: 5개 게시판

📝 게시글 마이그레이션 중...
  ... 100개 처리됨
  ... 200개 처리됨
  완료: 532개 게시글 (45개 건너뜀)

========================================
  마이그레이션 완료! (소요시간: 12.3초)
========================================

통계:
  - boards: 5
  - posts: 532
  - posts_skipped: 45
```

### 실제 마이그레이션

```bash
# 1. 백업 먼저!
mysqldump -u ekge_user -p ekge_church > backup_before_migration.sql

# 2. 마이그레이션 실행
php migrate.php

# 3. 타겟 DB 초기화 후 실행 (주의!)
php migrate.php --clear
```

### 도움말

```bash
php migrate.php --help
```

---

## 📂 파일 마이그레이션

### 1. 파일 복사 스크립트

```bash
#!/bin/bash
# file_migration.sh

SOURCE_DIR="/path/to/old/ekge/uploads"
TARGET_DIR="~/webapps/ekge/src/public/uploads/migrated"

echo "파일 마이그레이션 시작..."

# 디렉토리 생성
mkdir -p "$TARGET_DIR"

# 파일 복사 (progress 표시)
rsync -av --progress \
  --include="*.jpg" \
  --include="*.jpeg" \
  --include="*.png" \
  --include="*.gif" \
  --include="*.mp3" \
  --include="*.pdf" \
  --include="*.docx" \
  --exclude="*" \
  "$SOURCE_DIR/" \
  "$TARGET_DIR/"

# 권한 설정
chmod -R 755 "$TARGET_DIR"
chown -R www-data:www-data "$TARGET_DIR"

echo "완료! 복사된 파일 수:"
find "$TARGET_DIR" -type f | wc -l
```

### 2. 파일 복사 실행

```bash
chmod +x file_migration.sh
./file_migration.sh
```

### 3. DB 업데이트

```php
// attachments 테이블의 filePath 업데이트
UPDATE attachments
SET filePath = CONCAT('migrated/', old_file_path)
WHERE id > 0;
```

---

## ✅ 검증

### 1. 데이터 검증

```bash
# MySQL 콘솔 접속
mysql -u ekge_user -p ekge_church

# 게시판 수 확인
SELECT COUNT(*) FROM boards;

# 게시글 수 확인 (게시판별)
SELECT b.boardName, COUNT(p.id) as post_count
FROM boards b
LEFT JOIN posts p ON b.id = p.board_id
GROUP BY b.id;

# 첨부파일 수 확인
SELECT COUNT(*) FROM attachments;
```

### 2. 웹 접속 테스트

```bash
# API 테스트
curl http://localhost:8080/api/boards

# 게시글 조회
curl http://localhost:8080/api/boards/sermon/posts

# 특정 게시글 상세
curl http://localhost:8080/api/posts/1
```

### 3. 파일 접근 테스트

```bash
# 웹 브라우저에서
http://localhost:8080/uploads/migrated/file_xxx.jpg

# 또는 curl
curl -I http://localhost:8080/uploads/migrated/file_xxx.jpg
```

---

## ⚠️ 문제 해결

### 문제 1: DB 연결 실패

```
❌ 오류: 구 DB 연결 실패
```

**해결:**
1. DB 호스트/포트 확인
2. 사용자명/비밀번호 확인
3. DB 존재 여부 확인
```bash
mysql -u root -p
SHOW DATABASES;
```

### 문제 2: 게시판 매핑 없음

```
⊘ B_ID=27: 매핑 없음, 건너뜀
```

**해결:**
`migration.config.php`에 게시판 매핑 추가
```php
'board_mapping' => [
    '27' => 'truth',  // 진리의깃발 추가
],
```

### 문제 3: 메모리 부족

```
PHP Fatal error: Allowed memory size exhausted
```

**해결:**
```bash
# php.ini 수정
memory_limit = 512M

# 또는 실행 시
php -d memory_limit=512M migrate.php
```

### 문제 4: 파일 권한 오류

```
Warning: move_uploaded_file(): Permission denied
```

**해결:**
```bash
# 권한 설정
sudo chown -R www-data:www-data ~/webapps/ekge/src/public/uploads
sudo chmod -R 755 ~/webapps/ekge/src/public/uploads
```

---

## 🔄 재마이그레이션 (업데이트 시)

### 언제 재실행하나요?

1. **홈페이지 정식 오픈 직전**: 최신 데이터로 다시 마이그레이션
2. **테스트 중 데이터 변경**: 새로운 데이터 반영
3. **마이그레이션 설정 변경**: 게시판 매핑 수정 후

### 재실행 방법

```bash
# 1. 최신 DB 백업 받기
mysqldump -u root -p Backup_ekge > backup_latest.sql

# 2. 새 DB 초기화 (기존 데이터 삭제)
php migrate.php --clear

# 3. 최신 파일 복사
./file_migration.sh

# 4. 검증
curl http://localhost:8080/api/boards
```

---

## 📊 예상 소요 시간

| 작업 | 데이터 | 예상 시간 |
|------|--------|-----------|
| DB 마이그레이션 | 500개 게시글 | 10-20초 |
| 파일 복사 | 2,000개 (5.5GB) | 5-10분 |
| 검증 및 테스트 | - | 5-10분 |
| **총 소요 시간** | - | **15-30분** |

---

## 📝 체크리스트

### 마이그레이션 전
- [ ] 구 DB 백업 확인
- [ ] 구 파일 경로 확인
- [ ] 디스크 용량 확인 (최소 10GB)
- [ ] 설정 파일 작성 (migration.config.php)
- [ ] Dry-run 테스트 완료

### 마이그레이션 중
- [ ] 새 DB 백업
- [ ] 마이그레이션 실행
- [ ] 오류 없이 완료 확인

### 마이그레이션 후
- [ ] 데이터 개수 확인
- [ ] 웹 API 테스트
- [ ] 파일 접근 테스트
- [ ] 한글 인코딩 확인
- [ ] 관리자 계정 생성

---

## 🆘 지원

문제가 발생하면:
1. 로그 확인: `logs/` 디렉토리
2. DB 상태 확인: `mysql -u ekge_user -p ekge_church`
3. 파일 권한 확인: `ls -l public/uploads`

---

**작성일**: 2025-11-20
**버전**: 1.0
**최종 업데이트**: 실제 마이그레이션 후 업데이트 예정

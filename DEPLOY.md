# EKGE 프로덕션 배포 가이드

## 사전 준비 (개발 환경에서)

### 1. SQL 파일 생성
```bash
bash scripts/export_data.sh
```
결과물: `scripts/output/schema.sql`, `scripts/output/data.sql`

### 2. 프론트엔드 빌드
```bash
cd frontend && npm run build
```
결과물: `frontend/dist/`

---

## 프로덕션 배포 (PHP 호스팅)

### Step 1: 파일 업로드 (FTP)

서버에 다음 구조로 업로드:
```
웹루트/
├── public/              <- src/public/ 내용
│   ├── index.php
│   └── setup.php        <- 설치 스크립트
├── config/              <- src/config/
├── module/              <- src/module/
├── vendor/              <- src/vendor/ (composer install 결과)
├── data/
│   ├── setup/           <- SQL 파일 (새로 만듬)
│   │   ├── schema.sql   <- scripts/output/schema.sql 복사
│   │   └── data.sql     <- scripts/output/data.sql 복사
│   └── uploads/
│       └── upfile/      <- data/upfile/ 내용 (사진 파일들)
│           ├── 2016/
│           ├── 2017/
│           └── ...
└── frontend/
    └── dist/            <- 프론트엔드 빌드 결과
```

**참고:**
- `vendor/` 폴더: SSH 가능하면 서버에서 `composer install --no-dev` 실행. 안 되면 로컬에서 빌드 후 통째로 업로드.
- `data/uploads/` 폴더에 쓰기 권한 필요 (chmod 755 또는 777)

### Step 2: setup.php 설정

`public/setup.php` 상단의 설정값 수정:
```php
define('SECRET_KEY', '나만의-비밀키');     // 변경 필수!
define('DB_HOST', 'localhost');            // 호스팅 제공 DB 호스트
define('DB_PORT', '3306');
define('DB_NAME', 'ekge_church');          // 호스팅 제공 DB 이름
define('DB_USER', 'your_db_user');         // 호스팅 제공 DB 유저
define('DB_PASS', 'your_db_password');     // 호스팅 제공 DB 비밀번호
```

### Step 3: 설치 실행

브라우저에서 접속:
```
https://yoursite.com/setup.php?key=나만의-비밀키
```

자동으로 실행되는 작업:
1. DB 연결 테스트
2. 테이블 생성 (schema.sql)
3. 데이터 import (data.sql)
4. 첨부파일 fileSize 업데이트

### Step 4: setup.php 삭제

설치 완료 후 **반드시** FTP에서 `setup.php`와 `data/setup/` 폴더를 삭제하세요.

### Step 5: 설정 파일 수정

`config/autoload/doctrine.global.php`의 DB 접속 정보를 프로덕션 값으로 수정.

### Step 6: 확인

- [ ] 메인 페이지 로드
- [ ] 게시판 목록 표시
- [ ] 게시글 상세 + 이미지 표시
- [ ] 관리자 로그인 (admin@ekge-church.de)
- [ ] 파일 서빙: `/api/files/1` 접속 시 이미지 표시

---

## 사진 파일만 나중에 올리는 경우

사진 파일을 나중에 올리면:
1. FTP로 `data/uploads/upfile/` 에 파일들 업로드
2. 다시 `setup.php?key=비밀키` 실행 (fileSize 업데이트만 다시 실행됨)
3. setup.php 삭제

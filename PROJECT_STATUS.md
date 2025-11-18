# EKGE 에센 한인교회 홈페이지 프로젝트 현황

## 📋 프로젝트 개요

**프로젝트명**: EKGE 에센 한인교회 홈페이지  
**목적**: 독일 에센 소재 한인교회(매주 40-50명 예배) 웹사이트 현대화  
**목표 완료**: 2025년 1월  
**배포 환경**: 독일 웹 호스팅 업체

### 기술 스택
- **Backend**: Laminas Framework (PHP 8.2)
- **Frontend**: Vue.js 3 (계획 중)
- **Database**: MariaDB 10.11
- **Infrastructure**: Docker (Raspberry Pi 5 개발 환경)
- **ORM**: Doctrine (설치 예정)

---

## ✅ 완료된 작업 (2024-11-18)

### 1. 개발 환경 구축
- ✅ Raspberry Pi 5 (ARM64, 8GB RAM) Docker 환경
- ✅ Docker Compose 설정 완료
  - MariaDB 10.11 컨테이너
  - PHP 8.2-FPM 컨테이너
  - Nginx 컨테이너
  - Adminer 컨테이너 (DB 관리)
- ✅ 포트 설정
  - 8080: 웹사이트
  - 8081: Adminer
  - 3306: MariaDB

### 2. 프로젝트 초기화
- ✅ GitHub 레포지토리 생성 및 연동
  - Repository: `ekge` (private)
  - SSH 키 설정 완료
- ✅ Git 초기 커밋 완료
- ✅ 프로젝트 디렉토리 구조 생성

### 3. Laminas Framework 설치
- ✅ `laminas/laminas-mvc-skeleton` 설치 완료
- ✅ Composer 의존성 설치 완료
- ✅ 기본 환영 페이지 동작 확인

### 4. 데이터베이스 설계
- ✅ 기존 JSP 사이트 DB 분석 완료 (2,040개 첨부파일 포함)
- ✅ 새 스키마 설계 완료 (8개 테이블)
- ✅ MariaDB에 스키마 적용 완료
- ✅ 초기 데이터 삽입 완료

### 5. 데이터베이스 연결
- ✅ `laminas/laminas-db` 설치
- ✅ 데이터베이스 연결 설정 (`config/autoload/database.local.php`)
- ✅ 테스트 컨트롤러로 연결 확인 완료

---

## 📁 프로젝트 구조

```
~/webapps/ekge/
├── docker/
│   ├── php/
│   │   ├── Dockerfile              # PHP 8.2-FPM 설정
│   │   └── php.ini                 # PHP 커스텀 설정
│   ├── nginx/
│   │   └── default.conf            # Nginx 설정
│   └── mariadb/
│       ├── init.sql                # 초기화 스크립트
│       └── schema.sql              # 전체 스키마 정의 ✅
├── src/                            # Laminas 애플리케이션
│   ├── config/
│   │   └── autoload/
│   │       └── database.local.php  # DB 연결 설정 ✅
│   ├── module/
│   │   └── Application/
│   │       ├── src/
│   │       │   └── Controller/
│   │       │       ├── TestController.php          # DB 테스트 컨트롤러 ✅
│   │       │       └── Factory/
│   │       │           └── TestControllerFactory.php ✅
│   │       └── config/
│   │           └── module.config.php  # 라우팅 설정 업데이트됨 ✅
│   ├── public/
│   │   └── index.php               # 진입점
│   ├── vendor/                     # Composer 의존성
│   └── composer.json
├── data/
│   ├── mysql/                      # MariaDB 데이터 (영구 저장)
│   └── uploads/                    # 업로드 파일 (준비됨)
├── logs/                           # 로그 파일
├── .env                            # 환경 변수
├── .gitignore
├── docker-compose.yml
└── README.md
```

---

## 🗄️ 데이터베이스 스키마

### 테이블 구조 (8개)

#### 1. users (관리자)
```sql
- id (PK)
- username (unique)
- password (해시)
- email
- displayName
- role (admin/editor)
- isActive
- lastLoginAt
- createdAt, updatedAt
```

#### 2. boards (게시판 종류)
```sql
- id (PK)
- boardCode (unique) -- 'sermon', 'weekly', 'gallery' 등
- boardName
- boardType (notice/gallery/general/qna/category)
- description
- displayOrder
- isVisible
- postsPerPage
- allowAttachment
- requireAuth
- createdAt, updatedAt
```

#### 3. posts (게시글)
```sql
- id (PK)
- board_id (FK → boards)
- title
- content (LONGTEXT)
- authorName
- user_id (FK → users, nullable)
- viewCount
- isNotice
- isPublished
- publishedAt
- createdAt, updatedAt

인덱스:
- board_id, isNotice, isPublished, publishedAt, createdAt
- FULLTEXT (title, content)
```

#### 4. attachments (첨부파일)
```sql
- id (PK)
- post_id (FK → posts)
- originalName
- savedName
- filePath
- fileSize
- mimeType
- fileType (image/audio/video/document/other)
- imageWidth, imageHeight (이미지인 경우)
- downloadCount
- displayOrder
- createdAt
```

#### 5. menus (메뉴 구조)
```sql
- id (PK)
- parent_id (FK → menus, nullable)
- menuName
- menuType (board/html/external)
- board_id (FK → boards, nullable)
- externalUrl
- displayOrder
- depth (1~3)
- isVisible
- createdAt, updatedAt
```

#### 6. menuContents (메뉴 HTML)
```sql
- id (PK)
- menu_id (FK → menus, unique)
- content (LONGTEXT)
- updatedAt
```

#### 7. banners (메인 배너)
```sql
- id (PK)
- title
- description
- imagePath
- linkUrl
- displayOrder
- isActive
- startDate, endDate
- createdAt, updatedAt
```

#### 8. settings (사이트 설정)
```sql
- id (PK)
- settingKey (unique)
- settingValue (TEXT)
- description
- updatedAt
```

### 초기 데이터
- **관리자**: username=`admin`, password=`admin123` (변경 필요)
- **게시판 5개**: sermon, weekly, notice, gallery, free
- **사이트 설정**: site_name, contact_email, service_time 등

---

## 🎯 다음 단계 (우선순위 순)

### Phase 1: Doctrine ORM 설정 (현재 단계)

#### 1.1 Doctrine 설치
```bash
cd ~/webapps/ekge
docker-compose exec php bash
composer require doctrine/orm
composer require doctrine/doctrine-orm-module
exit
```

#### 1.2 Doctrine 설정 파일 생성
- `config/autoload/doctrine.global.php`
- `config/autoload/doctrine.local.php`

#### 1.3 기존 DB에서 Entity 자동 생성
```bash
# Entity 자동 생성
vendor/bin/doctrine orm:convert-mapping \
  --from-database annotation ./module/Application/src/Entity

# Repository 생성
# Entity 파일 정리 및 네임스페이스 수정
```

#### 1.4 Entity 검증
- 각 Entity 파일 확인
- Annotation 정리
- Getter/Setter 추가

### Phase 2: 기본 CRUD 구현

#### 2.1 Repository 패턴 구축
- `BoardRepository.php`
- `PostRepository.php`
- `AttachmentRepository.php`

#### 2.2 Service Layer 생성
- `BoardService.php`
- `PostService.php`
- `FileUploadService.php`

#### 2.3 관리자 인증 시스템
- `laminas/laminas-authentication` 설치
- 로그인/로그아웃
- 세션 관리

### Phase 3: 게시판 기능 구현

#### 3.1 게시판 목록/상세
- 게시판별 목록 조회
- 페이징 처리
- 검색 기능

#### 3.2 게시글 CRUD
- 작성/수정/삭제
- 첨부파일 업로드
- 이미지 리사이징

#### 3.3 파일 관리
- 업로드 처리
- 썸네일 생성
- 다운로드 카운트

### Phase 4: 데이터 마이그레이션

#### 4.1 기존 데이터 분석
- `20250727_ekge_DB_Backup.sql` 파싱
- 데이터 매핑 계획 수립

#### 4.2 변환 스크립트 작성
```
구 테이블 → 새 테이블
BOARD_MST → boards
BOARD_DTL → posts
BOARD_ATTC → attachments
MEMBER → users (필요시)
```

#### 4.3 첨부파일 마이그레이션
- 2,040개 파일 경로 확인
- 파일 복사 스크립트
- 경로 매핑

#### 4.4 데이터 검증
- 개수 일치 확인
- 관계 무결성 체크
- 한글 인코딩 확인

### Phase 5: Frontend (Vue.js)

#### 5.1 Vue.js 3 설정
- Vite 빌드 설정
- API 엔드포인트 연결

#### 5.2 주요 컴포넌트
- 게시판 목록/상세
- 이미지 갤러리
- 파일 업로드

### Phase 6: 배포 준비

#### 6.1 프로덕션 설정
- `.env.production`
- 보안 설정 강화
- 성능 최적화

#### 6.2 독일 호스팅 배포
- 서버 환경 확인
- 데이터 이전
- DNS 설정

---

## ⚙️ 환경 설정

### .env 파일 (중요 설정)
```ini
# 데이터베이스
DB_HOST=mariadb
DB_PORT=3306
DB_NAME=ekge_church
DB_USER=ekge_user
DB_PASSWORD=ekge_password_change_this
DB_ROOT_PASSWORD=root_password_change_this

# 애플리케이션
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080

# 타임존
TZ=Europe/Berlin
```

### Docker 컨테이너 상태 확인
```bash
docker-compose ps

# 예상 출력:
# ekge_mariadb   Up   3306->3306
# ekge_php       Up   9000
# ekge_nginx     Up   8080->80
# ekge_adminer   Up   8081->8080
```

### 데이터베이스 접속 정보
- **Adminer URL**: http://localhost:8081
- **서버**: mariadb
- **사용자명**: ekge_user
- **비밀번호**: ekge_password_change_this
- **데이터베이스**: ekge_church

---

## 🔧 개발 명령어

### Docker 관리
```bash
cd ~/webapps/ekge

# 컨테이너 시작
docker-compose up -d

# 컨테이너 중지
docker-compose down

# 로그 확인
docker-compose logs -f nginx
docker-compose logs -f php
docker-compose logs -f mariadb

# 컨테이너 재시작
docker-compose restart

# PHP 컨테이너 접속
docker-compose exec php bash
```

### Composer 명령어
```bash
# PHP 컨테이너 안에서
composer install
composer update
composer require {패키지명}
composer dump-autoload
```

### 데이터베이스 작업
```bash
# SQL 파일 실행
docker-compose exec -T mariadb mysql -u root -proot_password_change_this ekge_church < schema.sql

# DB 백업
docker-compose exec mariadb mysqldump -u root -p ekge_church > backup.sql

# DB 복원
docker-compose exec -T mariadb mysql -u root -proot_password_change_this ekge_church < backup.sql
```

### Git 작업
```bash
git status
git add .
git commit -m "메시지"
git push origin main
```

---

## 📚 참고 자료

### 공식 문서
- Laminas Framework: https://docs.laminas.dev/
- Doctrine ORM: https://www.doctrine-project.org/
- MariaDB: https://mariadb.org/documentation/

### 프로젝트 관련 파일
- **DB 백업**: `/mnt/user-data/uploads/20250727_ekge_DB_Backup.sql`
- **스키마**: `~/webapps/ekge/docker/mariadb/schema.sql`
- **분석 문서**: 이전 대화에서 작성한 `db_analysis.md` 참조

---

## ⚠️ 주의사항

### 보안
- [ ] 프로덕션 배포 전 `admin` 계정 비밀번호 변경
- [ ] `.env` 파일을 Git에 커밋하지 않기 (이미 .gitignore에 포함됨)
- [ ] DB 비밀번호 강력하게 변경

### 성능
- [ ] 프로덕션에서 `APP_DEBUG=false` 설정
- [ ] OPcache 최적화 확인
- [ ] 이미지 최적화 (WebP 변환 고려)

### 데이터 마이그레이션
- [ ] 기존 첨부파일 실제 위치 확인 필요
- [ ] 2,040개 파일 모두 존재하는지 검증
- [ ] 한글 인코딩 깨짐 없는지 확인

### 호스팅 환경
- [ ] 독일 호스팅 PHP 버전 확인 (8.1+ 필요)
- [ ] Composer 설치 가능 여부
- [ ] 데이터베이스 권한 확인

---

## 🎓 개발 철학

### MVP 우선
- 핵심 기능(게시판, 주보, 사진 갤러리)만 먼저 구현
- 완벽함보다 빠른 배포
- 점진적 개선

### 코드 품질
- PSR-12 코딩 표준 준수
- Repository 패턴 사용
- Service Layer로 비즈니스 로직 분리

### 문서화
- 코드 주석 (한국어)
- README 업데이트
- API 문서 (필요시)

---

## 💬 현재 상태 요약

**단계**: Phase 1 시작 전 (Doctrine 설치 직전)

**완료율**: 약 30%
- ✅ 환경 구축 (100%)
- ✅ DB 설계 (100%)
- ⏳ 백엔드 구현 (5% - DB 연결만)
- ⏳ 프론트엔드 (0%)
- ⏳ 데이터 마이그레이션 (0%)

**다음 작업**: Doctrine ORM 설치 및 Entity 자동 생성

---

## 📞 연락처

**개발자**: Seungjae  
**프로젝트 위치**: `~/webapps/ekge`  
**개발 환경**: Raspberry Pi 5, Debian Linux  
**GitHub**: ekge (private repository)

---

**마지막 업데이트**: 2024-11-18 19:45 (CET)
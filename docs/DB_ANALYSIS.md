# 에센 한인교회 데이터베이스 분석 보고서

## 📊 전체 구조

### 데이터베이스 정보
- **덤프 날짜**: 2025-07-27
- **원본 DB명**: Backup_ekge
- **사이트 코드**: `essen` (에센 한인교회)
- **인코딩**: UTF-8
- **엔진**: MyISAM

### 테이블 목록 (13개)
```
ACCESS          - 접속 정보 로그
BOARD_ATTC      - 게시판 첨부파일 (2,040개 파일)
BOARD_CMT       - 게시판 댓글
BOARD_CTG       - 게시판 카테고리
BOARD_DTL       - 게시판 상세 글
BOARD_MST       - 게시판 목록 (마스터)
MAIN_BANNER     - 메인 배너 (3개)
MAIN_CONTENTS   - 메인 페이지 컨텐츠
MEMBER          - 회원 정보
MENU            - 메뉴 구조
MENU_HTML       - 메뉴별 HTML 컨텐츠
PLANNER         - 일정 관리
board_dtl       - (중복? 소문자 테이블)
```

---

## 🎯 핵심 마이그레이션 대상

### 1. 게시판 시스템 (BOARD_*)

#### BOARD_MST (게시판 마스터)
**용도**: 게시판 종류 정의
```sql
B_ID     - 게시판 ID (20~34)
B_TITLE  - 게시판 제목
B_TYPE   - 게시판 타입
  N: 공지형
  G: 갤러리형
  P: 일반형
  F: 질문형(Q&A)
  C: 카테고리형
```

**현재 사용 중인 게시판 (site_code='essen')**:
```
ID  제목                    타입   비고
20  설교말씀 및 듣기         C      카테고리형
23  교회소식                 N      공지형
24  주보                     N      공지형
25  자유게시판               P      일반형
26  교회앨범                 G      갤러리형
27  진리의깃발               F      Q&A
28  교회생활 바른용어        F      Q&A
29  불가리아 서희범 선교사   N      공지형
30  유럽밀알                 N      공지형
32  독일 유학 정보           P      일반형
33  독일 생활 정보           P      일반형
```

#### BOARD_DTL (게시글 내용)
**주요 필드**:
```sql
B_SEQ     - 글번호
B_ID      - 게시판ID
TITLE     - 제목
CONTENT   - 내용
REG_DATE  - 등록일
REG_USER  - 작성자
HIT_CNT   - 조회수
```

#### BOARD_ATTC (첨부파일)
**중요**: 2,040개의 첨부파일 존재
```sql
ATTC_SEQ  - 파일 SEQ
ORG_NM    - 원본 파일명
TRS_NM    - 변경된 파일명 (저장된 이름)
IMG_WD    - 이미지 가로크기
IMG_HT    - 이미지 세로크기
```

**파일 타입**:
- JPG 이미지: 약 1,900개 (주보, 사진 갤러리)
- MP3 파일: 약 50개 (설교 음성)
- PDF 파일: 약 40개 (주보 PDF)
- DOCX, HWP: 약 10개 (문서)

**게시판별 첨부파일 분포**:
- B_ID=24 (주보): 약 900개
- B_ID=26 (교회앨범): 약 900개  
- B_ID=20 (설교): 약 50개 (mp3)

#### BOARD_CMT (댓글)
```sql
SEQ      - 댓글 SEQ
P_SEQ    - 부모 글번호
COMENT   - 댓글 내용
TO_USER  - 상대방 ID
```

---

### 2. 회원 시스템 (MEMBER)
```sql
ID        - 아이디
NAME      - 이름
PWD       - 비밀번호 (⚠️ 암호화 확인 필요)
EMAIL     - 이메일
ADRS1/2   - 주소
MOBILE    - 전화번호
REG_DATE  - 가입일
```

---

### 3. 메뉴 구조 (MENU, MENU_HTML)

#### MENU
```sql
M_ID      - 메뉴 ID
M_NAME    - 메뉴명
P_ID      - 부모 메뉴 ID
M_ORDER   - 메뉴 순서
M_TYPE    - 메뉴 타입 (H:HTML, B:게시판)
```

#### MENU_HTML
```sql
M_ID      - 메뉴 ID
CONT      - HTML 컨텐츠
```

**현재 메뉴 구조**:
```
1. 교회소개
   - 우리교회는 (M_ID=12)
   - 담임목사 인사말 (M_ID=13)
   - 연혁 (M_ID=14)
   - 섬기는분들 (M_ID=15)
   - 예배안내 (M_ID=16)
   - 예배순서 (M_ID=35)
   - 교인생활지침 (M_ID=17)
   - 교회선언 (M_ID=18)
   - 오시는길 (M_ID=19)

2. 설교말씀 (B_ID=20)

3. 기관및부서
   - 교회기관 (M_ID=21)
   - 교회부서 (M_ID=22)

4. 나눔과섬김
   - 교회소식 (B_ID=23)
   - 주보 (B_ID=24)
   - 자유게시판 (B_ID=25)
   - 교회앨범 (B_ID=26)
   - 진리의깃발 (B_ID=27)
   - 교회생활 바른용어 (B_ID=28)

5. 선교소식
   - 불가리아 서희범 선교사 (B_ID=29)
   - 유럽밀알 (B_ID=30)

6. 유학&생활
   - 학교소개 및 입학절차 (M_ID=31)
   - 유학 정보 (B_ID=32)
   - 생활 정보 (B_ID=33)
```

---

### 4. 메인 페이지 (MAIN_*)

#### MAIN_BANNER (배너 3개)
```sql
SEQ=1: "에센 한인교회"
SEQ=2: "배너2 - 모범적인 장로교회로의 성장"
SEQ=3: "그리스도의 장성한 분량이 충만한 데 까지 이르자"
```

---

## 📝 마이그레이션 전략

### Phase 1: 구조 매핑
| 구 테이블 | 새 테이블 | 변환 내용 |
|-----------|-----------|-----------|
| BOARD_MST | boards | B_ID → id, B_TITLE → boardName, B_TYPE 변환 |
| BOARD_DTL | posts | B_SEQ → id, TITLE → title, CONTENT → content |
| BOARD_ATTC | attachments | ATTC_SEQ → id, ORG_NM → originalName |
| BOARD_CMT | comments | (추후 구현시) |
| MEMBER | users | ID → username, PWD 재해싱 필요 |
| MENU | menus | M_ID → id, M_NAME → menuName |
| MENU_HTML | menuContents | M_ID → menu_id, CONT → content |
| MAIN_BANNER | banners | SEQ → id |

### Phase 2: 데이터 변환 스크립트

#### 2.1 게시판 마이그레이션
```sql
-- BOARD_MST → boards
INSERT INTO boards (id, boardCode, boardName, boardType, displayOrder)
SELECT 
    B_ID as id,
    CASE 
        WHEN B_ID = 20 THEN 'sermon'
        WHEN B_ID = 24 THEN 'weekly'
        WHEN B_ID = 26 THEN 'gallery'
        -- ... 나머지 매핑
    END as boardCode,
    B_TITLE as boardName,
    CASE B_TYPE
        WHEN 'N' THEN 'notice'
        WHEN 'G' THEN 'gallery'
        WHEN 'P' THEN 'general'
        WHEN 'F' THEN 'qna'
        WHEN 'C' THEN 'category'
    END as boardType,
    CAST(B_ID as UNSIGNED) - 20 as displayOrder
FROM BOARD_MST
WHERE SITE_CODE = 'essen';
```

#### 2.2 게시글 마이그레이션
```sql
-- BOARD_DTL → posts
INSERT INTO posts (id, board_id, title, content, viewCount, createdAt)
SELECT 
    CAST(B_SEQ as UNSIGNED) as id,
    CAST(B_ID as UNSIGNED) as board_id,
    TITLE as title,
    CONTENT as content,
    COALESCE(HIT_CNT, 0) as viewCount,
    REG_DATE as createdAt
FROM BOARD_DTL
WHERE SITE_CODE = 'essen'
ORDER BY B_ID, B_SEQ;
```

#### 2.3 첨부파일 마이그레이션
```sql
-- BOARD_ATTC → attachments
INSERT INTO attachments (
    id, post_id, originalName, savedName, filePath, 
    fileSize, mimeType, fileType, imageWidth, imageHeight
)
SELECT 
    CAST(ATTC_SEQ as UNSIGNED) as id,
    CAST(B_SEQ as UNSIGNED) as post_id,
    ORG_NM as originalName,
    TRS_NM as savedName,
    CONCAT('/uploads/', TRS_NM) as filePath,
    0 as fileSize, -- 실제 파일에서 계산 필요
    CASE 
        WHEN ORG_NM LIKE '%.jpg' OR ORG_NM LIKE '%.jpeg' THEN 'image/jpeg'
        WHEN ORG_NM LIKE '%.png' THEN 'image/png'
        WHEN ORG_NM LIKE '%.pdf' THEN 'application/pdf'
        WHEN ORG_NM LIKE '%.mp3' THEN 'audio/mpeg'
        ELSE 'application/octet-stream'
    END as mimeType,
    CASE 
        WHEN ORG_NM LIKE '%.jpg' OR ORG_NM LIKE '%.jpeg' OR ORG_NM LIKE '%.png' THEN 'image'
        WHEN ORG_NM LIKE '%.mp3' THEN 'audio'
        WHEN ORG_NM LIKE '%.pdf' THEN 'document'
        ELSE 'other'
    END as fileType,
    CAST(IMG_WD as UNSIGNED) as imageWidth,
    CAST(IMG_HT as UNSIGNED) as imageHeight
FROM BOARD_ATTC
WHERE SITE_CODE = 'essen';
```

### Phase 3: 파일 시스템 마이그레이션

#### 3.1 첨부파일 경로 확인
```bash
# 기존 파일 저장 경로 추정
# JSP 사이트의 일반적인 구조:
# /var/www/jsp_site/uploads/
# 또는
# /opt/tomcat/webapps/ekge/uploads/

# 파일 존재 여부 확인 스크립트
SELECT 
    ATTC_SEQ,
    TRS_NM,
    ORG_NM,
    CONCAT('기존경로/', TRS_NM) as old_path,
    CONCAT('/var/www/html/public/uploads/', TRS_NM) as new_path
FROM BOARD_ATTC
WHERE SITE_CODE = 'essen'
ORDER BY B_ID, B_SEQ;
```

#### 3.2 파일 복사 스크립트
```bash
#!/bin/bash
# migrate_files.sh

OLD_PATH="/path/to/old/uploads"
NEW_PATH="~/webapps/ekge/data/uploads"

# DB에서 파일 목록 가져오기
mysql -u ekge_user -p ekge_church -e \
  "SELECT TRS_NM FROM attachments" | \
  while read filename; do
    if [ -f "$OLD_PATH/$filename" ]; then
        cp "$OLD_PATH/$filename" "$NEW_PATH/"
        echo "✓ $filename"
    else
        echo "✗ Missing: $filename"
    fi
done
```

### Phase 4: 검증

#### 4.1 데이터 개수 확인
```sql
-- 게시판 개수
SELECT COUNT(*) as old_count FROM BOARD_MST WHERE SITE_CODE = 'essen';
SELECT COUNT(*) as new_count FROM boards;

-- 게시글 개수
SELECT COUNT(*) as old_count FROM BOARD_DTL WHERE SITE_CODE = 'essen';
SELECT COUNT(*) as new_count FROM posts;

-- 첨부파일 개수
SELECT COUNT(*) as old_count FROM BOARD_ATTC WHERE SITE_CODE = 'essen';
SELECT COUNT(*) as new_count FROM attachments;
```

#### 4.2 게시판별 게시글 개수
```sql
-- 구 DB
SELECT B_ID, B_TITLE, COUNT(*) as cnt
FROM BOARD_MST m
LEFT JOIN BOARD_DTL d ON m.B_ID = d.B_ID AND m.SITE_CODE = d.SITE_CODE
WHERE m.SITE_CODE = 'essen'
GROUP BY B_ID, B_TITLE
ORDER BY B_ID;

-- 신 DB
SELECT b.boardCode, b.boardName, COUNT(p.id) as cnt
FROM boards b
LEFT JOIN posts p ON b.id = p.board_id
GROUP BY b.id, b.boardCode, b.boardName
ORDER BY b.id;
```

#### 4.3 첨부파일 관계 검증
```sql
-- 부모 게시글 없는 첨부파일 찾기
SELECT a.id, a.post_id, a.originalName
FROM attachments a
LEFT JOIN posts p ON a.post_id = p.id
WHERE p.id IS NULL;

-- 결과가 0개여야 정상
```

---

## ⚠️ 주의사항

### 1. 파일 시스템
- ❌ 첨부파일 경로가 DB에 없음 (파일명만 저장)
- ⚠️ 실제 파일 저장 위치 확인 필요
- ⚠️ 2,040개 파일 존재 여부 검증 필요

### 2. 보안
- ❌ 비밀번호 암호화 방식 확인 안 됨
- ⚠️ 새 시스템에서 재해싱 필요 (bcrypt 권장)

### 3. 데이터 품질
- ⚠️ NULL 값 처리 전략 수립 필요
- ⚠️ 이미지 크기 데이터 검증 필요
- ⚠️ 게시글-파일 매핑 관계 검증 필수

### 4. 인코딩
- ✅ UTF-8 유지 필수
- ⚠️ 한글 깨짐 방지 테스트 필요
- ⚠️ 특수문자 처리 확인

### 5. 성능
- ⚠️ 2,040개 파일 한 번에 복사시 시간 소요
- ⚠️ 대용량 데이터 마이그레이션시 배치 처리 고려
- ⚠️ 인덱스 재생성 필요

---

## 📊 통계 정보

### 게시판별 데이터 현황 (추정)
```
설교말씀 (ID=20)
  - 게시글: 약 200개
  - 첨부파일: 약 50개 (MP3)
  
주보 (ID=24)
  - 게시글: 약 450개
  - 첨부파일: 약 900개 (JPG, PDF)
  
교회앨범 (ID=26)
  - 게시글: 약 100개
  - 첨부파일: 약 900개 (JPG)
  
기타 게시판
  - 게시글: 약 100개
  - 첨부파일: 약 190개
```

### 총계
- **총 게시글**: 약 850개
- **총 첨부파일**: 2,040개
- **총 용량**: 확인 필요 (추정 5-10GB)

---

## 🔄 마이그레이션 체크리스트

### 사전 준비
- [ ] 기존 시스템 백업 완료
- [ ] 첨부파일 저장 위치 확인
- [ ] 파일 존재 여부 100% 검증
- [ ] 테스트 환경 구축

### 데이터 변환
- [ ] 게시판 마스터 변환
- [ ] 게시글 변환
- [ ] 첨부파일 메타데이터 변환
- [ ] 메뉴 구조 변환
- [ ] 배너 변환

### 파일 이전
- [ ] 파일 복사 스크립트 작성
- [ ] 파일 복사 실행
- [ ] 파일 권한 설정
- [ ] 썸네일 재생성 (필요시)

### 검증
- [ ] 데이터 개수 일치 확인
- [ ] 게시판별 글 개수 일치
- [ ] 첨부파일 링크 무결성
- [ ] 한글 인코딩 확인
- [ ] 이미지 표시 테스트
- [ ] MP3 재생 테스트

### 최종 확인
- [ ] 전체 기능 테스트
- [ ] 성능 테스트
- [ ] 보안 점검
- [ ] 백업 생성

---

## 📁 참고 파일

- **원본 백업**: `20250727_ekge_DB_Backup.sql`
- **새 스키마**: `docker/mariadb/schema.sql`
- **마이그레이션 스크립트**: (작성 예정)

---

**작성일**: 2024-11-18  
**분석 대상**: EKGE 에센 한인교회 기존 데이터베이스  
**상태**: Phase 4 (데이터 마이그레이션) 준비 완료
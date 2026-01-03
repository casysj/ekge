# 다음 세션 작업 가이드

## 📊 현재 완료된 작업 (이번 세션)

### ✅ 백엔드
- 2004개 게시글 마이그레이션 완료
- 첨부파일 메타데이터 마이그레이션 완료
  - DB에 레거시 경로 저장 (`upfile/YYYY/YYYYMMDD/파일명`)
  - fileSize = 0 (실제 파일 아직 없음)
  - 이미지 크기 정보 포함
- 5개 게시판 (설교말씀, 주보, 교회소식, 자유게시판, 교회앨범)
- 3개 배너 마이그레이션
- 세션 기반 인증 구현

### ✅ 프론트엔드
- 메인 페이지 (3개 섹션, 각 5개씩)
- 게시판 목록 (페이지네이션)
- 게시글 상세보기
  - **첨부파일 목록 표시** (준비중 배지)
  - **이미지 플레이스홀더** (서버 이전 후 표시 안내)
- 갤러리 (그리드 + 페이지네이션)
- Admin 패널 (로그인, 대시보드, 게시글 CRUD)

## 🎯 다음 세션에서 할 작업들

### 우선순위 1: 데이터 정리 🔧

**문제:** posts 테이블에 중복 게시글 존재
- 마이그레이션을 여러 번 실행해서 중복 데이터 생성됨

**해결 방법:**

```sql
-- 1. 중복 게시글 확인
SELECT title, publishedAt, COUNT(*) as cnt
FROM posts
GROUP BY title, publishedAt
HAVING cnt > 1
LIMIT 20;

-- 2. 중복 제거 (최신 ID만 남기고 삭제)
DELETE p1 FROM posts p1
INNER JOIN posts p2
WHERE p1.id < p2.id
  AND p1.title = p2.title
  AND p1.publishedAt = p2.publishedAt;

-- 3. 첨부파일 정리 (고아 파일 확인)
SELECT COUNT(*) FROM attachments a
LEFT JOIN posts p ON a.post_id = p.id
WHERE p.id IS NULL;
```

### 우선순위 2: Admin 기능 개선 ⚙️

#### A. 게시글 수정/삭제 기능
- `/admin/posts/:id/edit` - 기존 글 수정
- 삭제 기능 구현
- 수정 시 첨부파일 관리

#### B. 파일 업로드 기능 (새 글 작성 시)
- PostForm.vue에 파일 업로드 UI
- 백엔드 파일 업로드 처리
- 새 경로: `/var/www/html/public/uploads/YYYY/MM/파일명`

#### C. 대시보드 통계 개선
- 실제 데이터 기반 통계
- 최근 활동 로그
- 게시판별 통계

### 우선순위 3: 실제 파일 서빙 📁

#### 호스팅 서버 결정 후 진행:

**1. 파일 복사 스크립트**
```bash
# 구 서버 파일들을 새 서버로 복사
rsync -avz /old/server/upfile/ /new/server/uploads/legacy/
```

**2. 레거시 파일 서빙 API**
```php
// LegacyFileController.php
public function serveAction() {
    $path = $this->params()->fromRoute('path');
    $fullPath = "/var/www/html/public/uploads/legacy/" . $path;

    if (!file_exists($fullPath)) {
        return new Response('File not found', 404);
    }

    // 파일 서빙 로직
}
```

**3. 프론트엔드 이미지 경로 수정**
```javascript
// BoardDetail.vue의 processedContent 수정
content = content.replace(
  /src="\/upfile\/([^"]+)"/gi,
  'src="/api/legacy-files/$1"'
)
```

### 우선순위 4: UI/UX 개선 🎨

#### A. 검색 기능
- 게시판별 검색
- 제목/내용 검색
- 작성자 검색

#### B. 메뉴 관리
- Admin 패널에서 메뉴 편집
- 메뉴 순서 변경
- 메뉴 표시/숨김

#### C. 배너 관리
- 배너 이미지 업로드
- 배너 순서 변경
- 메인 페이지에 배너 표시

### 우선순위 5: 추가 기능 ✨

#### A. 댓글 시스템
- Entity 설계
- API 구현
- 프론트엔드 UI

#### B. 사용자 관리
- 회원 가입/로그인
- 권한 관리
- 프로필 관리

#### C. 통계 및 분석
- 방문자 통계
- 인기 게시글
- Google Analytics 연동

## 📝 현재 브랜치 정보

**Branch:** `claude/continue-ekge-website-01DGGHVdTZi5ZipQjDY7M7np`

**최근 커밋:**
- `cb6ac1d` - feat: Add attachment UI and image placeholder
- `f941739` - feat: Implement attachment metadata migration
- `548e859` - fix: Add getBoardById method to BoardService

## 🔑 중요 파일 위치

### 백엔드
- 마이그레이션: `/src/bin/migration/`
- Entity: `/src/module/Application/src/Entity/`
- Controller: `/src/module/Application/src/Controller/`
- Service: `/src/module/Application/src/Service/`

### 프론트엔드
- Views: `/frontend/src/views/`
- Components: `/frontend/src/components/`
- Services: `/frontend/src/services/`

## 🚀 다음 세션 시작 시

```bash
# 1. 최신 코드 가져오기
cd ~/webapps/ekge
git pull origin claude/continue-ekge-website-01DGGHVdTZi5ZipQjDY7M7np

# 2. 새 브랜치 생성 (선택사항)
git checkout -b claude/admin-improvements

# 3. 컨테이너 상태 확인
docker compose ps
docker compose logs -f frontend php

# 4. 프론트엔드 접속
http://dev.local:5173/
http://dev.local:5173/admin/login
```

## 💡 팁

1. **중복 데이터 정리**를 먼저 하면 다른 작업이 깔끔해져
2. **파일 업로드**는 Admin 개선과 함께 하는 게 효율적
3. **실제 파일 서빙**은 호스팅 서버가 준비된 후에
4. **새로운 기능**은 별도 브랜치에서 작업하는 것도 고려해봐

## 🐛 알려진 이슈

1. posts 테이블 중복 데이터 (해결 필요)
2. 첨부파일 실제 파일 없음 (fileSize=0)
3. 레거시 이미지 경로 (플레이스홀더로 표시 중)

---

**작성일:** 2026-01-03
**작성자:** Claude (이번 세션)
**다음 세션:** 위 우선순위 순서대로 진행 추천!

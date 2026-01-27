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

### ✅ 우선순위 0: 프로덕션 준비 - 버그 수정 (완료)

#### 버그 수정 목록 (모두 완료)

1. **관리자 세션 유지 문제** ✅
   - 원인: `checkAuth()`가 토큰 기반으로 동작했으나 백엔드는 세션 쿠키 기반
   - 수정: 토큰 체크 없이 항상 서버에 세션 확인하도록 변경
   - 수정 파일: `frontend/src/composables/useAuth.js`

2. **게시글 작성자 자동 설정** ✅
   - 수정: 로그인한 사용자의 displayName을 자동 설정, 입력 필드 읽기 전용으로 변경
   - 수정 파일: `frontend/src/views/admin/PostForm.vue`

3. **게시글 수정 시 게시판 변경 금지** ✅
   - 수정: 수정 모드에서 게시판 선택을 disabled로 설정
   - 수정 파일: `frontend/src/views/admin/PostForm.vue`

4. **공지사항 게시글 표시 문제** ✅
   - 원인: API는 notices와 posts를 분리 반환하지만 프론트에서 notices 무시
   - 수정: 공개 게시판에서 공지사항 상단 표시 (노란 배경 + "공지" 배지)
   - 수정: Admin 게시글 목록에서 공지사항과 일반글 합쳐서 표시
   - 수정 파일: `frontend/src/views/BoardList.vue`, `frontend/src/views/admin/PostList.vue`

5. **게시글 목록에 게시판 컬럼 표시 안됨** ✅
   - 원인: API 응답에 board 정보 누락 (특히 notices)
   - 수정: posts와 notices 모두 board 정보(id, code, name) 포함
   - 수정 파일: `src/module/Application/src/Controller/BoardController.php`

6. **HTML Entity 디코딩 문제** ✅
   - 수정: `decodeHtmlEntities()` 함수 추가하여 제목/내용 디코딩
   - 적용 위치: 게시글 상세, 공개 게시판 목록, Admin 게시글 목록
   - 수정 파일: `frontend/src/views/BoardDetail.vue`, `frontend/src/views/BoardList.vue`, `frontend/src/views/admin/PostList.vue`

---

### ✅ 우선순위 0.5: 새 기능 추가 목록

1. **Rich Text Editor 도입** ✅ (2026-01-20)
   - 라이브러리: @vueup/vue-quill
   - 구현 위치: PostForm.vue
   - 툴바: 제목, 굵게/기울임/밑줄, 색상, 목록, 정렬, 링크/이미지
   - HTML entity 디코딩 마이그레이션 완료 (1634개 게시글)
   - 백업: `/src/bin/migration/backup_posts_2026-01-20_21-13-53.sql`

2. **팝업 알림 기능** ✅ (2026-01-24)
   - 용도: 중요 공지사항 (예: 예배 장소 변경, 긴급 공지)
   - 동작: 웹사이트 접속 시 팝업으로 표시
   - 구현 완료:
     - Backend: Popup Entity, PopupService, PopupController
     - Frontend: PopupList.vue, PopupForm.vue, PopupModal.vue
     - 활성화된 팝업은 동시에 1개만 가능 (자동 비활성화)
     - 표시 기간 설정 (시작/종료 일시)
     - "오늘 하루 보지 않기" 기능 (localStorage)
     - Rich Text Editor 지원
   - API 엔드포인트:
     - `GET /api/popup/active` - 활성 팝업 조회 (공개)
     - `GET/POST /api/admin/popups` - 목록/생성
     - `GET/PUT/DELETE /api/admin/popups/:id` - 상세/수정/삭제
     - `POST /api/admin/popups/:id/toggle` - 활성화 토글

3. **교회소개 메뉴 분할** (추후 작업)
   - 현재: 단일 "교회소개" 메뉴
   - 계획: 여러 하위 메뉴로 분할 (예: 소개, 예배안내, 연혁 등)
   - 정확한 분류는 추후 결정

4. **Admin 게시글 관리 UI 개선** 📋
   - 문제: 게시판을 select로 선택하는 방식
   - 증상: 글 수정 후 뒤로가기하면 select가 초기화되어 매번 게시판 다시 선택해야 함
   - 수정: 게시판별 버튼/탭 방식으로 변경하여 직접 클릭해서 들어갈 수 있도록
   - 영향 파일: `frontend/src/views/admin/PostList.vue`

---

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
- 방문자 통계 (사용자 트래킹)
  - 누가 방문했는지 (익명화된 사용자 ID)
  - 언제 방문했는지 (타임스탬프)
  - 어떤 페이지를 봤는지 (URL, 게시글 ID 등)
  - 통계 대시보드 (관리자 전용)
  - 프라이버시 고려사항:
    - 개인정보 수집 최소화 (IP 해시 등)
    - GDPR/개인정보보호법 준수
    - 쿠키 동의 배너 필요 여부 검토
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
4. **레거시 데이터 유실 문제** ⚠️
   - 일부 게시글 내용이 잘리거나 유실됨
   - 현재 데이터는 테스트용이므로 괜찮음
   - 프로덕션 배포 전 마이그레이션 전략 재검토 필요:
     - 원본 DB에서 직접 마이그레이션 (백업 아닌 실제 DB)
     - 긴 텍스트 필드 처리 검증
     - 마이그레이션 후 원본과 비교 검증 추가

---

## 📋 작업 진행 순서 (수정됨)

**프로덕션 준비를 위한 새로운 순서:**

1. ~~**우선순위 0**: 버그 수정~~ ✅ 완료 (2026-01-20)
2. ~~**우선순위 0.5**: Rich Text Editor~~ ✅ 완료 (2026-01-20)
3. ~~**우선순위 0.5**: 팝업 알림 기능~~ ✅ 완료 (2026-01-24)
4. **우선순위 1**: 데이터 정리
5. **우선순위 2**: Admin 기능 개선 (파일 업로드 등)
6. **우선순위 3**: 실제 파일 서빙 (호스팅 서버 결정 후)
7. **우선순위 4**: UI/UX 개선 (검색, 메뉴 관리, 배너 관리)
8. **우선순위 5**: 추가 기능 (댓글, 사용자 관리, 통계)

---

**작성일:** 2026-01-03 (최종 업데이트: 2026-01-20)
**작성자:** Claude
**다음 세션:** 팝업 알림 기능 또는 데이터 정리부터 시작!

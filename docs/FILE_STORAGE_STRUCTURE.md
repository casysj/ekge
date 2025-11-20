# 파일 저장 구조 설계

## 📁 전체 디렉토리 구조

```
~/webapps/ekge/
├── src/
│   └── public/
│       └── uploads/                 # 파일 업로드 루트 (웹 접근 가능)
│           ├── 2024/                # 연도별 디렉토리
│           │   ├── 01/              # 월별 디렉토리
│           │   │   ├── file_xxx.jpg
│           │   │   ├── file_yyy.mp3
│           │   │   └── ...
│           │   ├── 02/
│           │   └── ...
│           ├── 2025/
│           │   ├── 07/
│           │   └── 11/
│           └── thumbs/              # 썸네일 (선택사항)
│               └── 2025/
│                   └── 11/
├── data/
│   └── uploads/                     # 비공개 파일 (옵션)
│       └── private/
└── backup/                          # 백업
    └── files/
        └── 20250727/                # 날짜별 백업
```

---

## 🎯 파일 명명 규칙

### 1. **업로드된 파일**
```
파일명 형식: file_{uniqid}_{random}.{extension}
예시: file_6564a3f2e1b3c_a7f4e2d8b9c1.jpg
```

**구성 요소:**
- `file_` : 접두사
- `{uniqid}` : PHP uniqid() 함수 (타임스탬프 기반)
- `{random}` : 16자리 랜덤 hex (충돌 방지)
- `{extension}` : 원본 파일 확장자

**장점:**
- ✅ 파일명 중복 방지
- ✅ 보안 (원본 파일명 노출 안 됨)
- ✅ 정렬 용이 (시간순)

### 2. **썸네일 (이미지)**
```
파일명 형식: thumb_{원본파일명}
예시: thumb_file_6564a3f2e1b3c_a7f4e2d8b9c1.jpg
```

---

## 📂 디렉토리 구조 설명

### 1. **연도/월 기반 구조 (권장)**

```
public/uploads/
  ├── 2025/
  │   ├── 01/
  │   ├── 02/
  │   └── 11/
  │       ├── file_abc123_xxx.jpg  (주보 이미지)
  │       ├── file_def456_yyy.pdf  (주보 PDF)
  │       ├── file_ghi789_zzz.mp3  (설교 음성)
  │       └── ...
  └── 2026/
      └── 01/
```

**장점:**
- ✅ 파일 관리 용이
- ✅ 백업 편리 (월별로 백업 가능)
- ✅ 디렉토리당 파일 수 제한 (성능 최적화)
- ✅ 오래된 파일 정리 쉬움

**구현:**
- FileUploadService에서 자동으로 `YYYY/MM` 구조 생성
- 예: `2025/11/file_xxx.jpg`

### 2. **게시판별 구조 (대안)**

```
public/uploads/
  ├── sermon/      # 설교
  ├── weekly/      # 주보
  ├── gallery/     # 갤러리
  └── notice/      # 공지
```

**장단점:**
- ✅ 용도별 분리 명확
- ❌ 백업 불편 (전체를 백업해야 함)
- ❌ 한 디렉토리에 파일이 너무 많을 수 있음

**권장:** 연도/월 구조 사용

---

## 🗄️ 데이터베이스 저장 정보

### attachments 테이블
```sql
id              - 파일 ID
post_id         - 게시글 ID
originalName    - 원본 파일명 (예: "주보_2025_11_17.pdf")
savedName       - 저장된 파일명 (예: "file_xxx_yyy.pdf")
filePath        - 상대 경로 (예: "2025/11/file_xxx_yyy.pdf")
fileSize        - 파일 크기 (bytes)
mimeType        - MIME 타입 (예: "application/pdf")
fileType        - 파일 타입 (image/audio/video/document/other)
imageWidth      - 이미지 가로 (이미지만)
imageHeight     - 이미지 세로 (이미지만)
downloadCount   - 다운로드 횟수
displayOrder    - 표시 순서
createdAt       - 업로드 날짜
```

**웹 URL 생성:**
```php
$url = '/uploads/' . $attachment->getFilePath();
// 결과: /uploads/2025/11/file_xxx_yyy.pdf
```

---

## 📦 파일 타입별 처리

### 1. **이미지 파일**
- **타입:** JPG, JPEG, PNG, GIF, WEBP
- **용도:** 주보, 갤러리, 배너
- **처리:**
  - ✅ 원본 저장
  - ✅ 썸네일 생성 (선택사항)
  - ✅ 이미지 크기 정보 저장 (imageWidth, imageHeight)

```
원본: 2025/11/file_abc123.jpg (3000x2000)
썸네일: thumbs/2025/11/thumb_file_abc123.jpg (300x200)
```

### 2. **오디오 파일**
- **타입:** MP3
- **용도:** 설교 음성
- **처리:**
  - ✅ 원본 저장만
  - ❌ 썸네일 없음

### 3. **문서 파일**
- **타입:** PDF, DOCX, XLSX, HWP
- **용도:** 주보 PDF, 문서 공유
- **처리:**
  - ✅ 원본 저장만

### 4. **비디오 파일**
- **타입:** MP4, MPEG
- **용도:** 영상 게시
- **처리:**
  - ✅ 원본 저장
  - ✅ 썸네일 생성 (선택사항)

---

## 🔒 보안 고려사항

### 1. **파일 업로드 제한**
```php
// FileUploadService 설정
$maxFileSize = 10485760;  // 10MB

$allowedMimeTypes = [
    'image/jpeg', 'image/png', 'image/gif',
    'application/pdf',
    'audio/mpeg',
    'video/mp4',
];
```

### 2. **디렉토리 권한**
```bash
# 업로드 디렉토리 권한
chmod 755 public/uploads
chmod 755 public/uploads/2025
chmod 755 public/uploads/2025/11

# 소유자 (www-data는 웹서버 사용자)
chown -R www-data:www-data public/uploads
```

### 3. **.htaccess 보호 (Apache)**
```apache
# public/uploads/.htaccess
<FilesMatch "\.(php|phtml|php3|php4|php5|phps)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

### 4. **직접 접근 제한 (옵션)**
- 비공개 파일은 `data/uploads/private/` 에 저장
- PHP 스크립트를 통해서만 다운로드 가능
- 권한 확인 후 파일 전송

---

## 🔄 마이그레이션 시 파일 복사

### 구 홈페이지 파일 구조 (예상)
```
/old/ekge/uploads/
  ├── board/
  │   ├── 20/  (설교)
  │   ├── 24/  (주보)
  │   └── 26/  (갤러리)
  └── banner/
```

### 새 홈페이지로 복사
```bash
# 1. 구 파일 확인
ls -lh /old/ekge/uploads/board/24/

# 2. 복사 스크립트
rsync -av --progress \
  /old/ekge/uploads/board/ \
  /new/ekge/src/public/uploads/migrated/

# 3. 권한 설정
chown -R www-data:www-data /new/ekge/src/public/uploads/
```

### DB 업데이트
```php
// filePath 업데이트
UPDATE attachments
SET filePath = CONCAT('migrated/', oldPath)
WHERE id > 0;
```

---

## 📊 용량 관리

### 1. **예상 용량**
- 주보 (900개): 약 2GB
- 갤러리 (900개): 약 3GB
- 설교 MP3 (50개): 약 500MB
- **총합**: 약 5.5GB

### 2. **백업 전략**
```bash
# 월별 백업
tar -czf backup_2025_11.tar.gz public/uploads/2025/11/

# 전체 백업 (연도별)
tar -czf backup_2025.tar.gz public/uploads/2025/
```

### 3. **용량 모니터링**
```bash
# 디렉토리별 용량 확인
du -sh public/uploads/*

# 월별 용량 확인
du -sh public/uploads/2025/*

# 가장 큰 파일 찾기
find public/uploads -type f -exec du -h {} + | sort -rh | head -20
```

---

## 🎨 이미지 최적화 (선택사항)

### 1. **WebP 변환**
```bash
# JPEG → WebP 변환
cwebp -q 80 input.jpg -o output.webp

# 자동 변환 스크립트
find public/uploads -name "*.jpg" -exec cwebp -q 80 {} -o {}.webp \;
```

### 2. **리사이징**
```php
// FileUploadService에서 자동 리사이징
// 최대 1920x1080 크기로 제한
if ($width > 1920 || $height > 1080) {
    $this->resizeImage($sourcePath, $destPath, 1920, 1080);
}
```

---

## 📝 권장 사항

### ✅ DO (해야 할 것)
1. **연도/월 구조** 사용
2. **안전한 파일명** 생성 (uniqid + random)
3. **정기 백업** (월 1회 이상)
4. **용량 모니터링** (디스크 공간 확인)
5. **권한 설정** (755 디렉토리, 644 파일)

### ❌ DON'T (하지 말 것)
1. ~~원본 파일명 그대로 저장~~ (보안 위험)
2. ~~한 디렉토리에 모든 파일~~ (성능 문제)
3. ~~백업 없이 운영~~ (데이터 유실 위험)
4. ~~파일 타입 검증 없이 업로드~~ (보안 위험)

---

## 🚀 배포 시 체크리스트

- [ ] 업로드 디렉토리 권한 확인 (755)
- [ ] 파일 최대 크기 설정 (php.ini)
- [ ] .htaccess 설정 (PHP 실행 방지)
- [ ] 디스크 용량 충분한지 확인 (최소 10GB)
- [ ] 백업 스크립트 설정 (cron)
- [ ] 구 파일 마이그레이션 완료
- [ ] 썸네일 생성 (선택사항)
- [ ] 웹에서 다운로드 테스트

---

**작성일**: 2025-11-20
**버전**: 1.0

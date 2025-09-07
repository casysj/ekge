# 에센 한인교회 홈페이지

독일 에센 소재 한인교회의 새로운 홈페이지 개발 프로젝트입니다.

## 🏛️ 프로젝트 개요

- **교회명**: 에센 한인교회 (독일 에센)
- **규모**: 매주 40-50명 예배 참석
- **목적**: 기존 JSP/MySQL 사이트를 PHP(Laravel) + Vue.js로 리뉴얼
- **특징**: 기존 데이터 마이그레이션 포함

## 🔧 기술 스택

- **백엔드**: PHP 8.2, Laravel Framework, Eloquent ORM
- **프론트엔드**: Vue.js (하이브리드 방식)
- **데이터베이스**: MariaDB 10.11
- **개발환경**: Docker & Docker Compose
- **웹서버**: Apache
- **개발도구**: Adminer (데이터베이스 관리)

## 📁 프로젝트 구조

```
ekge/
├── docker-compose.yml              # Docker 서비스 구성
├── docker/
│   ├── php/
│   │   ├── Dockerfile             # PHP + Apache 컨테이너
│   │   └── php.ini                # PHP 설정
│   └── mariadb/
│       └── init.sql               # 데이터베이스 초기 설정
├── src/                           # Laravel 프로젝트 루트
│   ├── app/                       # Laravel 애플리케이션
│   ├── public/                    # 웹 루트 디렉토리
│   ├── resources/                 # 뷰, CSS, JS 리소스
│   └── ...
└── README.md                      # 이 파일
```

## 🚀 개발환경 설정

### 사전 요구사항
- Docker & Docker Compose 설치
- Git 설치
- sudo 권한 (파일 권한 설정용)
- 라즈베리파이 5 (8GB 권장) 또는 유사한 ARM64 환경

### 설치 및 실행

1. **저장소 클론**
   ```bash
   git clone [repository-url] ekge
   cd ekge
   ```

2. **파일 권한 설정**
   ```bash
   # src 디렉토리 권한을 www-data로 설정
   sudo chown -R 33:33 ./src
   sudo chmod -R 775 ./src
   ```

3. **Docker 컨테이너 실행**
   ```bash
   docker-compose up -d --build
   ```

4. **Laravel 설치** (최초 1회)
   ```bash
   docker exec -it ekge_web composer install
   docker exec -it ekge_web php artisan key:generate
   docker exec -it ekge_web php artisan migrate
   ```

### 접속 정보

- **웹사이트**: http://localhost:8081
- **데이터베이스 관리**: http://localhost:8082
- **데이터베이스 접속정보**:
  - 호스트: db
  - 사용자: church_user
  - 비밀번호: church_pass
  - 데이터베이스: essen_church

## 🎯 주요 기능 (개발 예정)

### 1차 개발 (필수 기능)
- [ ] 교회 소개 페이지
- [ ] 목사님 인사말
- [ ] 교회 연혁
- [ ] 예배 안내
- [ ] 교회 소식 게시판
- [ ] 교회 주보 게시판
- [ ] 교회 앨범 (사진 업로드)
- [ ] 관리자 기능
- [ ] 팝업 공지 기능

### 향후 확장
- [ ] 정적 페이지 관리자 수정 기능
- [ ] 추가 게시판 모듈
- [ ] 회원 관리 시스템 (필요시)

## 📖 추가 문서

- **[개발 지침](GUIDELINES.md)**: 개발 철학, 설계 원칙, 코딩 컨벤션
- **[API 문서](docs/API.md)**: RESTful API 명세서 (개발 예정)
- **[배포 가이드](docs/DEPLOYMENT.md)**: 프로덕션 배포 절차 (개발 예정)

## 📈 개발 단계

1. ✅ **Docker 개발환경 구축**
2. 🔄 **Laravel 기본 설정 및 구조 생성**
3. 🔄 **정적 페이지 구현**
4. 🔄 **게시판 기능 구현**
5. 🔄 **관리자 기능 구현**
6. 🔄 **기존 데이터 마이그레이션**
7. 🔄 **Vue.js 동적 기능 추가**
8. 🔄 **테스트 및 배포**

## 🔍 문제 해결

### 파일 권한 문제
```bash
# src 디렉토리 권한 재설정
sudo chown -R 33:33 ./src
sudo chmod -R 775 ./src

# 또는 현재 사용자도 접근 가능하도록
sudo chown -R $USER:33 ./src
sudo chmod -R 775 ./src
```

### 포트 충돌 시
```bash
# docker-compose.yml에서 포트 번호 변경
# 예: 8081 → 8080, 8082 → 8083
```

### 컨테이너 재빌드 필요 시
```bash
docker-compose down
docker-compose up -d --build
```

```bash
# 컨테이너 상태 확인
docker-compose ps

# 로그 확인
docker-compose logs -f web

# 컨테이너 내부 접속
docker exec -it ekge_web bash

# Laravel 명령어 실행
docker exec -it ekge_web php artisan [command]

# Composer 패키지 설치
docker exec -it ekge_web composer install
```

## 🔧 유용한 명령어

1. 이슈 생성 또는 기존 이슈 확인
2. 브랜치 생성 (`git checkout -b feature/기능명`)
3. 커밋 (`git commit -am '기능 추가'`)
4. 푸시 (`git push origin feature/기능명`)
5. Pull Request 생성

## 📞 연락처

- **개발자**: [개발자 정보]
- **교회 담당자**: [담당자 정보]

## 📄 라이선스

이 프로젝트는 에센 한인교회의 소유입니다.

---

**에센 한인교회에서 진심으로 환영합니다!** 🏛️✨
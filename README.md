# EKGE 에센 한인교회 홈페이지

독일 에센 소재 한인교회 웹사이트 프로젝트

## 기술 스택

- **Backend**: Laminas Framework (PHP 8.2)
- **Database**: MariaDB 10.11
- **Frontend**: Vue.js 3
- **Infrastructure**: Docker, Nginx

## 개발 환경 요구사항

- Docker 20.10+
- Docker Compose 1.29+
- Git

## 시작하기

### 1. 환경 변수 설정

```bash
cp .env.example .env
# .env 파일을 열어서 데이터베이스 비밀번호 변경
```

### 2. Docker 컨테이너 실행

```bash
docker-compose up -d
```

### 3. 접속 정보

- **웹사이트**: http://localhost:8080
- **Adminer (DB 관리)**: http://localhost:8081
  - 서버: mariadb
  - 사용자명: ekge_user (또는 .env에서 설정한 값)
  - 비밀번호: .env 파일 참조
  - 데이터베이스: ekge_church

### 4. Laminas 설치 (최초 1회)

```bash
docker-compose exec php bash
composer create-project laminas/laminas-mvc-skeleton .
exit
```

## 유용한 명령어

```bash
# 컨테이너 시작
docker-compose up -d

# 컨테이너 중지
docker-compose down

# 로그 확인
docker-compose logs -f

# PHP 컨테이너 접속
docker-compose exec php bash

# 데이터베이스 백업
docker-compose exec mariadb mysqldump -u root -p ekge_church > backup.sql

# 데이터베이스 복원
docker-compose exec -T mariadb mysql -u root -p ekge_church < backup.sql
```

## 프로젝트 구조

```
ekge/
├── docker/              # Docker 설정 파일
│   ├── php/            # PHP-FPM 설정
│   ├── nginx/          # Nginx 설정
│   └── mariadb/        # MariaDB 초기화
├── src/                # Laminas 소스코드
├── data/               # 데이터 저장
│   ├── mysql/         # MariaDB 데이터
│   └── uploads/       # 업로드 파일
├── logs/               # 로그 파일
└── public/             # 웹 루트
```

## 개발 가이드라인

- PHP 코딩 표준: PSR-12
- Git 브랜치 전략: Git Flow
- 커밋 메시지: Conventional Commits

## 라이선스

Proprietary - EKGE Church

#!/bin/bash

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=====================================${NC}"
echo -e "${GREEN}   EKGE Church 개발환경 설치${NC}"
echo -e "${GREEN}=====================================${NC}"
echo ""

# 1. SSH 키 생성
echo -e "${YELLOW}[1/8] SSH 키 생성...${NC}"
if [ ! -f ~/.ssh/id_ed25519 ]; then
    ssh-keygen -t ed25519 -C "ekge-church-dev" -f ~/.ssh/id_ed25519 -N ""
    echo -e "${GREEN}✓ SSH 키가 생성되었습니다.${NC}"
    echo -e "${YELLOW}GitHub에 등록할 공개키:${NC}"
    cat ~/.ssh/id_ed25519.pub
    echo ""
    echo -e "${YELLOW}이 키를 GitHub (Settings > SSH and GPG keys)에 등록하세요.${NC}"
    read -p "등록 완료 후 Enter를 누르세요..."
else
    echo -e "${GREEN}✓ SSH 키가 이미 존재합니다.${NC}"
fi

# 2. 프로젝트 디렉토리 생성
echo -e "${YELLOW}[2/8] 프로젝트 디렉토리 생성...${NC}"
cd ~
mkdir -p ekge
cd ekge

# 디렉토리 구조 생성
mkdir -p docker/{php,nginx,mariadb}
mkdir -p src data logs public
mkdir -p data/mysql data/uploads

echo -e "${GREEN}✓ 디렉토리 구조 생성 완료${NC}"

# 3. .gitignore 생성
echo -e "${YELLOW}[3/8] .gitignore 생성...${NC}"
cat > .gitignore << 'EOF'
# 환경 설정
.env
.env.local

# 데이터 디렉토리
/data/mysql/*
!/data/mysql/.gitkeep
/data/uploads/*
!/data/uploads/.gitkeep

# 로그 파일
/logs/*
!/logs/.gitkeep

# Composer
/vendor/
composer.lock

# IDE
.idea/
.vscode/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# 임시 파일
*.tmp
*.bak
*.cache
EOF

# .gitkeep 파일 생성
touch data/mysql/.gitkeep
touch data/uploads/.gitkeep
touch logs/.gitkeep

echo -e "${GREEN}✓ .gitignore 생성 완료${NC}"

# 4. 환경 변수 파일 생성
echo -e "${YELLOW}[4/8] 환경 변수 파일 생성...${NC}"
cat > .env.example << 'EOF'
# 데이터베이스 설정
DB_HOST=mariadb
DB_PORT=3306
DB_NAME=ekge_church
DB_USER=ekge_user
DB_PASSWORD=ekge_password_change_this
DB_ROOT_PASSWORD=root_password_change_this

# 애플리케이션 설정
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080

# 타임존
TZ=Europe/Berlin
EOF

# 실제 사용할 .env 파일 복사
cp .env.example .env

echo -e "${GREEN}✓ 환경 변수 파일 생성 완료${NC}"

# 5. Docker Compose 파일 생성
echo -e "${YELLOW}[5/8] docker-compose.yml 생성...${NC}"
cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  # MariaDB
  mariadb:
    image: mariadb:10.11
    container_name: ekge_mariadb
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_NAME}
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      TZ: ${TZ}
    volumes:
      - ./data/mysql:/var/lib/mysql
      - ./docker/mariadb/init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    networks:
      - ekge_network
    command: 
      - --character-set-server=utf8mb4
      - --collation-server=utf8mb4_unicode_ci

  # PHP-FPM
  php:
    build:
      context: ./docker/php
      dockerfile: Dockerfile
    container_name: ekge_php
    restart: unless-stopped
    working_dir: /var/www/html
    environment:
      TZ: ${TZ}
    volumes:
      - ./src:/var/www/html
      - ./data/uploads:/var/www/html/public/uploads
      - ./logs:/var/www/html/logs
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini
    networks:
      - ekge_network
    depends_on:
      - mariadb

  # Nginx
  nginx:
    image: nginx:alpine
    container_name: ekge_nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./src:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./logs:/var/log/nginx
    networks:
      - ekge_network
    depends_on:
      - php

  # Adminer (데이터베이스 관리)
  adminer:
    image: adminer:latest
    container_name: ekge_adminer
    restart: unless-stopped
    ports:
      - "8081:8080"
    environment:
      ADMINER_DEFAULT_SERVER: mariadb
      ADMINER_DESIGN: nette
    networks:
      - ekge_network
    depends_on:
      - mariadb

networks:
  ekge_network:
    driver: bridge
EOF

echo -e "${GREEN}✓ docker-compose.yml 생성 완료${NC}"

# 6. PHP Dockerfile 생성
echo -e "${YELLOW}[6/8] PHP Dockerfile 생성...${NC}"
cat > docker/php/Dockerfile << 'EOF'
FROM php:8.2-fpm

# 시스템 패키지 업데이트 및 필요한 라이브러리 설치
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        zip \
        gd \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 작업 디렉토리 설정
WORKDIR /var/www/html

# 권한 설정
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
EOF

# PHP 설정 파일
cat > docker/php/php.ini << 'EOF'
[PHP]
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
date.timezone = Europe/Berlin

[opcache]
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
EOF

echo -e "${GREEN}✓ PHP 설정 완료${NC}"

# 7. Nginx 설정 생성
echo -e "${YELLOW}[7/8] Nginx 설정 생성...${NC}"
cat > docker/nginx/default.conf << 'EOF'
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    client_max_body_size 20M;
}
EOF

echo -e "${GREEN}✓ Nginx 설정 완료${NC}"

# 8. MariaDB 초기화 SQL 생성
echo -e "${YELLOW}[8/8] MariaDB 초기화 스크립트 생성...${NC}"
cat > docker/mariadb/init.sql << 'EOF'
-- 데이터베이스 설정
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 타임존 설정
SET time_zone = '+01:00';

-- 초기 테이블 생성은 추후 마이그레이션에서 진행
CREATE TABLE IF NOT EXISTS `migration_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `migration_name` VARCHAR(255) NOT NULL,
  `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `migration_name` (`migration_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOF

echo -e "${GREEN}✓ MariaDB 초기화 스크립트 생성 완료${NC}"

# 9. README.md 생성
cat > README.md << 'EOF'
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
EOF

# 10. Git 초기화
echo -e "${YELLOW}Git 초기화...${NC}"
git init
git add .
git commit -m "Initial commit: Docker development environment setup"

echo ""
echo -e "${GREEN}=====================================${NC}"
echo -e "${GREEN}   설치 완료!${NC}"
echo -e "${GREEN}=====================================${NC}"
echo ""
echo -e "${YELLOW}다음 단계:${NC}"
echo ""
echo -e "1. GitHub에 새 레포지토리 생성"
echo -e "   - https://github.com/new"
echo -e "   - Repository name: ekge"
echo ""
echo -e "2. SSH 공개키 등록"
echo -e "   - https://github.com/settings/ssh/new"
echo -e "   - 아래 키를 복사해서 등록:"
echo ""
cat ~/.ssh/id_ed25519.pub
echo ""
echo -e "3. Git 원격 저장소 연결 (레포지토리 생성 후)"
echo -e "   ${GREEN}cd ~/ekge${NC}"
echo -e "   ${GREEN}git remote add origin git@github.com:YOUR_USERNAME/ekge.git${NC}"
echo -e "   ${GREEN}git branch -M main${NC}"
echo -e "   ${GREEN}git push -u origin main${NC}"
echo ""
echo -e "4. Docker 컨테이너 시작"
echo -e "   ${GREEN}cd ~/ekge${NC}"
echo -e "   ${GREEN}docker-compose up -d${NC}"
echo ""
echo -e "5. 웹 브라우저에서 확인"
echo -e "   - http://localhost:8080 (웹사이트)"
echo -e "   - http://localhost:8081 (Adminer)"
echo ""
echo -e "${YELLOW}설치 스크립트 위치: ~/ekge${NC}"
echo ""
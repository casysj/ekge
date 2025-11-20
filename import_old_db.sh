#!/bin/bash

echo "========================================="
echo "  구 DB 백업 임포트 시작"
echo "========================================="
echo ""

# 1. Backup_ekge 데이터베이스 생성
echo "1. Backup_ekge 데이터베이스 생성 중..."
docker-compose exec mariadb mysql -u root -proot_password_change_this -e "CREATE DATABASE IF NOT EXISTS Backup_ekge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ $? -eq 0 ]; then
    echo "✓ 데이터베이스 생성 완료"
else
    echo "❌ 데이터베이스 생성 실패"
    exit 1
fi

echo ""

# 2. SQL 백업 파일 임포트
echo "2. SQL 백업 파일 임포트 중 (시간이 걸릴 수 있습니다)..."
docker-compose exec -T mariadb mysql -u root -proot_password_change_this Backup_ekge < docs/old_backup.sql

if [ $? -eq 0 ]; then
    echo "✓ 백업 파일 임포트 완료"
else
    echo "❌ 백업 파일 임포트 실패"
    exit 1
fi

echo ""

# 3. 테이블 확인
echo "3. 임포트된 테이블 확인..."
docker-compose exec mariadb mysql -u root -proot_password_change_this Backup_ekge -e "SHOW TABLES;"

echo ""
echo "========================================="
echo "  구 DB 임포트 완료!"
echo "========================================="

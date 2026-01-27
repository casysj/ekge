#!/bin/bash
#
# 개발 DB에서 프로덕션용 SQL 파일 덤프
# 실행: bash scripts/export_data.sh
# 결과: scripts/output/schema.sql, scripts/output/data.sql
#

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
OUTPUT_DIR="$SCRIPT_DIR/output"

# .env에서 DB 정보 읽기
source "$PROJECT_DIR/.env"

DB_CONTAINER="ekge_mariadb"
DB_USER="${DB_USER:-ekge_user}"
DB_PASS="${DB_PASSWORD:-}"
DB_NAME="${DB_NAME:-ekge_church}"

mkdir -p "$OUTPUT_DIR"

echo "=== EKGE 데이터 덤프 시작 ==="
echo ""

# 1. 중복 데이터 정리
echo "[1/3] 중복 게시글 정리..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" <<'SQL'
-- 중복 게시글 제거 (같은 title + publishedAt이면 낮은 ID 삭제)
DELETE p1 FROM posts p1
INNER JOIN posts p2
ON p1.title = p2.title
  AND p1.publishedAt = p2.publishedAt
  AND p1.board_id = p2.board_id
WHERE p1.id < p2.id;

-- 고아 첨부파일 삭제
DELETE a FROM attachments a
LEFT JOIN posts p ON a.post_id = p.id
WHERE p.id IS NULL;
SQL
echo "  완료"

# 2. 스키마 덤프
echo "[2/3] 스키마 덤프..."
cp "$PROJECT_DIR/docker/mariadb/schema.sql" "$OUTPUT_DIR/schema.sql"

# popups 테이블 추가 (schema.sql에 없을 수 있으므로)
if ! grep -q "CREATE TABLE.*popups" "$OUTPUT_DIR/schema.sql"; then
  cat "$PROJECT_DIR/src/bin/migration/create_popups_table.sql" >> "$OUTPUT_DIR/schema.sql"
fi
echo "  -> $OUTPUT_DIR/schema.sql"

# 3. 데이터 덤프 (INSERT문만)
echo "[3/3] 데이터 덤프..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T mariadb \
  mysqldump -u"$DB_USER" -p"$DB_PASS" \
  --no-create-info \
  --skip-triggers \
  --complete-insert \
  --skip-extended-insert \
  --set-charset \
  "$DB_NAME" \
  users boards posts attachments menus menuContents banners settings popups \
  > "$OUTPUT_DIR/data.sql" 2>/dev/null

echo "  -> $OUTPUT_DIR/data.sql"

# 결과 요약
echo ""
echo "=== 덤프 완료 ==="
echo "schema: $(wc -l < "$OUTPUT_DIR/schema.sql") 줄"
echo "data:   $(wc -l < "$OUTPUT_DIR/data.sql") 줄"
echo ""
echo "파일 위치: $OUTPUT_DIR/"
ls -lh "$OUTPUT_DIR/"*.sql

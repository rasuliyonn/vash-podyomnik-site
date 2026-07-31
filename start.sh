#!/usr/bin/env bash
set -euo pipefail

echo "=== Применение локальных миграций D1 ==="
npx wrangler d1 execute DB --local --config=wrangler.migrations.toml --file=drizzle/0000_glamorous_wiccan.sql 2>/dev/null || true
npx wrangler d1 execute DB --local --config=wrangler.migrations.toml --file=drizzle/0001_site_content.sql 2>/dev/null || true

echo "=== Запуск локального сервера ==="
WRANGLER_LOG_PATH=.wrangler/wrangler.log npx vite

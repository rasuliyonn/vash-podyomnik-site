#!/usr/bin/env bash
set -euo pipefail

echo "=== Сборка проекта ==="
npm run build

echo "=== Применение миграций БД ==="
npx wrangler d1 migrations apply DB --remote

echo "=== Деплой на Cloudflare ==="
npx wrangler deploy

echo "=== Готово ==="

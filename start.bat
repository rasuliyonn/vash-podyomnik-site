@echo off
echo === Применение локальных миграций D1 ===
npx wrangler d1 execute DB --local --config=wrangler.migrations.toml --file=drizzle/0000_glamorous_wiccan.sql 2>nul
npx wrangler d1 execute DB --local --config=wrangler.migrations.toml --file=drizzle/0001_site_content.sql 2>nul
echo === Запуск локального сервера ===
set WRANGLER_LOG_PATH=.wrangler\wrangler.log
npx vite

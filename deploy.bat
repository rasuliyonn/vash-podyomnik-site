@echo off
echo === Сборка проекта ===
npm run build
if errorlevel 1 exit /b 1

echo === Применение миграций БД ===
npx wrangler d1 migrations apply DB --remote
if errorlevel 1 exit /b 1

echo === Деплой на Cloudflare ===
npx wrangler deploy
if errorlevel 1 exit /b 1

echo === Готово ===

<?php
/**
 * Роутер только для локального предпросмотра (php -S): отдаёт статику как Apache,
 * остальное — через index.php. На боевой хостинг НЕ нужен (есть .htaccess).
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = '/' . trim((string) $path, '/');
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false; // встроенный сервер отдаст файл как есть
}
require __DIR__ . '/index.php';

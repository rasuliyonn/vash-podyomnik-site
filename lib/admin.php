<?php
/**
 * Админка: сессии, CSRF, общий каркас страниц.
 * Авторизация: bcrypt-хэш пароля в site_content (ключ admin_password_hash).
 * Пока хэша нет — действует env ADMIN_PASSWORD (по умолчанию «admin»; ОБЯЗАТЕЛЬНО смените).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/seo.php';

function admin_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');
session_set_cookie_params([
            'lifetime' => 7 * 24 * 3600,
            'path' => '/upravlenie',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('vp_admin');
        session_start();
    }
}

function admin_password_hash(): ?string
{
    try {
        $stmt = db()->prepare('SELECT value FROM site_content WHERE key = ?');
        $stmt->execute(['admin_password_hash']);
        $value = $stmt->fetchColumn();
        $value = is_string($value) ? trim($value) : '';
        if ($value !== '' && $value[0] === '{') {
            // храним как JSON {"hash": ...} для единообразия с остальным контентом
            $decoded = json_decode($value, true);
            if (is_array($decoded) && !empty($decoded['hash'])) {
                return (string) $decoded['hash'];
            }
            return null;
        }
        return $value !== '' ? $value : null;
    } catch (Throwable $e) {
        return null;
    }
}

function admin_check_password(string $password): bool
{
    $hash = admin_password_hash();
    if ($hash) {
        return password_verify($password, $hash);
    }
    $env = getenv('ADMIN_PASSWORD');
    $stored = $env !== false && $env !== '' ? $env : 'admin';
    return hash_equals($stored, $password);
}

function admin_set_password(string $password): void
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO site_content (key, value, updated_at) VALUES (?, ?, datetime(\'now\'))
        ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = datetime(\'now\')');
    $stmt->execute(['admin_password_hash', json_encode(['hash' => $hash], JSON_UNESCAPED_UNICODE)]);
}

function admin_is_logged_in(): bool
{
    admin_session();
    return !empty($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function require_admin(): void
{
    if (!admin_is_logged_in()) {
        http_response_code(303);
        header('Location: /upravlenie/login');
        exit;
    }
}

function admin_login(string $password): bool
{
    if (!admin_check_password($password)) {
        return false;
    }
    admin_session();
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    return true;
}

function admin_logout(): void
{
    admin_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function admin_csrf_token(): string
{
    admin_session();
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['admin_csrf'];
}

function admin_csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(admin_csrf_token()) . '">';
}

function admin_csrf_check(): bool
{
    admin_session();
    $token = $_POST['_csrf'] ?? '';
    return is_string($token) && isset($_SESSION['admin_csrf']) && hash_equals((string) $_SESSION['admin_csrf'], $token);
}

function admin_page_start(string $title): void
{
    $user_ok = true;
    ?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — Админка «Ваш Подъёмник»</title>
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/fonts.css">
<link rel="stylesheet" href="/assets/style.css">
<link rel="stylesheet" href="/assets/site-ext.css">
<link rel="stylesheet" href="/assets/responsive.css?v=1">
</head>
<body class="admin-body">
<div class="admin-root">
<header class="admin-header">
  <a href="/upravlenie" class="admin-logout-btn" style="background: var(--red); color: #fff">Админка</a>
  <nav class="admin-nav" aria-label="Разделы админки">
    <a href="/upravlenie/submissions">Заявки</a>
    <a href="/upravlenie/equipment">Оборудование</a>
    <a href="/upravlenie/works">Работы</a>
    <a href="/upravlenie/settings">Настройки</a>
  </nav>
  <a class="admin-logout-btn" href="/upravlenie/logout">Выйти</a>
</header>
<main class="admin-main">
<?php
}

function admin_page_end(): void
{
    echo '</main></div></body></html>' . "\n";
}

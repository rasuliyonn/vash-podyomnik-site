<?php
/**
 * Вход в админку: /upravlenie/login
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';

if (admin_is_logged_in()) {
    header('Location: /upravlenie', true, 303);
    exit;
}

$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check()) {
        $error = 'Сессия устарела, попробуйте ещё раз.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        if (admin_login($password)) {
            header('Location: /upravlenie', true, 303);
            exit;
        }
        $error = 'Неверный пароль.';
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Вход — Админка «Ваш Подъёмник»</title>
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/fonts.css">
<link rel="stylesheet" href="/assets/style.css">
<link rel="stylesheet" href="/assets/site-ext.css">
<link rel="stylesheet" href="/assets/responsive.css?v=1">
</head>
<body class="admin-body">
<div class="admin-login-page">
  <div class="admin-login-box">
    <div class="admin-login-brand">
      <svg class="brand-mark" viewBox="0 0 64 64" width="56" height="56" aria-hidden="true" focusable="false">
        <rect x="11" y="12" width="6" height="32" fill="#F4F4F2"/>
        <rect x="47" y="12" width="6" height="32" fill="#F4F4F2"/>
        <path d="M32 3 L19.5 20.5 H27.5 V44 H36.5 V20.5 H44.5 Z" fill="#F4F4F2"/>
        <rect x="6" y="49" width="52" height="9" fill="#D3191D"/>
      </svg>
      <b>ВАШ ПОДЪЁМНИК</b>
      <small>админка сайта</small>
    </div>
    <?php if ($error !== ''): ?>
    <p class="admin-error-box" role="alert"><?= e($error) ?></p>
    <?php endif; ?>
    <form method="post" action="/upravlenie/login" class="admin-login-form">
      <?= admin_csrf_field() ?>
      <label class="admin-label">
        <span class="admin-label-text">Пароль</span>
        <input type="password" name="password" required autocomplete="current-password" autofocus>
      </label>
      <button type="submit" class="primary-button form-button">Войти</button>
    </form>
  </div>
</div>
</body>
</html>

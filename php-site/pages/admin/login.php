<?php
/**
 * Вход в админку: /admin/login
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';

if (admin_is_logged_in()) {
    header('Location: /admin', true, 303);
    exit;
}

$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check()) {
        $error = 'Сессия устарела, попробуйте ещё раз.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        if (admin_login($password)) {
            header('Location: /admin', true, 303);
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
<link rel="stylesheet" href="/assets/style.css">
<link rel="stylesheet" href="/assets/site-ext.css">
</head>
<body class="admin-body">
<div class="admin-login-page">
  <div class="admin-login-box">
    <div class="admin-login-brand">
      <img class="brand-logo" src="/assets/vash-podyomnik-logo.jpg" alt="Логотип Ваш Подъёмник" width="50" height="50">
      <b>ВАШ ПОДЪЁМНИК</b>
      <small>админка сайта</small>
    </div>
    <?php if ($error !== ''): ?>
    <p class="admin-error-box" role="alert"><?= e($error) ?></p>
    <?php endif; ?>
    <form method="post" action="/admin/login" class="admin-login-form">
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

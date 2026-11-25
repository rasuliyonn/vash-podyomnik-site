<?php
/**
 * Настройки: /upravlenie/settings — смена пароля и подсказки по env-переменным.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';

require_admin();

$notice = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check()) {
        http_response_code(400);
        exit('Неверный CSRF-токен');
    }
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (!admin_check_password($current)) {
        $error = 'Текущий пароль неверен.';
    } elseif (mb_strlen($new) < 8) {
        $error = 'Новый пароль — минимум 8 символов.';
    } elseif ($new !== $confirm) {
        $error = 'Пароли не совпадают.';
    } else {
        admin_set_password($new);
        $notice = 'Пароль обновлён. Храните его в надёжном месте.';
    }
}

$defaultPassword = admin_password_hash() === null;

admin_page_start('Настройки');
?>
<?php if ($defaultPassword): ?>
<p class="admin-warn">Сейчас действует пароль из переменной <code>ADMIN_PASSWORD</code> (или «admin» по умолчанию). После смены здесь пароль хранится в базе только в виде bcrypt-хэша.</p>
<?php endif; ?>
<?php if ($notice !== ''): ?><p class="admin-ok"><?= e($notice) ?></p><?php endif; ?>
<?php if ($error !== ''): ?><p class="admin-error-box"><?= e($error) ?></p><?php endif; ?>

<form method="post" action="/upravlenie/settings" class="admin-form" style="max-width: 520px">
  <?= admin_csrf_field() ?>
  <h2 class="admin-list-header">Сменить пароль</h2>
  <label class="admin-label"><span class="admin-label-text">Текущий пароль</span>
    <input type="password" name="current_password" required autocomplete="current-password">
  </label>
  <label class="admin-label"><span class="admin-label-text">Новый пароль (мин. 8 символов)</span>
    <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
  </label>
  <label class="admin-label"><span class="admin-label-text">Повторите новый пароль</span>
    <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
  </label>
  <div class="admin-form-actions">
    <button type="submit" class="primary-button form-button">Сохранить пароль</button>
  </div>
</form>

<section class="admin-section" style="margin-top: 36px">
  <h2 class="admin-list-header">Конфигурация через окружение</h2>
  <p class="admin-hint">На shared-хостинге переменные задаются через <code>SetEnv</code> в <code>.htaccess</code> (пример — в файле <code>.htaccess</code> в корне) или в панели хостинга. Доступны:</p>
  <ul class="admin-hint" style="line-height: 1.9">
    <li><code>CONTACT_PHONE</code> — телефон на сайте и в schema.org (по умолчанию +7 (952) 603-57-85; пусто = не показываем)</li>
    <li><code>MAX_PROFILE_URL</code> — ссылка на профиль в мессенджере MAX (<code>https://max.ru/u/…</code>)</li>
    <li><code>CONTACT_HOURS</code> — часы работы</li>
    <li><code>YANDEX_METRIKA_ID</code> — счётчик Яндекс.Метрики</li>
    <li><code>GA4_ID</code> — Google Analytics 4</li>
    <li><code>YANDEX_VERIFICATION</code>, <code>GOOGLE_VERIFICATION</code> — коды верификации вебмастеров</li>
    <li><code>ADMIN_PASSWORD</code> — пароль админки до первой смены здесь</li>
    <li><code>SITE_URL</code> — канонический адрес (по умолчанию https://vashpodemnik61.ru)</li>
  </ul>
</section>
<?php
admin_page_end();

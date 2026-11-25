<?php
/**
 * Дашборд админки: /upravlenie
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';
require_once __DIR__ . '/../../lib/equipment.php';
require_once __DIR__ . '/../../lib/blog.php';

require_admin();

$newCount = 0;
$totalCount = 0;
$last = [];
try {
    $newCount = (int) db()->query("SELECT COUNT(*) FROM form_submissions WHERE status = 'new'")->fetchColumn();
    $totalCount = (int) db()->query('SELECT COUNT(*) FROM form_submissions')->fetchColumn();
    $last = db()->query('SELECT * FROM form_submissions ORDER BY id DESC LIMIT 5')->fetchAll();
} catch (Throwable $e) {
}
$worksCount = 0;
try {
    $worksCount = (int) db()->query('SELECT COUNT(*) FROM works_images')->fetchColumn();
} catch (Throwable $e) {
}
$equipmentCount = count(equipment_all());
$articlesCount = blog_article_count();
$defaultPassword = admin_password_is_default();
$consentNew = 0;
try {
    submissions_purge_expired();
    $consentNew = (int) db()->query("SELECT COUNT(*) FROM consent_requests WHERE status = 'new'")->fetchColumn();
} catch (Throwable $e) {
}

admin_page_start('Панель');
?>
<?php if ($defaultPassword): ?>
<p class="admin-warn">Используется пароль по умолчанию. <a href="/upravlenie/settings">Смените его в настройках</a> — иначе админка открыта любому.</p>
<?php endif; ?>

<div class="admin-dashboard-grid">
  <a class="admin-card" href="/upravlenie/submissions">
    <span class="admin-card-icon"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></span>
    <b>Заявки</b>
    <small><?= $newCount ?> новых · <?= $totalCount ?> всего</small>
  </a>
  <a class="admin-card" href="/upravlenie/consent-requests">
    <span class="admin-card-icon"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg></span>
    <b>Обращения по ПД</b>
    <small><?= (int) $consentNew ?> новых · отзыв согласия и запросы</small>
  </a>
  <a class="admin-card" href="/upravlenie/equipment">
    <span class="admin-card-icon"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4.5 4.5 0 0 0-6 5.6L3 17.6a2.1 2.1 0 0 0 3 3l5.7-5.7a4.5 4.5 0 0 0 5.6-6l-2.8 2.8-2.6-2.6 2.8-2.8z"/></svg></span>
    <b>Оборудование</b>
    <small><?= $equipmentCount ?> типов · карточки и фото</small>
  </a>
  <a class="admin-card" href="/upravlenie/works">
    <span class="admin-card-icon"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 15-4.35-4.35a1.5 1.5 0 0 0-2.12 0L7 18"/></svg></span>
    <b>Наши работы</b>
    <small><?= $worksCount ?> фото в портфолио</small>
  </a>
  <a class="admin-card" href="/blog" target="_blank" rel="noopener">
    <span class="admin-card-icon"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L10 16l-4 1 1-4L16.5 3.5z"/></svg></span>
    <b>Блог</b>
    <small><?= $articlesCount ?> статей · открыть сайт ↗</small>
  </a>
</div>

<h2 class="admin-list-header">Последние заявки</h2>
<?php if (!$last): ?>
<p class="admin-empty">Заявок пока нет.</p>
<?php else: ?>
<div class="admin-table-wrap">
<table class="admin-table submissions-table">
  <thead>
    <tr><th>Дата</th><th>Имя</th><th>Телефон</th><th>Оборудование</th><th>Город</th><th>Статус</th></tr>
  </thead>
  <tbody>
    <?php foreach ($last as $row): ?>
    <tr>
      <td><?= e($row['created_at']) ?></td>
      <td><?= e($row['name']) ?></td>
      <td><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', (string) $row['phone'])) ?>"><?= e($row['phone']) ?></a></td>
      <td><?= e($row['lift_type']) ?>, <?= e($row['capacity']) ?></td>
      <td><?= e($row['city']) ?></td>
      <td><span class="admin-badge <?= $row['status'] === 'new' ? 'yellow' : ($row['status'] === 'done' ? 'green' : 'gray') ?>"><?= e($row['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<p style="margin-top: 14px"><a class="admin-action-btn secondary" href="/upravlenie/submissions">Все заявки →</a></p>
<?php endif; ?>
<?php
admin_page_end();

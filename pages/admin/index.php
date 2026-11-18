<?php
/**
 * Дашборд админки: /admin
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
$defaultPassword = admin_password_hash() === null;

admin_page_start('Панель');
?>
<?php if ($defaultPassword): ?>
<p class="admin-warn">Используется пароль по умолчанию. <a href="/admin/settings">Смените его в настройках</a> — иначе админка открыта любому.</p>
<?php endif; ?>

<div class="admin-dashboard-grid">
  <a class="admin-card" href="/admin/submissions">
    <span class="admin-card-icon">📥</span>
    <b>Заявки</b>
    <small><?= $newCount ?> новых · <?= $totalCount ?> всего</small>
  </a>
  <a class="admin-card" href="/admin/equipment">
    <span class="admin-card-icon">🏗️</span>
    <b>Оборудование</b>
    <small><?= $equipmentCount ?> типов · карточки и фото</small>
  </a>
  <a class="admin-card" href="/admin/works">
    <span class="admin-card-icon">🖼️</span>
    <b>Наши работы</b>
    <small><?= $worksCount ?> фото в портфолио</small>
  </a>
  <a class="admin-card" href="/blog" target="_blank" rel="noopener">
    <span class="admin-card-icon">✍️</span>
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
<p style="margin-top: 14px"><a class="admin-action-btn secondary" href="/admin/submissions">Все заявки →</a></p>
<?php endif; ?>
<?php
admin_page_end();

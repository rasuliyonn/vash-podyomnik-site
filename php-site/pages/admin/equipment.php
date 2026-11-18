<?php
/**
 * Оборудование: /admin/equipment — список типов + быстрые ссылки на редактирование.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';
require_once __DIR__ . '/../../lib/equipment.php';

require_admin();

$items = equipment_all();

admin_page_start('Оборудование');
?>
<h2 class="admin-list-header">Оборудование (<?= count($items) ?>)</h2>
<p class="admin-hint">Статические данные из кода можно переопределять здесь: заголовок, описание, характеристики, видео. Изменения применяются на сайте сразу после сохранения.</p>

<div class="admin-table-wrap">
<table class="admin-table">
  <thead>
    <tr><th>Тип</th><th>Заголовок</th><th>Фото</th><th>Документы</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($items as $slug => $item): ?>
    <tr>
      <td><code><?= e($slug) ?></code></td>
      <td><?= e($item['title']) ?></td>
      <td><?= count($item['images']) ?></td>
      <td><?= count($item['documents']) ?></td>
      <td>
        <a class="admin-action-btn secondary" href="/admin/equipment/edit?slug=<?= e($slug) ?>">Редактировать</a>
        <a class="admin-action-btn secondary" href="/equipment/<?= e($slug) ?>" target="_blank" rel="noopener">Смотреть ↗</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php
admin_page_end();

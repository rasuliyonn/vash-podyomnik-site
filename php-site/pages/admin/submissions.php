<?php
/**
 * Заявки: /admin/submissions — список, смена статуса, удаление.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';

require_admin();

$notice = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check()) {
        http_response_code(400);
        exit('Неверный CSRF-токен');
    }
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        if ($action === 'status') {
            $status = (string) ($_POST['status'] ?? '');
            if (in_array($status, ['new', 'viewed', 'done'], true)) {
                $stmt = db()->prepare('UPDATE form_submissions SET status = ? WHERE id = ?');
                $stmt->execute([$status, $id]);
                $notice = 'Статус обновлён.';
            }
        } elseif ($action === 'delete') {
            $stmt = db()->prepare('DELETE FROM form_submissions WHERE id = ?');
            $stmt->execute([$id]);
            $notice = 'Заявка удалена.';
        }
    }
}

$filter = (string) ($_GET['status'] ?? 'all');
$where = in_array($filter, ['new', 'viewed', 'done'], true) ? "WHERE status = '" . $filter . "'" : '';
$rows = db()->query("SELECT * FROM form_submissions $where ORDER BY id DESC")->fetchAll();

admin_page_start('Заявки');
?>
<?php if ($notice !== ''): ?>
<p class="admin-ok"><?= e($notice) ?></p>
<?php endif; ?>

<div class="admin-list-header">
  <h2 style="margin: 0">Заявки (<?= count($rows) ?>)</h2>
  <p class="admin-hint" style="margin: 8px 0 0">Фильтр:
    <?php foreach (['all' => 'все', 'new' => 'новые', 'viewed' => 'просмотренные', 'done' => 'выполненные'] as $key => $label): ?>
    <a href="/admin/submissions?status=<?= e($key) ?>"<?= $filter === $key ? ' style="color: var(--red-bright)"' : '' ?>><?= e($label) ?></a><?= $key !== 'done' ? ' · ' : '' ?>
    <?php endforeach; ?>
  </p>
</div>

<?php if (!$rows): ?>
<p class="admin-empty">Заявок нет.</p>
<?php else: ?>
<div class="admin-table-wrap">
<table class="admin-table submissions-table">
  <thead>
    <tr><th>#</th><th>Дата</th><th>Имя</th><th>Телефон</th><th>Email</th><th>Оборудование</th><th>Остановки</th><th>Город</th><th>Статус</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $row): ?>
    <tr>
      <td><?= (int) $row['id'] ?></td>
      <td><?= e($row['created_at']) ?></td>
      <td><?= e($row['name']) ?></td>
      <td><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', (string) $row['phone'])) ?>"><?= e($row['phone']) ?></a></td>
      <td><?= e($row['email']) ?></td>
      <td><?= e($row['lift_type']) ?><br><small><?= e($row['capacity']) ?><?= $row['height'] !== '' ? ' · ' . e($row['height']) . ' мм' : '' ?><?= $row['platform_size'] !== '' ? ' · ' . e($row['platform_size']) : '' ?></small></td>
      <td><?= e($row['stops']) ?></td>
      <td><?= e($row['city']) ?></td>
      <td>
        <form method="post" action="/admin/submissions" style="margin: 0">
          <?= admin_csrf_field() ?>
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
          <select name="status" class="status-select <?= $row['status'] === 'new' ? 'yellow' : ($row['status'] === 'done' ? 'green' : '') ?>" onchange="this.form.submit()">
            <?php foreach (['new' => 'Новая', 'viewed' => 'Просмотрена', 'done' => 'Выполнена'] as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= $row['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </td>
      <td>
        <form method="post" action="/admin/submissions" style="margin: 0" onsubmit="return confirm('Удалить заявку #<?= (int) $row['id'] ?>?');">
          <?= admin_csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
          <button type="submit" class="admin-action-btn danger">Удалить</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php
admin_page_end();

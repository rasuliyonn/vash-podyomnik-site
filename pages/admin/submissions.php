<?php
/**
 * Заявки: /upravlenie/submissions — список, поиск, статусы, удаление.
 * Заявки хранятся ограниченное время (SUBMISSION_RETENTION_MONTHS) и
 * автоматически удаляются при открытии раздела.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';

require_admin();

$notice = '';
$error = '';
$purged = submissions_purge_expired();
if ($purged > 0) {
    $notice = 'По сроку хранения удалено заявок: ' . $purged . '.';
}

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
            // Удаление заявки по обращению субъекта: удаляем и журнал согласия
            // по этому субъекту (хеш контактов), чтобы данные не остались в базе.
            try {
                $find = db()->prepare('SELECT phone, email FROM form_submissions WHERE id = ?');
                $find->execute([$id]);
                $row = $find->fetch();
                $stmt = db()->prepare('DELETE FROM form_submissions WHERE id = ?');
                $stmt->execute([$id]);
                if (is_array($row)) {
                    $hash = contact_hash((string) $row['phone'], (string) $row['email']);
                    $events = db()->prepare('DELETE FROM consent_events WHERE subject_hash = ?');
                    $events->execute([$hash]);
                }
                $notice = 'Заявка и связанные записи согласия удалены.';
            } catch (Throwable $e) {
                error_log('[admin-delete] ' . $e->getMessage());
                $error = 'Не удалось удалить заявку.';
            }
        }
    }
}

$filter = (string) ($_GET['status'] ?? 'all');
$where = [];
$params = [];
if (in_array($filter, ['new', 'viewed', 'done'], true)) {
    $where[] = 'status = ?';
    $params[] = $filter;
}
$query = trim((string) ($_GET['q'] ?? ''));
if ($query !== '') {
    $where[] = '(phone LIKE ? OR email LIKE ? OR name LIKE ? OR city LIKE ?)';
    $like = '%' . $query . '%';
    array_push($params, $like, $like, $like, $like);
}
$sql = 'SELECT * FROM form_submissions';
if ($where !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY id DESC LIMIT 500';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

admin_page_start('Заявки');
?>
<?php if ($notice !== ''): ?>
<p class="admin-ok"><?= e($notice) ?></p>
<?php endif; ?>
<?php if ($error !== ''): ?>
<p class="admin-error-box"><?= e($error) ?></p>
<?php endif; ?>

<div class="admin-list-header">
  <h2 style="margin: 0">Заявки (<?= count($rows) ?><?= count($rows) >= 500 ? '+, показаны последние 500' : '' ?>)</h2>
  <p class="admin-hint" style="margin: 8px 0 0">Фильтр:
    <?php foreach (['all' => 'все', 'new' => 'новые', 'viewed' => 'просмотренные', 'done' => 'выполненные'] as $key => $label): ?>
    <a href="/upravlenie/submissions?status=<?= e($key) ?><?= $query !== '' ? '&q=' . urlencode($query) : '' ?>"<?= $filter === $key ? ' style="color: var(--red-bright)"' : '' ?>><?= e($label) ?></a><?= $key !== 'done' ? ' · ' : '' ?>
    <?php endforeach; ?>
  </p>
  <p class="admin-hint" style="margin: 8px 0 0">Срок хранения: <?= (int) SUBMISSION_RETENTION_MONTHS ?> мес. с даты заявки; затем запись удаляется автоматически при открытии этого раздела.</p>
</div>

<form method="get" action="/upravlenie/submissions" class="admin-form" style="margin-bottom: 20px; max-width: 520px">
  <input type="hidden" name="status" value="<?= e($filter) ?>">
  <label class="admin-label"><span class="admin-label-text">Поиск по телефону, email, имени или городу</span>
    <input type="search" name="q" value="<?= e($query) ?>" placeholder="+7… / name@example.com">
  </label>
  <div class="admin-form-actions">
    <button type="submit" class="admin-action-btn secondary">Найти</button>
    <?php if ($query !== ''): ?><a class="admin-action-btn secondary" href="/upravlenie/submissions?status=<?= e($filter) ?>">Сбросить</a><?php endif; ?>
  </div>
</form>

<?php if (!$rows): ?>
<p class="admin-empty">Заявок нет.</p>
<?php else: ?>
<div class="admin-table-wrap">
<table class="admin-table submissions-table">
  <thead>
    <tr><th>#</th><th>Дата</th><th>Имя</th><th>Телефон</th><th>Email</th><th>Оборудование</th><th>Город</th><th>Согласие</th><th>Статус</th><th></th></tr>
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
      <td><?= e($row['city']) ?></td>
      <td>
        <small>
          ПД: <?= e((string) ($row['consent_version'] ?? '')) ?><br>
          <?= e((string) ($row['consent_given_at'] ?? '')) ?> UTC<br>
          Реклама: <?= !empty($row['marketing_consent']) ? 'да' : 'нет' ?><br>
          <?= e((string) ($row['source_page'] ?? '')) ?>
        </small>
      </td>
      <td>
        <form method="post" action="/upravlenie/submissions" style="margin: 0">
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
        <form method="post" action="/upravlenie/submissions" style="margin: 0" onsubmit="return confirm('Удалить заявку #<?= (int) $row['id'] ?> и связанные записи согласия?');">
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

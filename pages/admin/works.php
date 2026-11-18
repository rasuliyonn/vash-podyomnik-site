<?php
/**
 * Работы: /admin/works — список, загрузка, удаление, импорт статических файлов.
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
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'upload') {
            if (empty($_FILES['work_file'])) {
                throw new RuntimeException('Файл не выбран');
            }
            $files = $_FILES['work_file'];
            $count = is_array($files['name']) ? count($files['name']) : 1;
            $sort = (int) db()->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM works_images')->fetchColumn();
            $uploaded = 0;
            for ($i = 0; $i < $count; $i++) {
                $tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $size = is_array($files['size']) ? $files['size'][$i] : $files['size'];
                if (!$tmp || !is_uploaded_file($tmp)) {
                    continue;
                }
                if ($size > 8 * 1024 * 1024) {
                    continue;
                }
                $ext = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    continue;
                }
                if (@getimagesize($tmp) === false) {
                    continue;
                }
                $targetDir = UPLOADS_DIR . '/works';
                if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
                    throw new RuntimeException('Не удалось создать каталог загрузок');
                }
                $filename = 'work-' . bin2hex(random_bytes(6)) . '.' . $ext;
                $target = $targetDir . '/' . $filename;
                if (!move_uploaded_file($tmp, $target)) {
                    continue;
                }
                $stmt = db()->prepare('INSERT INTO works_images (url, caption, sort_order) VALUES (?, ?, ?)');
                $stmt->execute(['/uploads/works/' . $filename, '', $sort + $i]);
                $uploaded++;
            }
            $notice = "Загружено файлов: $uploaded";
        } elseif ($action === 'import_static') {
            $total = 71;
            $stmt = db()->prepare('INSERT OR IGNORE INTO works_images (url, caption, sort_order) VALUES (?, ?, ?)');
            $inserted = 0;
            for ($i = 1; $i <= $total; $i++) {
                if (!is_file(WORKS_DIR . '/work-' . $i . '.jpg')) {
                    continue;
                }
                $stmt->execute(['/works/work-' . $i . '.jpg', '', $i - 1]);
                $inserted += $stmt->rowCount();
            }
            $notice = "Импортировано: $inserted (повторные — пропущены)";
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = db()->prepare('SELECT url FROM works_images WHERE id = ?');
                $stmt->execute([$id]);
                $url = (string) $stmt->fetchColumn();
                $del = db()->prepare('DELETE FROM works_images WHERE id = ?');
                $del->execute([$id]);
                if ($url && str_starts_with($url, '/uploads/')) {
                    $path = __DIR__ . '/../../' . ltrim($url, '/');
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
                $notice = 'Фото удалено.';
            }
        } elseif ($action === 'caption') {
            $id = (int) ($_POST['id'] ?? 0);
            $caption = trim((string) ($_POST['caption'] ?? ''));
            if ($id > 0) {
                $stmt = db()->prepare('UPDATE works_images SET caption = ? WHERE id = ?');
                $stmt->execute([$caption, $id]);
                $notice = 'Подпись сохранена.';
            }
        }
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

$works = db()->query('SELECT id, url, caption, sort_order FROM works_images ORDER BY sort_order ASC, id ASC')->fetchAll();

admin_page_start('Наши работы');
?>
<?php if ($notice !== ''): ?><p class="admin-ok"><?= e($notice) ?></p><?php endif; ?>
<?php if ($error !== ''): ?><p class="admin-error-box"><?= e($error) ?></p><?php endif; ?>

<h2 class="admin-list-header">Наши работы (<?= count($works) ?>)</h2>

<form method="post" action="/admin/works" enctype="multipart/form-data" class="admin-form" style="margin-bottom: 24px">
  <?= admin_csrf_field() ?>
  <input type="hidden" name="action" value="upload">
  <label class="admin-label"><span class="admin-label-text">Добавить фото (JPG/PNG/WebP, до 8 МБ, можно несколько)</span>
    <input type="file" name="work_file[]" accept="image/jpeg,image/png,image/webp" multiple required>
  </label>
  <button type="submit" class="primary-button form-button">Загрузить</button>
</form>

<form method="post" action="/admin/works" style="margin-bottom: 24px">
  <?= admin_csrf_field() ?>
  <input type="hidden" name="action" value="import_static">
  <button type="submit" class="admin-action-btn secondary">Импортировать статические work-1…71.jpg</button>
</form>

<div class="admin-works-grid">
  <?php foreach ($works as $work): ?>
  <div class="admin-work-card">
    <img class="admin-thumb" src="<?= e($work['url']) ?>" alt="<?= e($work['caption']) ?>" loading="lazy">
    <div class="admin-work-meta">
      <form method="post" action="/admin/works" class="admin-work-caption">
        <?= admin_csrf_field() ?>
        <input type="hidden" name="action" value="caption">
        <input type="hidden" name="id" value="<?= (int) $work['id'] ?>">
        <input type="text" name="caption" value="<?= e($work['caption']) ?>" placeholder="Подпись (объект, город)…">
        <button type="submit" class="admin-action-btn secondary">OK</button>
      </form>
      <form method="post" action="/admin/works" onsubmit="return confirm('Удалить фото?');">
        <?= admin_csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int) $work['id'] ?>">
        <button type="submit" class="admin-action-btn danger">Удалить</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php
admin_page_end();

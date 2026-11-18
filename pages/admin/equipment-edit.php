<?php
/**
 * Редактирование оборудования: /admin/equipment/edit?slug=shaftnye
 * Основные поля + списки (построчно) + фото и документы.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/admin.php';
require_once __DIR__ . '/../../lib/equipment.php';

require_admin();

$slug = (string) ($_GET['slug'] ?? ($_POST['slug'] ?? ''));
$static = equipment_static()[$slug] ?? null;
if ($static === null) {
    http_response_code(404);
    exit('Неизвестный тип оборудования');
}

$notice = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check()) {
        http_response_code(400);
        exit('Неверный CSRF-токен');
    }
    $action = (string) ($_POST['action'] ?? 'save');

    try {
        if ($action === 'save') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $videoUrl = trim((string) ($_POST['video_url'] ?? ''));
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $visible = isset($_POST['visible']) ? 1 : 0;

            $lines = static function (string $raw): array {
                $items = array_map('trim', preg_split('/\r\n|\r|\n/', $raw) ?: []);
                return array_values(array_filter($items, fn($l) => $l !== ''));
            };
            $specLines = static function (string $raw): array {
                $specs = [];
                foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    $parts = array_map('trim', explode('::', $line, 2));
                    if (count($parts) === 2 && $parts[0] !== '') {
                        $specs[] = ['name' => $parts[0], 'value' => $parts[1]];
                    }
                }
                return $specs;
            };

            $features = $lines((string) ($_POST['features'] ?? ''));
            $advantages = $lines((string) ($_POST['advantages'] ?? ''));
            $applications = $lines((string) ($_POST['applications'] ?? ''));
            $techSpecs = $specLines((string) ($_POST['tech_specs'] ?? ''));

            $stmt = db()->prepare('INSERT INTO equipment_types
                    (slug, title, description, features_json, advantages_json, applications_json, tech_specs_json, video_url, sort_order, visible)
                VALUES
                    (:slug, :title, :description, :features_json, :advantages_json, :applications_json, :tech_specs_json, :video_url, :sort_order, :visible)
                ON CONFLICT(slug) DO UPDATE SET
                    title = excluded.title,
                    description = excluded.description,
                    features_json = excluded.features_json,
                    advantages_json = excluded.advantages_json,
                    applications_json = excluded.applications_json,
                    tech_specs_json = excluded.tech_specs_json,
                    video_url = excluded.video_url,
                    sort_order = excluded.sort_order,
                    visible = excluded.visible');
            $stmt->execute([
                ':slug' => $slug,
                ':title' => $title,
                ':description' => $description,
                ':features_json' => json_encode($features, JSON_UNESCAPED_UNICODE),
                ':advantages_json' => json_encode($advantages, JSON_UNESCAPED_UNICODE),
                ':applications_json' => json_encode($applications, JSON_UNESCAPED_UNICODE),
                ':tech_specs_json' => json_encode($techSpecs, JSON_UNESCAPED_UNICODE),
                ':video_url' => $videoUrl !== '' ? $videoUrl : null,
                ':sort_order' => $sortOrder,
                ':visible' => $visible,
            ]);
            $notice = 'Сохранено.';
        } elseif ($action === 'upload_image' || $action === 'upload_document') {
            $equipmentId = equipment_id_by_slug($slug);
            $fileKey = $action === 'upload_image' ? 'image_file' : 'doc_file';
            if (empty($_FILES[$fileKey]) || !is_uploaded_file($_FILES[$fileKey]['tmp_name'])) {
                throw new RuntimeException('Файл не загружен');
            }
            $upload = $_FILES[$fileKey];
            if (($upload['size'] ?? 0) > 8 * 1024 * 1024) {
                throw new RuntimeException('Файл больше 8 МБ');
            }
            $ext = strtolower(pathinfo((string) $upload['name'], PATHINFO_EXTENSION));
            $isImageAction = $action === 'upload_image';
            $allowed = $isImageAction ? ['jpg', 'jpeg', 'png', 'webp'] : ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'];
            if (!in_array($ext, $allowed, true)) {
                throw new RuntimeException('Недопустимый тип файла: .' . $ext);
            }
            if ($isImageAction && @getimagesize($upload['tmp_name']) === false) {
                throw new RuntimeException('Файл не является изображением');
            }
            $subdir = $isImageAction ? 'equipment' : 'docs';
            $targetDir = UPLOADS_DIR . '/' . $subdir;
            if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
                throw new RuntimeException('Не удалось создать каталог загрузок');
            }
            $filename = $slug . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
            $target = $targetDir . '/' . $filename;
            if (!move_uploaded_file($upload['tmp_name'], $target)) {
                throw new RuntimeException('Не удалось сохранить файл');
            }
            $url = '/uploads/' . $subdir . '/' . $filename;
            if ($isImageAction) {
                $caption = trim((string) ($_POST['caption'] ?? ''));
                $stmt = db()->prepare('INSERT INTO equipment_images (equipment_id, url, caption, image_type, sort_order) VALUES (?, ?, ?, \'gallery\', ?)');
                $sort = (int) db()->query('SELECT COUNT(*) FROM equipment_images WHERE equipment_id = ' . $equipmentId)->fetchColumn();
                $stmt->execute([$equipmentId, $url, $caption, $sort]);
                $notice = 'Фото загружено.';
            } else {
                $name = trim((string) ($_POST['doc_name'] ?? ''));
                if ($name === '') {
                    $name = pathinfo((string) $upload['name'], PATHINFO_FILENAME);
                }
                $docType = in_array((string) ($_POST['doc_type'] ?? 'pdf'), ['pdf', 'certificate', 'instruction', 'scheme', 'video'], true) ? (string) $_POST['doc_type'] : 'pdf';
                $sizeKb = (int) round(((int) $upload['size']) / 1024);
                $stmt = db()->prepare('INSERT INTO equipment_documents (equipment_id, url, name, doc_type, size_kb) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$equipmentId, $url, $name, $docType, $sizeKb]);
                $notice = 'Документ загружен.';
            }
        } elseif ($action === 'delete_image') {
            $id = (int) ($_POST['id'] ?? 0);
            delete_equipment_media('equipment_images', $id);
            $notice = 'Фото удалено.';
        } elseif ($action === 'delete_document') {
            $id = (int) ($_POST['id'] ?? 0);
            delete_equipment_media('equipment_documents', $id);
            $notice = 'Документ удалён.';
        }
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

function equipment_id_by_slug(string $slug): int
{
    // Гарантируем наличие строки-переопределения
    $stmt = db()->prepare('INSERT INTO equipment_types (slug, title) VALUES (?, ?) ON CONFLICT(slug) DO NOTHING');
    $static = equipment_static()[$slug] ?? null;
    $stmt->execute([$slug, $static['title'] ?? $slug]);
    $id = db()->prepare('SELECT id FROM equipment_types WHERE slug = ?');
    $id->execute([$slug]);
    return (int) $id->fetchColumn();
}

function delete_equipment_media(string $table, int $id): void
{
    if ($id <= 0) {
        return;
    }
    $stmt = db()->prepare("SELECT url FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $url = (string) $stmt->fetchColumn();
    $del = db()->prepare("DELETE FROM $table WHERE id = ?");
    $del->execute([$id]);
    if ($url && str_starts_with($url, '/uploads/')) {
        $path = __DIR__ . '/../../' . ltrim($url, '/');
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

$data = equipment_get($slug);
$effective = $data ?? ['title' => $static['title'], 'description' => $static['description']];
$features = $data['features'] ?? $static['features'];
$advantages = $data['advantages'] ?? $static['advantages'];
$applications = $data['applications'] ?? $static['applications'];
$techSpecs = $data['techSpecs'] ?? $static['techSpecs'];
$videoUrl = $data['videoUrl'] ?? '';
$visible = $data['visible'] ?? true;
$images = $data['images'] ?? [];
$documents = $data['documents'] ?? [];
$sortOrder = 0;
try {
    $stmt = db()->prepare('SELECT sort_order FROM equipment_types WHERE slug = ?');
    $stmt->execute([$slug]);
    $sortOrder = (int) ($stmt->fetchColumn() ?: 0);
} catch (Throwable $e) {
}

admin_page_start('Оборудование: ' . $effective['title']);
?>
<p><a class="admin-action-btn secondary" href="/admin/equipment">← К списку</a>
   <a class="admin-action-btn secondary" href="/equipment/<?= e($slug) ?>" target="_blank" rel="noopener">Открыть карточку ↗</a></p>

<?php if ($notice !== ''): ?><p class="admin-ok"><?= e($notice) ?></p><?php endif; ?>
<?php if ($error !== ''): ?><p class="admin-error-box"><?= e($error) ?></p><?php endif; ?>

<form method="post" action="/admin/equipment/edit?slug=<?= e($slug) ?>" class="admin-form">
  <?= admin_csrf_field() ?>
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="slug" value="<?= e($slug) ?>">

  <label class="admin-label"><span class="admin-label-text">Заголовок</span>
    <input type="text" name="title" value="<?= e($effective['title']) ?>" required>
  </label>

  <label class="admin-label"><span class="admin-label-text">Описание</span>
    <textarea name="description" rows="4" required><?= e($effective['description']) ?></textarea>
  </label>

  <label class="admin-label"><span class="admin-label-text">Видео (URL, например embed YouTube/Vimeo)</span>
    <input type="url" name="video_url" value="<?= e((string) $videoUrl) ?>" placeholder="https://www.youtube.com/embed/...">
  </label>

  <div class="admin-form-row">
    <label class="admin-label"><span class="admin-label-text">Порядок сортировки</span>
      <input type="number" name="sort_order" value="<?= (int) $sortOrder ?>" min="0">
    </label>
    <label class="admin-checkbox"><input type="checkbox" name="visible"<?= $visible ? ' checked' : '' ?>> Показывать на сайте</label>
  </div>

  <label class="admin-label"><span class="admin-label-text">Особенности (каждая с новой строки)</span>
    <textarea name="features" rows="6" placeholder="Особенность 1&#10;Особенность 2"><?= e(implode("\n", $features)) ?></textarea>
  </label>

  <label class="admin-label"><span class="admin-label-text">Преимущества (каждое с новой строки)</span>
    <textarea name="advantages" rows="6" placeholder="Преимущество 1&#10;Преимущество 2"><?= e(implode("\n", $advantages)) ?></textarea>
  </label>

  <label class="admin-label"><span class="admin-label-text">Сферы применения (каждая с новой строки)</span>
    <textarea name="applications" rows="6" placeholder="Склады&#10;Производства"><?= e(implode("\n", $applications)) ?></textarea>
  </label>

  <label class="admin-label"><span class="admin-label-text">Технические характеристики (формат: Параметр :: Значение, по строке)</span>
    <textarea name="tech_specs" rows="8" placeholder="Грузоподъёмность :: 100 – 5 000 кг&#10;Высота подъёма :: до 50 м"><?= e(implode("\n", array_map(fn($s) => $s['name'] . ' :: ' . $s['value'], $techSpecs))) ?></textarea>
  </label>

  <div class="admin-form-actions">
    <button type="submit" class="primary-button form-button">Сохранить</button>
  </div>
</form>

<section class="admin-media-section">
  <h3>Фотографии (<?= count($images) ?>)</h3>
  <?php if ($images): ?>
  <div class="admin-image-grid">
    <?php foreach ($images as $img): ?>
    <div class="admin-image-card">
      <img class="admin-thumb" src="<?= e($img['url']) ?>" alt="<?= e($img['caption']) ?>">
      <div class="admin-image-info">
        <small><?= e($img['caption'] !== '' ? $img['caption'] : 'без подписи') ?></small>
        <form method="post" action="/admin/equipment/edit?slug=<?= e($slug) ?>" onsubmit="return confirm('Удалить фото?');">
          <?= admin_csrf_field() ?>
          <input type="hidden" name="action" value="delete_image">
          <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
          <button type="submit" class="admin-action-btn danger">Удалить</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form method="post" action="/admin/equipment/edit?slug=<?= e($slug) ?>" enctype="multipart/form-data" class="admin-form" style="margin-top: 14px">
    <?= admin_csrf_field() ?>
    <input type="hidden" name="action" value="upload_image">
    <input type="hidden" name="slug" value="<?= e($slug) ?>">
    <div class="admin-image-upload-field">
      <label class="admin-label"><span class="admin-label-text">Файл (JPG/PNG/WebP, до 8 МБ)</span>
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" required>
      </label>
      <label class="admin-label"><span class="admin-label-text">Подпись (необязательно)</span>
        <input type="text" name="caption" placeholder="Например: монтаж в Ростове-на-Дону">
      </label>
    </div>
    <button type="submit" class="admin-action-btn secondary">Загрузить фото</button>
  </form>
</section>

<section class="admin-media-section">
  <h3>Документы (<?= count($documents) ?>)</h3>
  <?php if ($documents): ?>
  <div class="admin-doc-list">
    <?php foreach ($documents as $doc): $sizeKb = (int) ($doc['size_kb'] ?? 0); ?>
    <div class="admin-image-card" style="flex-direction: row; align-items: center; gap: 12px; padding: 10px">
      <div class="admin-image-info" style="display: flex; align-items: center; gap: 12px">
        <a href="<?= e($doc['url']) ?>" target="_blank" rel="noopener"><?= e($doc['name']) ?></a>
        <small><?= e($doc['doc_type']) ?><?= $sizeKb > 0 ? ' · ' . ($sizeKb > 1024 ? number_format($sizeKb / 1024, 1) . ' МБ' : $sizeKb . ' КБ') : '' ?></small>
        <form method="post" action="/admin/equipment/edit?slug=<?= e($slug) ?>" onsubmit="return confirm('Удалить документ?');" style="margin: 0">
          <?= admin_csrf_field() ?>
          <input type="hidden" name="action" value="delete_document">
          <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>">
          <button type="submit" class="admin-action-btn danger">Удалить</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form method="post" action="/admin/equipment/edit?slug=<?= e($slug) ?>" enctype="multipart/form-data" class="admin-form" style="margin-top: 14px">
    <?= admin_csrf_field() ?>
    <input type="hidden" name="action" value="upload_document">
    <input type="hidden" name="slug" value="<?= e($slug) ?>">
    <div class="admin-form-row">
      <label class="admin-label"><span class="admin-label-text">Файл (PDF, DOC, XLS, ZIP… до 8 МБ)</span>
        <input type="file" name="doc_file" required>
      </label>
      <label class="admin-label"><span class="admin-label-text">Название (необязательно)</span>
        <input type="text" name="doc_name" placeholder="Паспорт на оборудование">
      </label>
      <label class="admin-label"><span class="admin-label-text">Тип</span>
        <select name="doc_type">
          <option value="pdf">PDF</option>
          <option value="certificate">Сертификат</option>
          <option value="instruction">Инструкция</option>
          <option value="scheme">Схема</option>
          <option value="video">Видео</option>
        </select>
      </label>
    </div>
    <button type="submit" class="admin-action-btn secondary">Загрузить документ</button>
  </form>
</section>
<?php
admin_page_end();

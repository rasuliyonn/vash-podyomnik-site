<?php
/**
 * PDO/SQLite: подключение, схема, авто-сид работ из /works/*.jpg.
 * База создаётся автоматически при первом обращении (каталог data/ должен
 * быть доступен на запись).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR, 0775, true) && !is_dir(DATA_DIR)) {
        throw new RuntimeException('Не удалось создать каталог data/. Сделайте его доступным на запись.');
    }

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    db_migrate($pdo);

    return $pdo;
}

function db_migrate(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS equipment_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        description TEXT NOT NULL DEFAULT '',
        features_json TEXT NOT NULL DEFAULT '[]',
        advantages_json TEXT NOT NULL DEFAULT '[]',
        applications_json TEXT NOT NULL DEFAULT '[]',
        tech_specs_json TEXT NOT NULL DEFAULT '[]',
        video_url TEXT,
        sort_order INTEGER NOT NULL DEFAULT 0,
        visible INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS equipment_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        equipment_id INTEGER NOT NULL,
        url TEXT NOT NULL,
        caption TEXT NOT NULL DEFAULT '',
        image_type TEXT NOT NULL DEFAULT 'gallery',
        sort_order INTEGER NOT NULL DEFAULT 0
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_equipment_images_equipment ON equipment_images(equipment_id, sort_order)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS equipment_documents (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        equipment_id INTEGER NOT NULL,
        url TEXT NOT NULL,
        name TEXT NOT NULL,
        doc_type TEXT NOT NULL DEFAULT 'pdf',
        size_kb INTEGER
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_equipment_documents_equipment ON equipment_documents(equipment_id)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS form_submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL DEFAULT '',
        phone TEXT NOT NULL DEFAULT '',
        email TEXT NOT NULL DEFAULT '',
        lift_type TEXT NOT NULL,
        capacity TEXT NOT NULL,
        height TEXT NOT NULL DEFAULT '',
        platform_size TEXT NOT NULL DEFAULT '',
        city TEXT NOT NULL DEFAULT '',
        stops TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'new',
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_content (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL DEFAULT '{}',
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS works_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        url TEXT NOT NULL UNIQUE,
        image_data TEXT,
        caption TEXT NOT NULL DEFAULT '',
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");

    // Первый запуск: заполняем портфолио файлами из /works/*.jpg
    $count = (int) $pdo->query('SELECT COUNT(*) FROM works_images')->fetchColumn();
    if ($count === 0 && is_dir(WORKS_DIR)) {
        $files = array_merge(
            (array) glob(WORKS_DIR . '/*.jpg'),
            (array) glob(WORKS_DIR . '/*.jpeg'),
            (array) glob(WORKS_DIR . '/*.png'),
            (array) glob(WORKS_DIR . '/*.webp')
        );
        sort($files, SORT_NATURAL);
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO works_images (url, caption, sort_order) VALUES (?, ?, ?)');
        foreach ($files as $i => $file) {
            $stmt->execute(['/works/' . basename($file), '', $i]);
        }
    }
}

/** Список работ (для главной и /nashi-raboty). */
function works_list(): array
{
    try {
        $rows = db()->query('SELECT url, caption FROM works_images ORDER BY sort_order ASC, id ASC')->fetchAll();
        if ($rows) {
            return $rows;
        }
    } catch (Throwable $e) {
        // БД недоступна — падаем на статический список
    }
    $fallback = [];
    foreach ((array) glob(WORKS_DIR . '/*.jpg') as $i => $file) {
        $fallback[] = ['url' => '/works/' . basename($file), 'caption' => ''];
    }
    sort($fallback);
    return $fallback;
}

function submission_insert(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO form_submissions (name, phone, email, lift_type, capacity, height, platform_size, city, stops)
         VALUES (:name, :phone, :email, :lift_type, :capacity, :height, :platform_size, :city, :stops)'
    );
    $stmt->execute([
        ':name' => $data['name'] ?? '',
        ':phone' => $data['phone'] ?? '',
        ':email' => $data['email'] ?? '',
        ':lift_type' => $data['liftType'] ?? '',
        ':capacity' => $data['capacity'] ?? '',
        ':height' => $data['height'] ?? '',
        ':platform_size' => $data['platformSize'] ?? '',
        ':city' => $data['city'] ?? '',
        ':stops' => $data['stops'] ?? '',
    ]);

    return (int) db()->lastInsertId();
}

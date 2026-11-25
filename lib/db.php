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

    // Миграция существующей базы: поля доказательства согласия и минимизации данных.
    // ALTER TABLE ADD COLUMN безопасен для SQLite и не затрагивает имеющиеся строки.
    $submissionColumns = [
        'consent_version' => "TEXT NOT NULL DEFAULT ''",
        'consent_given_at' => "TEXT NOT NULL DEFAULT ''",
        'marketing_consent' => 'INTEGER NOT NULL DEFAULT 0',
        'marketing_consent_at' => "TEXT NOT NULL DEFAULT ''",
        'ip_truncated' => "TEXT NOT NULL DEFAULT ''",
        'source_page' => "TEXT NOT NULL DEFAULT ''",
    ];
    foreach ($submissionColumns as $column => $definition) {
        if (!db_column_exists($pdo, 'form_submissions', $column)) {
            $pdo->exec('ALTER TABLE form_submissions ADD COLUMN ' . $column . ' ' . $definition);
        }
    }
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_form_submissions_created ON form_submissions(created_at)');

    // Журнал фактов согласия. Персональные данные в журнале не хранятся:
    // только псевдонимизированный идентификатор (хеш) и технические сведения.
    $pdo->exec("CREATE TABLE IF NOT EXISTS consent_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_hash TEXT NOT NULL,
        scope TEXT NOT NULL,
        granted INTEGER NOT NULL DEFAULT 1,
        doc_version TEXT NOT NULL DEFAULT '',
        form TEXT NOT NULL DEFAULT '',
        page TEXT NOT NULL DEFAULT '',
        ip_truncated TEXT NOT NULL DEFAULT '',
        user_agent TEXT NOT NULL DEFAULT '',
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_consent_events_created ON consent_events(created_at)');

    // Обращения субъектов ПД: отзыв согласия, доступ, уточнение, удаление.
    $pdo->exec("CREATE TABLE IF NOT EXISTS consent_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        contact TEXT NOT NULL,
        kind TEXT NOT NULL DEFAULT 'withdraw',
        message TEXT NOT NULL DEFAULT '',
        status TEXT NOT NULL DEFAULT 'new',
        ip_truncated TEXT NOT NULL DEFAULT '',
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        processed_at TEXT NOT NULL DEFAULT ''
    )");
    if (!db_column_exists($pdo, 'consent_requests', 'ip_truncated')) {
        $pdo->exec("ALTER TABLE consent_requests ADD COLUMN ip_truncated TEXT NOT NULL DEFAULT ''");
    }
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_consent_requests_status ON consent_requests(status, created_at)');

    // Защита админки от перебора пароля.
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_hash TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_login_attempts ON login_attempts(ip_hash, created_at)');

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

    // Первый запуск: заполняем портфолио файлами из /works/*.jpg (оригиналы, не превью)
    $count = (int) $pdo->query('SELECT COUNT(*) FROM works_images')->fetchColumn();
    if ($count === 0 && is_dir(WORKS_DIR)) {
        $files = array_merge(
            (array) glob(WORKS_DIR . '/*.jpg'),
            (array) glob(WORKS_DIR . '/*.jpeg'),
            (array) glob(WORKS_DIR . '/*.png')
        );
        // отбрасываем превью -480 и webp-версии: в портфолио только оригиналы
        $files = array_values(array_filter($files, static function (string $file): bool {
            $base = basename($file);
            return !str_contains($base, '-480.');
        }));
        sort($files, SORT_NATURAL);
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO works_images (url, caption, sort_order) VALUES (?, ?, ?)');
        foreach ($files as $i => $file) {
            $stmt->execute(['/works/' . basename($file), '', $i]);
        }
    }
}

function db_column_exists(PDO $pdo, string $table, string $column): bool
{
    try {
        foreach ($pdo->query('PRAGMA table_info(' . $table . ')') as $info) {
            if (isset($info['name']) && $info['name'] === $column) {
                return true;
            }
        }
    } catch (Throwable $e) {
        return false;
    }
    return false;
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
    foreach ((array) glob(WORKS_DIR . '/*.jpg') as $file) {
        $base = basename($file);
        if (str_contains($base, '-480.')) {
            continue;
        }
        $fallback[] = ['url' => '/works/' . $base, 'caption' => ''];
    }
    sort($fallback);
    return $fallback;
}

/**
 * Сохранение заявки вместе с доказательством согласия (ч. 3 ст. 9 152-ФЗ).
 */
function submission_insert(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO form_submissions
            (name, phone, email, lift_type, capacity, height, platform_size, city, stops,
             consent_version, consent_given_at, marketing_consent, marketing_consent_at,
             ip_truncated, source_page)
         VALUES
            (:name, :phone, :email, :lift_type, :capacity, :height, :platform_size, :city, :stops,
             :consent_version, :consent_given_at, :marketing_consent, :marketing_consent_at,
             :ip_truncated, :source_page)'
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
        ':consent_version' => $data['consentVersion'] ?? CONSENT_VERSION,
        ':consent_given_at' => $data['consentGivenAt'] ?? gmdate('Y-m-d H:i:s'),
        ':marketing_consent' => !empty($data['marketingConsent']) ? 1 : 0,
        ':marketing_consent_at' => !empty($data['marketingConsent']) ? gmdate('Y-m-d H:i:s') : '',
        ':ip_truncated' => $data['ipTruncated'] ?? '',
        ':source_page' => mb_substr((string) ($data['sourcePage'] ?? ''), 0, 200),
    ]);

    return (int) db()->lastInsertId();
}

/**
 * Псевдонимизированный идентификатор субъекта для журнала согласий.
 * Журнал не содержит телефона/email — только хеш.
 */
function contact_hash(string $phone, string $email): string
{
    $phone = preg_replace('/\D/', '', $phone) ?? '';
    $email = mb_strtolower(trim($email));
    return hash('sha256', $phone . '|' . $email . '|' . SITE_URL);
}

/** Обрезает IP (IPv4 — /24, IPv6 — /64): достаточно для доказательства, меньше данных. */
function ip_truncated(?string $ip = null): string
{
    $ip = $ip ?? (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        $parts[3] = '0';
        return implode('.', $parts) . '/24';
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ip);
        return implode(':', array_slice($parts, 0, 4)) . '::/64';
    }
    return '';
}

function client_user_agent(): string
{
    return mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

/** Запись факта согласия (или отказа) в журнал. */
function consent_event_add(string $subjectHash, string $scope, bool $granted, string $docVersion, string $form, string $page): void
{
    $stmt = db()->prepare(
        'INSERT INTO consent_events (subject_hash, scope, granted, doc_version, form, page, ip_truncated, user_agent)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $subjectHash,
        $scope,
        $granted ? 1 : 0,
        $docVersion,
        mb_substr($form, 0, 50),
        mb_substr($page, 0, 200),
        ip_truncated(),
        client_user_agent(),
    ]);
}

/** Автоматическое удаление заявок и технических журналов по срокам хранения. */
function submissions_purge_expired(): int
{
    $deleted = 0;
    try {
        $stmt = db()->prepare("DELETE FROM form_submissions WHERE created_at < datetime('now', ?)");
        $stmt->execute(['-' . max(1, SUBMISSION_RETENTION_MONTHS) . ' months']);
        $deleted = $stmt->rowCount();

        $events = db()->prepare("DELETE FROM consent_events WHERE created_at < datetime('now', ?)");
        $events->execute(['-' . CONSENT_EVENT_RETENTION_MONTHS . ' months']);

        $attempts = db()->prepare("DELETE FROM login_attempts WHERE created_at < datetime('now', '-1 day')");
        $attempts->execute();
    } catch (Throwable $e) {
        error_log('[retention] ' . $e->getMessage());
    }
    return $deleted;
}

function consent_request_add(string $contact, string $kind, string $message): int
{
    $allowed = ['withdraw', 'marketing', 'access', 'correct', 'delete', 'other'];
    if (!in_array($kind, $allowed, true)) {
        $kind = 'other';
    }
    $stmt = db()->prepare('INSERT INTO consent_requests (contact, kind, message, ip_truncated) VALUES (?, ?, ?, ?)');
    $stmt->execute([mb_substr($contact, 0, 200), $kind, mb_substr($message, 0, 2000), ip_truncated()]);
    return (int) db()->lastInsertId();
}

function consent_requests_all(): array
{
    return db()->query('SELECT * FROM consent_requests ORDER BY id DESC')->fetchAll();
}

function consent_request_set_status(int $id, string $status): void
{
    $allowed = ['new', 'processing', 'done'];
    if (!in_array($status, $allowed, true)) {
        $status = 'new';
    }
    $stmt = db()->prepare("UPDATE consent_requests SET status = ?, processed_at = CASE WHEN ? = 'done' THEN datetime('now') ELSE '' END WHERE id = ?");
    $stmt->execute([$status, $status, $id]);
}

function consent_request_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM consent_requests WHERE id = ?');
    $stmt->execute([$id]);
}

function login_attempt_fail(): void
{
    $stmt = db()->prepare('INSERT INTO login_attempts (ip_hash) VALUES (?)');
    $stmt->execute([hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? '') . '|login')]);
}

function login_attempt_count(): int
{
    $stmt = db()->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_hash = ? AND created_at > datetime('now', '-15 minutes')");
    $stmt->execute([hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? '') . '|login')]);
    return (int) $stmt->fetchColumn();
}

function login_attempt_clear(): void
{
    $stmt = db()->prepare('DELETE FROM login_attempts WHERE ip_hash = ?');
    $stmt->execute([hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? '') . '|login')]);
}

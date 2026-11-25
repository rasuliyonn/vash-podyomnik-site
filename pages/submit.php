<?php
/**
 * Обработчик заявки: POST /submit
 * Принимает JSON (fetch из формы) или form-urlencoded (без JS).
 * Возвращает JSON либо делает редирект с флагом ?ok=1.
 *
 * Заявка не принимается без явного согласия на обработку персональных данных.
 * Факт согласия сохраняется вместе с версией документа и временем (ч. 3 ст. 9 152-ФЗ).
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/seo.php';
require_once __DIR__ . '/../lib/consent.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input') ?: '{}';
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        $input = [];
    }
    $wantsJson = true;
} else {
    $input = $_POST;
    $wantsJson = false;
}

$name = trim((string) ($input['name'] ?? ''));
$phone = trim((string) ($input['phone'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$liftType = trim((string) ($input['liftType'] ?? $input['lift_type'] ?? ''));
$capacity = trim((string) ($input['capacity'] ?? ''));
$height = trim((string) ($input['height'] ?? ''));
$platformSize = trim((string) ($input['platformSize'] ?? $input['platform_size'] ?? ''));
$city = trim((string) ($input['city'] ?? ''));
$stops = trim((string) ($input['stops'] ?? ''));
$form = trim((string) ($input['form'] ?? 'calculator'));
$page = trim((string) ($input['page'] ?? ''));
$honeypot = trim((string) ($input['website'] ?? ''));

// Согласие: принимаем только явные значения «1»/«on»/true (не «пустую строку» и не произвольный текст).
$consentRaw = $input['consentData'] ?? false;
$consentData = $consentRaw === true || $consentRaw === 1 || $consentRaw === '1' || $consentRaw === 'on';
$marketingRaw = $input['consentNews'] ?? false;
$consentNews = $marketingRaw === true || $marketingRaw === 1 || $marketingRaw === '1' || $marketingRaw === 'on';

$errors = [];
if ($honeypot !== '') {
    $errors[] = 'Не удалось отправить заявку. Напишите на ' . CONTACT_EMAIL . '.';
}
if ($phone === '') {
    $errors[] = 'Укажите телефон для связи';
}
if (!$consentData) {
    $errors[] = 'Необходимо отметить согласие на обработку персональных данных';
}
if (strlen($phone) > 40) {
    $errors[] = 'Телефон слишком длинный';
}
if ($liftType === '' || $capacity === '' || $stops === '') {
    $errors[] = 'Обязательные поля отсутствуют';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Проверьте адрес электронной почты';
}
foreach (['name' => $name, 'email' => $email, 'city' => $city] as $field => $value) {
    if (mb_strlen($value) > 200) {
        $errors[] = 'Слишком длинное значение в поле ' . $field;
    }
}

// Простое ограничение частоты: не более 5 заявок с одного /24 за 10 минут.
if ($errors === []) {
    try {
        $rateStmt = db()->prepare("SELECT COUNT(*) FROM form_submissions WHERE ip_truncated = ? AND created_at > datetime('now', '-10 minutes')");
        $rateStmt->execute([ip_truncated()]);
        if ((int) $rateStmt->fetchColumn() >= 5) {
            http_response_code(429);
            if ($wantsJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'error' => 'Слишком много заявок. Попробуйте позже или напишите на ' . CONTACT_EMAIL . '.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            exit('<h1>Слишком много заявок</h1><p>Попробуйте позже или напишите на ' . e(CONTACT_EMAIL) . '.</p>');
        }
    } catch (Throwable $e) {
        error_log('[submit-rate] ' . $e->getMessage());
    }
}

if ($errors) {
    if ($wantsJson) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => implode('. ', $errors)], JSON_UNESCAPED_UNICODE);
        exit;
    }
    http_response_code(400);
    echo '<h1>Ошибка</h1><p>' . e(implode('. ', $errors)) . '</p><p><a href="/raschet">Вернуться к форме</a></p>';
    exit;
}

$consentAt = gmdate('Y-m-d H:i:s');
$subjectHash = contact_hash($phone, $email);

try {
    $id = submission_insert([
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'liftType' => $liftType,
        'capacity' => $capacity,
        'height' => $height,
        'platformSize' => $platformSize,
        'city' => $city,
        'stops' => $stops,
        'consentVersion' => CONSENT_VERSION,
        'consentGivenAt' => $consentAt,
        'marketingConsent' => $consentNews,
        'ipTruncated' => ip_truncated(),
        'sourcePage' => $page !== '' ? $page : '/raschet',
    ]);

    // Журнал согласий: хеш контактов, без открытых персональных данных.
    consent_event_add($subjectHash, 'data', true, CONSENT_VERSION, $form, $page !== '' ? $page : '/raschet');
    if ($consentNews) {
        consent_event_add($subjectHash, 'marketing', true, MARKETING_CONSENT_VERSION, $form, $page !== '' ? $page : '/raschet');
    }
} catch (Throwable $ex) {
    error_log('[submit] ' . $ex->getMessage());
    if ($wantsJson) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Не удалось сохранить заявку. Напишите на ' . CONTACT_EMAIL . '.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    http_response_code(500);
    echo '<h1>Ошибка</h1><p>Не удалось сохранить заявку. Попробуйте позже или напишите на ' . e(CONTACT_EMAIL) . '.</p>';
    exit;
}

if ($wantsJson) {
    http_response_code(201);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'id' => $id,
        'message' => 'Заявка отправлена. Инженер свяжется с вами для уточнения деталей.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Location: /raschet?ok=1', true, 303);
exit;

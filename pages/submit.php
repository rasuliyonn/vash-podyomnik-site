<?php
/**
 * Обработчик заявки: POST /submit
 * Принимает JSON (fetch из формы) или form-urlencoded (без JS).
 * Возвращает JSON либо делает редирект с флагом ?ok=1.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/seo.php';

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

// Серверная валидация согласия на обработку персональных данных: без отметки
// (см. отметку `consentData` в pages/partials/calculator.php) заявка не принимается.
$consentData = (bool) ($input['consentData'] ?? false);
$consentNews = (bool) ($input['consentNews'] ?? false);

$errors = [];
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
foreach (['name' => $name, 'email' => $email, 'city' => $city] as $field => $value) {
    if (mb_strlen($value) > 200) {
        $errors[] = 'Слишком длинное значение в поле ' . $field;
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
    ]);
} catch (Throwable $ex) {
    error_log('[submit] ' . $ex->getMessage());
    if ($wantsJson) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Не удалось сохранить заявку'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    http_response_code(500);
    echo '<h1>Ошибка</h1><p>Не удалось сохранить заявку. Попробуйте позже или напишите на ' . e(CONTACT_EMAIL) . '.</p>';
    exit;
}

if ($wantsJson) {
    http_response_code(201);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'id' => $id], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Location: /raschet?ok=1', true, 303);
exit;

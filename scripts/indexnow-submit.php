<?php
/**
 * Отправка всех публичных URL сайта в IndexNow (Яндекс, Bing, api.indexnow.org).
 * Запуск из консоли: php scripts/indexnow-submit.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/seo.php';
require_once __DIR__ . '/../lib/blog.php';
require_once __DIR__ . '/../lib/landings.php';

$key = '1c3fddc1f0b4408442f6c2e4f3f6f662';
$host = (string) parse_url(SITE_URL, PHP_URL_HOST);
$keyLocation = SITE_URL . '/' . $key . '.txt';

$paths = [
    '/',
    '/oborudovanie',
    '/raschet',
    '/gruzovye-podemniki-po-proektu',
    '/montazh-gruzovogo-podemnika',
    '/rostov-na-donu',
    '/equipment/shaftnye',
    '/equipment/konsolnye',
    '/equipment/servisnye',
    '/equipment/kottedzhnye',
    '/equipment/platformy',
    '/nashi-raboty',
    '/voprosy',
    '/blog',
    '/top-articles',
    '/o-kompanii',
    '/kontakty',
    '/requisites',
    '/offer',
    '/privacy',
    '/terms',
    '/consent',
    '/cookies',
    '/marketing',
    '/otzyv-soglasiya',
];

foreach (blog_articles() as $article) {
    $paths[] = '/blog/' . $article['slug'];
}

$urlList = array_values(array_unique(array_map(static fn(string $p): string => abs_url($p), $paths)));

$payload = json_encode([
    'host' => $host,
    'key' => $key,
    'keyLocation' => $keyLocation,
    'urlList' => $urlList,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$endpoints = [
    'IndexNow API' => 'https://api.indexnow.org/indexnow',
    'Yandex IndexNow' => 'https://yandex.com/indexnow',
    'Bing IndexNow' => 'https://www.bing.com/indexnow',
];

echo 'Отправка ' . count($urlList) . ' URL для хоста ' . $host . "...\n";

foreach ($endpoints as $name => $endpoint) {
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen((string) $payload),
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch) === 60 && PHP_OS_FAMILY === 'Windows') {
        // Локальная портативная сборка PHP на Windows без прописанного curl.cainfo
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
    }
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error !== '') {
        echo sprintf("  [ERR] %-16s %s\n", $name . ':', $error);
    } else {
        echo sprintf("  [HTTP %d] %-16s %s\n", $status, $name . ':', trim((string) $response) ?: 'OK');
    }
}

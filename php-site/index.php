<?php
/**
 * Front controller: единая точка маршрутизации PHP-версии сайта.
 * Статика (/assets, /works, /uploads) отдаётся Apache напрямую (см. .htaccess).
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/seo.php';
require_once __DIR__ . '/lib/blog.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (!is_string($rawPath)) {
    $rawPath = '/';
}
$path = '/' . trim($rawPath, '/');
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

function send_page(string $file, array $query = []): void
{
    foreach ($query as $key => $value) {
        $_GET[$key] = $value;
    }
    require __DIR__ . '/pages/' . $file;
}

$simpleRoutes = [
    '/' => 'home.php',
    '/oborudovanie' => 'catalog.php',
    '/nashi-raboty' => 'works.php',
    '/voprosy' => 'faq.php',
    '/blog' => 'blog-index.php',
    '/blog/rss.xml' => 'rss.php',
    '/o-kompanii' => 'about.php',
    '/kontakty' => 'contacts.php',
    '/raschet' => 'raschet.php',
    '/privacy' => 'privacy.php',
    '/terms' => 'terms.php',
    '/submit' => 'submit.php',
];

$adminRoutes = [
    '/admin' => 'admin/index.php',
    '/admin/login' => 'admin/login.php',
    '/admin/logout' => 'admin/logout.php',
    '/admin/submissions' => 'admin/submissions.php',
    '/admin/equipment' => 'admin/equipment.php',
    '/admin/equipment/edit' => 'admin/equipment-edit.php',
    '/admin/works' => 'admin/works.php',
    '/admin/settings' => 'admin/settings.php',
];

if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $now = SITE_BUILD_DATE;
    $urls = [
        ['loc' => '/', 'priority' => '1.0', 'freq' => 'weekly', 'lastmod' => $now],
        ['loc' => '/oborudovanie', 'priority' => '0.9', 'freq' => 'monthly', 'lastmod' => $now],
        ['loc' => '/raschet', 'priority' => '0.9', 'freq' => 'monthly', 'lastmod' => $now],
        ['loc' => '/nashi-raboty', 'priority' => '0.8', 'freq' => 'monthly', 'lastmod' => $now],
        ['loc' => '/voprosy', 'priority' => '0.8', 'freq' => 'monthly', 'lastmod' => $now],
        ['loc' => '/blog', 'priority' => '0.7', 'freq' => 'weekly', 'lastmod' => $now],
        ['loc' => '/o-kompanii', 'priority' => '0.6', 'freq' => 'yearly', 'lastmod' => $now],
        ['loc' => '/kontakty', 'priority' => '0.6', 'freq' => 'yearly', 'lastmod' => $now],
        ['loc' => '/privacy', 'priority' => '0.2', 'freq' => 'yearly', 'lastmod' => $now],
        ['loc' => '/terms', 'priority' => '0.2', 'freq' => 'yearly', 'lastmod' => $now],
    ];
    foreach (equipment_order_for_sitemap() as $slug) {
        $urls[] = ['loc' => '/equipment/' . $slug, 'priority' => '0.8', 'freq' => 'monthly', 'lastmod' => $now];
    }
    foreach (blog_articles() as $article) {
        $urls[] = [
            'loc' => '/blog/' . $article['slug'],
            'priority' => '0.6',
            'freq' => 'monthly',
            'lastmod' => $article['date'] ?? $now,
        ];
    }
    $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $entry) {
        $out .= "  <url>\n";
        $out .= '    <loc>' . abs_url($entry['loc']) . "</loc>\n";
        $out .= '    <lastmod>' . e($entry['lastmod']) . "</lastmod>\n";
        $out .= '    <changefreq>' . e($entry['freq']) . "</changefreq>\n";
        $out .= '    <priority>' . e($entry['priority']) . "</priority>\n";
        $out .= "  </url>\n";
    }
    $out .= "</urlset>\n";
    echo $out;
    exit;
}

function equipment_order_for_sitemap(): array
{
    return ['shaftnye', 'konsolnye', 'servisnye', 'kottedzhnye', 'platformy'];
}

if (isset($simpleRoutes[$path])) {
    $file = $simpleRoutes[$path];
    if ($file === 'submit.php' && $method !== 'POST') {
        http_response_code(303);
        header('Location: /raschet');
        exit;
    }
    send_page($file);
    exit;
}

if (isset($adminRoutes[$path])) {
    $file = $adminRoutes[$path];
    send_page($file);
    exit;
}

if (preg_match('#^/equipment/([a-z0-9-]+)$#', $path, $m)) {
    send_page('equipment-detail.php', ['slug' => $m[1]]);
    exit;
}

if (preg_match('#^/blog/([a-z0-9-]+)$#', $path, $m)) {
    send_page('blog-post.php', ['slug' => $m[1]]);
    exit;
}

send_page('not-found.php');

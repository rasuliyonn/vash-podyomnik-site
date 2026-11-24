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

/**
 * Security-заголовки на PHP-уровне: на части shared-хостингов (например REG.RU)
 * mod_headers недоступен и .htaccess-заголовки молча игнорируются.
 * Браузеры игнорируют HSTS по HTTP, но игнорируют корректно — слать его не страшно.
 */
function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://mc.yandex.ru https://www.googletagmanager.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self' https://mc.yandex.ru https://www.google-analytics.com https://region1.google-analytics.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
}
send_security_headers();

if (str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/upravlenie')) {
    header('X-Robots-Tag: noindex, nofollow');
}

$path = '/' . trim($rawPath, '/');
if ($rawPath !== '/' && substr($rawPath, -1) === '/' && $path !== '/') {
    // 301 на canonical-URL без завершающего слэша (дубликаты не обслуживаем)
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ' . $path . ($qs !== '' ? '?' . $qs : ''), true, 301);
    exit;
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
    '/top-articles' => 'top-articles.php',
    '/blog/rss.xml' => 'rss.php',
    '/o-kompanii' => 'about.php',
    '/kontakty' => 'contacts.php',
    '/raschet' => 'raschet.php',
    '/privacy' => 'privacy.php',
    '/terms' => 'terms.php',
    '/submit' => 'submit.php',
];

$adminRoutes = [
    '/upravlenie' => 'admin/index.php',
    '/upravlenie/login' => 'admin/login.php',
    '/upravlenie/logout' => 'admin/logout.php',
    '/upravlenie/submissions' => 'admin/submissions.php',
    '/upravlenie/equipment' => 'admin/equipment.php',
    '/upravlenie/equipment/edit' => 'admin/equipment-edit.php',
    '/upravlenie/works' => 'admin/works.php',
    '/upravlenie/settings' => 'admin/settings.php',
];

if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    require_once __DIR__ . '/lib/db.php';
    // lastmod отдаём только там, где он отражает реальную дату правки (статьи);
    // для статических страниц дату сборки намеренно НЕ выдаём — массовый
    // одинаковый lastmod поисковику ничего не сообщает.
    $urls = [
        ['loc' => '/', 'priority' => '1.0', 'freq' => 'weekly', 'images' => true],
        ['loc' => '/oborudovanie', 'priority' => '0.9', 'freq' => 'monthly'],
        ['loc' => '/raschet', 'priority' => '0.9', 'freq' => 'monthly'],
        ['loc' => '/nashi-raboty', 'priority' => '0.8', 'freq' => 'monthly', 'images' => true],
        ['loc' => '/voprosy', 'priority' => '0.8', 'freq' => 'monthly'],
        ['loc' => '/blog', 'priority' => '0.7', 'freq' => 'weekly'],
        ['loc' => '/top-articles', 'priority' => '0.7', 'freq' => 'weekly'],
        ['loc' => '/o-kompanii', 'priority' => '0.6', 'freq' => 'yearly'],
        ['loc' => '/kontakty', 'priority' => '0.6', 'freq' => 'yearly'],
        ['loc' => '/privacy', 'priority' => '0.2', 'freq' => 'yearly'],
        ['loc' => '/terms', 'priority' => '0.2', 'freq' => 'yearly'],
    ];
    foreach (equipment_order_for_sitemap() as $slug) {
        $urls[] = ['loc' => '/equipment/' . $slug, 'priority' => '0.8', 'freq' => 'monthly'];
    }
    foreach (blog_articles() as $article) {
        $urls[] = [
            'loc' => '/blog/' . $article['slug'],
            'priority' => '0.6',
            'freq' => 'monthly',
            'lastmod' => $article['date'] ?? null,
        ];
    }
    $worksImages = [];
    foreach (works_list() as $i => $work) {
        $title = $work['caption'] !== '' ? $work['caption'] : 'Выполненный объект ' . ($i + 1) . ' — грузовой подъёмник «Ваш Подъёмник»';
        $worksImages[] = ['loc' => $work['url'], 'title' => $title];
    }
    $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    foreach ($urls as $entry) {
        $out .= "  <url>\n";
        $out .= '    <loc>' . abs_url($entry['loc']) . "</loc>\n";
        if (!empty($entry['lastmod'])) {
            $out .= '    <lastmod>' . e($entry['lastmod']) . "</lastmod>\n";
        }
        $out .= '    <changefreq>' . e($entry['freq']) . "</changefreq>\n";
        $out .= '    <priority>' . e($entry['priority']) . "</priority>\n";
        if (!empty($entry['images'])) {
            foreach ($worksImages as $image) {
                $out .= "    <image:image>\n";
                $out .= '      <image:loc>' . abs_url($image['loc']) . "</image:loc>\n";
                $out .= '      <image:title>' . e($image['title']) . "</image:title>\n";
                $out .= "    </image:image>\n";
            }
        }
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

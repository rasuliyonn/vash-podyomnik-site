<?php
/**
 * Front controller: единая точка маршрутизации PHP-версии сайта.
 * Статика (/assets, /works, /uploads) отдаётся Apache напрямую (см. .htaccess).
 */

declare(strict_types=1);

// Не показываем посетителю внутренние ошибки: они могут содержать пути и данные.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/seo.php';
require_once __DIR__ . '/lib/blog.php';
require_once __DIR__ . '/lib/landings.php';

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
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://mc.yandex.ru; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://mc.yandex.ru; font-src 'self'; connect-src 'self' https://mc.yandex.ru; frame-src https://www.youtube.com https://player.vimeo.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
    header('X-Permitted-Cross-Domain-Policies: none');
}
send_security_headers();

if (str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/upravlenie')) {
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store, max-age=0');
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
    '/consent' => 'consent.php',
    '/cookies' => 'cookies.php',
    '/terms' => 'terms.php',
    '/offer' => 'offer.php',
    '/marketing' => 'marketing.php',
    '/requisites' => 'requisites.php',
    '/otzyv-soglasiya' => 'consent-withdraw.php',
    '/submit' => 'submit.php',
];

$landingRoutes = [
    '/gruzovye-podemniki-po-proektu' => 'gruzovye-podemniki-po-proektu',
    '/montazh-gruzovogo-podemnika' => 'montazh-gruzovogo-podemnika',
    '/rostov-na-donu' => 'rostov-na-donu',
];

$adminRoutes = [
    '/upravlenie' => 'admin/index.php',
    '/upravlenie/login' => 'admin/login.php',
    '/upravlenie/logout' => 'admin/logout.php',
    '/upravlenie/submissions' => 'admin/submissions.php',
    '/upravlenie/consent-requests' => 'admin/consent-requests.php',
    '/upravlenie/equipment' => 'admin/equipment.php',
    '/upravlenie/equipment/edit' => 'admin/equipment-edit.php',
    '/upravlenie/works' => 'admin/works.php',
    '/upravlenie/settings' => 'admin/settings.php',
];

if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    require_once __DIR__ . '/lib/db.php';
    require_once __DIR__ . '/lib/equipment.php';
    // lastmod отдаём только там, где он отражает реальную дату правки (статьи);
    // для статических страниц дату сборки намеренно НЕ выдаём — массовый
    // одинаковый lastmod поисковику ничего не сообщает.
    $urls = [
        [
            'loc' => '/',
            'priority' => '1.0',
            'freq' => 'weekly',
            'imageList' => [
                ['loc' => '/assets/hero-real.jpg', 'title' => 'Грузовой подъёмник от производителя «Ваш Подъёмник» в Ростове-на-Дону'],
                ['loc' => '/assets/object-real.jpg', 'title' => 'Шахтный грузовой подъёмник на промышленном объекте — «Ваш Подъёмник»'],
            ],
        ],
        ['loc' => '/oborudovanie', 'priority' => '0.9', 'freq' => 'monthly'],
        ['loc' => '/raschet', 'priority' => '0.9', 'freq' => 'monthly'],
    ];
    foreach (landings_slugs() as $landingSlug) {
        $landing = landing_get($landingSlug);
        if ($landing !== null) {
            $urls[] = ['loc' => $landing['path'], 'priority' => '0.9', 'freq' => 'monthly'];
        }
    }
    $eqAll = equipment_static();
    foreach (equipment_order_for_sitemap() as $slug) {
        $eqTitle = $eqAll[$slug]['title'] ?? $slug;
        $urls[] = [
            'loc' => '/equipment/' . $slug,
            'priority' => '0.9',
            'freq' => 'monthly',
            'imageList' => [
                ['loc' => '/assets/equipment-' . $slug . '.jpg', 'title' => $eqTitle . ' — производство и монтаж «Ваш Подъёмник»'],
            ],
        ];
    }
    $urls[] = ['loc' => '/nashi-raboty', 'priority' => '0.8', 'freq' => 'monthly', 'images' => true];
    $urls[] = ['loc' => '/voprosy', 'priority' => '0.8', 'freq' => 'monthly'];
    $urls[] = ['loc' => '/blog', 'priority' => '0.7', 'freq' => 'weekly'];
    $urls[] = ['loc' => '/top-articles', 'priority' => '0.7', 'freq' => 'weekly'];
    $urls[] = ['loc' => '/o-kompanii', 'priority' => '0.6', 'freq' => 'yearly'];
    $urls[] = ['loc' => '/kontakty', 'priority' => '0.6', 'freq' => 'yearly'];
    foreach (blog_articles() as $article) {
        $urls[] = [
            'loc' => '/blog/' . $article['slug'],
            'priority' => '0.6',
            'freq' => 'monthly',
            'lastmod' => $article['date'] ?? null,
        ];
    }
    $urls[] = ['loc' => '/requisites', 'priority' => '0.3', 'freq' => 'yearly'];
    $urls[] = ['loc' => '/offer', 'priority' => '0.3', 'freq' => 'yearly'];
    $urls[] = ['loc' => '/privacy', 'priority' => '0.3', 'freq' => 'yearly'];
    $urls[] = ['loc' => '/terms', 'priority' => '0.2', 'freq' => 'yearly'];
    $urls[] = ['loc' => '/consent', 'priority' => '0.2', 'freq' => 'yearly'];
    $urls[] = ['loc' => '/cookies', 'priority' => '0.2', 'freq' => 'yearly'];
    $urls[] = ['loc' => '/marketing', 'priority' => '0.1', 'freq' => 'yearly'];
    $urls[] = ['loc' => '/otzyv-soglasiya', 'priority' => '0.1', 'freq' => 'yearly'];

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
        $entryImages = !empty($entry['images']) ? $worksImages : ($entry['imageList'] ?? []);
        foreach ($entryImages as $image) {
            $out .= "    <image:image>\n";
            $out .= '      <image:loc>' . abs_url($image['loc']) . "</image:loc>\n";
            $out .= '      <image:title>' . e($image['title']) . "</image:title>\n";
            $out .= "    </image:image>\n";
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

if (isset($landingRoutes[$path])) {
    send_page('landing.php', ['landing' => $landingRoutes[$path]]);
    exit;
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

<?php
/**
 * SEO/JSON-LD helpers.
 * Все функции возвращают строки, безопасные для вывода (или чистые JSON-LD).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Абсолютный URL из относительного пути. */
function abs_url(string $path): string
{
    if ($path === '/' || $path === '') {
        return SITE_URL . '/';
    }
    return SITE_URL . '/' . ltrim($path, '/');
}

/** Organization + LocalBusiness (единый граф сущности бренда). */
function organization_jsonld(): array
{
    $org = [
        '@type' => ['Organization', 'LocalBusiness'],
        '@id' => SITE_URL . '/#organization',
        'name' => SITE_NAME,
        'legalName' => COMPANY_NAME,
        'url' => SITE_URL . '/',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => abs_url('/assets/vash-podyomnik-logo.jpg'),
        ],
        'image' => abs_url('/assets/og-cover.png'),
        'email' => CONTACT_EMAIL,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'ул. Промышленная, д. 12',
            'addressLocality' => COMPANY_CITY,
            'postalCode' => '344000',
            'addressCountry' => 'RU',
        ],
        'taxID' => 'RU' . COMPANY_INN,
        'vatID' => COMPANY_INN,
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Россия',
        ],
        'knowsAbout' => [
            'грузовые подъёмники',
            'шахтные подъёмники',
            'консольные подъёмники',
            'сервисные (малые грузовые) подъёмники',
            'коттеджные лифты',
            'грузовые платформы',
            'ТР ТС 011/2011',
        ],
    ];
    if (CONTACT_PHONE !== '') {
        $org['telephone'] = CONTACT_PHONE;
    }
    if (CONTACT_HOURS !== '') {
        $org['openingHours'] = CONTACT_HOURS;
    }

    return ['@context' => 'https://schema.org', '@graph' => [$org]];
}

function website_jsonld(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => SITE_URL . '/#website',
        'url' => SITE_URL . '/',
        'name' => SITE_NAME,
        'inLanguage' => 'ru',
        'publisher' => ['@id' => SITE_URL . '/#organization'],
    ];
}

/** Хлебные крошки: [['name' => ..., 'path' => ...], ...] */
function breadcrumb_jsonld(array $crumbs): array
{
    $items = [];
    foreach ($crumbs as $i => $crumb) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $crumb['name'],
            'item' => abs_url($crumb['path']),
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

function itemlist_jsonld(string $name, array $urls): array
{
    $items = [];
    $position = 0;
    foreach ($urls as $url) {
        $position++;
        $items[] = ['@type' => 'ListItem', 'position' => $position, 'url' => abs_url($url)];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $name,
        'itemListElement' => $items,
    ];
}

function service_jsonld(string $name, string $description, string $path): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'serviceType' => $name,
        'name' => $name,
        'description' => $description,
        'url' => abs_url($path),
        'provider' => ['@id' => SITE_URL . '/#organization'],
        'areaServed' => ['@type' => 'Country', 'name' => 'Россия'],
    ];
}

function article_jsonld(array $article): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'mainEntityOfPage' => abs_url('/blog/' . $article['slug']),
        'headline' => $article['title'],
        'description' => $article['description'],
        'datePublished' => $article['date'],
        'dateModified' => $article['dateModified'] ?? $article['date'],
        'inLanguage' => 'ru',
        'image' => abs_url('/assets/og-cover.png'),
        'author' => ['@id' => SITE_URL . '/#organization'],
        'publisher' => ['@id' => SITE_URL . '/#organization'],
        'articleSection' => $article['category'] ?? 'Гайды',
    ];
}

/** FAQPage из плоского списка [['q'=>..., 'a'=>...], ...] */
function faq_jsonld(array $items): array
{
    $entities = [];
    foreach ($items as $item) {
        $entities[] = [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $entities,
    ];
}

function jsonld_script(array $data): string
{
    return '<script type="application/ld+json">' .
        json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        '</script>' . "\n";
}

<?php
/**
 * SEO/JSON-LD helpers.
 * Все функции возвращают строки, безопасные для вывода (или чистые JSON-LD).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/company.php';

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

/** Сокращение до $max символов по границе слова (для meta description). */
function meta_truncate(string $s, int $max = 160): string
{
    if (mb_strlen($s) <= $max) {
        return $s;
    }
    $cut = mb_substr($s, 0, $max);
    $pos = mb_strrpos($cut, ' ');
    if ($pos !== false && $pos >= (int) ($max * 0.6)) {
        $cut = mb_substr($cut, 0, $pos);
    }
    return rtrim($cut, " \t\n\r\0\x0B,;.—–-«»()\"'") . '…';
}

/** Organization + LocalBusiness (единый граф сущности бренда). */
function organization_jsonld(): array
{
    $org = [
        '@type' => ['Organization', 'LocalBusiness'],
        '@id' => SITE_URL . '/#organization',
        'name' => SITE_NAME,
        'legalName' => COMPANY_LEGAL_NAME !== '' ? COMPANY_LEGAL_NAME : SITE_NAME,
        'url' => SITE_URL . '/',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => abs_url('/assets/apple-touch-icon.png'), // растр 180×180 (Google не индексирует SVG-логотипы надёжно)
            'width' => 180,
            'height' => 180,
        ],
        'image' => abs_url('/assets/og-cover.png'),
        'email' => CONTACT_EMAIL,
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => COMPANY_CITY,
            'addressRegion' => COMPANY_REGION,
            'addressCountry' => 'RU',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'sales',
            'email' => CONTACT_EMAIL,
            'areaServed' => 'RU',
            'availableLanguage' => ['Russian'],
        ],
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
    // Улица добавляется только если в адресе указано что-то кроме города:
    // дублировать «г. Ростов-на-Дону» в streetAddress и addressLocality нельзя.
    $address = trim((string) COMPANY_ADDRESS);
    if ($address !== '' && mb_stripos($address, COMPANY_CITY) === false) {
        $org['address']['streetAddress'] = $address;
    }
    // ИНН публикуется только после проверки формата и контрольной суммы.
    if (company_inn_value() !== '') {
        $org['taxID'] = company_inn_value();
    }
    if (CONTACT_PHONE !== '') {
        $org['telephone'] = CONTACT_PHONE;
    }
    if (MAX_PROFILE_URL !== '') {
        $org['sameAs'] = [MAX_PROFILE_URL];
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

function service_jsonld(string $name, string $description, string $path, ?array $areaServed = null): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'serviceType' => $name,
        'name' => $name,
        'description' => $description,
        'url' => abs_url($path),
        'provider' => ['@id' => SITE_URL . '/#organization'],
        'areaServed' => $areaServed ?? ['@type' => 'Country', 'name' => 'Россия'],
    ];
}

/** Число слов в тексте (unicode-aware, для Article wordCount). */
function word_count_ru(string $html): int
{
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    return is_array($words) ? count($words) : 0;
}

function article_jsonld(array $article): array
{
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'mainEntityOfPage' => abs_url('/blog/' . $article['slug']),
        'headline' => $article['title'],
        'description' => $article['description'],
        'datePublished' => $article['date'],
        'dateModified' => $article['dateModified'] ?? $article['date'],
        'inLanguage' => 'ru',
        'image' => [
            '@type' => 'ImageObject',
            'url' => abs_url('/assets/og-cover.png'),
            'width' => 1200,
            'height' => 630,
        ],
        'author' => ['@id' => SITE_URL . '/#organization'],
        'publisher' => ['@id' => SITE_URL . '/#organization'],
        'articleSection' => $article['category'] ?? 'Гайды',
    ];
    if (!empty($article['body'])) {
        $data['wordCount'] = word_count_ru((string) $article['body']);
    }
    return $data;
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

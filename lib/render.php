<?php
/**
 * Общий каркас страниц: <head>, шапка, футер, аналитика.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/seo.php';

function nav_links(): array
{
    return [
        ['href' => '/oborudovanie', 'label' => 'Оборудование'],
        ['href' => '/nashi-raboty', 'label' => 'Наши работы'],
        ['href' => '/voprosy', 'label' => 'Вопросы'],
        ['href' => '/blog', 'label' => 'Блог'],
        ['href' => '/kontakty', 'label' => 'Контакты'],
    ];
}

function logo_src(): string
{
    return '/assets/logo.svg';
}

/** href для tel: — из отображаемого телефона (+7 (952) 603-57-85 → +79526035785). */
function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^+0-9]/', '', $phone);
}

/**
 * Ссылка на мессенджер MAX. Прямой ссылки «по номеру» у MAX нет, поэтому без
 * MAX_PROFILE_URL ведём на веб-версию мессенджера (там контакт ищется по номеру).
 */
function max_href(): string
{
    return MAX_PROFILE_URL !== '' ? MAX_PROFILE_URL : MAX_WEB_URL;
}

/**
 * Знак компании, встроенный SVG (прозрачный фон, для тёмного интерфейса).
 */
function brand_mark_svg(): void
{
    ?><svg class="brand-mark" viewBox="0 0 64 64" width="46" height="46" aria-hidden="true" focusable="false">
    <rect x="11" y="12" width="6" height="32" fill="#F4F4F2"/>
    <rect x="47" y="12" width="6" height="32" fill="#F4F4F2"/>
    <path d="M32 3 L19.5 20.5 H27.5 V44 H36.5 V20.5 H44.5 Z" fill="#F4F4F2"/>
    <rect x="6" y="49" width="52" height="9" fill="#D3191D"/>
  </svg><?php
}

function render_header(): void
{
    ?>
<a class="skip-link" href="#top">Перейти к содержимому</a>
<header class="site-header">
  <a class="brand" href="/">
    <?php brand_mark_svg(); ?>
    <span>
      <b>ВАШ ПОДЪЁМНИК</b>
      <small>Ростов-на-Дону · вся Россия</small>
    </span>
  </a>
  <nav aria-label="Основная навигация">
    <?php foreach (nav_links() as $link): ?>
    <a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="header-actions">
    <?php if (CONTACT_PHONE !== ''): ?><a class="header-phone" href="<?= e(tel_href(CONTACT_PHONE)) ?>"><?= e(CONTACT_PHONE) ?></a><?php endif; ?>
    <a class="header-cta" href="/raschet"><span class="header-cta-label"><span class="header-cta-long">Получить </span>расчёт</span><span class="header-cta-arrow" aria-hidden="true">↗</span></a>
  </div>
</header>
<nav class="mobile-bottom-nav" aria-label="Мобильная навигация">
  <?php foreach (nav_links() as $link): ?>
  <a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a>
  <?php endforeach; ?>
  <a href="/raschet" class="mobile-nav-cta"><span><span class="nav-cta-long">Получить </span>расчёт</span></a>
</nav>
<?php
}

function render_footer(): void
{
    ?>
<footer>
  <div class="footer-main">
    <div>
      <div class="brand footer-brand">
        <?php brand_mark_svg(); ?>
        <span>
          <b>ВАШ ПОДЪЁМНИК</b>
          <small>Проектирование · производство · монтаж</small>
        </span>
      </div>
      <h2>Поднимем вашу задачу<br><em>на нужный уровень</em></h2>
    </div>
    <div class="footer-action">
      <p>Получите консультацию инженера и предварительный расчёт оборудования под ваш объект.</p>
      <a class="primary-button" href="/raschet">Рассчитать проект<span aria-hidden="true">↗</span></a>
    </div>
  </div>
  <div class="footer-cols">
    <div>
      <h3>Оборудование</h3>
      <a href="/oborudovanie">Каталог оборудования</a>
      <a href="/equipment/shaftnye">Шахтные подъёмники</a>
      <a href="/equipment/konsolnye">Консольные подъёмники</a>
      <a href="/equipment/servisnye">Сервисные подъёмники</a>
      <a href="/equipment/kottedzhnye">Коттеджные лифты</a>
      <a href="/equipment/platformy">Грузовые платформы</a>
    </div>
    <div>
      <h3>Компания</h3>
      <a href="/o-kompanii">О компании</a>
      <a href="/nashi-raboty">Наши работы</a>
      <a href="/voprosy">Вопросы и ответы</a>
      <a href="/blog">Блог</a>
      <a href="/top-articles">Топ статей</a>
      <a href="/raschet">Расчёт стоимости</a>
      <a href="/kontakty">Контакты</a>
    </div>
    <div>
      <h3>Документы</h3>
      <a href="/privacy">Политика конфиденциальности</a>
      <a href="/terms">Пользовательское соглашение</a>
      <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>
    </div>
    <div>
      <h3>Производитель</h3>
      <p class="footer-nap">
        <?= e(COMPANY_NAME) ?><br>
        ИНН <?= e(COMPANY_INN) ?> · ОГРН <?= e(COMPANY_OGRN) ?><br>
        <?= e(COMPANY_ADDRESS) ?>
        <?php if (CONTACT_PHONE !== ''): ?><br><a href="<?= e(tel_href(CONTACT_PHONE)) ?>"><?= e(CONTACT_PHONE) ?></a><?php endif; ?>
        <?php if (MAX_PROFILE_URL !== ''): ?><br><a href="<?= e(MAX_PROFILE_URL) ?>" rel="noopener">Мессенджер MAX</a><?php endif; ?>
        <?php if (CONTACT_HOURS !== ''): ?><br><?= e(CONTACT_HOURS) ?><?php endif; ?>
      </p>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 · Ростов-на-Дону · Работаем по России</span>
    <div class="footer-links">
      <a href="/privacy">Политика конфиденциальности</a>
      <a href="/terms">Пользовательское соглашение</a>
      <a href="<?= e('mailto:' . CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>
      <a href="/">НА ГЛАВНУЮ ↑</a>
    </div>
  </div>
</footer>
<?php
}

function render_analytics(): void
{
    // Аналитика загружается только после явного согласия пользователя на cookie
    // (см. баннер и cookie vashpodemnik61_cookie_ok=1). Без согласия скрипты не выполняются.
    if (($_COOKIE['vashpodemnik61_cookie_ok'] ?? '') !== '1') {
        return;
    }
    if (YANDEX_METRIKA_ID !== '') {
        $id = (int) YANDEX_METRIKA_ID;
        // anonymizeIP — IP-адреса обезличиваются; webvisor отключён, чтобы не записывать
        // ввод пользователей в формы (персональные данные) без отдельного согласия.
        echo '<script>(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window,document,"script","https://mc.yandex.ru/metrika/tag.js","ym");ym(' . $id . ',"init",{clickmap:true,trackLinks:true,accurateTrackBounce:true,anonymizeIP:true});</script>' . "\n";
        echo '<noscript><div><img src="https://mc.yandex.ru/watch/' . $id . '" style="position:absolute;left:-9999px" alt=""></div></noscript>' . "\n";
    }
    if (GA4_ID !== '') {
        $g = preg_replace('/[^A-Za-z0-9-]/', '', GA4_ID);
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $g . '"></script>' . "\n";
        echo '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . $g . '",{"anonymize_ip":true});</script>' . "\n";
    }
}

/**
 * Уведомление о файлах cookie (см. assets/cookie-banner.js).
 * Баннер скрывается, если пользователь уже принял их (cookie vashpodemnik61_cookie_ok).
 * При нажатии на «Принять» записывается cookie на год — это согласие на использование
 * анонимизированной статистики (см. Политику обработки персональных данных, раздел 8.3).
 */
function render_cookie_banner(): void
{
    if (isset($_COOKIE['vashpodemnik61_cookie_ok'])) {
        return;
    }
    ?>
    <div class="cookie-notice" id="cookieNotice" role="status" aria-live="polite">
      <p>Этот сайт использует файлы cookie и подсчитывает статистику визитов (Яндекс.Метрика и Google Analytics с анонимизацией IP-адресов). Персональные данные не собираются и не передаются третьим лицам без необходимости исполнения договора.</p>
      <div class="cookie-notice-actions">
        <button type="button" class="cookie-notice-accept" id="cookieNoticeAccept">Принять</button>
        <button type="button" class="cookie-notice-reject" id="cookieNoticeReject">Отклонить</button>
      </div>
    </div>
    <?php
}

/**
 * Начало страницы.
 * @param string $title Заголовок страницы (без суффикса, он добавится)
 * @param string $description Мета-описание
 * @param string $canonicalPath Абсолютный путь (/blog/post) для canonical/OG
 * @param array  $options og_type ('website'|'article'), jsonld (array of arrays), noindex (bool), h1_class
 */
function page_start(string $title, string $description, string $canonicalPath, array $options = []): void
{
    $ogType = $options['og_type'] ?? 'website';
    $jsonld = $options['jsonld'] ?? [];
    $noindex = (bool) ($options['noindex'] ?? false);
    $fullTitle = $title === '' ? SITE_NAME : $title . ' — ' . SITE_NAME;
    $canonical = abs_url($canonicalPath);
    $ogImage = abs_url('/assets/og-cover.png');
    ?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if ($noindex): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
<meta name="theme-color" content="#070909">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
<link rel="preload" href="/assets/fonts/pt-sans-narrow-700-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/pt-sans-400-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/site.css?v=5">
<link rel="stylesheet" href="/assets/responsive.css?v=2">
<meta property="og:type" content="<?= e($ogType) ?>">
<meta property="og:locale" content="ru_RU">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($fullTitle) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<?php if (YANDEX_VERIFICATION !== ''): ?><meta name="yandex-verification" content="<?= e(YANDEX_VERIFICATION) ?>"><?php endif; ?>
<?php if (GOOGLE_VERIFICATION !== ''): ?><meta name="google-site-verification" content="<?= e(GOOGLE_VERIFICATION) ?>"><?php endif; ?>
<link rel="alternate" type="application/rss+xml" title="Блог «Ваш Подъёмник»" href="<?= abs_url('/blog/rss.xml') ?>">
<?php
    echo jsonld_script(organization_jsonld());
    echo jsonld_script(website_jsonld());
    foreach ($jsonld as $block) {
        echo jsonld_script($block);
    }
    ?>
</head>
<body>
<?php
    render_header();
}

function page_end(): void
{
    render_footer();
    render_cookie_banner();
    echo '<script src="/assets/app.js" defer></script>' . "\n";
    echo '<script src="/assets/cookie-banner.js" defer></script>' . "\n";
    render_analytics();
    echo "</body>\n</html>\n";
}


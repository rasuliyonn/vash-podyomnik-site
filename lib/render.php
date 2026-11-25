<?php
/**
 * Общий каркас страниц: <head>, шапка, футер, аналитика, cookie.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/company.php';
require_once __DIR__ . '/consent.php';

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

/** Текущий путь без query string (для форм и журнала согласий). */
function current_path(): string
{
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    return is_string($path) && $path !== '' ? $path : '/';
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

/** Список юридических документов (единый источник для футера и страниц). */
function legal_document_links(): array
{
    return [
        ['href' => '/privacy', 'label' => 'Политика обработки персональных данных'],
        ['href' => '/consent', 'label' => 'Согласие на обработку персональных данных'],
        ['href' => '/cookies', 'label' => 'Политика использования cookie'],
        ['href' => '/terms', 'label' => 'Пользовательское соглашение'],
        ['href' => '/offer', 'label' => 'Условия заказа, оплаты и возврата'],
        ['href' => '/marketing', 'label' => 'Согласие на рекламные сообщения'],
        ['href' => '/requisites', 'label' => 'Реквизиты и информация о продавце'],
        ['href' => '/otzyv-soglasiya', 'label' => 'Отозвать согласие / обращение по ПД'],
    ];
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
      <a href="/gruzovye-podemniki-po-proektu">По индивидуальному проекту</a>
      <a href="/equipment/shaftnye">Шахтные подъёмники</a>
      <a href="/equipment/konsolnye">Консольные подъёмники</a>
      <a href="/equipment/servisnye">Сервисные подъёмники</a>
      <a href="/equipment/kottedzhnye">Коттеджные лифты</a>
      <a href="/equipment/platformy">Грузовые платформы</a>
    </div>
    <div>
      <h3>Компания</h3>
      <a href="/o-kompanii">О компании</a>
      <a href="/montazh-gruzovogo-podemnika">Монтаж подъёмников</a>
      <a href="/rostov-na-donu">Подъёмники в Ростове-на-Дону</a>
      <a href="/nashi-raboty">Наши работы</a>
      <a href="/voprosy">Вопросы и ответы</a>
      <a href="/blog">Блог</a>
      <a href="/top-articles">Топ статей</a>
      <a href="/raschet">Расчёт стоимости</a>
      <a href="/kontakty">Контакты</a>
    </div>
    <div>
      <h3>Документы</h3>
      <?php foreach (legal_document_links() as $doc): ?>
      <a href="<?= e($doc['href']) ?>"><?= e($doc['label']) ?></a>
      <?php endforeach; ?>
    </div>
    <div>
      <h3>Производитель</h3>
      <p class="footer-nap">
        <?= company_requisites_html(true) ?>
        <?php if (CONTACT_PHONE !== ''): ?><br><a href="<?= e(tel_href(CONTACT_PHONE)) ?>"><?= e(CONTACT_PHONE) ?></a><?php endif; ?>
        <?php if (MAX_PROFILE_URL !== ''): ?><br><a href="<?= e(MAX_PROFILE_URL) ?>" rel="noopener noreferrer">Мессенджер MAX</a><?php endif; ?>
        <?php if (CONTACT_HOURS !== ''): ?><br><?= e(CONTACT_HOURS) ?><?php endif; ?>
        <br><a href="<?= e('mailto:' . CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>
      </p>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 · Ростов-на-Дону · Работаем по России</span>
    <div class="footer-links">
      <a href="/privacy">Политика конфиденциальности</a>
      <a href="/terms">Пользовательское соглашение</a>
      <a href="/cookies">Cookie</a>
      <button type="button" class="footer-cookie-settings" id="cookieSettingsOpen">Настройки cookie</button>
      <a href="/">НА ГЛАВНУЮ ↑</a>
    </div>
  </div>
</footer>
<?php
}

/**
 * Аналитика: только Яндекс.Метрика и только после согласия на аналитические cookie.
 * GA4/Google Tag не используются — данные не передаются за пределы РФ.
 */
function render_analytics(): void
{
    if (!consent_analytics_allowed()) {
        return;
    }
    $id = (int) YANDEX_METRIKA_ID;
    // webvisor отключён: запись ввода в формы — это персональные данные.
    echo '<script>(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window,document,"script","https://mc.yandex.ru/metrika/tag.js","ym");ym(' . $id . ',"init",{clickmap:true,trackLinks:true,accurateTrackBounce:true,anonymizeIP:true});</script>' . "\n";
    echo '<noscript><div><img src="https://mc.yandex.ru/watch/' . $id . '" style="position:absolute;left:-9999px" alt=""></div></noscript>' . "\n";
}

/**
 * Баннер cookie с выбором категорий. Необязательные скрипты не загружаются
 * до сохранения согласия; выбор можно изменить кнопкой в футере.
 */
function render_cookie_banner(): void
{
    $state = consent_state();
    $hidden = $state['set'] ? ' hidden' : '';
    ?>
    <div class="cookie-notice" id="cookieNotice" role="dialog" aria-modal="false" aria-labelledby="cookieNoticeTitle" aria-describedby="cookieNoticeText" data-analytics="<?= $state['analytics'] ? '1' : '0' ?>"<?= $hidden ?>>
      <div class="cookie-notice-body">
        <p class="cookie-notice-title" id="cookieNoticeTitle">Файлы cookie</p>
        <p id="cookieNoticeText">Необходимые файлы cookie обеспечивают работу сайта. Аналитические cookie (Яндекс.Метрика) помогают понять, как используется сайт, и включаются только с вашего согласия. Подробнее — в <a href="/cookies">Политике использования cookie</a>.</p>
        <div class="cookie-notice-settings" id="cookieNoticeSettings" hidden>
          <label class="cookie-option">
            <input type="checkbox" checked disabled>
            <span><b>Необходимые</b> — всегда включены: сохранение выбора cookie, защита форм и админки.</span>
          </label>
          <label class="cookie-option">
            <input type="checkbox" id="cookieOptAnalytics">
            <span><b>Аналитические</b> — Яндекс.Метрика: обезличенная статистика посещений.</span>
          </label>
        </div>
      </div>
      <div class="cookie-notice-actions">
        <button type="button" class="cookie-notice-accept" id="cookieNoticeAcceptAll">Принять все</button>
        <button type="button" class="cookie-notice-reject" id="cookieNoticeAcceptNecessary">Только необходимые</button>
        <button type="button" class="cookie-notice-settings-btn" id="cookieNoticeSettingsToggle" aria-expanded="false">Настроить</button>
        <button type="button" class="cookie-notice-accept" id="cookieNoticeSave" hidden>Сохранить выбор</button>
        <a class="cookie-notice-more" href="/cookies">Что это значит</a>
      </div>
      <noscript><p class="cookie-notice-noscript">Без JavaScript аналитика не загружается. Настройки cookie можно изменить, включив JavaScript.</p></noscript>
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
<link rel="stylesheet" href="/assets/site.css?v=6">
<link rel="stylesheet" href="/assets/responsive.css?v=3">
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
    echo '<script src="/assets/cookie-consent.js" defer></script>' . "\n";
    render_analytics();
    echo "</body>\n</html>\n";
}

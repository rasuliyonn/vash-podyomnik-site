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
    return '/assets/vash-podyomnik-logo.jpg';
}

function render_header(): void
{
    $phone = CONTACT_PHONE;
    ?>
<header class="site-header">
  <a class="brand" href="/" aria-label="На главную">
    <img class="brand-logo" src="<?= logo_src() ?>" alt="Логотип Ваш Подъёмник" width="50" height="50">
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
  <a class="header-cta" href="/raschet">Получить расчёт<span aria-hidden="true">↗</span></a>
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
        <img class="brand-logo" src="<?= logo_src() ?>" alt="Логотип Ваш Подъёмник" width="50" height="50">
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
        <?php if (CONTACT_PHONE !== ''): ?><br><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', CONTACT_PHONE)) ?>"><?= e(CONTACT_PHONE) ?></a><?php endif; ?>
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
    if (YANDEX_METRIKA_ID !== '') {
        $id = (int) YANDEX_METRIKA_ID;
        echo '<script>(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window,document,"script","https://mc.yandex.ru/metrika/tag.js","ym");ym(' . $id . ',"init",{clickmap:true,trackLinks:true,accurateTrackBounce:true,webvisor:true});</script>' . "\n";
        echo '<noscript><div><img src="https://mc.yandex.ru/watch/' . $id . '" style="position:absolute;left:-9999px" alt=""></div></noscript>' . "\n";
    }
    if (GA4_ID !== '') {
        $g = preg_replace('/[^A-Za-z0-9-]/', '', GA4_ID);
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $g . '"></script>' . "\n";
        echo '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . $g . '");</script>' . "\n";
    }
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
<link rel="icon" href="<?= logo_src() ?>" type="image/jpeg">
<link rel="apple-touch-icon" href="<?= logo_src() ?>">
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
<link rel="stylesheet" href="/assets/style.css">
<link rel="stylesheet" href="/assets/site-ext.css">
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
    echo '<script src="/assets/app.js" defer></script>' . "\n";
    render_analytics();
    echo "</body>\n</html>\n";
}


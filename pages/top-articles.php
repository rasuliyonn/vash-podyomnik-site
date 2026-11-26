<?php
/**
 * ТОП СТАТЕЙ — /top-articles
 * Статьи-флаги «ТОП СТАТЕЙ» (блог о подъёмниках).
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/blog.php';

$tops = blog_top_articles();
$topUrls = array_map(fn($a) => '/blog/' . $a['slug'], $tops);

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'ТОП СТАТЕЙ', 'path' => '/top-articles'],
    ]),
    itemlist_jsonld('Топ статей блога «Ваш Подъёмник»', $topUrls),
];

page_start(
    'ТОП СТАТЕЙ о выборе, стоимости и монтаже грузовых подъёмников',
    'Ключевые статьи «Ваш Подъёмник» — выбор подъёмника, цены, сроки 5–60 дней, ТР ТС 011/2011, модернизация и монтаж.',
    '/top-articles',
    ['jsonld' => $jsonld]
);
?>
<main class="top-articles" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">ТОП СТАТЕЙ</span>
  </nav>

  <div class="top-articles-head">
    <h1 class="top-articles-heading">ТОП СТАТЕЙ</h1>
  </div>

  <div class="top-articles-grid">
    <?php foreach ($tops as $i => $a): ?>
    <a class="top-article" href="/blog/<?= e($a['slug']) ?>" aria-label="Топ статья: <?= e($a['title']) ?>">
      <span class="top-article-cat"><?= e($a['category'] ?? 'Гайды') ?></span>
      <b class="top-article-title"><?= e($a['title']) ?></b>
      <p class="top-article-lead"><?= e($a['description']) ?></p>
      <span class="top-article-meta"><?= e($a['date']) ?> · <?= (int) ($a['readMin'] ?? 8) ?> мин чтения</span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="section-more">
    <a class="text-link" href="/blog">Полный список статей блога →</a>
  </div>
</main>
<?php page_end(); ?>

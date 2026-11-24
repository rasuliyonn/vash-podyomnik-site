<?php
/**
 * Блог: индекс /blog
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/blog.php';

$articles = blog_articles();
$tops = blog_top_articles();
// ТОП-статьи вначале (блок «ТОП СТАТЬИ» и ItemList), затем — остальные, с бейджем
$articles = array_merge($tops, array_values(array_filter($articles, fn($a) => !article_is_top($a))));
$count = count($articles);
$categories = blog_categories();
$urls = array_map(fn($a) => '/blog/' . $a['slug'], $articles);

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'Блог', 'path' => '/blog'],
    ]),
    itemlist_jsonld('Статьи блога «Ваш Подъёмник»', $urls),
];

page_start(
    'Блог о грузовых подъёмниках',
    'Гайды и нормативы: как выбрать грузовой подъёмник, цена, сроки 5–60 дней, ТР ТС 011/2011, обслуживание. Практика инженера.',
    '/blog',
    ['jsonld' => $jsonld]
);
?>
<main class="blog-index" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">Блог</span>
  </nav>

  <?php if ($tops): ?>
  <section class="top-articles blog-top" id="top-articles">
    <h2 class="top-articles-heading">ТОП СТАТЬИ</h2>
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
  </section>
  <?php endif; ?>

  <h1>Блог</h1>
  <p class="blog-lead">Практический опыт проектирования, производства и монтажа грузовых подъёмников: выбор, цена, сроки, нормативы и эксплуатация. <?= $count ?> статей.</p>

  <p class="blog-meta" style="margin-top: 16px">
    <?php foreach ($categories as $cat => $n): ?>
    <span><?= e($cat) ?> · <?= (int) $n ?></span>
    <?php endforeach; ?>
  </p>

  <div class="blog-grid">
    <?php foreach ($articles as $article): ?>
    <a class="blog-card" href="/blog/<?= e($article['slug']) ?>">
      <div class="blog-card-head">
        <span class="blog-cat"><?= e($article['category']) ?></span>
        <?php if (article_is_top($article)): ?><span class="blog-card-badge">Топ</span><?php endif; ?>
      </div>
      <h2><?= e($article['title']) ?></h2>
      <p><?= e($article['description']) ?></p>
      <span class="blog-card-foot blog-meta" style="margin: 0">
        <span><?= e($article['date']) ?></span>
        <span><?= (int) $article['readMin'] ?> мин</span>
      </span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="section-more" style="margin-top: 44px">
    <a class="primary-button" href="/raschet">Получить расчёт<span aria-hidden="true"> →</span></a>
  </div>
</main>
<?php page_end(); ?>

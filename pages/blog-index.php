<?php
/**
 * Блог: индекс /blog
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/blog.php';

$articles = blog_articles();
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
    'Гайды, сравнения и нормативы: как выбрать грузовой подъёмник, сколько он стоит, сроки 5–60 дней, ТР ТС 011/2011, монтаж в действующее здание, обслуживание. Практика инженера-проектировщика.',
    '/blog',
    ['jsonld' => $jsonld]
);
?>
<main class="blog-index" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">Блог</span>
  </nav>

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
      <span class="blog-cat"><?= e($article['category']) ?></span>
      <h3><?= e($article['title']) ?></h3>
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

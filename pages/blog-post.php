<?php
/**
 * Страница статьи: /blog/{slug}
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/blog.php';

$slug = (string) ($_GET['slug'] ?? '');
$article = blog_find($slug);

if ($article === null || !preg_match('/^[a-z0-9-]+$/', $slug)) {
    $_SERVER['SCRIPT_NAME'] = '/404';
    require __DIR__ . '/not-found.php';
    return;
}

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'Блог', 'path' => '/blog'],
        ['name' => $article['title'], 'path' => '/blog/' . $article['slug']],
    ]),
    article_jsonld($article),
];
$articleFaq = $article['faq'] ?? [];
if ($articleFaq) {
    $jsonld[] = faq_jsonld($articleFaq);
}

page_start(
    $article['title'],
    meta_truncate($article['description'], 160),
    '/blog/' . $article['slug'],
    ['jsonld' => $jsonld, 'og_type' => 'article']
);
?>
<main class="blog-article" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><a href="/blog">Блог</a><span aria-hidden="true">›</span><span aria-current="page"><?= e($article['title']) ?></span>
  </nav>

  <p class="blog-meta" style="margin-top: 12px">
    <span><?= e($article['category']) ?></span>
    <span><?= e($article['date']) ?></span>
    <span><?= (int) $article['readMin'] ?> мин чтения</span>
  </p>

  <h1><?= e($article['title']) ?></h1>
  <p class="blog-lead"><?= e($article['description']) ?></p>

  <article class="prose" style="margin-top: 30px">
    <?= $article['body'] ?>
  </article>

  <?php if ($articleFaq): ?>
  <div class="article-faq">
    <h2>Короткие ответы</h2>
    <?php foreach ($articleFaq as $item): ?>
    <h3><?= e($item['q']) ?></h3>
    <p><?= e($item['a']) ?></p>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php
  $related = [];
  foreach (($article['related'] ?? []) as $relatedSlug) {
      $rel = blog_find($relatedSlug);
      if ($rel) {
          $related[] = $rel;
      }
  }
  ?>
  <?php if ($related): ?>
  <div class="related-articles">
    <h2>Читайте также</h2>
    <ul>
      <?php foreach ($related as $rel): ?>
      <li><a href="/blog/<?= e($rel['slug']) ?>"><?= e($rel['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="related-articles">
    <h2>По типу оборудования</h2>
    <ul>
      <li><a href="/equipment/shaftnye">Шахтные подъёмники</a></li>
      <li><a href="/equipment/konsolnye">Консольные подъёмники</a></li>
      <li><a href="/equipment/servisnye">Сервисные (малые грузовые) подъёмники</a></li>
      <li><a href="/equipment/kottedzhnye">Коттеджные лифты</a></li>
      <li><a href="/equipment/platformy">Грузовые платформы</a></li>
      <li><a href="/oborudovanie">Все типы оборудования</a></li>
    </ul>
  </div>

  <div class="article-faq" style="margin-bottom: 0">
    <h2>Нужно решение под ваш объект?</h2>
    <p style="margin: 0 0 12px">Инженер бесплатно изучит задачу и подготовит расчёт.</p>
    <a class="primary-button" href="/raschet">Получить расчёт<span aria-hidden="true"> →</span></a>
  </div>
</main>
<?php page_end(); ?>

<?php
/**
 * Портфолио: /nashi-raboty
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/db.php';

$works = works_list();
$count = count($works);

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'Наши работы', 'path' => '/nashi-raboty'],
    ]),
    [
        '@context' => 'https://schema.org',
        '@type' => 'ImageGallery',
        'name' => 'Выполненные объекты «Ваш Подъёмник»',
        'image' => array_map(fn($w) => abs_url($w['url']), array_slice($works, 0, 50)),
    ],
];

page_start(
    'Наши работы — выполненные объекты по России',
    "Портфолио «Ваш Подъёмник»: {$count} выполненных объектов — склады, производства, торговые центры, рестораны и частные дома. Монтаж подъёмников по всей России.",
    '/nashi-raboty',
    ['jsonld' => $jsonld]
);
?>
<main class="works-page" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">Наши работы</span>
  </nav>

  <h1>Наши <em>работы</em></h1>
  <p class="blog-lead"><?= $count ?> выполненных объектов: склады и логистические центры, производства, торговые центры, рестораны и частные дома. Каждое решение — под конкретный объект: от шахтного подъёмника до грузовой платформы по ТЗ.</p>

  <div class="hero-metrics" style="margin: 30px 0 0" aria-label="Ключевые факты">
    <div><strong><?= $count ?></strong><span>объектов<br>сдано</span></div>
    <div><strong>5–60</strong><span>рабочих дней<br>на изготовление</span></div>
    <div><strong>по РФ</strong><span>доставка<br>и монтаж</span></div>
  </div>

  <div class="works-grid" data-works-grid>
    <?php foreach ($works as $i => $work): ?>
    <button class="works-cell" type="button" data-lightbox-index="<?= $i ?>" aria-label="Открыть фото объекта <?= $i + 1 ?>">
      <img src="<?= e($work['url']) ?>" alt="Выполненный объект <?= $i + 1 ?><?= $work['caption'] !== '' ? ' — ' . e($work['caption']) : '' ?>" loading="lazy">
      <span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
    </button>
    <?php endforeach; ?>
  </div>

  <div class="section-more" style="margin-top: 44px">
    <a class="primary-button" href="/raschet">Обсудить ваш объект<span aria-hidden="true"> →</span></a>
  </div>
</main>
<?php page_end(); ?>

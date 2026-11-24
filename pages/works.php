<?php
/**
 * Портфолио: /nashi-raboty
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/works_images.php';

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

  <div class="prose" style="max-width: none; font-size: 15px; margin-top: 28px">
    <p>Каждая фотография на этой странице — реальный объект, сданный заказчику: от сервисного подъёмника для ресторана до шахтного подъёмника на производстве и открытой грузовой платформы на несколько тонн. Ни одного типового рендера или стоковой картинки.</p>
    <p>Типы объектов в портфолио: склады и логистические центры, промышленные предприятия, торговые центры и магазины, автосервисы и техцентры, рестораны и кафе, гостиницы, частные дома и коттеджи. Под каждый объект мы проектируем решение индивидуально — по планировке, грузопотоку и имеющимся проёмам.</p>
    <p>Хотите такой же результат на своём объекте? Оставьте заявку — инженер изучит планировку и предложит параметры подъёмника, зафиксированные в договоре. Нажмите на любое фото, чтобы увеличить его.</p>
  </div>

  <div class="works-grid" data-works-grid>
    <?php foreach ($works as $i => $work): ?>
    <button class="works-cell" type="button" data-lightbox-index="<?= $i ?>">
      <?= works_picture($work['url'], 'Выполненный объект ' . ($i + 1) . ($work['caption'] !== '' ? ' — ' . $work['caption'] : '') . ': монтаж грузового подъёмника под ключ', '(max-width: 640px) 50vw, (max-width: 1000px) 33vw, 25vw', $i < 4) ?>
    </button>
    <?php endforeach; ?>
  </div>

  <div class="section-more" style="margin-top: 44px">
    <a class="primary-button" href="/raschet">Обсудить ваш объект<span aria-hidden="true"> →</span></a>
  </div>
</main>
<?php page_end(); ?>

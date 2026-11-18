<?php
/**
 * Страница типа оборудования: /equipment/{slug}
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/equipment.php';

$slug = $_GET['slug'] ?? '';
$data = equipment_get($slug);
if ($data === null) {
    // пробрасываем в обработчик 404
    $_SERVER['SCRIPT_NAME'] = '/404';
    require __DIR__ . '/not-found.php';
    return;
}

$title = $data['title'];
$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'Оборудование', 'path' => '/oborudovanie'],
        ['name' => $title, 'path' => '/equipment/' . $slug],
    ]),
    service_jsonld($title, $data['description'], '/equipment/' . $slug),
];

page_start(
    $title,
    mb_substr($data['description'], 0, 160),
    '/equipment/' . $slug,
    ['jsonld' => $jsonld]
);
?>
<main class="eq-page" id="top">

  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><a href="/oborudovanie">Оборудование</a><span aria-hidden="true">›</span><span aria-current="page"><?= e($title) ?></span>
  </nav>

  <section class="eq-hero">
    <div class="eq-hero-copy">
      <span class="eyebrow"><span></span>Производство · монтаж · гарантия</span>
      <h1><?= e($title) ?></h1>
      <p><?= e($data['description']) ?></p>
      <a class="primary-button" href="/raschet">Рассчитать стоимость<span aria-hidden="true">→</span></a>
    </div>
  </section>

  <?php if (!empty($data['images'])): ?>
  <section class="eq-section">
    <div class="eq-section-label"><span>/ ГАЛЕРЕЯ</span></div>
    <div class="gallery-grid">
      <?php foreach ($data['images'] as $img): ?>
      <a class="gallery-thumb" href="<?= e($img['url']) ?>" target="_blank" rel="noopener noreferrer">
        <img src="<?= e($img['url']) ?>" alt="<?= e($img['caption'] !== '' ? $img['caption'] : $title) ?>" loading="lazy">
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="eq-section eq-two-col">
    <div>
      <div class="eq-section-label"><span>/ ОСОБЕННОСТИ</span></div>
      <ul class="eq-list">
        <?php foreach ($data['features'] as $i => $f): ?>
        <li><span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($f) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <div class="eq-section-label"><span>/ ПРЕИМУЩЕСТВА</span></div>
      <ul class="eq-list advantages">
        <?php foreach ($data['advantages'] as $a): ?>
        <li><span>◆</span><?= e($a) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <section class="eq-section">
    <div class="eq-section-label"><span>/ ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</span></div>
    <table class="eq-specs-table">
      <tbody>
        <?php foreach ($data['techSpecs'] as $spec): ?>
        <tr><td><?= e($spec['name']) ?></td><td><?= e($spec['value']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="eq-specs-note">Точные параметры определяются при проектировании под ваш объект.</p>
  </section>

  <section class="eq-section">
    <div class="eq-section-label"><span>/ СФЕРЫ ПРИМЕНЕНИЯ</span></div>
    <div class="eq-applications">
      <?php foreach ($data['applications'] as $i => $app): ?>
      <div class="eq-app-card">
        <span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <p><?= e($app) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if (!empty($data['videoUrl'])): ?>
  <section class="eq-section">
    <div class="eq-section-label"><span>/ ВИДЕО</span></div>
    <div class="eq-video-wrap">
      <iframe src="<?= e($data['videoUrl']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen title="Видео: <?= e($title) ?>"></iframe>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($data['documents'])): ?>
  <section class="eq-section">
    <div class="eq-section-label"><span>/ ДОКУМЕНТЫ</span></div>
    <div class="eq-documents">
      <?php
      $docIcons = ['pdf' => '📄', 'certificate' => '🏆', 'instruction' => '📋', 'scheme' => '📐', 'video' => '🎬'];
      foreach ($data['documents'] as $doc):
          $sizeKb = (int) ($doc['size_kb'] ?? 0);
          $size = $sizeKb > 1024 ? number_format($sizeKb / 1024, 1) . ' МБ' : ($sizeKb > 0 ? $sizeKb . ' КБ' : '');
      ?>
      <a class="eq-doc-card" href="<?= e($doc['url']) ?>" target="_blank" rel="noopener noreferrer" download>
        <span class="eq-doc-icon"><?= $docIcons[$doc['doc_type']] ?? '📄' ?></span>
        <span class="eq-doc-name"><?= e($doc['name']) ?></span>
        <?php if ($size !== ''): ?><span class="eq-doc-size"><?= e($size) ?></span><?php endif; ?>
        <span class="eq-doc-dl">↓</span>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="eq-section">
    <div class="eq-section-label"><span>/ ПОЛЕЗНО ЗНАТЬ</span></div>
    <p class="eq-specs-note" style="margin-top: 0">Читайте в блоге:
      <a href="/blog/kak-vybrat-gruzovoj-podemnik">Как выбрать грузовой подъёмник</a> ·
      <a href="/blog/tr-ts-011-2011">ТР ТС 011/2011 простыми словами</a> ·
      <a href="/blog/stoimost-gruzovogo-podemnika">Из чего складывается цена</a> ·
      <a href="/voprosy">Все вопросы и ответы</a>
    </p>
  </section>

  <section class="eq-cta">
    <div>
      <h2>Нужен расчёт для вашего объекта?</h2>
      <p>Инженер бесплатно изучит задачу и подберёт оптимальные параметры.</p>
    </div>
    <a class="primary-button" href="/raschet">Рассчитать стоимость<span aria-hidden="true">↗</span></a>
  </section>

  <div class="eq-back">
    <a href="/oborudovanie">← Все типы оборудования</a>
  </div>
</main>
<?php page_end(); ?>

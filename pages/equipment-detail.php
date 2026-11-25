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
    $title . ': характеристики, грузоподъёмность, монтаж',
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
        <li><span>◆</span><?= e($f) ?></li>
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
        <p><?= e($app) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if (!empty($data['videoUrl'])): ?>
  <section class="eq-section">
    <div class="eq-section-label"><span>/ ВИДЕО</span></div>
    <div class="eq-video-wrap">
      <button type="button" class="eq-video-facade" data-video-load data-video-src="<?= e($data['videoUrl']) ?>" data-video-title="Видео: <?= e($title) ?>">
        <span class="eq-video-play" aria-hidden="true">▶</span>
        <span>Смотреть видео</span>
      </button>
      <p class="eq-video-note">Видео размещено на внешнем сервисе (YouTube или Vimeo) и загружается только после нажатия. При просмотре внешний сервис может получать данные о вашем устройстве и устанавливать свои cookie — <a href="/cookies">подробнее</a>.</p>
      <noscript><p><a href="<?= e($data['videoUrl']) ?>" target="_blank" rel="noopener noreferrer">Открыть видео в новой вкладке</a></p></noscript>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($data['documents'])): ?>
  <section class="eq-section">
    <div class="eq-section-label"><span>/ ДОКУМЕНТЫ</span></div>
    <div class="eq-documents">
      <?php
      // Иконки документов: Lucide-стиль, stroke-based SVG
      $docIcon = static function (string $type): string {
          $paths = [
              'pdf' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="10" y1="13" x2="14" y2="13"/><line x1="10" y1="17" x2="14" y2="17"/>',
              'certificate' => '<circle cx="12" cy="9" r="6"/><path d="M8.5 13.5 7 22l5-3 5 3-1.5-8.5"/><path d="M12 6.5v5"/><path d="M9.5 9h5"/>',
              'instruction' => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 12h6"/><path d="M9 16h4"/>',
              'scheme' => '<path d="M2 17l8-11 6 8 2.5-3.5L22 17z"/><path d="M2 21h20"/>',
              'video' => '<rect x="2" y="5" width="20" height="14" rx="2"/><polygon points="10 9 15 12 10 15" fill="currentColor" stroke="none"/>',
          ];
          $inner = $paths[$type] ?? $paths['pdf'];
          return '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
      };
      foreach ($data['documents'] as $doc):
          $sizeKb = (int) ($doc['size_kb'] ?? 0);
          $size = $sizeKb > 1024 ? number_format($sizeKb / 1024, 1) . ' МБ' : ($sizeKb > 0 ? $sizeKb . ' КБ' : '');
      ?>
      <a class="eq-doc-card" href="<?= e($doc['url']) ?>" target="_blank" rel="noopener noreferrer" download>
        <span class="eq-doc-icon"><?= $docIcon((string) ($doc['doc_type'] ?? 'pdf')) ?></span>
        <span class="eq-doc-name"><?= e($doc['name']) ?></span>
        <?php if ($size !== ''): ?><span class="eq-doc-size"><?= e($size) ?></span><?php endif; ?>
        <span class="eq-doc-dl" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><polyline points="6.5 11 12 16.5 17.5 11"/><path d="M4 21h16"/></svg></span>
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
      <a href="/gruzovye-podemniki-po-proektu">Изготовление по индивидуальному проекту</a> ·
      <a href="/montazh-gruzovogo-podemnika">Монтаж под ключ</a> ·
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

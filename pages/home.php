<?php
/**
 * Главная страница.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/content.php';
require_once __DIR__ . '/../lib/equipment.php';
require_once __DIR__ . '/../lib/db.php';

$c = site_content();
$works = works_list();
$worksCount = count($works);

$jsonld = [
    faq_jsonld($c['faq']['items']),
];

page_start(
    'Грузовые подъёмники и лифты под ваш объект',
    '«Ваш Подъёмник» — производство, проектирование и монтаж грузовых подъёмников и лифтов под ваш объект. Доставка по всей России. От 5 рабочих дней на производство, цена фиксируется в договоре.',
    '/',
    ['jsonld' => $jsonld]
);
?>
<main id="top">

  <section class="hero" style="background-image: linear-gradient(90deg,rgba(5,7,7,.99) 0%,rgba(5,7,7,.96) 31%,rgba(5,7,7,.63) 53%,rgba(5,7,7,.08) 78%),linear-gradient(0deg,rgba(0,0,0,.34),rgba(0,0,0,.08)),url('/assets/client-lift-hero.png')">
    <div class="hero-noise" aria-hidden="true"></div>
    <div class="hero-copy">
      <div class="eyebrow"><span></span><?= e($c['hero']['eyebrow']) ?></div>
      <h1><?= e($c['hero']['h1']) ?><br> подъёмники <em><?= e($c['hero']['h1em']) ?></em></h1>
      <p class="hero-lead"><?= e($c['hero']['lead']) ?></p>
      <div class="hero-actions">
        <a class="primary-button" href="/raschet"><?= e($c['hero']['ctaPrimary']) ?><span aria-hidden="true">→</span></a>
        <a class="text-link" href="/oborudovanie"><?= e($c['hero']['ctaSecondary']) ?><span aria-hidden="true">↓</span></a>
      </div>
      <div class="hero-metrics" aria-label="Ключевые преимущества">
        <?php foreach ($c['hero']['metrics'] as $m): ?>
        <div>
          <strong><?= e($m['value']) ?></strong>
          <span style="white-space: pre-line"><?= e($m['label']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="hero-visual" aria-label="Промышленное подъёмное оборудование">
      <div class="visual-label top">
        <span>01</span>
        <p style="white-space: pre-line"><?= e($c['hero']['visualTop']) ?></p>
      </div>
      <div class="visual-label bottom">
        <p style="white-space: pre-line"><?= e($c['hero']['visualBottom']) ?></p>
        <span>↗</span>
      </div>
      <div class="capacity-card">
        <small><?= e($c['hero']['capacityLabel']) ?></small>
        <b><?= e($c['hero']['capacityValue']) ?></b>
        <i></i>
      </div>
    </div>
  </section>

  <section class="trust-strip" aria-label="Ключевые гарантии">
    <?php foreach ($c['trust_strip'] as $i => $item): ?>
    <?php if ($i > 0): ?><span>◆</span><?php endif; ?>
    <p><?= e($item) ?></p>
    <?php endforeach; ?>
  </section>

  <section class="section equipment" id="equipment">
    <div class="section-heading">
      <div>
        <span class="section-index">/ 01</span>
        <p><?= e($c['equipment_section']['label']) ?></p>
      </div>
      <h2><?= e($c['equipment_section']['heading']) ?><br><em><?= e($c['equipment_section']['headingEm']) ?></em></h2>
    </div>

    <div class="equipment-grid">
      <?php foreach ($c['equipment_cards'] as $item): ?>
      <a class="equipment-card" href="/equipment/<?= e($item['slug']) ?>" aria-label="Подробнее: <?= e($item['title']) ?>">
        <div class="card-top">
          <span><?= e($item['number']) ?></span>
          <span aria-hidden="true">↗</span>
        </div>
        <div class="lift-symbol symbol-<?= e($item['number']) ?>" aria-hidden="true">
          <i></i><i></i><i></i>
        </div>
        <h3><?= e($item['title']) ?></h3>
        <p><?= e($item['text']) ?></p>
        <div class="tags">
          <?php foreach ($item['tags'] as $tag): ?><span><?= e($tag) ?></span><?php endforeach; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="section-more">
      <a class="text-link" href="/oborudovanie">Каталог оборудования и сравнение типов<span aria-hidden="true"> →</span></a>
    </div>
  </section>

  <section class="object-section">
    <div class="object-image" role="img" aria-label="Современный складской объект">
      <div class="image-caption">
        <span><?= e($c['object_section']['location']) ?></span>
        <span><?= e($c['object_section']['productionLabel']) ?></span>
      </div>
    </div>
    <div class="object-copy">
      <span class="section-index">/ 02</span>
      <p class="kicker"><?= e($c['object_section']['kicker']) ?></p>
      <h2><?= e($c['object_section']['heading']) ?></h2>
      <p class="object-lead"><?= e($c['object_section']['lead']) ?></p>
      <div class="object-list">
        <?php foreach ($c['object_section']['items'] as $i => $item): ?>
        <div>
          <span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <p><?= e($item) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
      <a class="primary-button dark" href="/raschet"><?= e($c['object_section']['cta']) ?><span aria-hidden="true">→</span></a>
    </div>
  </section>

  <section class="section process" id="process">
    <div class="section-heading compact">
      <div>
        <span class="section-index">/ 03</span>
        <p>КАК МЫ РАБОТАЕМ</p>
      </div>
      <h2><?= e($c['process']['headingMain']) ?><br><em><?= e($c['process']['headingEm']) ?></em></h2>
    </div>

    <div class="timeline">
      <?php foreach ($c['process']['steps'] as $step): ?>
      <article>
        <span class="timeline-number"><?= e($step['number']) ?></span>
        <div class="timeline-dot"></div>
        <h3><?= e($step['title']) ?></h3>
        <p><?= e($step['text']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section works" id="works">
    <div class="works-heading">
      <div class="works-heading-left">
        <span class="section-index">/ 04</span>
        <p>ПОРТФОЛИО</p>
        <h2>Наши <em>работы</em></h2>
      </div>
      <div class="works-heading-right">
        <span class="works-count"><?= $worksCount ?></span>
        <span class="works-count-label">выполненных<br>объектов</span>
        <span class="works-scroll-hint">прокрутите →</span>
      </div>
    </div>
    <div class="works-gallery" data-gallery>
      <button class="works-arrow works-arrow-prev" type="button" aria-label="Назад" data-gallery-prev>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12.5 4L7 10L12.5 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <div class="works-strip" data-gallery-strip>
        <?php foreach ($works as $i => $work): ?>
        <button class="works-card" type="button" data-lightbox-index="<?= $i ?>" aria-label="Наша работа <?= $i + 1 ?>">
          <img src="<?= e($work['url']) ?>" alt="Выполненный объект <?= $i + 1 ?><?= $work['caption'] !== '' ? ' — ' . e($work['caption']) : '' ?>" loading="lazy" draggable="false">
          <span class="works-card-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        </button>
        <?php endforeach; ?>
      </div>
      <button class="works-arrow works-arrow-next" type="button" aria-label="Вперёд" data-gallery-next>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7.5 4L13 10L7.5 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
    </div>
    <div class="section-more">
      <a class="text-link" href="/nashi-raboty">Все работы и объекты<span aria-hidden="true"> →</span></a>
    </div>
  </section>

  <section class="guarantees" id="guarantees">
    <div class="guarantee-title">
      <span class="section-index">/ 05</span>
      <p>ПОЧЕМУ НАМ ДОВЕРЯЮТ</p>
      <h2><?= e($c['guarantees']['heading']) ?><br><em><?= e($c['guarantees']['headingEm']) ?></em></h2>
    </div>
    <div class="guarantee-grid">
      <?php foreach ($c['guarantees']['items'] as $item): ?>
      <article>
        <span><?= e($item['number']) ?></span>
        <h3><?= e($item['title']) ?></h3>
        <p><?= e($item['text']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <?php require __DIR__ . '/partials/calculator.php'; ?>

  <section class="faq-section">
    <div class="faq-heading">
      <span class="section-index">/ 06</span>
      <p>ЧАСТЫЕ ВОПРОСЫ</p>
      <h2><?= e($c['faq']['heading']) ?></h2>
    </div>
    <div class="faq-list" data-faq>
      <?php foreach ($c['faq']['items'] as $i => $item): ?>
      <article<?= $i === 0 ? ' class="open"' : '' ?>>
        <button type="button" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
          <span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <b><?= e($item['q']) ?></b>
          <i aria-hidden="true"><?= $i === 0 ? '−' : '+' ?></i>
        </button>
        <div class="faq-answer"><p><?= e($item['a']) ?></p></div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="section-more">
      <a class="text-link" href="/voprosy">Все 28 вопросов и ответов<span aria-hidden="true"> →</span></a>
    </div>
  </section>

</main>
<?php page_end(); ?>

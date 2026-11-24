<?php
/**
 * Главная страница.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/content.php';
require_once __DIR__ . '/../lib/equipment.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/blog.php';

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

  <section class="hero" style="background-image: linear-gradient(90deg,rgba(5,7,7,.97) 0%,rgba(5,7,7,.9) 30%,rgba(5,7,7,.55) 55%,rgba(5,7,7,.12) 80%),linear-gradient(0deg,rgba(0,0,0,.42),rgba(0,0,0,.06)),url('/assets/hero-real.webp')">
    <div class="hero-copy">
      <div class="eyebrow"><?= e($c['hero']['eyebrow']) ?></div>
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

    <div class="hero-visual" role="img" aria-label="Промышленное подъёмное оборудование">
      <div class="visual-label top">
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
        <p><?= e($c['equipment_section']['label']) ?></p>
      </div>
      <h2><?= e($c['equipment_section']['heading']) ?><br><em><?= e($c['equipment_section']['headingEm']) ?></em></h2>
    </div>

    <div class="equipment-grid">
      <?php foreach ($c['equipment_cards'] as $item): ?>
      <a class="equipment-card" href="/equipment/<?= e($item['slug']) ?>">
        <div class="card-top">
          <span aria-hidden="true">↗</span>
        </div>
        <img src="/assets/equipment-<?= e($item['slug']) ?>.webp" alt="<?= e($item['title']) ?> — грузовой подъёмник производства «Ваш Подъёмник»" loading="lazy">
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
    <div class="object-image" role="img" aria-label="Реальный шахтный грузовой подъёмник в облицовке производственного здания">
      <div class="image-caption">
        <span><?= e($c['object_section']['location']) ?></span>
        <span><?= e($c['object_section']['productionLabel']) ?></span>
      </div>
    </div>
    <div class="object-copy">
      <p class="kicker"><?= e($c['object_section']['kicker']) ?></p>
      <h2><?= e($c['object_section']['heading']) ?></h2>
      <p class="object-lead"><?= e($c['object_section']['lead']) ?></p>
      <div class="object-list">
        <?php foreach ($c['object_section']['items'] as $i => $item): ?>
        <div>
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
        <p>КАК МЫ РАБОТАЕМ</p>
      </div>
      <h2><?= e($c['process']['headingMain']) ?><br><em><?= e($c['process']['headingEm']) ?></em></h2>
    </div>

    <div class="timeline">
      <?php foreach ($c['process']['steps'] as $step): ?>
      <article>
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
        <button class="works-card" type="button" data-lightbox-index="<?= $i ?>" aria-label="Наша работа">
          <img src="<?= e($work['url']) ?>" alt="Выполненный объект<?= $work['caption'] !== '' ? ' — ' . e($work['caption']) : '' ?>" loading="lazy" draggable="false">
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
      <p>ПОЧЕМУ НАМ ДОВЕРЯЮТ</p>
      <h2><?= e($c['guarantees']['heading']) ?><br><em><?= e($c['guarantees']['headingEm']) ?></em></h2>
    </div>
    <div class="guarantee-grid">
      <?php foreach ($c['guarantees']['items'] as $item): ?>
      <article>
        <h3><?= e($item['title']) ?></h3>
        <p><?= e($item['text']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <?php require __DIR__ . '/partials/calculator.php'; ?>

  <section class="faq-section">
    <div class="faq-heading">
      <p>ЧАСТЫЕ ВОПРОСЫ</p>
      <h2><?= e($c['faq']['heading']) ?></h2>
    </div>
    <div class="faq-list" data-faq>
      <?php foreach ($c['faq']['items'] as $i => $item): ?>
      <article<?= $i === 0 ? ' class="open"' : '' ?>>
        <button type="button" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
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

<?php
/**
 * Вопросы и ответы: /voprosy — 28 вопросов, 7 групп, FAQPage JSON-LD.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/faq.php';

$groups = faq_groups();
$flat = faq_items();

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'Вопросы и ответы', 'path' => '/voprosy'],
    ]),
    faq_jsonld($flat),
];

page_start(
    'Вопросы и ответы о грузовых подъёмниках',
    'Отвечаем на 28 популярных вопросов: стоимость и договор, сроки изготовления 5–60 дней, монтаж в действующее здание, ТР ТС 011/2011, гарантия, обслуживание, доставка по России.',
    '/voprosy',
    ['jsonld' => $jsonld]
);
?>
<main class="faq-page" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">Вопросы и ответы</span>
  </nav>

  <h1>Вопросы и <em>ответы</em></h1>
  <p class="blog-lead">Собрали 28 самых частых вопросов: от цены и сроков до документов и обслуживания. Не нашли свой? <a href="/kontakty">Напишите нам</a> — инженер ответит лично.</p>

  <?php foreach ($groups as $group): ?>
  <section class="faq-group">
    <h2><?= e($group['title']) ?></h2>
    <div class="faq-list" data-faq>
      <?php foreach ($group['items'] as $i => $item): ?>
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
  </section>
  <?php endforeach; ?>

  <div class="section-more" style="margin-top: 40px">
    <a class="primary-button" href="/raschet">Получить расчёт<span aria-hidden="true"> →</span></a>
  </div>
</main>
<?php page_end(); ?>

<?php
/**
 * О компании: /o-kompanii
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/content.php';

$c = site_content();

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'О компании', 'path' => '/o-kompanii'],
    ]),
];

page_start(
    'О компании — производство в Ростове-на-Дону',
    'ООО «Ваш Подъёмник» — собственное производство грузовых подъёмников в Ростове-на-Дону. Проектирование, изготовление, доставка и монтаж по всей России. ИНН 6163212345 · ОГРН 1236100000000.',
    '/o-kompanii',
    ['jsonld' => $jsonld]
);
?>
<main class="page-slim" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">О компании</span>
  </nav>

  <h1>О <em>компании</em></h1>
  <p class="blog-lead">«Ваш Подъёмник» — собственное производство подъёмного оборудования в Ростове-на-Дону. Проектируем, изготавливаем и монтируем шахтные, консольные и сервисные подъёмники, коттеджные лифты и грузовые платформы под ваш объект — с доставкой и запуском по всей России.</p>

  <div class="two-col">
    <div class="contact-rows" style="margin-top: 0">
      <div class="contact-row">
        <small>Юридическое лицо</small>
        <span><?= e(COMPANY_NAME) ?></span>
      </div>
      <div class="contact-row">
        <small>Реквизиты</small>
        <span>ИНН <?= e(COMPANY_INN) ?> · ОГРН <?= e(COMPANY_OGRN) ?></span>
      </div>
      <div class="contact-row">
        <small>Адрес</small>
        <span><?= e(COMPANY_ADDRESS) ?></span>
      </div>
      <div class="contact-row">
        <small>Email</small>
        <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>
      </div>
      <?php if (CONTACT_PHONE !== ''): ?>
      <div class="contact-row">
        <small>Телефон</small>
        <a href="tel:<?= e(preg_replace('/[^+0-9]/', '', CONTACT_PHONE)) ?>"><?= e(CONTACT_PHONE) ?></a>
      </div>
      <?php endif; ?>
      <?php if (CONTACT_HOURS !== ''): ?>
      <div class="contact-row">
        <small>Часы работы</small>
        <span><?= e(CONTACT_HOURS) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <div>
      <h2 class="page-title" style="font-size: 26px; margin-top: 0">Производство и подход</h2>
      <div class="prose" style="max-width: none; font-size: 15px">
        <p>Мы не дилеры и не посредники: оборудование изготавливается на собственном производстве в Ростове-на-Дону, поэтому отвечаем за результат на каждом этапе — от первого эскиза до запуска на объекте.</p>
        <p>Изготавливаем оборудование <strong>от 5 до 60 рабочих дней</strong>, цена фиксируется в договоре до начала производства. Оборудование соответствует требованиям <strong>ТР ТС 011/2011</strong> и поставляется с комплектом документов.</p>
        <p>География работ — вся Россия: доставляем оборудование на объект и организуем монтаж и пусконаладку с выездом специалистов.</p>
      </div>
    </div>
  </div>

  <h2 class="page-title" style="font-size: 26px; margin-top: 54px">Как мы работаем</h2>
  <div class="timeline" style="margin-top: 22px">
    <?php foreach ($c['process']['steps'] as $step): ?>
    <article>
      <span class="timeline-number"><?= e($step['number']) ?></span>
      <div class="timeline-dot"></div>
      <h3><?= e($step['title']) ?></h3>
      <p><?= e($step['text']) ?></p>
    </article>
    <?php endforeach; ?>
  </div>

  <div class="section-more" style="margin-top: 40px">
    <a class="primary-button" href="/raschet">Получить расчёт<span aria-hidden="true"> →</span></a>
    <a class="text-link" href="/nashi-raboty" style="margin-left: 18px">Смотреть наши работы<span aria-hidden="true"> →</span></a>
  </div>
</main>
<?php page_end(); ?>

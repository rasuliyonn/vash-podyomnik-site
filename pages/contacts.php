<?php
/**
 * Контакты: /kontakty — реквизиты, адрес, embedded-форма заявки.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'Контакты', 'path' => '/kontakty'],
    ]),
];

page_start(
    'Контакты в Ростове-на-Дону',
    'Контакты «Ваш Подъёмник»: телефон +7 (952) 603-57-85, мессенджер MAX, email info@vashpodemnik61.ru, производство в Ростове-на-Дону. Расчёт онлайн.',
    '/kontakty',
    ['jsonld' => $jsonld]
);
?>
<main id="top">
  <div class="page-slim" style="padding-bottom: 0">
    <nav class="breadcrumb" aria-label="Хлебные крошки">
      <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">Контакты</span>
    </nav>

    <h1><em>Контакты</em></h1>
    <p class="blog-lead">Расскажите о задаче — инженер свяжется, уточнит детали и подготовит предварительный расчёт. Работаем по всей России; производство — в Ростове-на-Дону.</p>

    <div class="contact-rows">
      <div class="contact-row">
        <small>Адрес производства</small>
        <span><?= e(COMPANY_ADDRESS) ?></span>
      </div>
      <div class="contact-row">
        <small>Email</small>
        <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>
      </div>
      <?php if (CONTACT_PHONE !== ''): ?>
      <div class="contact-row">
        <small>Телефон</small>
        <a href="<?= e(tel_href(CONTACT_PHONE)) ?>"><?= e(CONTACT_PHONE) ?></a>
      </div>
      <?php endif; ?>
      <?php if (CONTACT_PHONE !== '' || MAX_PROFILE_URL !== ''): ?>
      <div class="contact-row">
        <small>Мессенджер MAX</small>
        <a href="<?= e(max_href()) ?>" rel="noopener"><?= e(CONTACT_PHONE !== '' ? CONTACT_PHONE : 'Написать в MAX') ?></a>
      </div>
      <?php endif; ?>
      <?php if (CONTACT_HOURS !== ''): ?>
      <div class="contact-row">
        <small>Часы работы</small>
        <span><?= e(CONTACT_HOURS) ?></span>
      </div>
      <?php endif; ?>
      <div class="contact-row">
        <small>Реквизиты</small>
        <span><?= e(COMPANY_NAME) ?><br>ИНН <?= e(COMPANY_INN) ?> · ОГРН <?= e(COMPANY_OGRN) ?></span>
      </div>
    </div>

    <?php if (CONTACT_PHONE === ''): ?>
    <p class="prose-note" style="margin-top: 18px">Телефон на сайте пока не опубликован — быстрее всего ответим по email или через форму ниже. Если хотите, чтобы мы добавили телефон и часы работы, напишите нам на <?= e(CONTACT_EMAIL) ?>.</p>
    <?php endif; ?>
  </div>

  <?php require __DIR__ . '/partials/calculator.php'; ?>
</main>
<?php page_end(); ?>

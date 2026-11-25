<?php
/**
 * Блок «Бесплатный предварительный расчёт» — переиспользуется на главной и в /kontakty.
 * JS-логика в /assets/app.js (id калькулятора — #calcForm).
 */

declare(strict_types=1);

require_once __DIR__ . '/../../lib/content.php';

$cc = site_content()['calculator'];
?>
<section class="calculator" id="calculation">
  <div class="calculator-intro">

    <p><?= e($cc['sectionLabel']) ?></p>
    <h2><?= e($cc['heading']) ?></h2>
    <p><?= e($cc['intro']) ?></p>
    <div class="engineer-note">
      <span>И</span>
      <p><b><?= e($cc['engineerNote']) ?></b><?= e($cc['engineerNoteExtra']) ?></p>
    </div>
  </div>

  <form class="calculator-form" id="calcForm" action="/submit" method="post" novalidate data-contact-email="<?= e(CONTACT_EMAIL) ?>">
    <input type="hidden" name="form" value="calculator">
    <input type="hidden" name="page" value="<?= e(current_path()) ?>">
    <label>
      <span>01 · ТИП ОБОРУДОВАНИЯ</span>
      <select name="liftType">
        <?php foreach ($cc['liftTypes'] as $t): ?><option><?= e($t) ?></option><?php endforeach; ?>
      </select>
    </label>

    <label>
      <span>02 · ГРУЗОПОДЪЁМНОСТЬ</span>
      <select name="capacity">
        <?php foreach ($cc['capacities'] as $i => $cap): ?><option<?= $i === 1 ? ' selected' : '' ?>><?= e($cap) ?></option><?php endforeach; ?>
      </select>
    </label>

    <div class="form-row">
      <label>
        <span>03 · ВЫСОТА ПОДЪЁМА</span>
        <input type="number" name="height" placeholder="Высота в мм, напр. 3000" min="0" inputmode="numeric">
      </label>
      <label>
        <span>04 · ГАБАРИТЫ ПЛАТФОРМЫ</span>
        <div class="platform-inputs">
          <input type="number" name="platformWidth" placeholder="Ширина, мм" min="0" inputmode="numeric" aria-label="Ширина платформы, мм">
          <input type="number" name="platformDepth" placeholder="Глубина, мм" min="0" inputmode="numeric" aria-label="Глубина платформы, мм">
        </div>
      </label>
    </div>

    <label>
      <span>05 · ГОРОД ИЛИ ОБЪЕКТ</span>
      <input type="text" name="city" placeholder="Например: склад, Краснодар" autocomplete="address-level2">
    </label>

    <label>
      <span>06 · КОЛИЧЕСТВО ОСТАНОВОК</span>
      <select name="stops">
        <?php foreach ($cc['stops'] as $s): ?><option><?= e($s) ?></option><?php endforeach; ?>
      </select>
    </label>

    <div class="form-row">
      <label>
        <span>07 · ВАШЕ ИМЯ</span>
        <input type="text" name="name" placeholder="Например: Иван" autocomplete="name">
      </label>
      <label>
        <span>08 · ТЕЛЕФОН *</span>
        <input type="tel" name="phone" placeholder="+7 ___ ___-__-__" autocomplete="tel" required>
      </label>
    </div>

    <label>
      <span>09 · EMAIL (ОПЦИОНАЛЬНО)</span>
      <input type="email" name="email" placeholder="name@example.com" autocomplete="email">
    </label>

    <div class="request-preview">
      <small>ВАША ЗАЯВКА</small>
      <p id="requestPreview">Заявка на расчёт: Грузовой подъёмник, до 500 кг, 2 остановки. Нужна консультация инженера и предварительная стоимость.</p>
    </div>

    <div class="form-consents">
      <label class="consent-label">
        <input type="checkbox" name="consentData" value="1" required>
        <span>Я даю согласие на обработку моих персональных данных (имя, телефон, email, сведения об объекте) для рассмотрения заявки и подготовки расчёта на условиях <a href="/consent" target="_blank" rel="noopener">Согласия на обработку персональных данных</a> и <a href="/privacy" target="_blank" rel="noopener">Политики обработки персональных данных</a>. Согласие можно отозвать в любое время.</span>
      </label>
      <label class="consent-label consent-label-optional">
        <input type="checkbox" name="consentNews" value="1">
        <span>Отдельно согласен(-на) получать рекламные и информационные сообщения на условиях <a href="/marketing" target="_blank" rel="noopener">Согласия на получение рекламных сообщений</a>. Необязательно.</span>
      </label>
    </div>

    <div class="form-hp" aria-hidden="true">
      <label>Не заполняйте это поле <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <button class="primary-button form-button" type="submit" data-button-text>
      <?= e($cc['buttonText']) ?><span aria-hidden="true" data-button-arrow>→</span>
    </button>
    <small class="privacy"><?= e($cc['privacyText']) ?></small>
    <small class="privacy-link"><a href="/otzyv-soglasiya">Отозвать согласие или обратиться по персональным данным</a></small>
  </form>
</section>

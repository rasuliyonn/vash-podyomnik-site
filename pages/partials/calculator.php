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
    <span class="section-index">/ 05</span>
    <p><?= e($cc['sectionLabel']) ?></p>
    <h2><?= e($cc['heading']) ?></h2>
    <p><?= e($cc['intro']) ?></p>
    <div class="engineer-note">
      <span>И</span>
      <p><b><?= e($cc['engineerNote']) ?></b><?= e($cc['engineerNoteExtra']) ?></p>
    </div>
  </div>

  <form class="calculator-form" id="calcForm" action="/submit" method="post" novalidate>
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
        <input type="checkbox" name="consentData" required>
        <span>Я согласен(-на) на обработку моих персональных данных в соответствии с <a href="/privacy" target="_blank" rel="noopener">Политикой по обработке и защиты персональных данных</a></span>
      </label>
      <label class="consent-label">
        <input type="checkbox" name="consentNews">
        <span>Я согласен(-на) на получение новостных рассылок, рассылок рекламного, информационного характера</span>
      </label>
    </div>

    <button class="primary-button form-button" type="submit" data-button-text>
      <?= e($cc['buttonText']) ?><span aria-hidden="true" data-button-arrow>→</span>
    </button>
    <small class="privacy"><?= e($cc['privacyText']) ?></small>
  </form>
</section>

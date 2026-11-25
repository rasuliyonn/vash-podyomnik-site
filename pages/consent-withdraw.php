<?php
/**
 * Отзыв согласия и обращения субъекта персональных данных.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/legal.php';
require_once __DIR__ . '/../lib/db.php';

$errors = [];
$sentId = 0;

$kinds = [
    'withdraw' => 'Отозвать согласие на обработку персональных данных',
    'marketing' => 'Отказаться от рекламных сообщений',
    'access' => 'Получить сведения об обработке моих данных',
    'correct' => 'Уточнить мои данные',
    'delete' => 'Удалить мои данные',
    'other' => 'Другое обращение',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Honeypot: скрытое поле, которое заполняют только боты.
    $honeypot = trim((string) ($_POST['website'] ?? ''));
    $contact = trim((string) ($_POST['contact'] ?? ''));
    $kind = (string) ($_POST['kind'] ?? 'other');
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($honeypot !== '') {
        // Не раскрываем боту причину отказа, но и не сохраняем мусор.
        $sentId = 0;
        $errors[] = 'Не удалось отправить обращение. Напишите на ' . PRIVACY_EMAIL . '.';
    } else {
        if ($contact === '') {
            $errors[] = 'Укажите телефон или email для ответа.';
        } elseif (mb_strlen($contact) > 200) {
            $errors[] = 'Контакт слишком длинный.';
        }
        if (!isset($kinds[$kind])) {
            $kind = 'other';
        }
        if (mb_strlen($message) > 2000) {
            $errors[] = 'Текст обращения не должен превышать 2000 символов.';
        }

        if ($errors === []) {
            try {
                $sentId = consent_request_add($contact, $kind, $message);
                // Уведомление оператору — необязательное: обращение уже сохранено в панели.
                $subject = 'Обращение по персональным данным #' . $sentId;
                $body = 'Категория: ' . $kinds[$kind] . "\n" . 'Контакт: ' . $contact . "\n" . 'Сообщение: ' . $message . "\n";
                @mail(CONTACT_EMAIL, $subject, $body, 'From: ' . CONTACT_EMAIL);
            } catch (Throwable $e) {
                error_log('[consent-request] ' . $e->getMessage());
                $errors[] = 'Не удалось сохранить обращение. Напишите на ' . PRIVACY_EMAIL . '.';
            }
        }
    }
}

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'Отзыв согласия', 'path' => '/otzyv-soglasiya'],
    ]),
];

page_start(
    'Отозвать согласие и обращения по персональным данным',
    'Форма отзыва согласия на обработку персональных данных, отказа от рекламных сообщений и иных обращений субъекта: сроки рассмотрения и контакты.',
    '/otzyv-soglasiya',
    ['jsonld' => $jsonld]
);
?>
<main class="legal-page" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">Отзыв согласия</span>
  </nav>

  <article class="legal-doc">
    <div class="legal-doc-head">
      <h1>Отозвать согласие или обратиться по персональным данным</h1>
      <p class="legal-doc-meta"><?= e(legal_revision(PRIVACY_VERSION)) ?></p>
    </div>

    <?php if ($sentId > 0): ?>
      <div class="legal-success" role="status">
        <p><b>Обращение зарегистрировано, номер № <?= (int) $sentId ?>.</b> Ответ поступит на указанный контакт в течение 10 рабочих дней. Если вопрос срочный, напишите на <a href="<?= e('mailto:' . PRIVACY_EMAIL) ?>"><?= e(PRIVACY_EMAIL) ?></a>.</p>
      </div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
      <div class="legal-error" role="alert">
        <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <section>
      <h2>1. Как отозвать согласие</h2>
      <p>1.1. Согласие на обработку персональных данных можно отозвать в любое время (ч. 2 ст. 9 Федерального закона № 152-ФЗ). Заполните форму ниже или напишите на <a href="<?= e('mailto:' . PRIVACY_EMAIL) ?>"><?= e(PRIVACY_EMAIL) ?></a> с темой «Отзыв согласия».</p>
      <p>1.2. Отзыв согласия не прекращает обработку, если для неё есть иное законное основание: например, исполнение заключённого договора или требование законодательства (ч. 2 ст. 9 Закона). В этом случае Оператор сообщит, какие данные и на каком основании продолжают обрабатываться.</p>
      <p>1.3. После отзыва данных, обрабатывавшихся только на основании согласия, Оператор прекращает обработку и уничтожает их в срок не более 30 дней (ч. 5 ст. 21 Закона). Об уничтожении направляется уведомление.</p>
      <p>1.4. Отказ от рекламных сообщений оформляется этой же формой (пункт «Отказаться от рекламных сообщений») и исполняется в течение 3 рабочих дней (ч. 1 ст. 18 Федерального закона № 38-ФЗ).</p>
    </section>

    <section>
      <h2>2. Форма обращения</h2>
      <form class="legal-form" method="post" action="/otzyv-soglasiya" novalidate>
        <label class="legal-form-field">
          <span>Что хотите сделать *</span>
          <select name="kind" required>
            <?php foreach ($kinds as $key => $label): ?>
              <option value="<?= e($key) ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="legal-form-field">
          <span>Телефон или email для ответа *</span>
          <input type="text" name="contact" required maxlength="200" autocomplete="email" placeholder="+7 ___ ___-__-__ или name@example.com">
          <small>Укажите тот же контакт, который использовали при заявке, — так мы быстрее найдём ваши данные.</small>
        </label>

        <label class="legal-form-field">
          <span>Сообщение (необязательно)</span>
          <textarea name="message" rows="4" maxlength="2000" placeholder="Опишите требование, укажите номер заявки или договора"></textarea>
        </label>

        <div class="legal-form-hp" aria-hidden="true">
          <label>Не заполняйте это поле
            <input type="text" name="website" tabindex="-1" autocomplete="off">
          </label>
        </div>

        <p class="legal-form-note">Обращение и контакт для ответа обрабатываются на основании ст. 20, 21 Федерального закона № 152-ФЗ — для рассмотрения обращения; отдельное согласие на это не требуется. Данные хранятся не дольше 3 лет с момента рассмотрения. <a href="/privacy">Подробнее</a>.</p>

        <button class="primary-button form-button" type="submit">Отправить обращение<span aria-hidden="true">→</span></button>
      </form>
    </section>

    <section>
      <h2>3. Другие способы обращения</h2>
      <ul>
        <li>Электронная почта: <a href="<?= e('mailto:' . PRIVACY_EMAIL) ?>"><?= e(PRIVACY_EMAIL) ?></a>.</li>
        <li>Почтовый адрес: <?= e(COMPANY_ADDRESS) ?><?php if (COMPANY_LEGAL_NAME !== ''): ?>, <?= e(COMPANY_LEGAL_NAME) ?><?php endif; ?>.</li>
        <?php if (CONTACT_PHONE !== ''): ?><li>Телефон для справок: <a href="<?= e(tel_href(CONTACT_PHONE)) ?>"><?= e(CONTACT_PHONE) ?></a> (по телефону можно уточнить порядок обращения, но для отзыва согласия нужна письменная форма или форма на сайте).</li><?php endif; ?>
      </ul>
      <p>3.1. Срок рассмотрения — 10 рабочих дней; он может быть продлён не более чем на 5 рабочих дней с уведомлением заявителя (ч. 1, 2 ст. 20 Закона).</p>
      <p>3.2. Если ответ не получен или он вас не устроил, можно обратиться в Роскомнадзор или в суд (ст. 17, 23, 24 Закона).</p>
    </section>

    <?php legal_related_links('/otzyv-soglasiya'); ?>
    <?php legal_doc_footer(); ?>
  </article>
</main>
<?php page_end(); ?>

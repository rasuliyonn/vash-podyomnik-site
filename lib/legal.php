<?php
/**
 * Общие элементы юридических документов: реквизиты оператора, дата редакции,
 * единый подвал. Тексты документов — в pages/*.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/render.php';

/** Строка «Редакция от … · версия …». */
function legal_revision(string $version): string
{
    return 'Редакция от 26.11.2026 · версия ' . $version;
}

/** Юридическое описание оператора без выдуманных реквизитов. */
function legal_operator_text(): string
{
    $name = company_display_name();
    $text = $name . ' (индивидуальный предприниматель)';
    $ids = [];
    if (company_inn_value() !== '') {
        $ids[] = 'ИНН ' . company_inn_value();
    }
    if (company_ogrnip_value() !== '') {
        $ids[] = 'ОГРНИП ' . company_ogrnip_value();
    }
    if ($ids !== []) {
        $text .= ', ' . implode(', ', $ids);
    }
    return $text;
}

/** Подвал юридического документа: реквизиты и контакты. */
function legal_doc_footer(): void
{
    ?>
    <div class="legal-doc-footer">
      <p><b>Оператор / продавец:</b> <?= e(legal_operator_text()) ?></p>
      <p>Адрес: <?= e(COMPANY_ADDRESS) ?></p>
      <?php if (CONTACT_PHONE !== ''): ?><p>Телефон: <a href="<?= e(tel_href(CONTACT_PHONE)) ?>"><?= e(CONTACT_PHONE) ?></a></p><?php endif; ?>
      <p>Email: <a href="<?= e('mailto:' . CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a><?php if (PRIVACY_EMAIL !== CONTACT_EMAIL): ?> · по вопросам персональных данных: <a href="<?= e('mailto:' . PRIVACY_EMAIL) ?>"><?= e(PRIVACY_EMAIL) ?></a><?php endif; ?></p>
      <p><a href="/requisites">Полные сведения о продавце и операторе</a></p>
      <p>Документ опубликован на сайте <?= e(SITE_URL) ?>. Версия документа указана в начале страницы; предыдущие редакции предоставляются по запросу.</p>
    </div>
    <?php
}

/** Список ссылок «другие документы» для юридических страниц. */
function legal_related_links(string $currentPath = ''): void
{
    ?>
    <nav class="legal-related" aria-label="Другие юридические документы">
      <h2>Другие документы</h2>
      <ul>
        <?php foreach (legal_document_links() as $doc): ?>
          <?php if ($doc['href'] === $currentPath) { continue; } ?>
          <li><a href="<?= e($doc['href']) ?>"><?= e($doc['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <?php
}

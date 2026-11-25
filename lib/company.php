<?php
/**
 * Сведения об операторе/продавце и их публикация.
 *
 * Реквизиты публикуются только после проверки формата и контрольных сумм:
 * ИНН ИП — 12 цифр, ОГРНИП — 15 цифр. Пустые/некорректные значения не выводятся
 * как факт, вместо них показывается корректная формулировка о предоставлении
 * сведений по запросу (см. config.php, комментарий о реквизитах).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/** ИНН юридического лица (10 цифр) — проверка контрольной суммы. */
function company_inn10_valid(string $value): bool
{
    if (!preg_match('/^\d{10}$/', $value)) {
        return false;
    }
    $weights = [2, 4, 10, 3, 5, 9, 4, 6, 8];
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int) $value[$i] * $weights[$i];
    }
    return ((int) $value[9]) === ($sum % 11) % 10;
}

/** ИНН индивидуального предпринимателя (12 цифр) — проверка контрольных сумм. */
function company_inn12_valid(string $value): bool
{
    if (!preg_match('/^\d{12}$/', $value)) {
        return false;
    }
    $w11 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
    $w12 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
    $s1 = 0;
    $s2 = 0;
    for ($i = 0; $i < 10; $i++) {
        $s1 += (int) $value[$i] * $w11[$i];
    }
    for ($i = 0; $i < 11; $i++) {
        $s2 += (int) $value[$i] * $w12[$i];
    }
    return ((int) $value[10]) === ($s1 % 11) % 10
        && ((int) $value[11]) === ($s2 % 11) % 10;
}

/** ИНН в допустимом для оператора виде (10 или 12 цифр) либо пустая строка. */
function company_inn_value(): string
{
    $value = preg_replace('/\D/', '', (string) COMPANY_INN) ?? '';
    if (company_inn12_valid($value) || company_inn10_valid($value)) {
        return $value;
    }
    return '';
}

/** ОГРНИП (15 цифр) с проверкой контрольного разряда либо пустая строка. */
function company_ogrnip_value(): string
{
    $value = preg_replace('/\D/', '', (string) COMPANY_OGRNIP) ?? '';
    if (!preg_match('/^\d{15}$/', $value)) {
        return '';
    }
    $base = (int) substr($value, 0, 14);
    $control = $base % 13 % 10;
    return $control === (int) $value[14] ? $value : '';
}

/** Юридическое наименование, если оно подтверждено владельцем, иначе бренд. */
function company_display_name(): string
{
    $legal = trim((string) COMPANY_LEGAL_NAME);
    return $legal !== '' ? $legal : COMPANY_NAME;
}

function company_requisites_verified(): bool
{
    return company_inn_value() !== '' && company_ogrnip_value() !== '';
}

/**
 * Строки реквизитов для футера и информационных страниц.
 * Ничего не выдумывает: отсутствующие данные заменяются корректной формулировкой.
 */
function company_requisites_lines(bool $withAddress = true): array
{
    $lines = [company_display_name()];
    $ids = [];
    if (company_inn_value() !== '') {
        $ids[] = 'ИНН ' . company_inn_value();
    }
    if (company_ogrnip_value() !== '') {
        $ids[] = 'ОГРНИП ' . company_ogrnip_value();
    }
    if ($ids !== []) {
        $lines[] = implode(' · ', $ids);
    } else {
        $lines[] = 'ИНН и ОГРНИП — по запросу (сведения ЕГРИП)';
    }
    if ($withAddress) {
        $lines[] = (string) COMPANY_ADDRESS;
    }
    return $lines;
}

/**
 * HTML-блок реквизитов (экранированный). Используется в футере и на страницах.
 */
function company_requisites_html(bool $withAddress = true, bool $withContacts = false): string
{
    $parts = [];
    foreach (company_requisites_lines($withAddress) as $line) {
        $parts[] = e($line);
    }
    if ($withContacts) {
        if (CONTACT_PHONE !== '') {
            $parts[] = '<a href="' . e(tel_href(CONTACT_PHONE)) . '">' . e(CONTACT_PHONE) . '</a>';
        }
        $parts[] = '<a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a>';
    }
    return implode('<br>', $parts);
}

/** Публичная ссылка на сведения о продавце/операторе. */
function company_requisites_link(string $label = 'Реквизиты и информация о продавце'): string
{
    return '<a href="/requisites">' . e($label) . '</a>';
}

/** Запись в реестре операторов ПД (если номер задан). */
function company_rkn_note(): string
{
    if (RKN_REGISTRY_NUMBER !== '') {
        return 'Запись в реестре операторов, осуществляющих обработку персональных данных: № ' . e(RKN_REGISTRY_NUMBER) . '.';
    }
    return 'Сведения об операторе подлежат включению в реестр операторов, осуществляющих обработку персональных данных (ст. 22 Федерального закона № 152-ФЗ).';
}

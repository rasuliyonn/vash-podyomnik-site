<?php
/**
 * Конфигурация сайта «Ваш Подъёмник» (PHP-версия для shared-хостинга).
 *
 * Секреты и контакты можно переопределить переменными окружения
 * (SetEnv в .htaccess) — см. README.md.
 *
 * ВАЖНО О РЕКВИЗИТАХ. Поля COMPANY_LEGAL_NAME / COMPANY_INN / COMPANY_OGRNIP
 * заполняются ТОЛЬКО реальными данными из ЕГРИП. Пока значения пусты, сайт
 * не публикует ИНН/ОГРНИП и показывает на /requisites, что сведения о
 * государственной регистрации предоставляются по запросу: выдуманные или
 * неверные реквизиты хуже, чем их отсутствие (ст. 5 ч. 3 ФЗ «О рекламе»,
 * ст. 10 Закона «О защите прав потребителей»). После получения выписки
 * ЕГРИП задайте значения здесь или через SetEnv.
 */

declare(strict_types=1);

define('SITE_URL', rtrim(getenv('SITE_URL') ?: 'https://vashpodemnik61.ru', '/'));
define('SITE_NAME', 'Ваш Подъёмник');
define('SITE_BUILD_DATE', '2026-11-26');

define('CONTACT_EMAIL', getenv('CONTACT_EMAIL') ?: 'info@vashpodemnik61.ru');
define('CONTACT_PHONE', getenv('CONTACT_PHONE') ?: '+7 (952) 603-57-85'); // реальный номер компании;
                                                          // пусто — блок телефона не выводится
define('CONTACT_HOURS', getenv('CONTACT_HOURS') ?: '');   // например: 'Пн–Пт 08:00–18:00'
// Отдельный адрес для обращений по персональным данным (если не задан — CONTACT_EMAIL)
define('PRIVACY_EMAIL', getenv('PRIVACY_EMAIL') ?: CONTACT_EMAIL);
// ФИО лица, ответственного за организацию обработки ПД. Публикуется, только если задано.
define('PRIVACY_OFFICER', getenv('PRIVACY_OFFICER') ?: '');
// Номер записи в реестре операторов Роскомнадзора (если оператор зарегистрирован).
define('RKN_REGISTRY_NUMBER', getenv('RKN_REGISTRY_NUMBER') ?: '');

// Мессенджер MAX (max.ru). Прямой ссылки «по номеру», как wa.me у WhatsApp, у MAX нет:
// ссылка на человека — это профиль вида https://max.ru/u/<код> (профиль → «Пригласить друзей»).
// Пока MAX_PROFILE_URL пуст, ссылка MAX ведёт на веб-версию мессенджера, а номер показывается текстом.
define('MAX_PROFILE_URL', getenv('MAX_PROFILE_URL') ?: '');
define('MAX_WEB_URL', 'https://max.ru/');

// Отображаемое название (бренд). Юридическое наименование — отдельно, только из ЕГРИП.
define('COMPANY_NAME', getenv('COMPANY_NAME') ?: '«Ваш Подъёмник»');
// Пример корректного значения: 'Индивидуальный предприниматель Иванов Иван Иванович'
define('COMPANY_LEGAL_NAME', getenv('COMPANY_LEGAL_NAME') ?: '');
// Для ИП: ИНН — 12 цифр, ОГРНИП — 15 цифр. Значения проверяются по контрольным суммам.
define('COMPANY_INN', getenv('COMPANY_INN') ?: '');
define('COMPANY_OGRNIP', getenv('COMPANY_OGRNIP') ?: '');
// Место нахождения (город/адрес из ЕГРИП). Полный адрес желательно указать полностью.
define('COMPANY_ADDRESS', getenv('COMPANY_ADDRESS') ?: 'г. Ростов-на-Дону');
define('COMPANY_CITY', getenv('COMPANY_CITY') ?: 'Ростов-на-Дону');
define('COMPANY_REGION', getenv('COMPANY_REGION') ?: 'Ростовская область');

// Хостинг-провайдер (обработчик ПД по поручению оператора). Серверы должны быть в РФ.
define('HOSTING_PROVIDER', getenv('HOSTING_PROVIDER') ?: 'хостинг-провайдер, серверы которого расположены на территории Российской Федерации');

// Сроки хранения (месяцы). Заявки старше срока удаляются автоматически в админке.
define('SUBMISSION_RETENTION_MONTHS', (int) (getenv('SUBMISSION_RETENTION_MONTHS') ?: 12));
define('CONSENT_EVENT_RETENTION_MONTHS', 36);

// Версии юридических документов. Меняются при каждой правке текста; версия
// сохраняется вместе с фактом согласия (доказательство согласия — ч. 3 ст. 9 152-ФЗ).
define('PRIVACY_VERSION', '2026.11-1');
define('TERMS_VERSION', '2026.11-1');
define('CONSENT_VERSION', '2026.11-1');
define('MARKETING_CONSENT_VERSION', '2026.11-1');
define('COOKIE_POLICY_VERSION', '2026.11-1');
define('OFFER_VERSION', '2026.11-1');

// Имя cookie с настройками согласия на cookie (JSON, см. lib/consent.php).
define('COOKIE_CONSENT_NAME', 'vp_consent');
// Устаревший cookie прежнего баннера: читается только для миграции.
define('COOKIE_LEGACY_NAME', 'vashpodemnik61_cookie_ok');

define('DATA_DIR', __DIR__ . '/data');
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('WORKS_DIR', __DIR__ . '/works');
define('ARTICLES_DIR', __DIR__ . '/lib/articles');

// Верификация поисковиков (мета-теги в <head> появятся, только если заданы)
define('YANDEX_VERIFICATION', getenv('YANDEX_VERIFICATION') ?: '');
define('GOOGLE_VERIFICATION', getenv('GOOGLE_VERIFICATION') ?: '');

// Счётчик Яндекс.Метрики (если пусто — ничего не выводится). Загружается только
// после согласия на аналитические cookie. Google Analytics намеренно не поддерживается:
// передача данных в США требует уведомления РКН о трансграничной передаче (ст. 12 152-ФЗ)
// и несовместима с требованием локализации (ч. 5 ст. 18 152-ФЗ).
define('YANDEX_METRIKA_ID', getenv('YANDEX_METRIKA_ID') ?: (getenv('NEXT_PUBLIC_YM_ID') ?: ''));

// SERVER_NAME для ссылок в письмах/логах не используется — не добавляйте сюда секреты.
define('DB_PATH', DATA_DIR . '/site.sqlite');

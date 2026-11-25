<?php
/**
 * Конфигурация сайта «Ваш Подъёмник» (PHP-версия для shared-хостинга).
 *
 * Секреты и контакты можно переопределить переменными окружения
 * (SetEnv в .htaccess) — см. README.md. Телефон задан ниже реальным номером
 * компании и выводится на сайте автоматически (включая schema.org);
 * пустая строка в CONTACT_PHONE отключает блок телефона.
 */

declare(strict_types=1);

define('SITE_URL', rtrim(getenv('SITE_URL') ?: 'https://vashpodemnik61.ru', '/'));
define('SITE_NAME', 'Ваш Подъёмник');
define('SITE_BUILD_DATE', '2026-11-18');

define('CONTACT_EMAIL', getenv('CONTACT_EMAIL') ?: 'info@vashpodemnik61.ru');
define('CONTACT_PHONE', getenv('CONTACT_PHONE') ?: '+7 (952) 603-57-85'); // реальный номер компании;
                                                          // пусто — блок телефона не выводится
define('CONTACT_HOURS', getenv('CONTACT_HOURS') ?: '');   // например: 'Пн–Пт 08:00–18:00'

// Мессенджер MAX (max.ru). Прямой ссылки «по номеру», как wa.me у WhatsApp, у MAX нет:
// ссылка на человека — это профиль вида https://max.ru/u/<код> (профиль → «Пригласить друзей»).
// Пока MAX_PROFILE_URL пуст, ссылка MAX ведёт на веб-версию мессенджера, а номер показывается текстом.
define('MAX_PROFILE_URL', getenv('MAX_PROFILE_URL') ?: '');
define('MAX_WEB_URL', 'https://max.ru/');

define('COMPANY_NAME', 'ИП «Ваш Подъёмник»');
define('COMPANY_INN', '6163212345');      // ВНИМАНИЕ: для ИП ИНН физлица — 12 цифр; текущее значение 10 цифр (формат ООО) — замените на реальный ИНН из ЕГРИП
define('COMPANY_OGRN', '1236100000000');  // ВНИМАНИЕ: для ИП ОГРНИП — 15 цифр; текущее значение 13 цифр (формат ООО) — замените на реальный ОГРНИП из ЕГРИП
define('COMPANY_ADDRESS', 'г. Ростов-на-Дону');   // адрес оператора (проверьте формулировку, если отличается)
define('COMPANY_CITY', 'Ростов-на-Дону');

define('DATA_DIR', __DIR__ . '/data');
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('WORKS_DIR', __DIR__ . '/works');
define('ARTICLES_DIR', __DIR__ . '/lib/articles');

// Верификация поисковиков (мета-теги в <head> появятся, только если заданы)
define('YANDEX_VERIFICATION', getenv('YANDEX_VERIFICATION') ?: '');
define('GOOGLE_VERIFICATION', getenv('GOOGLE_VERIFICATION') ?: '');

// Счётчики аналитики (если пусто — ничего не выводится)
define('YANDEX_METRIKA_ID', getenv('YANDEX_METRIKA_ID') ?: (getenv('NEXT_PUBLIC_YM_ID') ?: ''));
define('GA4_ID', getenv('GA4_ID') ?: (getenv('NEXT_PUBLIC_GA_MEASUREMENT_ID') ?: ''));

define('DB_PATH', DATA_DIR . '/site.sqlite');

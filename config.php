<?php
/**
 * Конфигурация сайта «Ваш Подъёмник» (PHP-версия для shared-хостинга).
 *
 * Секреты и контакты можно переопределить переменными окружения
 * (SetEnv в .htaccess) — см. README.md. Телефон и часы работы по умолчанию
 * НЕ заданы: выдумывать их нельзя. Задайте CONTACT_PHONE / CONTACT_HOURS
 * через SetEnv, и блок появится на сайте автоматически (включая schema.org).
 */

declare(strict_types=1);

define('SITE_URL', rtrim(getenv('SITE_URL') ?: 'https://vash-podyomnik.ru', '/'));
define('SITE_NAME', 'Ваш Подъёмник');
define('SITE_BUILD_DATE', '2026-11-18');

define('CONTACT_EMAIL', getenv('CONTACT_EMAIL') ?: 'info@vash-podyomnik.ru');
define('CONTACT_PHONE', getenv('CONTACT_PHONE') ?: '');   // пусто — блок телефона не выводится
define('CONTACT_HOURS', getenv('CONTACT_HOURS') ?: '');   // например: 'Пн–Пт 08:00–18:00'

define('COMPANY_NAME', 'ООО «Ваш Подъёмник»');
define('COMPANY_INN', '6163212345');
define('COMPANY_OGRN', '1236100000000');
define('COMPANY_ADDRESS', '344000, г. Ростов-на-Дону, ул. Промышленная, д. 12');
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

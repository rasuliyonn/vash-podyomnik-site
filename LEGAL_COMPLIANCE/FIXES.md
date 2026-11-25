# Внесённые исправления

Дата: 26.11.2026. Все изменения выполнены в репозитории; производственная база не изменялась. Локальная data/site.sqlite использовалась только для тестов и очищена после проверки.

## 1. Сводка по файлам

| Файл | Изменение |
|---|---|
| config.php | Реквизиты только из ЕГРИП, версии документов, сроки хранения, удалён GA4, добавлены PRIVACY_EMAIL/PRIVACY_OFFICER/RKN_REGISTRY_NUMBER/HOSTING_PROVIDER |
| lib/company.php | Новый: проверка ИНН/ОГРНИП по контрольным суммам, безопасный вывод реквизитов |
| lib/consent.php | Новый: разбор согласия cookie, миграция старого cookie, gating аналитики |
| lib/legal.php | Новый: единая шапка/подвал юридических документов, ссылки |
| lib/db.php | Схема: поля согласия, consent_events, consent_requests, login_attempts, миграции, purge, rate limit |
| lib/render.php | Футер со всеми документами, новый cookie-баннер, только Метрика, настройки cookie |
| lib/seo.php | taxID только при валидном ИНН, корректный адрес, legalName |
| index.php | Новые маршруты, CSP без Google, sitemap, no-store для админки |
| pages/privacy.php | Полностью переписана |
| pages/consent.php | Новый документ согласия |
| pages/cookies.php | Новый документ политики cookie |
| pages/marketing.php | Новое согласие на рекламу |
| pages/terms.php | Переписано, убраны незаконные условия |
| pages/offer.php | Новые условия заказа/оплаты/возврата |
| pages/requisites.php | Новые сведения о продавце/операторе |
| pages/consent-withdraw.php | Новая форма отзыва согласия и обращений |
| pages/submit.php | Строгая проверка согласия, сохранение доказательств, rate limit, honeypot, email-валидация |
| pages/partials/calculator.php | Раздельные чекбоксы, ссылки на документы, honeypot |
| assets/app.js | Реальная обработка ответа сервера, статусы, click-to-load видео |
| assets/cookie-consent.js | Новый менеджер согласия (категории, настройки, отзыв) |
| assets/cookie-banner.js | Удалён (заменён) |
| assets/site.css | Стили юридических страниц, таблиц, формы, баннера, видео |
| pages/equipment-detail.php | Видео по клику с предупреждением |
| pages/contacts.php, pages/about.php | Убраны недостоверные реквизиты и заявления |
| lib/equipment.php, lib/content.php | Формулировки о ТР ТС и сертификации |
| lib/admin.php | Обязательная смена пароля, strict session, ссылка на обращения |
| pages/admin/login.php | Лимит попыток входа |
| pages/admin/settings.php | Актуальные env-переменные, предупреждение |
| pages/admin/submissions.php | Поиск, согласие, срок хранения, каскадное удаление |
| pages/admin/index.php | Обращения по ПД, purge |
| pages/admin/consent-requests.php | Новый раздел обработки обращений |
| .htaccess | Закрыты docs/scripts/seo-content/LEGAL_COMPLIANCE, uploads, CSP, no-store |
| uploads/.htaccess, data/.htaccess | Новые: запрет исполнения и доступа |
| .well-known/security.txt | Policy → /privacy, Canonical, Expires |
| README.md, docs/rf-compliance.md | Актуализированы |

## 2. Ключевые изменения по существу

### 2.1. Реквизиты

Было:

- COMPANY_INN = 6163212345 (10 цифр, формат ЮЛ);
- COMPANY_OGRN = 1236100000000 (13 цифр, формат ОГРН ЮЛ);
- в политике и соглашении — адрес «г. Королёв, 1А-8».

Стало:

- COMPANY_LEGAL_NAME, COMPANY_INN, COMPANY_OGRNIP, COMPANY_ADDRESS — только из ЕГРИП;
- проверка ИНН (10/12 цифр) и ОГРНИП (15 цифр) по контрольным суммам;
- при отсутствии данных сайт не публикует номера, а сообщает о предоставлении сведений по запросу и даёт ссылку на ЕГРИП;
- адрес берётся из единой константы.

### 2.2. Согласие

Было: чекбокс `consentData` проверялся в PHP, но доказательства не сохранялись; текст ссылался только на политику.

Стало:

- отдельный документ /consent (версия 2026.11-1) и отдельный чекбокс без предустановки;
- сервер принимает только явные значения 1/on/true;
- сохраняются consent_version, consent_given_at, ip_truncated, source_page;
- журнал consent_events хранит хеш контактов, категорию (data/marketing), версию, форму, страницу, усечённый IP, User-Agent;
- отзыв — /otzyv-soglasiya, запись в consent_requests, обработка в админке.

### 2.3. Cookie

Было: один cookie `vashpodemnik61_cookie_ok` (1/0), кнопки «Принять»/«Отклонить», аналитика при 1.

Стало:

- `vp_consent` — JSON {v, necessary, analytics, marketing, ts, policy};
- баннер: «Принять все», «Только необходимые», «Настроить», «Сохранить выбор»;
- серверный gating: lib/render.php выводит Метрику только при consent_analytics_allowed();
- миграция старого cookie: 1 → аналитика разрешена, 0 → запрещена;
- кнопка «Настройки cookie» в футере, страница /cookies;
- GA4 удалён.

### 2.4. Форма

Было: JS отправлял fetch и сразу показывал «Заявка скопирована», не проверяя ответ; ошибки сервера терялись.

Стало:

- статус «Отправляем…», успех/ошибка из JSON, блокировка кнопки, сброс только при успехе;
- сервер: honeypot, проверка email, длины полей, rate limit 5 заявок/10 минут с /24;
- 400 без согласия, 201 с согласием;
- ошибка без JS показывает текст и ссылку на /raschet.

### 2.5. Аналитика

- GA4/Google Tag удалены из кода, CSP, README и чек-листа;
- Метрика: только после согласия, anonymizeIP, webvisor off;
- CSP: script-src/connect-src/img-src только self + mc.yandex.ru; frame-src — youtube/vimeo для видео по клику.

### 2.6. Админка

- при пароле по умолчанию все разделы, кроме настроек, недоступны;
- лимит 10 попыток входа за 15 минут, задержка 400 мс;
- strict mode сессии, Secure при HTTPS, HttpOnly, SameSite=Lax;
- в заявках видны версия/дата согласия, реклама, источник; поиск по контактам;
- новый раздел «Обращения по ПД»;
- удаление заявки удаляет и журнал согласия по хешу контактов.

### 2.7. Безопасность

- uploads/.htaccess: запрет .php/.phtml/.phar/.pl/.py/.cgi/.sh;
- data/.htaccess: Require all denied;
- .htaccess: закрыты docs/scripts/seo-content/LEGAL_COMPLIANCE, .git, start-php.bat, router.dev.php;
- Options -Indexes;
- админка no-store;
- security.txt исправлен.

## 3. Проверка

| Проверка | Команда/способ | Результат |
|---|---|---|
| Синтаксис PHP | php -l по 67 файлам | 0 ошибок |
| Публичные страницы | GET /, /privacy, /consent, /cookies, /terms, /offer, /marketing, /requisites, /otzyv-soglasiya, /raschet, /kontakty, /o-kompanii | Все 200, без PHP-ошибок |
| Sitemap | 42 URL из /sitemap.xml | Все 200 |
| Заявка без согласия | POST /submit JSON consentData=false | 400, текст «Необходимо отметить согласие…» |
| Заявка с согласием | POST /submit JSON consentData=true, consentNews=true | 201, id, запись в БД, 2 consent_events |
| Отзыв согласия | POST /otzyv-soglasiya | 200, запись consent_requests |
| Cookie gating | GET / с разными vp_consent | Без согласия и при analytics=false Метрики нет; при true — есть; legacy=1 — есть |
| Браузер | incognito, / и /raschet | Баннер, согласие, cookie, Метрика, успешная заявка, 0 ошибок консоли |
| Админка | login admin/admin | 303 → /upravlenie/settings?force=1; после смены пароля разделы 200 |
| Заголовки | GET / | CSP без Google, HSTS, nosniff, Referrer-Policy, X-Frame-Options |
| Тестовые данные | PHP-скрипт очистки | Заявки/события/обращения удалены, пароль возвращён в состояние «по умолчанию» |

## 4. Откат

Изменения обратимы через git (рабочее дерево). Перед деплоем рекомендуется резервная копия data/site.sqlite: миграции добавляют столбцы и таблицы, не удаляя существующие данные.

# «Ваш Подъёмник» — сайт (PHP 8 + SQLite)

Полноценный PHP 8.x сайт для обычного shared-хостинга (Apache + PHP + SQLite).
Весь репозиторий — это сайт: его можно заливать на FTP как есть.

## Что внутри

- Публичные страницы: главная, каталог оборудования, 5 карточек оборудования,
  «Наши работы» (71 фото), «Вопросы и ответы» (28 вопросов), блог (16 статей),
  «О компании», «Контакты», «Расчёт стоимости», privacy, terms, 404.
- SEO/GEO: мета-теги и canonical на каждую страницу, JSON-LD (Organization + LocalBusiness,
  WebSite, BreadcrumbList, FAQPage, Article, Service, ItemList, ImageGallery),
  `sitemap.xml` (генерируется), `robots.txt` с правилами для ответных ИИ-краулеров,
  `llms.txt`, RSS блога (`/blog/rss.xml`), OG-обложка.
- Форма заявки → SQLite `data/site.sqlite` (таблица `form_submissions`) +
  копирование текста заявки в буфер (работает и без JS).
- Админка `/upravlenie`: заявки (статусы), оборудование (тексты, хар-ки, фото, документы),
  работы (галерея: добавление/удаление/подписи), смена пароля.
- Портфолио: при первом обращении `works_images` автозаполняется файлами `works/work-*.jpg`.

## Требования

- PHP **8.0+** с расширениями: `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `json`
- Apache с `mod_rewrite`, `mod_headers`, `mod_expires`/`mod_deflate` (желательно)
- Каталог `data/` — доступен на запись (там создаётся `site.sqlite`)
- Каталог `uploads/` — доступен на запись (сюда попадают файлы из админки)

## Установка на shared-хостинг (FTP)

1. Залейте содержимое репозитория в корень сайта (обычно `public_html/` или `www/`).
   Папки `.git`, `.claude`, `.openai`, `.freebuff` заливать **не нужно**.
2. Убедитесь, что `.htaccess` перенёсся (он скрытый; в FileZilla: «Принудительный показ скрытых»).
3. Выставьте права (если нужно): `data` и `uploads` — `755` (или `775`).
4. Откройте `https://ваш-домен/` — главная должна загрузиться.
   База `data/site.sqlite` и таблицы создадутся автоматически при первом запросе.
5. Зайдите в `/upravlenie`, пароль по умолчанию — **admin**. **Сразу** смените в «Настройки».
6. Проверьте `/sitemap.xml`, `/robots.txt`, отправьте их в Яндекс.Вебмастер и Google Search Console.

## Настройка через окружение (SetEnv в .htaccess)

Пример — в закомментированных строках `.htaccess`. Раскомментируйте и подставьте:

```apache
SetEnv CONTACT_PHONE "+7 (863) 000-00-00"
SetEnv CONTACT_HOURS "Пн–Пт 08:00–18:00"
SetEnv YANDEX_METRIKA_ID "12345678"
SetEnv GA4_ID "G-XXXXXXXXXX"
SetEnv YANDEX_VERIFICATION "код-из-вебмастера"
SetEnv GOOGLE_VERIFICATION "код-из-search-console"
```

Телефон/часы по умолчанию пустые — блок не выводится, schema.org без телефона.
**Выдумывать номер нельзя — задайте реальный.**

## Локальный предпросмотр

```bash
start-php.bat
```
откроет сайт на http://127.0.0.1:8090/ (нужен PHP 8 на машине; если PHP нет — скрипт
попробует портабельную сборку из Temp и подскажет, где скачать).
Сайт также работает через `php -S` с роутером `router.dev.php`, который нужен
только локально; на хостинг он безвреден.

OG-обложка пересоздаётся: `python scripts/render-brand-pngs.py` и `python scripts/make-real-images.py` (нужны Pillow и шрифты из `scripts/fonts-ttf/`).

Фото из `works/` оптимизируются скриптом `python scripts/works-optimize.py` (ресайз до 1920px, progressive JPEG q78, WebP, превью `-480` для srcset, обновляет `lib/works_image_meta.php`). После добавления новых фото в `/upravlenie/works` прогоните скрипт ещё раз и закоммитьте `works/*.webp`, `works/*-480.*` и `lib/works_image_meta.php`.

После деплоя отправьте URL-ы в IndexNow (файл ключа `1c3fddc1f0b4408442f6c2e4f3f6f662.txt` лежит в корне сайта):

```
py -3 <путь-к-claude-seo>/scripts/runtime.py run indexnow_submit.py --host vashpodemnik61.ru \
  --key 1c3fddc1f0b4408442f6c2e4f3f6f662 \
  --key-location https://vashpodemnik61.ru/1c3fddc1f0b4408442f6c2e4f3f6f662.txt \
  --urls-file seo-audit-2026-11-18/urls.txt
```

Отчёт о состоянии: полный SEO-аудит от 18.11.2026 с планом внедрения — `C:\Users\Max\Desktop\seo-audit-2026-11-18\audit-report.md` (лежит вне репозитория, в деплой не попадёт).

## Что здесь НЕ используется

- Node.js/Next.js/Cloudflare Workers не нужны — сайт целиком на PHP.
- `.openai/hosting.json` — остаток старого стека (Cloudflare D1/Workers);
  для PHP-версии не используется, хранится в репозитории для истории.
- MAX-бот: заявки копятся в админке; если нужен бот — нужен токен, обращайтесь.
- Главные тексты сайта правятся в `lib/content.php` (не через админку).

## Структура

```
├── index.php          # front controller (маршрутизация)
├── .htaccess          # ЧПУ, защита data/lib/pages, заголовки, кеш
├── start-php.bat      # локальный запуск (Windows)
├── config.php         # SITE_URL, реквизиты, env (секреты сюда НЕ пишем)
├── lib/               # db, seo, render, content, equipment, faq, blog, admin
│   └── articles/      # 16 статей блога (файл = статья)
├── pages/             # страницы (*.php) + pages/partials/calculator.php
├── assets/            # style.css + site-ext.css + app.js + cookie-banner.js + картинки
├── docs/              # чек-лист соответствия законам РФ (регистрация в Роскомнадзоре и др. внешние обязательства)
├── works/             # 71 фото портфолио
├── uploads/           # файлы из админки (создаётся автоматически)
└── data/              # site.sqlite (создаётся автоматически)
```

## Частые проблемы

- **«Не удалось создать каталог data/»** — дайте `data/` права на запись.
- **404 вместо ЧПУ** — у хостинга нет mod_rewrite или выключен `.htaccess`;
  включите AllowOverride, либо обратитесь в поддержку хостинга.
- **Кириллица-«крокозябры»** — файлы .php должны остаться в UTF-8 (они уже в UTF-8);
  не пересохраняйте их в Windows Notepad.
- **Сайт белый/500** — смотрите `php-error.log` в корне; чаще всего — нет `pdo_sqlite`.

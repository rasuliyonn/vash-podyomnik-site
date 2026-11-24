# Findings & Decisions

## Requirements (from user, 2026-11-20)
- «Сделай сайт топом статьи» — сайт должен содержать ТОП СТАТЕЙ (featured articles), видимо на главной и в блоге.
- «Добавь SEO» — SEO-повышение: флаги/структура для новых блоков, FAQ для SEO.
- «Блоги под SEO, вопросы и ответы» — SEO-фокусные вопросы-ответы (FAQPage schema уже присутствует у статей с 'faq' — добавляем/расширяем).
- «Правь под SEO» — правки под SEO-аудит от 18.11.2026: новая статья из бэклога §11, перелинковка.
- «Дизайн, чтобы не было похоже на ИИ» — отличительная типографика и layout для нового блока.

## Research findings
- Сайт «Ваш Подъёмник» (PHP 8 + SQLite, shared hosting) — промышленный дизайн: тёмный (#070909), PT Sans Narrow + PT Sans + PT Mono, всё UPPERCASE, сетка карточек, секции с нумерацией /01../06.
- PHP 8.4.27 есть в портативной сборке C:\Users\Max\AppData\Local\Temp\opencode\php-8.4\php.exe — можно запустить php -S.
- SEO-аудит от 18.11.2026: 88/100, открытые волны: E-E-A-T (нет авторов/кейсов), новая статья из бэклога (§11 «Модернизация грузового подъёмника»), внутренняя перелинковка из 16 статей в хабы.
- Блог: 16 статей, категории: «Гайды» (по умолчанию), «Оборудование», «Цены и сроки», «Сравнение», «Нормативы», «Обслуживание», «Монтаж».
- blog-post.php уже выводит «По типу оборудования» (5 хабов) внизу статьи — перелинковка присутствует; related-списки в статьях используются через $article['related'].
- Sitemap: /sitemap.xml генерируется, /blog/<slug> для каждой статьи — новая статья автоматически попадёт в sitemap.
- RSS блога: автоматически включает все статьи.
- find-skills CLI (npx skills find): топовые скиллы для этой задачи — anthropics/skills@frontend-design (1.1M), blink-new/claude@seo-article-writing, sickn33/agentic-awesome-skills@seo-aeo-blog-writer (SEO/AEO), affaan-m/ecc@article-writing (14K). Локальные скиллы (frontend-design, qiaomu-seo, brainstorming, planning-with-files, verification-before-completion) — загружены и применены; устанавливать внешние не стал (работа сделана на локальных).

## Technical decisions
| Decision | Rationale |
|----------|-----------|
| Функция `blog_top_articles()` в lib/blog.php (флаг 'top' в статьях) | Одно место определения «Топ»; 5 статей: modernizaciya-gruzovogo-podemnika (новая), shaftnyj-vs-konsolnyj, stoimost-gruzovogo-podemnika, tr-ts-011-2011, kottedzhnyj-lift-dom |
| «ТОП СТАТЕЙ» на главной (секция / 07) и «ТОП СТАТЬИ» на /blog (верх страницы) | Сайт «топами» — ТОП СТАТЕЙ видны и на главной, и в блоге |
| Дизайн: warm-dark editorial (radial gradient света + тёплый акцент #c98e26, заголовки PT Sans 700 без заглавных, карточки «журнальные заметки») | Отличается от шаблонов dark-industrial; акцент — не бренд-красный, чтобы не перегружать |
| New article date = '2026-11-20', readMin = 10 | Сегодня, честно; текст статьи ~1150 слов → 10 мин |
| FAQ в новой статье: SEO-формулировки + честные ограничения (без выдуманных цифр) | Интент SEO + E-E-A-T + честность контента |
| CSS для топ-секций — отдельный файл assets/top-articles.css, подключён в render.php page_start | Чистота, изменения не в файле 3000+ строк style.css |

## Verification (2026-11-20)
- php -l: без ошибок: modernizaciya-gruzovogo-podemnika.php, tr-ts-011-2011.php, stoimost-gruzovogo-podemnika.php, shaftnyj-vs-konsolnyj.php, kottedzhnyj-lift-dom.php, tehnicheskoe-obsluzhivanie-podemnika.php, montazh-v-dejstvuyushchee-zdanie.php, lib/blog.php, lib/render.php, pages/home.php, pages/blog-index.php.
- FFBD-скан: 0 плохих символов во всех затронутых файлах (случайные «?» в текстах — исправлены байт-операциями).
- php -S на 127.0.0.1:8090: страницы отдаются: / (200), /blog (200), /blog/modernizaciya-gruzovogo-podemnika (200), /sitemap.xml (41201 байт, содержит новую статью), /blog/rss.xml (10937 байт, содержит новую статью).
- Browser snapshots: / — «ТОП СТАТЕЙ» + 5 карточек (Модернизация, Шахтный или консольный, Сколько стоит грузовой, ТР ТС 011, Коттеджный лифт); /blog — «ТОП СТАТЬИ» + Топ-бейджи на карточках; article page — заголовок, FAQ, «По типу оборудования», related-списки.
- Console: 0 сообщений на /, /blog, article page.
- screenshots: браузер не выдаёт скриншоты («needs a visible tab» — нет видимого окна); верификация через HTML-фетч + snapshots.

## Issues encountered
| Issue | Resolution |
|-------|------------|
| В тексте новой статьи «Кто выполняет модернизацію» был некорректный байт (U+FFBD) | Найден байт-сканнером (php, bin2hex), почищен через str_replace |
| Edit-инструмент не смог сопоставить строку с кириллицей (отличаются байты) | Правки через PHP-скрипты (php -l/вывод строки) |

## Visual/Browser Findings
| Page | Finding |
|---|---|
| / | Есть секция «ТОП СТАТЕЙ» (после hero): заголовок «ТОП СТАТЕЙ» (PT Sans 700, не заглавный), 5 карточек с номерами 01–05, категории (Гайды, Сравнение...), заголовки без заглавных (Модернизация грузового подъёмника, Шахтный или консольный: что выбрать...), lead-текст и мета (дата + мин чтения), акцент-цвет #c98e26 (светло-золотой), ссылка «Полный список статей блога →» |
| /blog | Секция «ТОП СТАТЬИ» вверху + бейдж «Топ» на карточках блога |
| /blog/modernizaciya-gruzovogo-podemnika | Заголовок «Модернизация грузового подъёмника», 5 SEO-вопросов (FAQ, FAQPage JSON-LD), тело статьи, «По типу оборудования» (5 хабов), related «Читайте также», секция «Нужно решение под ваш объект?» с кнопкой «Получить расчёт» |
| /sitemap.xml | Новая статья /blog/modernizaciya-gruzovogo-podemnika — true, tr-ts-011-2011 — true |
| /blog/rss.xml | Новая статья — true |

*Update after completing each phase or encountering errors*

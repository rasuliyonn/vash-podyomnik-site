# Task Plan: Сайт «Ваш Подъёмник» — ТОП СТАТЕЙ, SEO и дизайн

## Goal
На сайте «Ваш Подъёмник» сделать статьи в виде ТОП СТАТЕЙ (флаг «top» на 5 статьях), добавить SEO-блоки с вопросами-ответами (SEO FAQ) на статьях, провести правки под SEO по SEO-аудиту (новая статья «Модернизация грузового подъёмника», перелинковка) и обновить дизайн нового блока «ТОП СТАТЕЙ», чтобы он не выглядел как подборка ИИ-шаблонов (оригинальная типографика, отличный от шаблонного layout).

## Next Step
Delivery — итоговая сводка (это сообщение).

## Current Phase
Phase 5 (delivery)

## Phases

### Phase 1: SEO-контент и перелинковка — complete
- [x] Изучение сайта и SEO-аудит
- [x] Написать lib/articles/modernizaciya-gruzovogo-podemnika.php (текст, FAQ, related, top)
- [x] Выставить 'top' => true в 5 статьях: modernizaciya-gruzovogo-podemnika, shaftnyj-vs-konsolnyj, stoimost-gruzovogo-podemnika, tr-ts-011-2011, kottedzhnyj-lift-dom
- [x] Добавить SEO-FAQ: tr-ts-011-2011 (+1), stoimost-gruzovogo-podemnika (+2), shaftnyj-vs-konsolnyj (+1), kottedzhnyj-lift-dom (+2)
- [x] Перелинковка: related новых статей в tehnicheskoe-obsluzhivanie-podemnika, montazh-v-dejstvuyushchee-zdanie

### Phase 2: Функционал «ТОП СТАТЕЙ» — complete
- [x] lib/blog.php: blog_top_articles(), article_is_top()
- [x] pages/home.php: секция «ТОП СТАТЕЙ» после hero + require lib/blog.php
- [x] pages/blog-index.php: секция «ТОП СТАТЬИ», сортировка ItemList, бейдж «Топ»

### Phase 3: Дизайн (не-AI) — complete
- [x] assets/top-articles.css: --gold + стили .top-articles* + адаптив + бейдж «Топ» в блоге
- [x] lib/render.php: ссылка на top-articles.css

### Phase 4: Проверка — complete
- [x] php -l все изменённые файлы (нет ошибок)
- [x] php -S локально; browser: /, /blog, /blog/modernizaciya-gruzovogo-podemnika, /sitemap.xml, /blog/rss.xml — скриншоты: браузер не отдаёт (нет видимого окна) — верификация через snapshot + HTML-фетч + console

### Phase 5: Доставка — complete
- [x] Итоговая сводка для пользователя (это сообщение)

## Errors encountered
| Error | Attempt | Resolution |
|-------|---------|------------|
| (none so far) | — | — |

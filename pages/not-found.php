<?php
/**
 * 404 — страница не найдена.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';

http_response_code(404);

page_start(
    'Страница не найдена',
    'Запрошенная страница не найдена. Воспользуйтесь каталогом оборудования, блогом или вопросами и ответами.',
    '/404',
    ['noindex' => true]
);
?>
<main class="not-found" id="top">

  <h1>404</h1>
  <p class="blog-lead" style="margin: 0 auto 26px">Такой страницы не существует. Возможно, ссылка устарела — вот полезные разделы:</p>
  <p>
    <a class="text-link" href="/">Главная<span aria-hidden="true"> →</span></a><br><br>
    <a class="text-link" href="/oborudovanie">Каталог оборудования<span aria-hidden="true"> →</span></a><br><br>
    <a class="text-link" href="/voprosy">Вопросы и ответы<span aria-hidden="true"> →</span></a><br><br>
    <a class="text-link" href="/blog">Блог<span aria-hidden="true"> →</span></a>
  </p>
</main>
<?php page_end(); ?>

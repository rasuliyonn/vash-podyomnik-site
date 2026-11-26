<?php
/**
 * Коммерческие лендинги: /gruzovye-podemniki-po-proektu, /montazh-gruzovogo-podemnika,
 * /rostov-na-donu. Тексты — в lib/landings.php, вёрстка — здесь.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/landings.php';

$slug = (string) ($_GET['landing'] ?? '');
$page = landing_get($slug);
if ($page === null) {
    require __DIR__ . '/not-found.php';
    return;
}

$crumbs = array_merge([['name' => 'Главная', 'path' => '/']], $page['crumbs']);
$area = $page['service']['area'] ?? null;
$jsonld = [
    breadcrumb_jsonld($crumbs),
    service_jsonld($page['service']['name'], $page['service']['description'], $page['path'], $area),
];
if (!empty($page['faq'])) {
    $jsonld[] = faq_jsonld($page['faq']);
}

page_start($page['title'], $page['description'], $page['path'], ['jsonld' => $jsonld]);

/** Блоки контента: абзацы, списки, подзаголовки, таблицы. */
function landing_render_blocks(array $blocks): void
{
    foreach ($blocks as $block) {
        if (isset($block['p'])) {
            echo '<p>' . landing_inline((string) $block['p']) . '</p>';
        } elseif (isset($block['h3'])) {
            echo '<h3>' . e((string) $block['h3']) . '</h3>';
        } elseif (isset($block['ul'])) {
            echo '<ul>';
            foreach ($block['ul'] as $item) {
                echo '<li>' . landing_inline((string) $item) . '</li>';
            }
            echo '</ul>';
        } elseif (isset($block['ol'])) {
            echo '<ol>';
            foreach ($block['ol'] as $item) {
                echo '<li>' . landing_inline((string) $item) . '</li>';
            }
            echo '</ol>';
        } elseif (isset($block['table'])) {
            echo '<div class="table-scroll"><table class="compare-table">';
            echo '<thead><tr>';
            foreach ($block['table']['head'] as $th) {
                echo '<th>' . e((string) $th) . '</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ($block['table']['rows'] as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . landing_inline((string) $cell) . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table></div>';
        }
    }
}
?>
<main class="blog-article" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <?php foreach ($crumbs as $i => $crumb): ?>
    <?php if ($i > 0): ?><span aria-hidden="true">›</span><?php endif; ?>
    <?php if ($i === count($crumbs) - 1): ?>
      <span aria-current="page"><?= e($crumb['name']) ?></span>
    <?php else: ?>
      <a href="<?= e($crumb['path']) ?>"><?= e($crumb['name']) ?></a>
    <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <h1><?= e($page['h1']) ?></h1>
  <p class="blog-lead"><?= e($page['lead']) ?></p>

  <div class="section-more-links">
    <a class="primary-button" href="/raschet">Рассчитать стоимость<span aria-hidden="true">→</span></a>
    <a class="text-link" href="/nashi-raboty">Выполненные объекты<span aria-hidden="true">↗</span></a>
  </div>

  <div class="prose-note landing-tldr">
    <strong>Коротко</strong>
    <ul>
      <?php foreach ($page['tldr'] as $item): ?>
      <li><?= e($item) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <article class="prose" style="margin-top: 34px">
    <?php foreach ($page['sections'] as $section): ?>
    <h2><?= e($section['h2']) ?></h2>
    <?php landing_render_blocks($section['blocks']); ?>
    <?php endforeach; ?>
  </article>

  <div class="article-faq">
    <h2>Частые вопросы</h2>
    <?php foreach ($page['faq'] as $item): ?>
    <h3><?= e($item['q']) ?></h3>
    <p><?= e($item['a']) ?></p>
    <?php endforeach; ?>
  </div>

  <div class="related-articles">
    <h2>Смотрите также</h2>
    <ul>
      <?php foreach ($page['links'] as $link): ?>
      <li><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="article-faq" style="margin-bottom: 0">
    <h2>Нужен расчёт для вашего объекта?</h2>
    <p style="margin: 0 0 12px">Инженер бесплатно изучит задачу и подготовит расчёт.</p>
    <a class="primary-button" href="/raschet">Получить расчёт<span aria-hidden="true"> →</span></a>
  </div>
</main>
<?php page_end(); ?>
<?php
/**
 * Каталог оборудования: /oborudovanie
 * 5 типов, сравнение, как выбрать, ссылки на карточки.
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/render.php';
require_once __DIR__ . '/../lib/content.php';
require_once __DIR__ . '/../lib/equipment.php';

$c = site_content();
$items = equipment_all();
$eq_urls = [];
foreach ($items as $slug => $item) {
    $eq_urls[] = '/equipment/' . $slug;
}

$jsonld = [
    breadcrumb_jsonld([
        ['name' => 'Главная', 'path' => '/'],
        ['name' => 'Оборудование', 'path' => '/oborudovanie'],
    ]),
    itemlist_jsonld('Оборудование производства «Ваш Подъёмник»', $eq_urls),
    service_jsonld('Проектирование, производство и монтаж грузовых подъёмников', 'Полный цикл: инженерный расчёт, проектирование, производство в Ростове-на-Дону, доставка и монтаж по России.', '/oborudovanie'),
];

page_start(
    'Каталог грузовых подъёмников: шахтные, консольные, сервисные',
    'Каталог оборудования «Ваш Подъёмник»: шахтные и консольные подъёмники, сервисные малые грузовые лифты, коттеджные лифты и грузовые платформы. Сравнение типов, характеристики, производство в Ростове-на-Дону, монтаж по России.',
    '/oborudovanie',
    ['jsonld' => $jsonld]
);
?>
<main class="catalog-page" id="top">
  <nav class="breadcrumb" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">›</span><span aria-current="page">Оборудование</span>
  </nav>

  <h1>Каталог <em>оборудования</em></h1>
  <p class="blog-lead">Грузовой подъёмник — подъёмное оборудование для вертикального перемещения грузов между этажами; подбирается под параметры конкретного объекта. Не продаём «типовую коробку»: ниже — все типы оборудования, характеристики и сравнение. Инженер бесплатно изучит задачу и подготовит расчёт.</p>

  <div class="equipment-grid" style="margin-top: 40px">
    <?php foreach ($c['equipment_cards'] as $card): ?>
    <a class="equipment-card" href="/equipment/<?= e($card['slug']) ?>">
      <div class="card-top">
        <span><?= e($card['number']) ?></span>
        <span aria-hidden="true">↗</span>
      </div>
      <div class="lift-symbol symbol-<?= e($card['number']) ?>" aria-hidden="true">
        <i></i><i></i><i></i>
      </div>
      <h2 style="font-size: 22px; margin: 0 0 10px"><?= e($card['title']) ?></h2>
      <p><?= e($card['text']) ?></p>
      <div class="tags">
        <?php foreach ($card['tags'] as $tag): ?><span><?= e($tag) ?></span><?php endforeach; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <h2 class="page-title" style="font-size: 30px; margin-top: 64px">Сравнение типов</h2>
  <div class="compare-wrap">
    <table class="compare-table">
      <thead>
        <tr>
          <th>Тип</th>
          <th>Грузоподъёмность</th>
          <th>Высота подъёма</th>
          <th>Монтаж</th>
          <th>Кому подходит</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-label="Тип"><a href="/equipment/shaftnye">Шахтные подъёмники</a></td>
          <td data-label="Грузоподъёмность">100 – 5 000 кг</td>
          <td data-label="Высота подъёма">до 50 м</td>
          <td data-label="Монтаж">В закрытой металлической шахте, внутри или снаружи здания</td>
          <td data-label="Кому подходит">Склады, производства, торговые центры, интенсивная эксплуатация</td>
        </tr>
        <tr>
          <td data-label="Тип"><a href="/equipment/konsolnye">Консольные подъёмники</a></td>
          <td data-label="Грузоподъёмность">100 – 2 000 кг</td>
          <td data-label="Высота подъёма">до 15 м</td>
          <td data-label="Монтаж">У проёма перекрытия, без капитальной шахты</td>
          <td data-label="Кому подходит">Действующие здания, рестораны, магазины, антресоли</td>
        </tr>
        <tr>
          <td data-label="Тип"><a href="/equipment/servisnye">Сервисные подъёмники</a></td>
          <td data-label="Грузоподъёмность">20 – 300 кг</td>
          <td data-label="Высота подъёма">до 20 м</td>
          <td data-label="Монтаж">В стену или стойку, встраивание в стандартный проём</td>
          <td data-label="Кому подходит">Рестораны, кафе, гостиницы, медучреждения, офисы</td>
        </tr>
        <tr>
          <td data-label="Тип"><a href="/equipment/kottedzhnye">Коттеджные лифты</a></td>
          <td data-label="Грузоподъёмность">100 – 500 кг</td>
          <td data-label="Высота подъёма">до 20 м</td>
          <td data-label="Монтаж">Лестничный пролёт, выделенное пространство или снаружи</td>
          <td data-label="Кому подходит">Частные дома, дачи, таунхаусы, малоэтажные здания</td>
        </tr>
        <tr>
          <td data-label="Тип"><a href="/equipment/platformy">Грузовые платформы</a></td>
          <td data-label="Грузоподъёмность">500 – 10 000 кг</td>
          <td data-label="Высота подъёма">до 20 м</td>
          <td data-label="Монтаж">Открытая платформа по ТЗ заказчика</td>
          <td data-label="Кому подходит">Автосервисы, производства, склады, стройобъекты, АПК</td>
        </tr>
      </tbody>
    </table>
  </div>

  <h2 class="page-title" style="font-size: 30px; margin-top: 64px">Не уверены, что выбрать?</h2>
  <p class="blog-lead" style="font-size: 16px">Инженер сравнит решения на вашем объекте — по планировке, грузу, потокам и бюджету. Полезные статьи: <a href="/blog/shaftnyj-vs-konsolnyj">«Шахтный или консольный»</a>, <a href="/blog/kak-vybrat-gruzovoj-podemnik">«Как выбрать грузовой подъёмник»</a>, <a href="/blog/podemnik-ili-gruzovoj-lift">«Подъёмник или грузовой лифт»</a>. Ответы на типовые вопросы — на странице <a href="/voprosy">«Вопросы и ответы»</a>.</p>

  <div class="section-more" style="margin-top: 30px">
    <a class="primary-button" href="/raschet">Получить расчёт<span aria-hidden="true"> →</span></a>
  </div>
</main>
<?php page_end(); ?>

<?php
/**
 * RSS-лента статей блога: /blog/rss.xml
 */

declare(strict_types=1);

require_once __DIR__ . '/../lib/blog.php';
require_once __DIR__ . '/../lib/seo.php';

$articles = array_slice(blog_articles(), 0, 30);

header('Content-Type: application/rss+xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
echo '<channel>' . "\n";
echo '<title>Блог «Ваш Подъёмник»</title>' . "\n";
echo '<link>' . abs_url('/blog') . '</link>' . "\n";
echo '<description>Гайды, сравнения и нормативы о грузовых подъёмниках: выбор, цена, сроки, монтаж, обслуживание.</description>' . "\n";
echo '<language>ru</language>' . "\n";
echo '<atom:link href="' . abs_url('/blog/rss.xml') . '" rel="self" type="application/rss+xml"/>' . "\n";
foreach ($articles as $article) {
    $link = abs_url('/blog/' . $article['slug']);
    $pubDate = gmdate('D, d M Y H:i:s', (int) strtotime($article['date'] . ' 12:00:00')) . ' GMT';
    echo '<item>' . "\n";
    echo '<title>' . htmlspecialchars($article['title'], ENT_QUOTES | ENT_XML1, 'UTF-8') . '</title>' . "\n";
    echo '<link>' . $link . '</link>' . "\n";
    echo '<guid isPermaLink="true">' . $link . '</guid>' . "\n";
    echo '<pubDate>' . $pubDate . '</pubDate>' . "\n";
    echo '<description>' . htmlspecialchars($article['description'], ENT_QUOTES | ENT_XML1, 'UTF-8') . '</description>' . "\n";
    echo '</item>' . "\n";
}
echo '</channel>' . "\n";
echo '</rss>' . "\n";

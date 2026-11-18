<?php
/**
 * Загрузка статей блога из lib/articles/*.php (каждая статья — файл, возвращающий массив).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function blog_articles(): array
{
    static $articles = null;
    if ($articles !== null) {
        return $articles;
    }

    $articles = [];
    foreach ((array) glob(ARTICLES_DIR . '/*.php') as $file) {
        $article = require $file;
        if (is_array($article) && !empty($article['slug'])) {
            $articles[$article['slug']] = $article;
        }
    }

    uasort($articles, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

    return $articles;
}

function blog_find(string $slug): ?array
{
    $articles = blog_articles();
    return $articles[$slug] ?? null;
}

function blog_categories(): array
{
    $cats = [];
    foreach (blog_articles() as $article) {
        $cat = $article['category'] ?? 'Гайды';
        $cats[$cat] = ($cats[$cat] ?? 0) + 1;
    }
    ksort($cats);
    return $cats;
}

function blog_article_count(): int
{
    return count(blog_articles());
}

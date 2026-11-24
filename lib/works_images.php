<?php
/**
 * <picture> для фото работ: WebP + srcset с превью -480 и мастером,
 * width/height из lib/works_image_meta.php (генерируется scripts/works-optimize.py).
 * Нужен против CLS и для отдачи лёгких изображений на /nashi-raboty и главной.
 */

declare(strict_types=1);

require_once __DIR__ . '/seo.php';

/** @return array<string, array{0:int,1:int}> Карта '/works/work-N.jpg' => [width, height] */
function works_image_meta_map(): array
{
    static $map = null;
    if ($map === null) {
        $file = __DIR__ . '/works_image_meta.php';
        $map = is_file($file) ? (array) require $file : [];
    }
    return $map;
}

/**
 * Рендерим <picture> для фото работы.
 *
 * @param string $url    Путь к мастеру (/works/work-N.jpg)
 * @param string $alt    Альт-текст
 * @param string $sizes  Атрибут sizes (зависит от контекста: сетка или горизонтальная лента)
 * @param bool   $eager  true для первых изображений (LCP): loading=eager + fetchpriority=high
 */
function works_picture(string $url, string $alt, string $sizes, bool $eager = false): string
{
    $meta = works_image_meta_map();
    [$w, $h] = $meta[$url] ?? [0, 0];

    $info = pathinfo($url);
    $dir = $info['dirname'] === '.' ? '' : rtrim((string) $info['dirname'], '/');
    $base = (string) $info['filename'];
    $ext = (string) $info['extension'];
    $thumb = $dir . '/' . $base . '-480.' . $ext;
    $webp = $dir . '/' . $base . '.webp';
    $webpThumb = $dir . '/' . $base . '-480.webp';

    $loading = $eager ? 'eager' : 'lazy';
    $fetch = $eager ? ' fetchpriority="high"' : '';
    $dims = ($w > 0 && $h > 0) ? ' width="' . $w . '" height="' . $h . '"' : '';

    return '<picture>'
        . '<source type="image/webp" srcset="' . e($webpThumb) . ' 480w, ' . e($webp) . ' 1920w" sizes="' . e($sizes) . '">'
        . '<img src="' . e($url) . '" srcset="' . e($thumb) . ' 480w, ' . e($url) . ' 1920w" sizes="' . e($sizes) . '"' . $dims
        . ' alt="' . e($alt) . '" loading="' . $loading . '" decoding="async"' . $fetch . ' draggable="false">'
        . '</picture>';
}

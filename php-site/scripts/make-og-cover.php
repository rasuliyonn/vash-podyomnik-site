<?php
/**
 * Разовая генерация OG-обложки 1200×630 в стиле бренда (запускается один раз:
 * php scripts/make-og-cover.php). Результат — assets/og-cover.png.
 * Нужен PHP с GD и TrueType (локально уже есть).
 */

declare(strict_types=1);

$W = 1200;
$H = 630;
$img = imagecreatetruecolor($W, $H);
imagesavealpha($img, true);

$black = imagecolorallocate($img, 7, 9, 9);
$panel = imagecolorallocate($img, 17, 19, 19);
$red = imagecolorallocate($img, 211, 25, 29);
$redBright = imagecolorallocate($img, 239, 36, 39);
$white = imagecolorallocate($img, 244, 244, 242);
$soft = imagecolorallocate($img, 170, 174, 173);
$line = imagecolorallocatealpha($img, 255, 255, 255, 90);

imagefill($img, 0, 0, $black);

// Правая зона — стилизованный «мачт» шахтного подъёмника
$mastX = 905;
$mastW = 140;
imagefilledrectangle($img, $mastX, 40, $mastX + $mastW, $H - 40, $panel);
imagefilledrectangle($img, $mastX, 40, $mastX + 8, $H - 40, $red);
imagefilledrectangle($img, $mastX + $mastW - 8, 40, $mastX + $mastW, $H - 40, $red);

// Уровни этажа
foreach ([140, 265, 390] as $level) {
    imagefilledrectangle($img, $mastX, $level, $mastX + $mastW, $level + 3, $line);
}

// Платформа + кабинка
imagefilledrectangle($img, $mastX + 22, 455, $mastX + $mastW - 22, 505, $red);
imagefilledrectangle($img, $mastX + 30, 463, $mastX + $mastW - 30, 497, $redBright);

// Трос и блок сверху
imagefilledrectangle($img, $mastX + (int) ($mastW / 2) - 2, 40, $mastX + (int) ($mastW / 2) + 2, 150, $soft);
imagefilledellipse($img, $mastX + (int) ($mastW / 2), 122, 46, 46, $soft);
imagefilledellipse($img, $mastX + (int) ($mastW / 2), 122, 20, 20, $black);

// Левая красная вертикальная полоса
imagefilledrectangle($img, 0, 0, 22, $H, $red);

$fontDisplay = 'C:/Windows/Fonts/impact.ttf';
$fontBody = 'C:/Windows/Fonts/arialbd.ttf';
$fontRegular = 'C:/Windows/Fonts/arial.ttf';

// Бренд
imagettftext($img, 74, 0, 80, 200, $white, $fontDisplay, 'ВАШ ПОДЪЁМНИК');

// Красная черта
imagefilledrectangle($img, 80, 232, 620, 242, $red);

// Подзаголовок
imagettftext($img, 30, 0, 80, 310, $white, $fontBody, 'ГРУЗОВЫЕ ПОДЪЁМНИКИ');
imagettftext($img, 30, 0, 80, 352, $white, $fontBody, 'И ЛИФТЫ');

// Теглайн
imagettftext($img, 21, 0, 80, 415, $soft, $fontRegular, 'Проектирование · производство · монтаж');
imagettftext($img, 21, 0, 80, 448, $soft, $fontRegular, 'Ростов-на-Дону · вся Россия');

// Сайт внизу
imagettftext($img, 24, 0, 80, 560, $redBright, $fontBody, 'vash-podyomnik.ru');

imagepng($img, __DIR__ . '/../assets/og-cover.png');
imagedestroy($img);
echo "og-cover.png создан\n";

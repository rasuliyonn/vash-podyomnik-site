#!/usr/bin/env python3
"""
Оптимизация фото работ для веба (seo-images: file optimization).

Для каждого works/work-*.jpg:
  1. Применяет EXIF-ориентацию (ImageOps.exif_transpose).
  2. Уменьшает до MAX_SIDE по большей стороне (по умолчанию 1920px).
  3. Перезаписывает JPEG: progressive, quality 78, без EXIF (ориентация уже применена).
  4. Рядом создаёт .webp (quality 78) для <picture>.
  5. Создаёт превью work-N-480.jpg/.webp (max 480px) для srcset в сетке.
  6. Пишет lib/works_image_meta.php с размерами (width/height против CLS).

Использование:
  python scripts/works-optimize.py            # применить оптимизацию
  python scripts/works-optimize.py --dry-run  # только показать статистику
  python scripts/works-optimize.py --thumbs-only  # только превью и meta, без перезаписи мастеров

Требует Pillow (python -m pip install Pillow).
"""

from __future__ import annotations

import argparse
import os
import sys

from PIL import Image, ImageOps

WORKS_DIR = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'works')
META_FILE = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'lib', 'works_image_meta.php')
MAX_SIDE = 1920
JPEG_QUALITY = 78
WEBP_QUALITY = 78
THUMB_SIDE = 480
THUMB_JPEG_QUALITY = 74
THUMB_WEBP_QUALITY = 74
META_HEADER = """<?php
/**
 * Размеры фото работ (width/height в пикселях). Генерируется scripts/works-optimize.py.
 * Нужен для <img width height> (CLS) и sitemap изображений. Не редактировать вручную.
 */

declare(strict_types=1);

return """


def optimize(path: str, dry_run: bool, thumbs_only: bool) -> tuple[int, int, tuple[int, int]]:
    before = os.path.getsize(path)
    with Image.open(path) as im:
        im = ImageOps.exif_transpose(im)
        if thumbs_only:
            new_size = im.size
        else:
            if max(im.size) > MAX_SIDE:
                ratio = MAX_SIDE / max(im.size)
                im = im.resize((round(im.width * ratio), round(im.height * ratio)), Image.LANCZOS)
            new_size = im.size
            if not dry_run:
                rgb = im.convert('RGB')
                rgb.save(path, 'JPEG', quality=JPEG_QUALITY, optimize=True, progressive=True)
                rgb.save(path[:-4] + '.webp', 'WEBP', quality=WEBP_QUALITY, method=6)
        thumb = im.copy()
        if max(thumb.size) > THUMB_SIDE:
            ratio = THUMB_SIDE / max(thumb.size)
            thumb = thumb.resize((round(thumb.width * ratio), round(thumb.height * ratio)), Image.LANCZOS)
        if not dry_run:
            thumb_rgb = thumb.convert('RGB')
            thumb_rgb.save(path[:-4] + '-480.jpg', 'JPEG', quality=THUMB_JPEG_QUALITY, optimize=True, progressive=True)
            thumb_rgb.save(path[:-4] + '-480.webp', 'WEBP', quality=THUMB_WEBP_QUALITY, method=6)
    after = os.path.getsize(path) if not dry_run else before
    return before, after, new_size


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument('--dry-run', action='store_true')
    parser.add_argument('--thumbs-only', action='store_true',
                        help='не перезаписывать мастер-JPEG, только сгенерировать -480 превью и meta')
    args = parser.parse_args()

    total_before = total_after = 0
    meta: dict[str, tuple[int, int]] = {}
    jpgs = sorted(f for f in os.listdir(WORKS_DIR) if f.lower().endswith('.jpg') and not f.endswith('-480.jpg'))
    for name in jpgs:
        before, after, size = optimize(os.path.join(WORKS_DIR, name), args.dry_run, args.thumbs_only)
        total_before += before
        total_after += after
        meta['/works/' + name] = [size[0], size[1]]
        if not args.dry_run:
            webp = os.path.getsize(os.path.join(WORKS_DIR, name[:-4] + '.webp'))

    print(f'files={len(jpgs)} before={total_before / 1e6:.1f}MB after={total_after / 1e6:.1f}MB')
    if args.dry_run:
        return 0
    with open(META_FILE, 'w', encoding='utf-8', newline='\n') as fh:
        fh.write(META_HEADER)
        fh.write('[\n')
        for src, (w, h) in sorted(meta.items()):
            fh.write(f"    '{src}' => [{w}, {h}],\n")
        fh.write("];\n")
    print('meta written:', META_FILE)
    return 0


if __name__ == '__main__':
    sys.exit(main())

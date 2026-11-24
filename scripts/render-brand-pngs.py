"""Отрисовка брендовых PNG из новой векторной геометрии логотипа."""
from PIL import Image, ImageDraw, ImageFont

BLACK = "#070909"
WHITE = "#F4F4F2"
RED = "#D3191D"
RED_BRIGHT = "#EF2427"
SOFT = "#AAAEAD"
MUTED = "#737978"
GHOST = "#2A2F2E"

NARROW_700 = "scripts/fonts-ttf/pt-sans-narrow-700.ttf"
SANS_700 = "scripts/fonts-ttf/pt-sans-700.ttf"
MONO = "scripts/fonts-ttf/pt-mono-400.ttf"


def draw_glyph(d, ox, oy, s, rail=WHITE, arrow=WHITE, platform=RED, ties=None):
    """Знак: две направляющие мачты, стрела вверх, грузовая платформа."""
    def R(x, y, w, h, fill):
        d.rectangle([ox + x * s, oy + y * s, ox + (x + w) * s, oy + (y + h) * s], fill=fill)
    R(11, 12, 6, 32, rail)
    R(47, 12, 6, 32, rail)
    if ties:
        R(11, 22, 42, 2.4, ties)
        R(11, 34, 42, 2.4, ties)
    # Стрела: apex (32,3) — основание головы y=20.5, шток до y=44
    pts = [(32, 3), (19.5, 20.5), (27.5, 20.5), (27.5, 44), (36.5, 44), (36.5, 20.5), (44.5, 20.5)]
    d.polygon([(ox + px * s, oy + py * s) for px, py in pts], fill=arrow)
    R(6, 49, 52, 9, platform)


def draw_tracked(d, x, y, text, font, fill, tracking=0):
    cx = x
    for ch in text:
        d.text((cx, y), ch, font=font, fill=fill)
        cx += d.textlength(ch, font=font) + tracking
    return cx


def tracked_width(d, text, font, tracking=0):
    return sum(d.textlength(ch, font=font) for ch in text) + tracking * (len(text) - 1)


# ---------- apple-touch-icon.png 180x180 ----------
S = 180
img = Image.new("RGB", (S, S), BLACK)
d = ImageDraw.Draw(img)
d.rounded_rectangle([0, 0, S - 1, S - 1], radius=38, fill=BLACK)
draw_glyph(d, 0, 0, S / 64)
img.save("assets/apple-touch-icon.png", "PNG")
print("apple-touch-icon.png", img.size)

# ---------- og-cover.png 1200x630 ----------
W, H = 1200, 630
img = Image.new("RGB", (W, H), BLACK)
d = ImageDraw.Draw(img)

# Левая красная полоса
d.rectangle([0, 0, 14, H], fill=RED)

# Правая часть: крупный знак со связями мачты
draw_glyph(d, 878, 92, 4.9, rail=WHITE, arrow=WHITE, platform=RED, ties=GHOST)

mono_tag = ImageFont.truetype(MONO, 21)
narrow_hero = ImageFont.truetype(NARROW_700, 116)
narrow_sub = ImageFont.truetype(NARROW_700, 47)
mono_small = ImageFont.truetype(MONO, 20)
mono_site = ImageFont.truetype(MONO, 27)

draw_tracked(d, 72, 74, "ПРОИЗВОДСТВО ГРУЗОВЫХ ПОДЪЁМНИКОВ", mono_tag, RED_BRIGHT, tracking=3.4)
d.text((66, 112), "ВАШ ПОДЪЁМНИК", font=narrow_hero, fill=WHITE)
d.rectangle([72, 258, 332, 268], fill=RED)
d.text((70, 292), "Грузовые подъёмники и лифты", font=narrow_sub, fill=WHITE)
d.text((72, 368), "Проектирование · производство · монтаж", font=mono_small, fill=SOFT)
d.text((72, 400), "Ростов-на-Дону · вся Россия", font=mono_small, fill=MUTED)
d.text((72, 520), "vashpodemnik61.ru", font=mono_site, fill=RED_BRIGHT)

img.save("assets/og-cover.png", "PNG")
print("og-cover.png", img.size)

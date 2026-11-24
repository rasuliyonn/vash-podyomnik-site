from PIL import Image, ImageEnhance

def grade(img):
    img = ImageEnhance.Contrast(img).enhance(1.10)
    img = ImageEnhance.Color(img).enhance(0.90)
    img = ImageEnhance.Brightness(img).enhance(0.96)
    img = ImageEnhance.Sharpness(img).enhance(1.15)
    return img

# Hero: реальный консольный подъёмник на фасаде (works/work-65.jpg)
hero = Image.open('works/work-65.jpg')
print('hero src', hero.size)
crop = hero.crop((0, 330, hero.width, 1180))  # дверца + платформа + мачта
crop = grade(crop)
crop.save('assets/hero-real.jpg', 'JPEG', quality=84, optimize=True, progressive=True)
print('hero-real', crop.size)

# Секция «объект»: реальный шахтный подъёмник в облицовке здания (works/work-18.jpg)
obj = Image.open('works/work-18.jpg')
print('obj src', obj.size)
crop2 = obj.crop((0, 120, obj.width, 1500))
crop2 = grade(crop2)
crop2.save('assets/object-real.jpg', 'JPEG', quality=84, optimize=True, progressive=True)
print('object-real', crop2.size)

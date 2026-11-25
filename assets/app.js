/* Ваш Подъёмник — клиентская логика (без зависимостей):
   1) Калькулятор заявки: сбор текста, копирование в буфер, отправка в /submit
   2) Галерея «Наши работы»: drag-scroll, стрелки, лайтбокс с клавиатуры
   3) FAQ-аккордеон */

'use strict';

/* ───────────────────────── Калькулятор ───────────────────────── */
(function () {
  var form = document.getElementById('calcForm');
  if (!form) return;

  var preview = document.getElementById('requestPreview');
  var button = form.querySelector('[data-button-text]');
  var buttonArrow = form.querySelector('[data-button-arrow]');
  var submitButton = form.querySelector('button[type="submit"]');
  var defaultButtonHTML = button ? button.innerHTML : '';
  var successText = 'Заявка отправлена';

  var status = document.createElement('p');
  status.className = 'form-status';
  status.setAttribute('role', 'status');
  status.setAttribute('aria-live', 'polite');
  form.appendChild(status);

  function setStatus(message, kind) {
    status.textContent = message || '';
    status.className = 'form-status' + (kind ? ' form-status-' + kind : '');
  }

  function val(name) {
    var field = form.elements[name];
    return field ? String(field.value || '').trim() : '';
  }

  function platformSize() {
    var w = val('platformWidth'), d = val('platformDepth');
    var parts = [];
    if (w) parts.push(w + ' мм');
    if (d) parts.push(d + ' мм');
    return parts.join(' × ');
  }

  function buildRequestText() {
    var parts = [val('liftType'), val('capacity')];
    var height = val('height');
    if (height) parts.push('высота подъёма ' + height + ' мм');
    var platform = platformSize();
    if (platform) parts.push('платформа ' + platform);
    parts.push(val('stops'));
    var city = val('city');
    if (city) parts.push('объект: ' + city);
    var contact = [];
    if (val('name')) contact.push(val('name'));
    if (val('phone')) contact.push(val('phone'));
    if (val('email')) contact.push(val('email'));
    var contactPart = contact.length ? ' Контакт: ' + contact.join(', ') + '.' : '';
    return 'Заявка на расчёт: ' + parts.filter(Boolean).join(', ') + '.' + contactPart + ' Нужна консультация инженера и предварительная стоимость.';
  }

  function refreshPreview() {
    if (preview) preview.textContent = buildRequestText();
  }

  form.addEventListener('input', refreshPreview);
  form.addEventListener('change', refreshPreview);
  refreshPreview();

  function copyText(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text).catch(function () {});
    }
    var helper = document.createElement('textarea');
    helper.value = text;
    helper.setAttribute('readonly', '');
    helper.style.position = 'absolute';
    helper.style.left = '-9999px';
    document.body.appendChild(helper);
    helper.select();
    try { document.execCommand('copy'); } catch (e) { /* noop */ }
    document.body.removeChild(helper);
    return Promise.resolve();
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (!form.reportValidity()) {
      setStatus('Проверьте обязательные поля и отметьте согласие на обработку персональных данных.', 'error');
      return;
    }

    var consentField = form.elements['consentData'];
    var newsField = form.elements['consentNews'];
    var text = buildRequestText();

    var payload = {
      name: val('name'),
      phone: val('phone'),
      email: val('email'),
      liftType: val('liftType'),
      capacity: val('capacity'),
      height: val('height'),
      platformSize: platformSize(),
      city: val('city'),
      stops: val('stops'),
      form: 'calculator',
      page: window.location.pathname,
      consentData: !!(consentField && consentField.checked),
      consentNews: !!(newsField && newsField.checked)
    };

    if (submitButton) submitButton.disabled = true;
    setStatus('Отправляем заявку…', '');

    fetch('/submit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function (response) {
      var mail = form.getAttribute('data-contact-email') || 'info@vashpodemnik61.ru';
      return response.json().catch(function () { return { ok: false, error: 'Сервер вернул непонятный ответ. Напишите на ' + mail + '.' }; });
    }).then(function (data) {
      if (!data || !data.ok) {
        throw new Error((data && data.error) || 'Не удалось отправить заявку.');
      }
      setStatus(data.message || 'Заявка отправлена. Инженер свяжется с вами.', 'success');
      if (button) {
        button.innerHTML = successText + '<span aria-hidden="true"> ✓</span>';
      }
      copyText(text);
      form.reset();
      refreshPreview();
      window.setTimeout(function () {
        if (button) button.innerHTML = defaultButtonHTML;
        if (submitButton) submitButton.disabled = false;
      }, 6000);
    }).catch(function (error) {
      setStatus(error && error.message ? error.message : 'Не удалось отправить заявку. Проверьте связь и попробуйте ещё раз.', 'error');
      if (submitButton) submitButton.disabled = false;
      if (button) button.innerHTML = defaultButtonHTML;
    });
  });
})();

/* ───────────────────────── Галерея работ + лайтбокс ───────────────────────── */
(function () {
  var SCROLL_BY = 880;
  var gallery = document.querySelector('[data-gallery]');
  var strip = document.querySelector('[data-gallery-strip]');
  if (!gallery || !strip) return;

  var prev = gallery.querySelector('[data-gallery-prev]');
  var next = gallery.querySelector('[data-gallery-next]');

  function updateArrows() {
    if (!prev || !next) return;
    // Сначала читаем геометрию, потом меняем классы: чтение после записи форсирует компоновку
    var atStart = strip.scrollLeft <= 4;
    var atEnd = strip.scrollLeft + strip.clientWidth >= strip.scrollWidth - 4;
    prev.classList.toggle('works-arrow-hidden', atStart);
    next.classList.toggle('works-arrow-hidden', atEnd);
  }

  if (prev) prev.addEventListener('click', function () { strip.scrollBy({ left: -SCROLL_BY, behavior: 'smooth' }); });
  if (next) next.addEventListener('click', function () { strip.scrollBy({ left: SCROLL_BY, behavior: 'smooth' }); });
  strip.addEventListener('scroll', updateArrows, { passive: true });
  updateArrows();

  /* drag-to-scroll */
  var drag = { active: false, startX: 0, scrollLeft: 0, moved: false };
  strip.addEventListener('mousedown', function (e) {
    drag.active = true;
    drag.startX = e.clientX;
    drag.scrollLeft = strip.scrollLeft;
    drag.moved = false;
    strip.style.cursor = 'grabbing';
  });
  window.addEventListener('mousemove', function (e) {
    if (!drag.active) return;
    var dx = e.clientX - drag.startX;
    if (Math.abs(dx) > 5) {
      drag.moved = true;
      strip.scrollLeft = drag.scrollLeft - dx;
    }
  });
  window.addEventListener('mouseup', function () {
    drag.active = false;
    strip.style.cursor = '';
    window.setTimeout(function () { drag.moved = false; }, 0);
  });

  /* лайтбокс */
  var cards = Array.prototype.slice.call(gallery.querySelectorAll('[data-lightbox-index]'));
  if (!cards.length) return;
  var current = -1;
  var overlay = document.createElement('div');
  overlay.className = 'lightbox-overlay';
  overlay.setAttribute('role', 'dialog');
  overlay.setAttribute('aria-modal', 'true');
  overlay.setAttribute('aria-label', 'Просмотр работы');
  overlay.hidden = true;
  overlay.innerHTML =
    '<button class="lightbox-close" type="button" aria-label="Закрыть">×</button>' +
    '<button class="lightbox-nav prev" type="button" aria-label="Предыдущее">‹</button>' +
    '<div class="lightbox-content"><img alt=""><small></small></div>' +
    '<button class="lightbox-nav next" type="button" aria-label="Следующее">›</button>';
  document.body.appendChild(overlay);

  var img = overlay.querySelector('.lightbox-content img');
  var counter = overlay.querySelector('.lightbox-content small');

  function show(index) {
    current = (index + cards.length) % cards.length;
    var cardImg = cards[current].querySelector('img');
    img.src = cardImg ? cardImg.getAttribute('src') : '';
    img.alt = cardImg ? cardImg.getAttribute('alt') : '';
    counter.textContent = (current + 1) + ' / ' + cards.length;
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  function hide() {
    overlay.hidden = true;
    document.body.style.overflow = '';
  }

  cards.forEach(function (card) {
    card.addEventListener('click', function () {
      if (drag.moved) return;
      show(Number(card.getAttribute('data-lightbox-index')) || 0);
    });
  });

  overlay.addEventListener('click', function (e) { if (e.target === overlay) hide(); });
  overlay.querySelector('.lightbox-close').addEventListener('click', hide);
  overlay.querySelector('.lightbox-content').addEventListener('click', function (e) { e.stopPropagation(); });
  overlay.querySelector('.prev').addEventListener('click', function (e) { e.stopPropagation(); show(current - 1); });
  overlay.querySelector('.next').addEventListener('click', function (e) { e.stopPropagation(); show(current + 1); });

  window.addEventListener('keydown', function (e) {
    if (overlay.hidden) return;
    if (e.key === 'Escape') hide();
    if (e.key === 'ArrowLeft') show(current - 1);
    if (e.key === 'ArrowRight') show(current + 1);
  });
})();

/* ───────────────────────── Сетка работ (/nashi-raboty) ───────────────────────── */
(function () {
  var grid = document.querySelector('[data-works-grid]');
  if (!grid) return;
  var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-lightbox-index]'));
  if (!cards.length) return;

  var current = -1;
  var overlay = document.createElement('div');
  overlay.className = 'lightbox-overlay';
  overlay.setAttribute('role', 'dialog');
  overlay.setAttribute('aria-modal', 'true');
  overlay.setAttribute('aria-label', 'Просмотр работы');
  overlay.hidden = true;
  overlay.innerHTML =
    '<button class="lightbox-close" type="button" aria-label="Закрыть">×</button>' +
    '<button class="lightbox-nav prev" type="button" aria-label="Предыдущее">‹</button>' +
    '<div class="lightbox-content"><img alt=""><small></small></div>' +
    '<button class="lightbox-nav next" type="button" aria-label="Следующее">›</button>';
  document.body.appendChild(overlay);

  var img = overlay.querySelector('.lightbox-content img');
  var counter = overlay.querySelector('.lightbox-content small');

  function show(index) {
    current = (index + cards.length) % cards.length;
    var cardImg = cards[current].querySelector('img');
    img.src = cardImg ? cardImg.getAttribute('src') : '';
    img.alt = cardImg ? cardImg.getAttribute('alt') : '';
    counter.textContent = (current + 1) + ' / ' + cards.length;
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  function hide() {
    overlay.hidden = true;
    document.body.style.overflow = '';
  }

  cards.forEach(function (card) {
    card.addEventListener('click', function () { show(Number(card.getAttribute('data-lightbox-index')) || 0); });
  });
  overlay.addEventListener('click', function (e) { if (e.target === overlay) hide(); });
  overlay.querySelector('.lightbox-close').addEventListener('click', hide);
  overlay.querySelector('.lightbox-content').addEventListener('click', function (e) { e.stopPropagation(); });
  overlay.querySelector('.prev').addEventListener('click', function (e) { e.stopPropagation(); show(current - 1); });
  overlay.querySelector('.next').addEventListener('click', function (e) { e.stopPropagation(); show(current + 1); });
  window.addEventListener('keydown', function (e) {
    if (overlay.hidden) return;
    if (e.key === 'Escape') hide();
    if (e.key === 'ArrowLeft') show(current - 1);
    if (e.key === 'ArrowRight') show(current + 1);
  });
})();

/* ───────────────────────── FAQ-аккордеон ───────────────────────── */
(function () {
  document.querySelectorAll('[data-faq]').forEach(function (list) {
    list.addEventListener('click', function (e) {
      var button = e.target.closest('article > button');
      if (!button) return;
      var article = button.closest('article');
      var isOpen = article.classList.toggle('open');
      button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      var icon = button.querySelector('i');
      if (icon) icon.textContent = isOpen ? '−' : '+';
    });
  });
})();
/* ───────────────────────── Видео: загрузка по клику ───────────────────────── */
(function () {
  document.querySelectorAll('[data-video-load]').forEach(function (button) {
    button.addEventListener('click', function () {
      var src = button.getAttribute('data-video-src');
      if (!src) return;
      var iframe = document.createElement('iframe');
      iframe.src = src;
      iframe.title = button.getAttribute('data-video-title') || 'Видео';
      iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
      iframe.setAttribute('allowfullscreen', '');
      iframe.setAttribute('loading', 'lazy');
      button.replaceWith(iframe);
    });
  });
})();

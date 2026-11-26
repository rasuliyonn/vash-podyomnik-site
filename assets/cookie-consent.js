/* Управление согласием на cookie — assets/cookie-consent.js
 *
 * Необходимые cookie (сохранение выбора) ставятся всегда. Аналитические —
 * только после явного согласия. Выбор хранится в cookie vp_consent (JSON)
 * и может быть изменён в любой момент через «Настройки cookie» в футере.
 * Сервер (lib/consent.php, lib/render.php) не выводит счётчики без согласия,
 * поэтому после изменения выбора страница перезагружается.
 */
(function () {
  'use strict';

  var COOKIE_NAME = 'vp_consent';
  var POLICY = '2026.11-1';
  var MAX_AGE = 31536000; // 12 месяцев

  var notice = document.getElementById('cookieNotice');
  if (!notice) return;

  var acceptAll = document.getElementById('cookieNoticeAcceptAll');
  var acceptNecessary = document.getElementById('cookieNoticeAcceptNecessary');
  var toggle = document.getElementById('cookieNoticeSettingsToggle');
  var save = document.getElementById('cookieNoticeSave');
  var settings = document.getElementById('cookieNoticeSettings');
  var optAnalytics = document.getElementById('cookieOptAnalytics');
  var openFromFooter = document.getElementById('cookieSettingsOpen');

  function cookie(name) {
    var parts = document.cookie ? document.cookie.split('; ') : [];
    for (var i = 0; i < parts.length; i++) {
      var index = parts[i].indexOf('=');
      if (index > 0 && parts[i].slice(0, index) === name) {
        return decodeURIComponent(parts[i].slice(index + 1));
      }
    }
    return '';
  }

  function state() {
    try {
      var raw = cookie(COOKIE_NAME);
      if (!raw) return { analytics: false };
      var parsed = JSON.parse(raw);
      return { analytics: !!parsed.analytics };
    } catch (e) {
      return { analytics: false };
    }
  }

  function loadMetrika(ymId) {
    var id = parseInt(ymId, 10);
    if (!id || window.ym) return;
    (function (m, e, t, r, i, k, a) {
      m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments); };
      m[i].l = 1 * new Date();
      k = e.createElement(t);
      a = e.getElementsByTagName(t)[0];
      k.async = 1;
      k.src = r;
      a.parentNode.insertBefore(k, a);
    })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');
    window.ym(id, 'init', {
      clickmap: true,
      trackLinks: true,
      accurateTrackBounce: true,
      anonymizeIP: true,
      referrer: document.referrer,
      url: window.location.href
    });
  }

  function hideNotice() {
    notice.hidden = true;
    notice.setAttribute('hidden', '');
    if (settings) settings.hidden = true;
    if (save) save.hidden = true;
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
    if (acceptAll) acceptAll.hidden = false;
    if (acceptNecessary) acceptNecessary.hidden = false;
  }

  function write(analytics) {
    var prevAnalytics = state().analytics;
    var payload = {
      v: 1,
      necessary: true,
      analytics: !!analytics,
      marketing: false,
      ts: new Date().toISOString(),
      policy: POLICY
    };
    var attrs = COOKIE_NAME + '=' + encodeURIComponent(JSON.stringify(payload)) +
      '; path=/; max-age=' + MAX_AGE + '; SameSite=Lax';
    if (window.location.protocol === 'https:') attrs += '; Secure';
    document.cookie = attrs;
    notice.setAttribute('data-analytics', analytics ? '1' : '0');
    hideNotice();

    // При отзыве ранее данного согласия перезагружаем страницу, чтобы остановить счётчик.
    // При первом согласии подключаем Яндекс.Метрику динамически без reload, сохраняя поисковый referrer.
    if (prevAnalytics && !analytics) {
      window.location.reload();
      return;
    }
    if (analytics) {
      var ymId = notice.getAttribute('data-ym-id') || '';
      if (ymId) loadMetrika(ymId);
    }
  }

  function showNotice(expanded) {
    notice.hidden = false;
    notice.removeAttribute('hidden');
    if (expanded) {
      settings.hidden = false;
      save.hidden = false;
      if (toggle) toggle.setAttribute('aria-expanded', 'true');
      if (optAnalytics) { optAnalytics.checked = state().analytics; optAnalytics.focus(); }
      if (acceptAll) acceptAll.hidden = true;
      if (acceptNecessary) acceptNecessary.hidden = true;
    }
  }

  // Если согласие уже сохранено — баннер скрыт, но его можно открыть из футера.
  if (notice.hasAttribute('hidden')) {
    notice.hidden = true;
  }

  if (acceptAll) {
    acceptAll.addEventListener('click', function () { write(true); });
  }
  if (acceptNecessary) {
    acceptNecessary.addEventListener('click', function () { write(false); });
  }
  if (toggle) {
    toggle.addEventListener('click', function () {
      var expanded = settings.hidden;
      settings.hidden = !expanded;
      save.hidden = !expanded;
      toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      if (expanded && optAnalytics) {
        optAnalytics.checked = state().analytics;
        optAnalytics.focus();
      }
    });
  }
  if (save) {
    save.addEventListener('click', function () {
      write(!!(optAnalytics && optAnalytics.checked));
    });
  }
  if (openFromFooter) {
    openFromFooter.addEventListener('click', function (event) {
      event.preventDefault();
      showNotice(true);
    });
  }
})();

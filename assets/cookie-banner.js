/* Уведомление о файлах cookie — assets/cookie-banner.js
 *
 * Показывается, если пользователь ещё не принимал/отклонял cookie.
 * «Принять» записывает cookie vashpodemnik61_cookie_ok=1 на год и перезагружает
 * страницу, чтобы активировать анонимизированную аналитику.
 * «Отклонить» записывает cookie vashpodemnik61_cookie_ok=0 на год — аналитика
 * не загружается. Соответствует Политике обработки персональных данных.
 */
(function () {
  'use strict';

  var cookieName = 'vashpodemnik61_cookie_ok';
  var notice = document.getElementById('cookieNotice');
  var accept = document.getElementById('cookieNoticeAccept');
  var reject = document.getElementById('cookieNoticeReject');
  if (!notice) return;

  var match = document.cookie.match(new RegExp('(?:^|; )' + cookieName + '=([^;]*)'));
  if (match) {
    notice.hidden = true;
    notice.style.display = 'none';
    return;
  }

  if (accept) {
    accept.addEventListener('click', function () {
      try {
        document.cookie = cookieName + '=1; path=/; max-age=31536000; SameSite=Lax';
      } catch (err) { /* noop */ }
      window.location.reload();
    });
  }
  if (reject) {
    reject.addEventListener('click', function () {
      try {
        document.cookie = cookieName + '=0; path=/; max-age=31536000; SameSite=Lax';
      } catch (err) { /* noop */ }
      notice.hidden = true;
      notice.style.display = 'none';
    });
  }
})();
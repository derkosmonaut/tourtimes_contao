/*
 * Tagesliste "Termine heute" (z. B. Infomonitor an der Kasse).
 *
 * - Blendet Einträge aus, sobald ihr Zeitpunkt (data-tourtimes-hide-at, Unix-Zeit) erreicht ist,
 *   ohne die Seite neu zu laden.
 * - Zeigt den Hinweistext, wenn keine Einträge mehr übrig sind.
 * - Lädt die Seite nach Mitternacht neu (data-tourtimes-reload-at), damit der neue Tag erscheint.
 */
(function () {
  'use strict';

  var CHECK_INTERVAL = 15000;

  function update(list, now) {
    var items = list.querySelectorAll('[data-tourtimes-hide-at]');
    var visible = 0;

    items.forEach(function (item) {
      if (now >= parseInt(item.getAttribute('data-tourtimes-hide-at'), 10) * 1000) {
        item.remove();
      } else {
        visible++;
      }
    });

    var empty = list.querySelector('.tourtimes-today__empty');

    if (empty) {
      empty.hidden = visible > 0;
    }

    var reloadAt = parseInt(list.getAttribute('data-tourtimes-reload-at'), 10) * 1000;

    if (reloadAt && now >= reloadAt) {
      window.location.reload();
    }
  }

  function init() {
    var lists = document.querySelectorAll('[data-tourtimes-today]');

    if (!lists.length) {
      return;
    }

    function tick() {
      var now = Date.now();
      lists.forEach(function (list) {
        update(list, now);
      });
    }

    tick();
    window.setInterval(tick, CHECK_INTERVAL);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

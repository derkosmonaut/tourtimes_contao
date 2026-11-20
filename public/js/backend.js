/*
 * Schnellauswahl für die Wochentage eines Zeitfensters (Backend).
 * Fügt über der Checkbox-Gruppe die Buttons "Mo–Fr", "Wochenende" und "Alle Tage" ein.
 */
(function () {
  'use strict';

  var labels = (document.documentElement.lang || 'de').indexOf('de') === 0
    ? { weekdays: 'Mo–Fr', weekend: 'Sa + So', all: 'Alle Tage', none: 'Keine' }
    : { weekdays: 'Mon–Fri', weekend: 'Sat + Sun', all: 'All days', none: 'None' };

  var presets = [
    ['weekdays', [1, 2, 3, 4, 5]],
    ['weekend', [6, 7]],
    ['all', [1, 2, 3, 4, 5, 6, 7]],
    ['none', []]
  ];

  function init() {
    var boxes = document.querySelectorAll('input[type="checkbox"][name="weekdays[]"]');

    if (!boxes.length || document.querySelector('.tourtimes-presets')) {
      return;
    }

    var wrapper = document.createElement('p');
    wrapper.className = 'tourtimes-presets';

    presets.forEach(function (preset) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'tl_submit';
      button.style.marginRight = '.5em';
      button.textContent = labels[preset[0]];
      button.addEventListener('click', function () {
        boxes.forEach(function (box) {
          box.checked = preset[1].indexOf(parseInt(box.value, 10)) !== -1;
        });
      });
      wrapper.appendChild(button);
    });

    var group = boxes[0].closest('fieldset') || boxes[0].parentNode;
    group.parentNode.insertBefore(wrapper, group);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

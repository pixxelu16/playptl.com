// Counter animation
(function () {
  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function formatValue(value, format) {
    if (format === 'int') return Math.round(value).toString();
    if (format === 'compactK') {
      var n = Math.round(value);
      if (n >= 1000) {
        var k = n / 1000;
        var s = k % 1 === 0 ? k.toFixed(0) : k.toFixed(1);
        return s + 'K';
      }
      return n.toString();
    }
    if (format === 'usdK') {
      var m = Math.round(value);
      if (m >= 1000) {
        var kk = m / 1000;
        var ss = kk % 1 === 0 ? kk.toFixed(0) : kk.toFixed(1);
        return '$' + ss + 'K';
      }
      return '$' + m;
    }
    if (format === 'intComma') {
      return Math.round(value).toLocaleString('en-US');
    }
    return String(value);
  }

  var nodes = document.querySelectorAll('[data-counter]');
  var duration = 2200;

  function run() {
    if (!nodes.length) return;

    var start = performance.now();
    var targets = Array.from(nodes).map(function (el) {
      return {
        el: el,
        target: parseFloat(el.getAttribute('data-target'), 10),
        format: el.getAttribute('data-format') || 'int',
      };
    });

    function frame(now) {
      var t = Math.min(1, (now - start) / duration);
      var e = easeOutCubic(t);
      targets.forEach(function (item) {
        var v = item.target * e;
        item.el.textContent = formatValue(v, item.format);
      });
      if (t < 1) requestAnimationFrame(frame);
      else {
        targets.forEach(function (item) {
          item.el.textContent = formatValue(item.target, item.format);
        });
      }
    }
    requestAnimationFrame(frame);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();

// Nav dropdowns
(function () {
  function closeAll(except) {
    document.querySelectorAll('[data-dropdown].is-open').forEach(function (root) {
      if (except && root === except) return;
      root.classList.remove('is-open');
      var trigger = root.querySelector('[data-dropdown-trigger]');
      if (trigger) trigger.setAttribute('aria-expanded', 'false');
    });
  }

  function init() {
    document.querySelectorAll('[data-dropdown]').forEach(function (root) {
      var trigger = root.querySelector('[data-dropdown-trigger]');
      if (!trigger) return;

      trigger.addEventListener('click', function (event) {
        event.stopPropagation();
        var opening = !root.classList.contains('is-open');
        closeAll();
        if (opening) {
          root.classList.add('is-open');
          trigger.setAttribute('aria-expanded', 'true');
        }
      });
    });

    document.addEventListener('click', function () {
      closeAll();
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeAll();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

// Gallery tabs
(function () {
  function init() {
    var tabs = document.querySelectorAll('[data-gallery-tab]');
    var panels = document.querySelectorAll('[data-gallery-panel]');
    if (!tabs.length || !panels.length) return;

    function activate(key) {
      tabs.forEach(function (tab) {
        var id = tab.getAttribute('data-gallery-tab');
        var on = id === key;
        tab.setAttribute('aria-selected', on ? 'true' : 'false');
        tab.tabIndex = on ? 0 : -1;
      });
      panels.forEach(function (panel) {
        var match = panel.getAttribute('data-gallery-panel') === key;
        panel.classList.toggle('hidden', !match);
        panel.hidden = !match;
      });
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        activate(tab.getAttribute('data-gallery-tab'));
      });
    });

    var initial = document.querySelector('[data-gallery-tab][aria-selected="true"]') || tabs[0];
    if (initial) activate(initial.getAttribute('data-gallery-tab'));
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

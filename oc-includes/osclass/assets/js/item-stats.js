(function() {
  function boot() {
    var cfg = window.oscItemStats || {};
    if(!cfg.itemId || !cfg.ajaxUrl) {
      return;
    }

    var visibleMs = 0;
    var lastTick = Date.now();
    var minutes = 0;
    var engagedSent = false;
    var engagedMs = Math.max(5000, (parseInt(cfg.engagedSeconds, 10) || 15) * 1000);
    var phoneSel = cfg.phoneSelectors || '.phone, div.phone, .show-phone, .show-mobile, a[href^="tel:"]';
    var otherSel = cfg.otherSelectors || '.other, .contact-other, .show-other, [data-osc-stat="contactother_clicks"]';
    var allowed = (cfg.ajaxMeasures && cfg.ajaxMeasures.length) ? cfg.ajaxMeasures : [];

    function isVisible() {
      if(document.prerendering) {
        return false;
      }
      return document.visibilityState === 'visible';
    }

    function token() {
      var t = cfg.octoken || '';
      if(t.indexOf('octoken=') === 0) {
        return t.replace(/^octoken=/, '');
      }
      return t;
    }

    function send(measure, beacon) {
      if(!measure || allowed.indexOf(measure) === -1) {
        return;
      }
      var tok = token();
      var url = cfg.ajaxUrl || '';
      if(tok && url.indexOf('octoken=') === -1) {
        url += (url.indexOf('?') === -1 ? '?' : '&') + 'octoken=' + encodeURIComponent(tok);
      }
      var payload = {
        itemId: cfg.itemId,
        octoken: tok,
        measure: measure
      };
      if(beacon && navigator.sendBeacon) {
        var body = 'itemId=' + encodeURIComponent(cfg.itemId) + '&octoken=' + encodeURIComponent(tok) + '&measure=' + encodeURIComponent(measure);
        navigator.sendBeacon(url, new Blob([body], {type: 'application/x-www-form-urlencoded'}));
        return;
      }
      if(window.jQuery) {
        window.jQuery.post(url, payload);
      }
    }

    function accumulateVisible(useBeacon) {
      var now = Date.now();
      if(isVisible()) {
        visibleMs += (now - lastTick);
      }
      lastTick = now;
      if(!engagedSent && visibleMs >= engagedMs) {
        engagedSent = true;
        send('views_engaged', !!useBeacon);
      }
      while(minutes < 120 && visibleMs >= ((minutes + 1) * 60000)) {
        minutes += 1;
        send('view_minutes', !!useBeacon);
      }
    }

    setInterval(function() {
      accumulateVisible(false);
    }, 1000);
    document.addEventListener('visibilitychange', function() {
      accumulateVisible(false);
    }, false);

    window.addEventListener('pagehide', function() {
      accumulateVisible(true);
    });

    function closestMatch(el, selector) {
      if(!el || !el.closest || !selector) {
        return null;
      }
      try {
        return el.closest(selector);
      } catch(e) {
        return null;
      }
    }

    document.addEventListener('click', function(e) {
      var el = e.target;
      if(!el) {
        return;
      }
      var dataEl = closestMatch(el, '[data-osc-stat]');
      if(dataEl) {
        var key = dataEl.getAttribute('data-osc-stat');
        if(key) {
          send(key, false);
          return;
        }
      }
      if(closestMatch(el, phoneSel)) {
        send('phone_clicks', false);
        return;
      }
      if(closestMatch(el, otherSel)) {
        send('contactother_clicks', false);
      }
    }, false);
  }

  if(document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, false);
  } else {
    boot();
  }
})();

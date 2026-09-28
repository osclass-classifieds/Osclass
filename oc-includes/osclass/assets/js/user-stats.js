(function() {
  function oscClosestTip(el) {
    while(el && el !== document) {
      if(el.getAttribute && el.getAttribute('data-tip') !== null && ((el.classList && el.classList.contains('osc-stats-tip')) || (el.className && String(el.className.baseVal || el.className).indexOf('osc-stats-tip') >= 0))) {
        return el;
      }
      el = el.parentNode;
    }
    return null;
  }

  function oscChartOf(el) {
    while(el && el !== document) {
      if(el.classList && el.classList.contains('osc-stats-chart')) {
        return el;
      }
      el = el.parentNode;
    }
    return null;
  }

  function oscTipBox(chart) {
    return chart ? chart.querySelector('.osc-stats-tooltip') : null;
  }

  function oscClearActive(chart) {
    if(!chart) {
      return;
    }
    var nodes = chart.querySelectorAll('.osc-stats-tip.is-active');
    for(var i = 0; i < nodes.length; i++) {
      nodes[i].classList.remove('is-active');
    }
  }

  function oscHideTip(chart) {
    var box = oscTipBox(chart);
    if(box) {
      box.classList.remove('is-open');
      box.textContent = '';
    }
    if(chart) {
      chart.classList.remove('is-pinned');
    }
    oscClearActive(chart);
  }

  function oscHideAllTips(except) {
    var charts = document.querySelectorAll('.osc-stats-chart');
    for(var i = 0; i < charts.length; i++) {
      if(charts[i] !== except) {
        oscHideTip(charts[i]);
      }
    }
  }

  function oscPlaceTip(box, wrap, clientX, clientY) {
    var rect = wrap.getBoundingClientRect();
    var x = clientX - rect.left;
    var y = clientY - rect.top;
    box.style.left = Math.max(10, Math.min(rect.width - 10, x)) + 'px';
    box.style.top = Math.max(8, y) + 'px';
  }

  function oscShowTip(chart, tip, clientX, clientY, pinned) {
    var box = oscTipBox(chart);
    var wrap = chart.querySelector('.osc-stats-chart-wrap') || chart;
    if(!box || !wrap) {
      return;
    }
    oscClearActive(chart);
    tip.classList.add('is-active');
    box.textContent = tip.getAttribute('data-tip') || '';
    box.classList.add('is-open');
    if(pinned) {
      chart.classList.add('is-pinned');
    } else {
      chart.classList.remove('is-pinned');
    }
    oscPlaceTip(box, wrap, clientX, clientY);
  }

  function oscTipCenter(tip) {
    var hit = tip.querySelector ? tip.querySelector('.osc-stats-hit, .osc-stats-point') : null;
    var el = hit || tip;
    var b = el.getBoundingClientRect();
    return {x: b.left + (b.width / 2), y: b.top};
  }

  document.addEventListener('mouseover', function(e) {
    var tip = oscClosestTip(e.target);
    if(!tip) {
      return;
    }
    var chart = oscChartOf(tip);
    if(!chart || chart.classList.contains('is-pinned')) {
      return;
    }
    var c = oscTipCenter(tip);
    oscShowTip(chart, tip, c.x, c.y, false);
  }, true);

  document.addEventListener('mouseout', function(e) {
    var tip = oscClosestTip(e.target);
    if(!tip) {
      return;
    }
    var to = e.relatedTarget;
    if(to && tip.contains && tip.contains(to)) {
      return;
    }
    var chart = oscChartOf(tip);
    if(chart && !chart.classList.contains('is-pinned')) {
      oscHideTip(chart);
    }
  }, true);

  document.addEventListener('click', function(e) {
    var tip = oscClosestTip(e.target);
    var chart = tip ? oscChartOf(tip) : null;
    if(tip && chart) {
      e.preventDefault();
      e.stopPropagation();
      if(tip.classList.contains('is-active') && chart.classList.contains('is-pinned')) {
        oscHideTip(chart);
        return;
      }
      oscHideAllTips(chart);
      var c = oscTipCenter(tip);
      oscShowTip(chart, tip, c.x, c.y, true);
      return;
    }
    oscHideAllTips(null);
  }, true);

  document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape' || e.keyCode === 27) {
      oscHideAllTips(null);
      return;
    }
    if((e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32)) {
      var tip = oscClosestTip(e.target);
      if(!tip) {
        return;
      }
      var chart = oscChartOf(tip);
      if(!chart) {
        return;
      }
      e.preventDefault();
      if(tip.classList.contains('is-active') && chart.classList.contains('is-pinned')) {
        oscHideTip(chart);
        return;
      }
      oscHideAllTips(chart);
      var c = oscTipCenter(tip);
      oscShowTip(chart, tip, c.x, c.y, true);
    }
  }, true);
})();

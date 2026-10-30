$(function(){
  // sticky admin menu
  $(window).scroll(function(){
    adminMenuSticky();
  });

  $(window).resize(function(){
    adminMenuSticky();
  });

  $('#sidebar ul.oscmenu > li').each(function(){
    var menuHeight = Math.min(($(this).find('ul li').length)*28 + 12, 332);

    if($(window).height() - ($(this).position().top + 34) < menuHeight) {
      $(this).addClass('open-menu-from-bottom');
    }
    
    $(this).hover(function(){
      $(this).addClass('hover');
    },function(){
      $(this).removeClass('hover');
    });
  });
  $('#show-more > ul').each(function(){
    var $submenu = $(this);
    $submenu.parent().hover(function(){
      $(this).addClass('hover');
    },function(){
      $(this).removeClass('hover');
    });
  });
  $('#hidden-menus').on('mouseenter','li',function(){
    var $submenu = $(this).find('ul');
    $(this).addClass('hover');
    $submenu.css('top',($submenu.height()*-1)).css('margin-top','-22px');
  }).on('mouseleave','li',function(){
    $(this).removeClass('hover')
  });
  $('#language-tab li a').click(function(){
    $('#language-tab li').removeClass('ui-state-active').removeClass('ui-tabs-selected');
    $(this).parent().addClass('ui-tabs-selected ui-state-active');
    var currentLocale = $(this).attr('href').replace('#','');
    $(osc.locales.string).parent().hide();
    $('[name*="'+currentLocale+'"], .'+currentLocale).parent().show();
    osc.locales.current = currentLocale;
    return false;
  });
  $('#language-tab li a[href="#'+osc.locales._default+'"]').triggerHandler("click");
  //Row actions
  $('.table .actions').each(function(){
    var $actions = $(this);
    var $rowActions = $('#table-row-actions');
    $(this).parents('tr').mouseenter(function(event){
      event.preventDefault();
      var $container = $(this).closest('table.table-contains-actions, div.table-contains-actions');
      var $containterOffset = ($container.length ? $container.offset() : null);
      if(!$containterOffset) {
        $containterOffset = $('.table-contains-actions').first().offset();
      }
      $thisOffset = $(this).offset();
      // the bar starts at the cell that holds the actions (any column order, with or without status and bulk columns) and ends at the row end
      var $cell = $actions.closest('td');
      var extra_offset = $cell.offset().left - $containterOffset.left + ($container.is('div') ? $container.scrollLeft() : 0) - 1;
      $rowActions.empty().append($actions.clone()).css({
			width: $(this).offset().left + $(this).width() - $cell.offset().left + 1,
			top: ($thisOffset.top - 13 - $containterOffset.top) + $(this).height()-1,
        left: extra_offset
      }).show();
      $('tr').removeClass('collapsed-hover');
      if($(this).closest('div.table-contains-actions, .table-contains-actions-wrap').hasClass('table-collapsed')){
        var thatRow = $(this);
        thatRow.next().addClass('collapsed-hover');
        $rowActions.mouseleave(function(){
          $('tr').removeClass('collapsed-hover');
        });
      }
    });
  });
  $('.table-contains-actions-wrap, div.table-contains-actions').mouseleave(function(){
    $('tr').removeClass('collapsed-hover');
    $('#table-row-actions').hide();
  });
  //Close help
  $('.flashmessage .ico-close').on('click',function(){
    $(this).parents('.flashmessage').hide();
  });
  $('#help-box .ico-close').click(function(){
    $('#help-box').hide();
  });

  $('#content-head .ico-help').click(function(){
    $('#help-box').fadeIn();
  });
  $('#table-row-actions').on('click','.show-more-trigger', function(){
    $(this).parent().addClass('hover');
    return false;
  });
  //Selects
  $('select').each(function(){
    //selectUi($(this));
  });
  //Set Layout
	$(window).resize(function(){
	  resetLayout();
	}).resize();


  oscTab();
  $(".close-dialog").on("click", function(){
    $(".ui-dialog-content").dialog("close");
    return false;
  });
  //Dissable
  $('.btn-disabled, *:disabled').css('opacity','0.7').on('click',function(){
    return false;
  });
  //Compact mode
  var cmode_trigger = $("#osc_toolbar_switch_mode > .trigger");
  var cmode_bg = $("#osc_toolbar_switch_mode > .background");
  if($('body').hasClass('compact')){
    cmode_trigger.stop().animate({left:24},500);
    cmode_bg.stop().animate({backgroundColor:'#00e1f2'},500);
  } else {
    cmode_trigger.stop().animate({left:0},500);
    cmode_bg.stop().animate({backgroundColor:'#f3f3f3'},500);
  }
  $("#osc_toolbar_switch_mode ").on("click", function(){
    $.getJSON(
    $(this).attr('href'),
    function(data){
      if(data.compact_mode == false){
        $('body').removeClass('compact');
        cmode_trigger.stop().animate({left:0},500);
        cmode_bg.stop().animate({backgroundColor:'#f3f3f3'},500);
      } else {
        $('body').addClass('compact');
        cmode_trigger.stop().animate({left:24},500);
        cmode_bg.stop().animate({backgroundColor:'#00e1f2'},500);
      }
      resetLayout();
    });
    return false;
  });
});
function oscTab(callback){
  $(".osc-tab").tabs();
}
function selectUi(thatSelect){
  var uiSelect = $('<a href="#" class="select-box-trigger"></a>');
  var uiSelectIcon = $('<span class="select-box-icon"><div class="ico ico-20 ico-drop-down"></div></span>');
  var uiSelected = $('<span class="select-box-label">'+thatSelect.find("option:selected").text()+'</span>');

  thatSelect.css('filter', 'alpha(opacity=40)').css('opacity', '0');
  thatSelect.wrap('<div class="select-box '+thatSelect.attr('class')+'" />');


  uiSelect.append(uiSelected).append(uiSelectIcon);
  thatSelect.parent().append(uiSelect);
  uiSelect.click(function(){
    return false;
  });
  thatSelect.change(function(){
    uiSelected.text(thatSelect.find('option:selected').text());
  });
  thatSelect.on('remove', function() {
    uiSelect.remove();
  });
}
function resetLayout(){
  //calc how items can see
  var headerHeight = 36;
  var compactModeButtonHeight = 36;
  var menuItemHeight = 34;
  var thisHeight  = $(window).height()-headerHeight;
  var footeHeight = 38;
  var $sidebar = $('#sidebar');
  //reset vars if compact mode
  if($('body').hasClass('compact')){
    var headerHeight = 36 + 8;  // padding
    var compactModeButtonHeight = 34;
    var menuItemHeight = 40;
    var thisHeight  = $(window).height()-headerHeight;
    var footeHeight = 38;
    var $sidebar = $('#sidebar');
  }
  //calc
  //var visible = Math.floor((thisHeight-compactModeButtonHeight)/menuItemHeight)-1; //-1 for show moreBtn
  var visible = 9999999;

  //Global actions
  $('#sidebar ul.oscmenu > li').show();
  //actions depends mode
  if($('body').hasClass('compact')){
    $('#show-more').hide();
  } else {
    var hidden = $('#sidebar ul.oscmenu > li:gt('+(visible-1)+')'); //-1 fix gt starts in 0
    if(hidden.length > 1){
      $('#hidden-menus').empty().append(hidden.clone()).css({
        width: (hidden.length*menuItemHeight)
      })
      hidden.hide();
      $('#show-more').show();
    } else {
      $('#show-more').hide();
    }
  }
  //global footer
  $('#content-page').css({paddingBottom:60});
  $('#sidebar').css({
      position:'fixed',
      height: '100%',
      left:0,
      top:50
    });
  var calcPaddingBtm;
  if($(window).height() < (620+headerHeight)){
    if($('body').hasClass('compact')){
      calcHeigt = $('#content-render').height();
      if(calcHeigt<620){
        calcHeigt = 620;
      }
      $('#sidebar').css({
        position:'absolute',
        height: calcHeigt,
        left:-50,
        top:0
      });
    }
    //$('#content-page').css('background-color','red');
    calcPaddingBtm = 620-($('#content-render').height())+50+10;
  } else {
    calcPaddingBtm = $(window).height()-($('#content-render').height()-10);
    //$('#content-page').css('background-color','green');
  }
  $('#content-page').css({paddingBottom:calcPaddingBtm});
}
function tabberAutomatic(){
  $('.tabber:hidden').show();
  $('.tabber h2').remove();
  $(osc.locales.string).parent().hide();
  $('[name*="'+osc.locales.current+'"],.'+osc.locales.current).parent().show();
}

function adminMenuSticky() {
  var boxHeight = $('#sidebar > ul.oscmenu').outerHeight();   // os810
  var windowHeight = $(window).height();
  var windowWidth = $(window).width();
  var switchButtonHeight = $('.osc_switch_mode').height();
  var topOffset = $(document).scrollTop();
  var contentHeight = $('#content-page').height();
  var menuScroll = 0;

  if(windowWidth > 827) {   // used to be 980
    if(boxHeight > windowHeight) {
      if(contentHeight < boxHeight - 68) {
        $('#content-page').css('min-height', boxHeight - 68);
      }
      
      if(boxHeight > topOffset + windowHeight - 72) {
        menuScroll = -topOffset;
        $('.oscmenu').css('position', 'absolute').css('top', menuScroll).css('bottom', 'auto');
      } else if (topOffset <= 0) {
        $('.oscmenu').css('position', 'relative').css('top', 'auto').css('bottom', 'auto');
      } else {
        $('.oscmenu').css('position', 'fixed').css('top', 'auto').css('bottom', 0);
      }
    } else {
      $('.oscmenu').css('position', 'relative').css('top', 'auto').css('bottom', 'auto');
    }
  } else {
    $('.oscmenu').css('position', 'relative').css('top', 'auto').css('bottom', 'auto');
    $('#content-page').css('min-height', 'auto');
  }
}


// Pills: each .osc-ltabs box gets one pill per [data-ltab] key and shows one part at a time.
// Used for languages (default), but any set works: countries, types, plans. Boxes with the same
// data-ltabs-group (default "lang") switch together and remember the last choice. Call oscLtabs(el) for markup added later.
var oscLtabCur = {};

function oscLtabParts(box) {
  return Array.prototype.filter.call(box.querySelectorAll('[data-ltab]'), function(p) { return p.closest('.osc-ltabs') === box; });
}

function oscLtabs(root) {
  (root || document).querySelectorAll('.osc-ltabs:not([data-ltabs-ready])').forEach(function(box) {
    var codes = [], labels = {};
    box.setAttribute('data-ltabs-ready', '1');
    oscLtabParts(box).forEach(function(p) {
      var c = p.getAttribute('data-ltab');
      if(codes.indexOf(c) < 0) { codes.push(c); }
      if(!labels[c] && p.getAttribute('data-ltab-label')) { labels[c] = p.getAttribute('data-ltab-label'); }
    });

    if(codes.length < 2) { return; }

    var nav = Array.prototype.filter.call(box.querySelectorAll('.osc-ltabs-nav'), function(n) { return n.closest('.osc-ltabs') === box; })[0];
    if(!nav) {
      nav = document.createElement('div');
      nav.className = 'osc-ltabs-nav';
      box.insertBefore(nav, box.firstChild);
    }

    nav.setAttribute('role', 'tablist');
    codes.forEach(function(c) {
      var b = document.createElement('button');
      b.type = 'button';
      b.setAttribute('role', 'tab');
      b.setAttribute('data-ltab-btn', c);
      b.appendChild(document.createTextNode(labels[c] || c.toUpperCase()));
      nav.appendChild(b);
    });

    nav.addEventListener('click', function(e) {
      var b = e.target.closest('[data-ltab-btn]');
      if(b) { oscLtabShow(b.getAttribute('data-ltab-btn'), true, box); }
    });

    nav.addEventListener('keydown', function(e) {
      var list = Array.prototype.slice.call(nav.querySelectorAll('[data-ltab-btn]'));
      var i = list.indexOf(document.activeElement), step = {ArrowRight: 1, ArrowLeft: -1}[e.key];
      if(i < 0 || !step) { return; }
      e.preventDefault();
      var b = list[(i + step + list.length) % list.length];
      oscLtabShow(b.getAttribute('data-ltab-btn'), true, box);
      b.focus();
    });
  });

  // first show of every group found in this root
  var groups = [];
  (root || document).querySelectorAll('.osc-ltabs[data-ltabs-ready]').forEach(function(box) {
    var g = box.getAttribute('data-ltabs-group') || 'lang';
    if(groups.indexOf(g) < 0) { groups.push(g); }
  });

  groups.forEach(function(g) {
    if(oscLtabCur[g] === undefined) {
      try { oscLtabCur[g] = localStorage.getItem('osc_ltab_' + g) || ''; } catch(e) { oscLtabCur[g] = ''; }
    }

    oscLtabShow(oscLtabCur[g] || (g == 'lang' && window.osc && osc.locales ? osc.locales._default : ''), false, null, g);
  });
}

// Show key "code" in every box of the group (the group of "from" box, or "group"). A box without that key shows its
// first part; in the language group the same two letters also match (en and en_US).
function oscLtabShow(code, remember, from, group) {
  code = String(code || '');
  var g = group || (from ? from.getAttribute('data-ltabs-group') : '') || 'lang';
  if(remember) {
    oscLtabCur[g] = code;
    try { localStorage.setItem('osc_ltab_' + g, code); } catch(e) {}
  }

  document.querySelectorAll('.osc-ltabs[data-ltabs-ready]').forEach(function(box) {
    if((box.getAttribute('data-ltabs-group') || 'lang') != g) { return; }

    var btns = Array.prototype.filter.call(box.querySelectorAll('[data-ltab-btn]'), function(b) { return b.closest('.osc-ltabs') === box; });
    if(!btns.length) { return; }

    var key = function(b) { return b.getAttribute('data-ltab-btn'); };
    var pick = btns.filter(function(b) { return key(b) == code; })[0] || (g == 'lang' ? btns.filter(function(b) { return key(b).substr(0, 2).toLowerCase() == code.substr(0, 2).toLowerCase(); })[0] : null) || btns[0];
    var cur = key(pick);

    btns.forEach(function(b) {
      var on = (b === pick);
      b.setAttribute('aria-selected', on ? 'true' : 'false');
      b.tabIndex = (on ? 0 : -1);
    });

    oscLtabParts(box).forEach(function(p) {
      p.classList.toggle('osc-ltab-off', p.getAttribute('data-ltab') != cur);
    });
  });
}

// a required or invalid field in a hidden part opens that part before the browser shows its message
document.addEventListener('invalid', function(e) {
  var p = (e.target.closest ? e.target.closest('.osc-ltabs[data-ltabs-ready] [data-ltab]') : null);
  if(p) { oscLtabShow(p.getAttribute('data-ltab'), false, p.closest('.osc-ltabs')); }
}, true);

$(function() {
  oscLtabs(document);
});

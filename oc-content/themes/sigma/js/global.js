$(document).ready(function(event){
  
  $('body').on('click', 'a.comment-reply', function(e) {
    e.preventDefault();  
    var replyId = parseInt($(this).attr('data-id'));
    var replyText = $(this).attr('data-text');
    var replyRating = parseInt($(this).attr('data-rating'));

    if(replyId > 0) {
      $('form[name="comment_form"] input[name="replyId"]').val(replyId);
      $('form[name="comment_form"] .reply-text').text(replyText).show(0);
      
      if(replyRating == 0) {
        $('form[name="comment_form"] .control-group.rating').hide(0);
        $('form[name="comment_form"] input[name="rating"]').val('');
      }
    }
    
    $(window).scrollTop($('form[name="comment_form"]').offset().top - 80);
  });
  
  $('body').on('click', 'form[name="comment_form"] .reply-text', function(e) {
    e.preventDefault();  
    $('form[name="comment_form"] input[name="replyId"]').val('');
    $('form[name="comment_form"] .reply-text').text('').hide(0);
    $('form[name="comment_form"] .control-group.rating').show(0);
  });
  
  $('body').on('click', '.menu-icon', function(e) {
    e.preventDefault();

    if($(this).hasClass('opened')) {
      $(this).removeClass('opened');
      $('header .nav').fadeOut(200);
    } else {
      $(this).addClass('opened');
      $('header .nav').fadeIn(200);
    }
  });

  $('body').on('click', '.show-filters-btn', function(e) {
    e.preventDefault();
    $('body.search #sidebar').fadeIn(200);
  });

  $('body').on('click', '.show-contact-btn', function(e) {
    e.preventDefault();
    $('#contact-in').fadeIn(200);
  });

  $('body').on('click', '.show-menu-btn', function(e) {
    e.preventDefault();
    $('#sidebar').fadeIn(200);
  });

  $('body').on('click', '.fixed-close', function(e) {
    e.preventDefault();
    $(this).closest('.fixed-layout').fadeOut(200);
  });


  $('.see_by').hover(function(){
    $(this).addClass('hover');
  },function(){
    $(this).removeClass('hover');
  })
 
  $('.flashmessage .ico-close').click(function(){
    $(this).parents('.flashmessage').remove();
  });
  $('#mask_as_form select').on('change',function(){
    $('#mask_as_form').submit();
    $('#mask_as_form').submit();
  });

  /*
  if(typeof $.fancybox == 'function') {
    $("a.fancybox").fancybox({
      openEffect : 'none',
      closeEffect : 'none',
      nextEffect : 'fade',
      prevEffect : 'fade',
      loop : false,
      helpers : {
        title : {
          type : 'inside'
        }
      },
      tpl: {
        prev: '<a title="'+sigma.fancybox_prev+'" class="fancybox-nav fancybox-prev"><span></span></a>',
        next: '<a title="'+sigma.fancybox_next+'" class="fancybox-nav fancybox-next"><span></span></a>',
        closeBtn : '<a title="'+sigma.fancybox_closeBtn+'" class="fancybox-item fancybox-close" href="javascript:;"></a>'
      }
    });

    $(".main-photo").on('click', function(e) {
      e.preventDefault();
      $("a.fancybox").first().click();
    });
  }
  */


});

if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined' && typeof jQuery.ui.autocomplete !== 'undefined') {
  jQuery.ui.autocomplete.prototype._resizeMenu = function () {
    var ul = this.menu.element;
    ul.outerWidth(this.element.outerWidth());
  };
}

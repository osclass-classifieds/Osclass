<?php if(!defined('OC_ADMIN')) exit('Direct access is not allowed.'); ?>
<!DOCTYPE html>
<html lang="<?php echo substr(osc_current_admin_locale(), 0, 2); ?>">
<head>
  <meta charset="utf-8">
  <title><?php echo osc_apply_filter('admin_title', osc_page_title() . ' - Osclass'); ?></title>
  <meta name="title" content="<?php echo osc_apply_filter('admin_title', osc_page_title() . ' - Osclass'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0" />
  <meta http-equiv="content-language" content="<?php echo osc_current_admin_locale(); ?>" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <script type="text/javascript">
    <?php
      /* TODO: enqueue js lang strings */
      $lang = array(
        'nochange_expiration' => __('No change expiration'),
        'without_expiration' => __('Without expiration'),
        'expiration_day' => __('1 day'),
        'expiration_days' => __('%d days'),
        'select_category' => __('Select category'),
        'no_subcategory' => __('No subcategory'),
        'select_subcategory' => __('Select subcategory')
      );

      $params = Params::getParamsAsArray();
      $locales = osc_get_locales();
      $codes   = array();
      foreach($locales as $locale) {
        $codes[] = '\''. osc_esc_js($locale['pk_c_code']) . '\'';
      }
    ?>
    var osc = window.osc || {};
    osc.locales = {};
    osc.locales._default = '<?php echo osc_language(); ?>';
    osc.locales.current = '<?php echo osc_current_admin_locale(); ?>';
    osc.locales.codes = new Array(<?php echo join(',', $codes); ?>);
    osc.locales.string = '[name*="' + osc.locales.codes.join('"],[name*="') + '"],.' + osc.locales.codes.join(',.');
    osc.langs = <?php echo json_encode($lang); ?>;
  </script>

  <style>#show-more {display:none;}</style>
  <?php osc_run_hook('admin_header'); ?>
</head>

<?php
  $file = Params::getParam('file');

  if($file <> '') {
    $fl = explode('/', $file);
    $file = @$fl[0];
  }


  $author = '';
  if($file <> '') {
    $pluginInfo = osc_plugin_get_info($file . '/index.php');
    $author = @$pluginInfo['author'] <> '' ? $pluginInfo['author'] : @$pluginInfo['author_name'];
    
    // contains theme file
    if(strpos(Params::getParam('file'), OC_CONTENT_FOLDER . '/themes/') !== false) {
      $themeInfo = WebThemes::newInstance()->loadThemeInfo(WebThemes::newInstance()->getCurrentTheme());
      $author = @$themeInfo['author'] <> '' ? @$themeInfo['author'] : @$themeInfo['author_name'];
    }
    
    if(strtolower($author) == 'mb themes' || strtolower($author) == 'mb-themes' || strtolower($author) == 'mbthemes' || strtolower($author) == 'osclasspoint') {
      $author = 'osclasspoint';
    }
  } else {
    $file = Params::getParam('route');
  }
?>

<body class="<?php echo implode(' ',osc_apply_filter('admin_body_class', array())); ?> page-<?php echo (@$params['page'] <> '' ? @$params['page'] : 'dashboard'); ?> action-<?php echo (@$params['action'] <> '' ? @$params['action'] : 'none'); ?> file-<?php echo ($file <> '' ? $file : 'none'); ?> author-<?php echo ($author <> '' ? $author : 'none'); ?>">
  <script>
  $(document).ready(function(){
    $('.header-wrapper a').each(function() {
      $(this).attr('title', $(this).text());
    });
    
    if($(window).width() <= 980) {
      $('body').removeClass('compact');
    }
    
    $('body').on('click', '#sidebar > ul.oscmenu > li > h3 > a', function(e) {
      if($(window).width() <= 980) {
        if($(this).closest('li').find('ul').length) {
          e.preventDefault();
          
          if(!$(this).closest('li').hasClass('current')) {
            $('#sidebar > ul.oscmenu > li').removeClass('current').removeClass('hover');
            $(this).closest('li').addClass('current').addClass('hover');
          }
        }
      }
    });
    
    $('body').on('click', '#osc_toolbar_mobilemenu', function(e) {
      e.preventDefault();
      $('#header .osc_mobile_list').slideToggle(200);
    });
    
    $(document).click(function(event) { 
      var $target = $(event.target);
      if(!$target.closest('#osc_toolbar_mobilemenu').length && !$target.closest('.osc_mobile_list').length && $('.osc_mobile_list').is(":visible")) {
        $('#header .osc_mobile_list').slideUp(200);
      }        
    });
    
    
    if($('.header-wrapper a').lenth > 10) {
      $('#header').addClass('hide-useless');
    }
      
    $('.open-admin-menu').click(function(e) {
      e.preventDefault();
      
      if($(this).hasClass('active')) {   // hide menu
        $('#content').removeClass('opened');
        $(this).removeClass('active');
        $('#sidebar').hide(0);
      } else {
        $('#content').addClass('opened');
        $(this).addClass('active');
        $('#sidebar').show(0);
      }
    });
    
    $(document).click(function(event) { 
      var $target = $(event.target);
      if(!$target.closest('#sidebar').length && !$target.closest('.open-admin-menu').length && $('#content').hasClass('opened')) {
        $('#content').removeClass('opened');
        $('.open-admin-menu').removeClass('active');
        $('#sidebar').hide(0);
      }        
    });
  });
  </script>

  <?php AdminToolbar::newInstance()->render(); ?>
  <!--</div>-->

  <div id="content">
    <?php osc_draw_admin_menu(); ?>
    
    <div id="content-render">
      <div id="content-head">
        <?php 
          ob_start(); 
          osc_show_flash_message('admin');
          $flashmessages = ob_get_contents();
          ob_end_clean(); 

          osc_run_hook('admin_page_header'); 
        ?>
      </div>
      <div id="help-box">
        <a href="#" class="btn ico ico-20 ico-close">x</a>
        <?php osc_run_hook('help_box'); ?>
      </div>

      <div id="content-page">
        <div class="grid-system">

        <div class="newflash">
          <?php echo $flashmessages; ?>
          <div class="jsMessage flashmessage flashmessage-info hide">
          <a class="btn ico btn-mini ico-close">×</a>
          <p></p>
          </div>
        </div>

          <div class="grid-row grid-first-row grid-100">
            <div class="row-wrapper page-<?php echo (@$params['page'] <> '' ? @$params['page'] : 'dashboard'); ?> action-<?php echo (@$params['action'] <> '' ? @$params['action'] : 'none'); ?> file-<?php echo ($file <> '' ? $file : 'none'); ?> author-<?php echo ($author <> '' ? $author : 'none'); ?> <?php echo osc_apply_filter('render-wrapper', ''); ?>" id="nice-box">
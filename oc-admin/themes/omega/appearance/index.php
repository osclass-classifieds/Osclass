<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');
/*
 * Copyright 2014 Osclass
 * Copyright 2026 Osclass by OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * You may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Do not edit or add to this file if you wish to upgrade Osclass to newer
 * versions in the future. Software is distributed on an "AS IS" basis, without
 * warranties or conditions of any kind, either express or implied. Do not remove
 * this NOTICE section as it contains license information and copyrights.
 */


osc_enqueue_script('jquery-validate');

//getting variables for this view
$themes = __get("themes");
$info   = osc_theme_get_info(osc_theme());

//customize Head
function customHead() { ?>
  <script type="text/javascript">
    $(document).ready(function() {
      // dialog delete
      $("#dialog-delete-theme").dialog({
        autoOpen: false,
        modal: true,
        title: '<?php echo osc_esc_js( __('Delete theme') ); ?>'
      });
    });

    // dialog delete function
    function delete_dialog(theme) {
      $("#dialog-delete-theme input[name='webtheme']").attr('value', theme);
      $("#dialog-delete-theme").dialog('open');
      return false;
    }
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);

function addHelp() {
  echo '<p>' . sprintf(__("Change your site's look and feel by activating a theme among those available. You can download new themes from the <a href=\"%s\">market</a>. <strong>Be careful</strong>: if your theme has been customized, you'll lose all changes if you change to a new theme."), 'https://osclasspoint.com/osclass-themes') . '</p>';
}
osc_add_hook('help_box','addHelp');

osc_add_hook('admin_page_header','customPageHeader');
function customPageHeader() { 
  ?>
  <h1>
    <?php _e('Appearance'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=add" class="btn btn-green ico ico-add-white float-right"><?php _e('Add theme'); ?></a>
  </h1>
  <?php
}

function customPageTitle($string) {
  return sprintf(__('Appearance - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' );

$aThemesToUpdate = json_decode(osc_get_preference('themes_to_update'));

// check if themes eligible for update are really eligible and is not just cache problem
if(is_array($aThemesToUpdate) && count($aThemesToUpdate) > 0) {
  foreach($themes as $theme_name) {
    if(strpos($theme_name, '_child') !== false) {    // Child themes are not objective of update!
      if(($key = array_search($theme_name, $aThemesToUpdate)) !== false) {
        unset($aThemesToUpdate[$key]);
      }

    } else {
      $tinfo = osc_theme_get_info($theme_name);

      if(isset($tinfo['product_key']) && in_array($theme_name, $aThemesToUpdate)) {
        $check = _need_update($tinfo['product_key'], @$tinfo['version'], true);  // using cached version of product versions for any price

        // plugin is in list of plugins to be updated, but actually is in latest version now
        if($check === false) {
          if(($key = array_search($theme_name, $aThemesToUpdate)) !== false) {
            unset($aThemesToUpdate[$key]);
          }
        }
      }
    }
  }
}

$aThemesToUpdate = array_values(array_filter($aThemesToUpdate));


// maybe here, we should update back 'plugins_to_update' preference
if(is_array($aThemesToUpdate) && count($aThemesToUpdate) > 0) {
  $bThemesToUpdate = true;
} else {
  $bThemesToUpdate = false;
}

?>

<div id="appearance-page">
  <!-- themes list -->
  <div class="appearance">
    <div id="tabs" class="ui-osc-tabs">
      <div id="available-themes" class="ui-osc-tabs-panel">
        <h2 class="render-title"><?php _e('Current theme'); ?> <a href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=add" class="btn btn-mini"><?php _e('Add new'); ?></a></h2>
        <div class="current-theme">
          <div class="theme">
            <?php 
              if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/screenshot.png')) {
                $theme_logo = osc_base_url() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/screenshot.png';
              } else if (strpos(osc_theme(), 'child') !== false && file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . str_replace('_child', '', osc_theme()) . '/screenshot.png')) {
                $theme_logo = osc_base_url() . '/' . OC_CONTENT_FOLDER . '/themes/' . str_replace('_child', '', osc_theme()) . '/screenshot.png';
              } else {
                $theme_logo = false;
              }
            ?>
            
            <?php if($theme_logo !== false) { ?>
              <img src="<?php echo $theme_logo; ?>" title="<?php echo $info['name']; ?>" alt="<?php echo $info['name']; ?>" />
            <?php } ?>
            
            <div>
              <div class="theme-info">
                <h3><?php echo $info['name']; ?> <?php echo $info['version']; ?> <?php _e('by'); ?> <a target="_blank" href="<?php echo $info['author_url']; ?>"><?php echo $info['author_name']; ?></a></h3>
              </div>
              
              <div class="theme-description">
                <?php echo $info['description']; ?>
              </div>
              
              <div class="theme-actions">
                <?php
                  if($bThemesToUpdate) { 
                    if(in_array(osc_theme(),$aThemesToUpdate)){
                      ?>
                      <a href='#<?php echo htmlentities(@$info['product_key']); ?>' class="btn btn-mini btn-black market-popup"><?php _e("Update"); ?></a>
                      <?php 
                    }
                  } 
                ?>
                
                <?php if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/settings.php')) { ?>
                  <a class="btn btn-mini" href="<?php echo osc_admin_base_url(true) . '?page=appearance&action=render&file=' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/settings.php'; ?>"><?php _e('Configure'); ?></a>
                <?php } else if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/configure.php')) { ?>
                  <a class="btn btn-mini" href="<?php echo osc_admin_base_url(true) . '?page=appearance&action=render&file=' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/configure.php'; ?>"><?php _e('Configure'); ?></a>
                <?php } ?>
                
                <?php if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/logo.php')) { ?>
                  <a class="btn btn-mini" href="<?php echo osc_admin_base_url(true) . '?page=appearance&action=render&file=' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/logo.php'; ?>"><?php _e('Logo'); ?></a>
                <?php } else if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/header.php')) { ?>
                  <a class="btn btn-mini" href="<?php echo osc_admin_base_url(true) . '?page=appearance&action=render&file=' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/header.php'; ?>"><?php _e('Logo'); ?></a>
                <?php } ?>
                
                <?php if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/category.php')) { ?>
                  <a class="btn btn-mini" href="<?php echo osc_admin_base_url(true) . '?page=appearance&action=render&file=' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/category.php'; ?>"><?php _e('Icons'); ?></a>
                <?php } ?>
                
                <?php if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/banner.php')) { ?>
                  <a class="btn btn-mini" href="<?php echo osc_admin_base_url(true) . '?page=appearance&action=render&file=' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/banner.php'; ?>"><?php _e('Advertisement'); ?></a>
                <?php } ?>
                
                <?php if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/plugins.php')) { ?>
                  <a class="btn btn-mini" href="<?php echo osc_admin_base_url(true) . '?page=appearance&action=render&file=' . OC_CONTENT_FOLDER . '/themes/' . osc_theme() . '/admin/plugins.php'; ?>"><?php _e('Plugins'); ?></a>
                <?php } ?>
              </div>
            </div>
            <div class="clear"></div>
          </div>
        </div>
        
        <h2 class="render-title"><?php _e('Available themes'); ?></h2>
        <div class="available-theme">
          <?php 
          $csrf_token = osc_csrf_token_url();

          $counter = 0;
          foreach($themes as $theme) { ?>
            <?php
              if( $theme == osc_theme() ) {
                continue;
              }
              $counter++;
              $info = WebThemes::newInstance()->loadThemeInfo($theme);
              
              if(file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . $theme . '/screenshot.png')) {
                $theme_logo = osc_base_url() . '/' . OC_CONTENT_FOLDER . '/themes/' . $theme . '/screenshot.png';
              } else if (strpos($theme, 'child') !== false && file_exists(osc_base_path() . '/' . OC_CONTENT_FOLDER . '/themes/' . str_replace('_child', '', $theme) . '/screenshot.png')) {
                $theme_logo = osc_base_url() . '/' . OC_CONTENT_FOLDER . '/themes/' . str_replace('_child', '', $theme) . '/screenshot.png';
              } else {
                $theme_logo = false;
              }
            ?>
            <div class="theme">
              <div class="theme-stage">
                <div class="theme-img">
                  <?php if($theme_logo !== false) { ?>
                    <img src="<?php echo $theme_logo; ?>" title="<?php echo $info['name']; ?>" alt="<?php echo $info['name']; ?>" />
                  <?php } ?>
                </div>
              </div>
              <div class="theme-actions">
                <a href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=activate&amp;theme=<?php echo $theme; ?>&amp;<?php echo $csrf_token; ?>" class="btn btn-mini btn-green"><?php _e('Activate'); ?></a>
                <a target="_blank" href="<?php echo osc_base_url(true); ?>?theme=<?php echo $theme; ?>" class="btn btn-mini btn-gray"><?php _e('Preview'); ?></a>
                <a onclick="return delete_dialog('<?php echo $theme; ?>');" href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=delete&amp;webtheme=<?php echo $theme; ?>&amp;<?php echo $csrf_token; ?>" class="btn btn-mini btn-black float-right delete"><?php _e('Delete'); ?></a>
                <?php
                  if($bThemesToUpdate) {
                    if(in_array($theme,$aThemesToUpdate)){
                      ?>
                      <a href='#<?php echo htmlentities(@$info['product_key']); ?>' class="btn btn-mini btn-gray market-popup"><?php _e("Update"); ?></a>
                      <?php 
                    }
                  } 
                ?>
              </div>
              <div class="theme-info">
                <h3><?php echo $info['name']; ?> <?php echo $info['version']; ?> <?php _e('by'); ?> <a target="_blank" href="<?php echo $info['author_url']; ?>"><?php echo $info['author_name']; ?></a></h3>
              </div>
              <div class="theme-description">
                <?php echo $info['description']; ?>
              </div>
            </div>
          <?php } ?>

          <?php if($counter <= 0) { ?>
            <div class="flashmessage flashmessage-inline flashmessage-info"><?php _e('No other themes found'); ?></div>
          <?php } ?>
          <div class="clear"></div>
        </div>
      </div>

      <div id="market_installer" class="has-form-actions hide">
         <form name="mkti" action="<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>" method="post">
          <input type="hidden" name="section" value="themes" />
          <input type="hidden" name="redirect" value="1" />
          <input type="hidden" name="market_product_key" id="market_product_key" value="" />
          <div class="osc-modal-content-market">
            <table class="table" cellpadding="0" cellspacing="0">
              <tbody>
                <tr class="table-first-row">
                  <td><?php _e('Name'); ?></td>
                  <td><span id="market_name"><?php _e("Loading data"); ?></span></td>
                </tr>
                <tr class="even">
                  <td><?php _e('Version'); ?></td>
                  <td><span id="market_version"><?php _e("Loading data"); ?></span></td>
                </tr>
                <tr>
                  <td><?php _e('Changes'); ?></td>
                  <td><span id="market_changes"><?php _e("Loading data"); ?></span></td>
                </tr>
                <tr class="even">
                  <td><?php _e('Osclass'); ?></td>
                  <td><span id="market_osclass_version"><?php _e("Loading data"); ?></span></td>
                </tr>
                <tr>
                  <td><?php _e('URL'); ?></td>
                  <td><span id="market_url_span"><a id="market_url" href="#"><?php _e("Download manually"); ?></a></span></td>
                </tr>
              </tbody>
            </table>
            <div class="clear"></div>
          </div>

          <div class="form-actions">
            <div class="wrapper">
              <button id="market_install" class="btn btn-submit"><?php _e('Continue install'); ?></button>
              <button id="market_cancel" class="btn"><?php _e('Cancel'); ?></button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- /themes list -->
</div>

<form id="dialog-delete-theme" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
  <input type="hidden" name="page" value="appearance" />
  <input type="hidden" name="action" value="delete" />
  <input type="hidden" name="webtheme" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('This action can not be undone. Are you sure you want to delete the theme?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
        <input id="delete-theme-submit" type="submit" value="<?php echo osc_esc_html( __('Delete') ); ?>" class="btn btn-submit" />
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-delete-theme').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</form>

<script type="text/javascript">
  $(function() {
    $( "#tabs" ).tabs({ active: -1 });

    $("#market_cancel").on("click", function(){
      $(".ui-dialog-content").dialog("close");
      return false;
    });


    $("#market_install").on("click", function(){
      $(".ui-dialog-content").dialog("close");
      $('<div id="downloading"><div class="osc-modal-content"><img class="ui-download-loading" src="<?php echo osc_current_admin_theme_url(); ?>images/spinner.gif" alt="loading..."/><?php echo osc_esc_js(__('Please wait until the download is completed')); ?></div></div>').dialog({title:'<?php echo osc_esc_js(__('Downloading')); ?>...',modal:true});
      $.getJSON(
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>",
      {"market_product_key" : $("#market_product_key").attr("value"), "section" : 'themes'},
      function(data){
        var content = '';

        if(data.error == 0) { // no errors
          content += oscEscapeHTML(data.message);
          content += '<h3><?php echo osc_esc_js(__('Theme has been downloaded correctly.')); ?></h3>';
          content += "<p>";
          content += '<a class="btn btn-mini btn-green" href="<?php echo osc_admin_base_url(true); ?>?page=appearance&marketError='+data.error+'&message='+oscEscapeHTML(data.message)+'&slug='+oscEscapeHTML(data.data['download'])+'"><?php echo osc_esc_js(__('Ok')); ?></a>';
          content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
          content += "</p>";
        } else {
          content += '<p>' + oscEscapeHTML(data.message) + '</p><p>&nbsp;</p>';
          content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
        }
        $("#downloading .osc-modal-content").html(content);
      });
      return false;
    });
  });


  $('.market-popup').on('click',function(){
    $.getJSON(
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=check_market",
      {"code" : $(this).attr('href').replace('#',''), 'section' : 'product'},
      function(data){
        if(data!=null) {
          $("#market_product_key").attr("value", data.product_key);
          $("#market_name").text(data.name);
          $("#market_version").text(data.s_version);
          $("#market_changes").text(data.s_comment);
          $("#market_osclass_version").text(data.s_osc_version_from + ' - ' + data.s_osc_version_to);
          $("#market_url").attr('href',data.link);
          $('#market_install').text("<?php echo osc_esc_js( __('Update') ); ?>");


          var dialogWidth = 485;
          
          if($(window).width() < 525) {
            dialogWidth = $(window).width() - 40;
          }
    
          $('#market_installer').dialog({
            modal:true,
            title: '<?php echo osc_esc_js( __('Update theme from OsclassPoint') ); ?>',
            width: dialogWidth
          });
        }
      }
    );

    return false;
  });
</script>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>
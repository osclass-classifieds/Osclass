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


function customPageHeader(){ 
  ?>
  <h1><?php _e('Tools'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return __('Upgrade');
}

osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead(){ ?>
  <script type="text/javascript">
    $(document).ready(function(){
      if (typeof $.uniform != 'undefined') {
        $('textarea, button,select, input:file').uniform();
      }

      <?php if(Params::getParam('confirm')=='true') {?>
        $('#output').show();
        $('.tohide').hide();

        $.ajax({
          url: '<?php echo osc_admin_base_url(true); ?>?page=upgrade&action=upgrade-funcs',
          type: "GET",
          success: function(data){
            $('.upgr-load-box').hide();
            $('#result').append(data+"<br/>");
          },
          error: function(jqXHR, textStatus, errorThrown){
            console.log(textStatus);
            console.log(jqXHR);
           
            $('.upgr-load-box').hide();
            $('#result').append("<?php echo osc_esc_html(sprintf(__('There was problem with upgrade, %s has resulted with error.'), osc_admin_base_url(true) . '?page=upgrade&action=upgrade-funcs')); ?>");
          }
        });
      <?php } ?>
    });
  </script>
<?php }

osc_add_hook('admin_header','customHead', 10);

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<div id="backup-settings">
  <h2 class="render-title"><?php _e('Upgrade'); ?></h2>
  <div id="result">
    <div id="output" style="display:none">
      <p class="upgr-load-box">
        <img id="loading_immage" src="<?php echo osc_current_admin_theme_url('images/loader2.gif'); ?>" title="" alt="" />
        <span><?php _e('Upgrading your Osclass installation (this could take a while)', 'admin'); ?></span>
      </p>
    </div>
    
    <div id="tohide" class="tohide">
      <p><?php _e('You have uploaded a new version of Osclass, you need to upgrade Osclass for it to work correctly.'); ?></p>
      
      <?php if(defined('DEMO')) { ?>
        <p><strong><?php _e('This action cannot be done because it is a demo site. Disable demo mode in config.php to continue with upgrade.'); ?></strong></p>
        <a class="btn btn-submit upgrade-now-btn disabled" disabled href="#" onclick="return false;"><?php _e('Upgrade now'); ?></a>

      <?php } else { ?>
        <a class="btn btn-submit upgrade-now-btn" href="<?php echo osc_admin_base_url(true); ?>?page=upgrade&confirm=true"><?php _e('Upgrade now'); ?></a>
      <?php } ?>
    </div>
  </div>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>
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


$maintenance = file_exists( osc_base_path() . '.maintenance');

function render_offset(){
  return 'row-offset';
}

function addHelp() {
  echo '<p>' . __('Show a "Site in maintenance mode" message to your users while you\'re updating your site or modifying its configuration.') . '</p>';
}
osc_add_hook('help_box','addHelp');

osc_add_hook('admin_page_header','customPageHeader');
function customPageHeader(){ ?>
  <h1><?php _e('Tools'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
<?php
}

function customPageTitle($string) {
  return sprintf(__('Maintenance - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<div id="backup-setting">
  <!-- settings form -->
  <div id="backup-settings">
    <h2 class="render-title"><?php _e('Maintenance'); ?></h2>
    <form>
      <fieldset>
        <div class="form-horizontal">
          <div class="form-row">
            <?php _e("While in maintenance mode, users can't access your website. Useful if you need to make changes on your website. Use the following button to toggle maintenance mode ON/OFF."); ?>
            <div class="help-box">
              <?php printf( __('Maintenance mode is: <strong>%s</strong>'), ($maintenance ? __('ON') : __('OFF') ) ); ?>
            </div>
          </div>
          <div class="form-actions no-padding">
            <input type="button" value="<?php echo ( $maintenance ? osc_esc_html( __('Disable maintenance mode') ) : osc_esc_html( __('Enable maintenance mode') ) ); ?>" onclick="window.location.href='<?php echo osc_admin_base_url(true); ?>?page=tools&amp;action=maintenance&amp;mode=<?php echo ( $maintenance ? 'off' : 'on' ) . "&amp;" . osc_csrf_token_url(); ?>';" class="btn btn-submit" />
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>
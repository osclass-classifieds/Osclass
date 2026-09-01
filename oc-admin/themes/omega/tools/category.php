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


function render_offset(){
  return 'row-offset';
}


function customPageHeader(){ 
  ?>
  <h1><?php _e('Tools'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Category stats - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<div id="backup-setting">
  <!-- settings form -->
  <div id="backup-settings">
    <h2 class="render-title"><?php _e('Category stats'); ?></h2>
    <p><?php _e('You can recalculate stats by category, useful if the stats seem to be incorrect.'); ?>.</p>
    <form id="backup_form" name="backup_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
      <input type="hidden" name="page" value="tools" />
      <input type="hidden" name="action" value="category_post" />
      <fieldset>
        <div class="form-horizontal">
          <div class="form-actions no-padding">
            <input type="submit" id="backup_save" value="<?php echo osc_esc_html( __('Calculate category stats')); ?>" class="btn btn-submit" />
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>
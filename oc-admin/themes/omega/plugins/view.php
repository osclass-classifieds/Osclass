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

$file = __get('file');
osc_add_hook('admin_page_header','customPageHeader');
function customPageHeader() { 
  ?>
  <h1><?php echo osc_apply_filter('custom_plugin_title',__('Plugins')); ?></h1>
  <?php
}


function customPageTitle($string) {
  return sprintf(__('Plugins - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<!-- theme files -->
<div class="theme-files">
  <?php
    if(strpos($file, '../') === false && strpos($file, '..\\')==false && file_exists($file)) {
      require_once $file;
    } else {
      ?>
        <div class="flashmessage flashmessage-error flashmessage-inline">
          <div class="row" style="margin-bottom:5px;"><?php _e('Plugin page does not exists or is incorrect. Discuss following path with plugin author:'); ?></div>
          <div class="row"><strong><?php echo str_replace(osc_base_path(), '', $file); ?></strong></div>
          <div class="row">&nbsp;</div>
          <div class="row"><?php echo sprintf(__('If you see something extra before "%s", disucuss default path settings on server with your hosting provider. Expected form of file path.'), OC_CONTENT_FOLDER); ?></div>
          <div class="row"><strong><?php echo OC_CONTENT_FOLDER; ?>/plugins/{plugin_name}/{admin_dir}/{file_name}.php</strong></div>
        </div>
      <?php
    }
  ?>
</div>
<!-- /theme files -->
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>
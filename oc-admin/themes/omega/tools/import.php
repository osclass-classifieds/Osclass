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


function addHelp() {
  echo '<p>' . __("Upload registers from other Osclass installations or upload new geographic information to your site. <strong>Be careful</strong>: don’t use this option if you're not 100% sure what you're doing.") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Tools'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Import - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<!-- settings form -->
<div id="backup-settings">
  <div class="flashmessage flashmessage-info">
    <p class="info">
      <?php _e('Upload custom SQL file, where you can upload new data, update database structure, create or drop tables and keys.'); ?><br/>
      <?php echo sprintf(__('You may use variables %s and %s for table prefix and default language code.'), '/*TABLE_PREFIX*/', '/*LOCALE_CODE*/'); ?>
    </p>
  </div>
  
  
  <h2 class="render-title"><?php _e('Import SQL data'); ?></h2>
  <form id="backup_form" name="backup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="import_post" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <div class="form-label"><?php _e('Select file (.sql)'); ?></div>
          <div class="form-controls">
           <input type="file" name="sql" id="sql" />
          </div>
        </div>
        
        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html(__('Import data')); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>
<!-- /settings form -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
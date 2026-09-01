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
  <h1><?php _e('Settings'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Add language - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); 
?>

<div class="appearance">
  <h2 class="render-title"><?php _e('Add language'); ?></h2>
  <div id="upload-language">
    <div class="form-horizontal">
    <?php if( is_writable( osc_translations_path() ) ) { ?>
      <div class="flashmessage flashmessage-info flashmessage-inline" style="display:block;">
        <p class="info"><?php printf( __('You can import new languages and translations directly in %s section.'), '<a href="' . osc_admin_base_url(true) . '?page=market&action=languages" target="_blank">' . __('Market > Languages') . '</a>'); ?></p>
      </div>
      <form class="" action="<?php echo osc_admin_base_url(true); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_post" />
        <input type="hidden" name="page" value="languages" />

        <div class="form-row">
          <div class="form-label"> <?php _e('Language package (.zip)'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox"><input type="file" name="package" id="package" /></div>
          </div>
        </div>
        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Upload') ); ?>" class="btn btn-submit" />
        </div>
      </form>
    <?php } else { ?>
      <div class="flashmessage flashmessage-error">
        <a class="btn ico btn-mini ico-close" href="#">×</a>
        <p><?php _e("Can't install a new language"); ?></p>
      </div>
      <p class="text">
        <?php _e("The translations folder is not writable on your server so you can't upload translations from the administration panel. Please make the translation folder writable and try again."); ?>
      </p>
      <p class="text">
        <?php _e('To make the directory writable under UNIX execute this command from the shell:'); ?>
      </p>
      <pre>chmod a+w <?php echo osc_translations_path(); ?></pre>
    <?php } ?>
    </div>
  </div>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
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
function customPageHeader() { ?>
  <h1><?php echo osc_apply_filter('custom_appearance_title', __('Appearance')); ?></h1>
<?php
}

function customPageTitle($string) {
  return sprintf(__('Appearance - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<!-- theme files -->
<div class="theme-files">
  <?php
    if(strpos($file, '../')===false && strpos($file, '..\\')==false && file_exists($file)) {
      require_once $file;
    }
  ?>
</div>
<!-- /theme files -->
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>
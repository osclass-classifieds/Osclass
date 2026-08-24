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


osc_enqueue_script('jquery-treeview');

$categories  = __get('categories');
$selected  = __get('selected');
$plugin_data = __get('plugin_data');

osc_add_hook('admin_page_header','customPageHeader');
function customPageHeader() { ?>
  <h1><?php echo osc_apply_filter('custom_plugin_title', __('Plugins')); ?></h1>
<?php
}

//customize Head
function customHead() { 
  ?>
  <script type="text/javascript">
    // check all the categories
    function checkAll(id, check) {
      aa = $('#' + id + ' input[type=checkbox]').each(function() {
        $(this).prop('checked', check);
      });
    }

    function checkCat(id, check) {
      aa = $('#cat' + id + ' input[type=checkbox]').each(function() {
        $(this).prop('checked', check);
      });
    }

    $(document).ready(function(){
      $("#plugin_tree").treeview({
        animated: "fast",
        collapsed: true
      });
    });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function customPageTitle($string) {
  return sprintf(__('Plugins - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<!-- plugin configuration -->
<div class="plugin-configuration form-horizontal">
  <form id="plugin-frm" action="<?php echo osc_admin_base_url(true); ?>?page=plugins" method="post">
    <input type="hidden" name="action" value="configure_post" />
    <input type="hidden" name="plugin" value="<?php echo $plugin_data['filename']; ?>" />
    <input type="hidden" name="plugin_short_name" value="<?php echo $plugin_data['short_name']; ?>" />
    <fieldset>
      <h2 class="render-title"><?php  echo $plugin_data['plugin_name']; ?></h2>
      <p class="text"><?php echo $plugin_data['description']; ?></p>
      <div class="form-row">
        <div><?php _e('Select the categories where you want to apply these attribute:'); ?></div>
        <div class="separate-top">
          <div class="form-label">
            <a href="javascript:void(0);" onclick="checkAll('plugin_tree', true); return false;"><?php _e('Check all'); ?></a> &middot;
            <a href="javascript:void(0);" onclick="checkAll('plugin_tree', false); return false;"><?php _e('Uncheck all'); ?></a>
          </div>
          <div class="form-controls">
            <ul id="plugin_tree">
              <?php CategoryForm::categories_tree($categories, $selected); ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="form-actions">
        <input type="submit" id="plugin-configuration-submit" value="<?php echo osc_esc_html(__('Update')); ?>" class="btn btn-submit" />
      </div>
    </fieldset>
  </form>
</div>
<!-- /theme files -->
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>
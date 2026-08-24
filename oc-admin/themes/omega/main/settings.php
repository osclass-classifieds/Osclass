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


function addHelp() {
  echo '<p>' . __("Manage backoffice home page widgets") . '</p>';
}

osc_add_hook('help_box','addHelp');


osc_add_hook('admin_page_header','customPageHeader');
function customPageHeader(){ 
  ?>
  <h1><?php _e('Backoffice widget settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

function customPageTitle($string) {
  return sprintf(__('Backoffice Widget Settings - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');

$cols_hidden = explode(',', osc_get_preference('admindash_columns_hidden', 'osclass')); 
$widgets_hidden = explode(',', osc_get_preference('admindash_widgets_hidden', 'osclass')); 

$col1_widgets = array('glance','api','items','comments','users','links','banrules');
$col2_widgets = array('chart-items','chart-users','chart-comments','items-category');
$col3_widgets = array('blog','update','products','product-updates');
?>

<form action="<?php echo osc_admin_base_url(true); ?>" method="post">
  <input type="hidden" name="page" value="main" />
  <input type="hidden" name="action" value="settings_post" />
  <fieldset>
    <div class="form-horizontal">
      <?php osc_run_hook('admin_dashboard_setting_top'); ?>

      <h2 class="render-title"><?php echo sprintf(__('Column #%s setup'), 1); ?></h2>

      <!-- Column #1 setup -->
      <div class="form-row">
        <div class="form-label"><?php _e('Column'); ?></div>
        <div class="form-controls">
          <label id="col_1" class="form-label-checkbox">
            <input type="checkbox" id="col_1" name="col_1" <?php echo (in_array(1, $cols_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php _e('Hide this column'); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Widgets'); ?></div>
        <div class="form-controls">
          <label id="widget_glance" class="form-label-checkbox">
            <input type="checkbox" id="widget_glance" name="widget_glance" <?php echo (in_array('glance', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('At a glance')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_optimization" class="form-label-checkbox">
            <input type="checkbox" id="widget_optimization" name="widget_optimization" <?php echo (in_array('optimization', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Optimization')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_api" class="form-label-checkbox">
            <input type="checkbox" id="widget_api" name="widget_api" <?php echo (in_array('api', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Market accessibility')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_items" class="form-label-checkbox">
            <input type="checkbox" id="widget_items" name="widget_items" <?php echo (in_array('items', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Listings activity')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_comments" class="form-label-checkbox">
            <input type="checkbox" id="widget_comments" name="widget_comments" <?php echo (in_array('comments', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Comments activity')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_users" class="form-label-checkbox">
            <input type="checkbox" id="widget_users" name="widget_users" <?php echo (in_array('users', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Users activity')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_links" class="form-label-checkbox">
            <input type="checkbox" id="widget_links" name="widget_links" <?php echo (in_array('links', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Useful links')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_banrules" class="form-label-checkbox">
            <input type="checkbox" id="widget_banrules" name="widget_banrules" <?php echo (in_array('banrules', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Ban Rules')); ?>
          </label> 
        </div>
      </div>
      
      <?php osc_run_hook('admin_dashboard_setting_col1'); ?>
      
      
      <!-- Column #2 setup -->
      <h2 class="render-title separate-top"><?php echo sprintf(__('Column #%s setup'), 2); ?></h2>

      <div class="form-row">
        <div class="form-label"><?php _e('Column'); ?></div>
        <div class="form-controls">
          <label id="col_2" class="form-label-checkbox">
            <input type="checkbox" id="col_2" name="col_2" <?php echo (in_array(2, $cols_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php _e('Hide this column'); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Widgets'); ?></div>
        <div class="form-controls">
          <label id="widget_chart-items" class="form-label-checkbox">
            <input type="checkbox" id="widget_chart-items" name="widget_chart-items" <?php echo (in_array('chart-items', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Listing statistics')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_chart-comments" class="form-label-checkbox">
            <input type="checkbox" id="widget_chart-comments" name="widget_chart-comments" <?php echo (in_array('chart-comments', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Comments statistics')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_chart-users" class="form-label-checkbox">
            <input type="checkbox" id="widget_chart-users" name="widget_chart-users" <?php echo (in_array('chart-users', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('User statistics')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_items-category" class="form-label-checkbox">
            <input type="checkbox" id="widget_items-category" name="widget_items-category" <?php echo (in_array('items-category', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Listings by category')); ?>
          </label> 
        </div>
      </div>
      
      <?php osc_run_hook('admin_dashboard_setting_col2'); ?>
      
      
      
      <!-- Column #3 setup -->
      <h2 class="render-title separate-top"><?php echo sprintf(__('Column #%s setup'), 3); ?></h2>

      <div class="form-row">
        <div class="form-label"><?php _e('Column'); ?></div>
        <div class="form-controls">
          <label id="col_3" class="form-label-checkbox">
            <input type="checkbox" id="col_3" name="col_3" <?php echo (in_array(3, $cols_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php _e('Hide this column'); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Widgets'); ?></div>
        <div class="form-controls">
          <label id="widget_blog" class="form-label-checkbox">
            <input type="checkbox" id="widget_blog" name="widget_blog" <?php echo (in_array('blog', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('News on blog')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_update" class="form-label-checkbox">
            <input type="checkbox" id="widget_update" name="widget_update" <?php echo (in_array('update', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Osclass update')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_products" class="form-label-checkbox">
            <input type="checkbox" id="widget_products" name="widget_products" <?php echo (in_array('products', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Latest products')); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank">&nbsp;</div>
        <div class="form-controls">
          <label id="widget_product-updates" class="form-label-checkbox">
            <input type="checkbox" id="widget_product-updates" name="widget_product-updates" <?php echo (in_array('product-updates', $widgets_hidden) ? 'checked="checked"' : '' ); ?> value="1" />
            <?php echo sprintf(__('Hide "%s" widget'), __('Product updates')); ?>
          </label> 
        </div>
      </div>
      
      <?php osc_run_hook('admin_dashboard_setting_col3'); ?>
      
      
      <div class="form-actions">
        <input type="submit" id="save_changes" value="<?php echo osc_esc_html( __('Save changes') ); ?>" class="btn btn-submit" />
      </div>
    </div>
  </fieldset>
</form>

<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>
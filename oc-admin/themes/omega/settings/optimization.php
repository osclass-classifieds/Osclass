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

//customize Head
function customHead() {}
osc_add_hook('admin_header','customHead', 10);


function addHelp() {
  echo '<p>' . __('Optimize your site by merging and compressing CSS and JavaScript, and schedule database optimization via cron.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Optimization settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' );
$optimizationFilesStats = osc_optimization_files_stats();
?>

<div id="general-settings">
  <ul id="error_list"></ul>
  <form name="optimization_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="settings" />
    <input type="hidden" name="action" value="optimization_post" />
    <fieldset>
      <div class="form-horizontal">
        <div class="flashmessage flashmessage-info">
          <?php echo sprintf(__('Your optimization folder has %d files in total size %s. Remove optimization files if you made changes in your CSS or JavaScript files to reflect these changes.'), (int)$optimizationFilesStats['count'], osc_esc_html($optimizationFilesStats['size_formatted'])); ?>
        </div>

        <h2 class="render-title"><?php _e('Optimization settings'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Automatic cleanup'); ?></div>
          <div class="form-controls">
            <select name="optimization_cleanup_frequency">
              <option value="none" <?php echo (osc_optimization_cleanup_frequency() == 'none' ? 'selected="selected"' : ''); ?>><?php _e('None'); ?></option>
              <option value="weekly" <?php echo (osc_optimization_cleanup_frequency() == 'weekly' ? 'selected="selected"' : ''); ?>><?php _e('Weekly'); ?></option>
              <option value="monthly" <?php echo (osc_optimization_cleanup_frequency() == 'monthly' ? 'selected="selected"' : ''); ?>><?php _e('Monthly'); ?></option>
            </select>
            <span class="help-box"><?php _e('Optimized CSS & JS files can be cleaned automatically using cron.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('CSS Stylesheets'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo ( osc_css_merge() ? 'checked="checked"' : '' ); ?> name="css_merge" value="1" />
                <?php _e('Merge internal CSS style sheets into one'); ?>
              </label>
              <span class="help-box"><?php _e('External stylesheets are not merged.'); ?></span>
            </div>

            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo ( osc_css_minify() ? 'checked="checked"' : '' ); ?> name="css_minify" value="1" />
                <?php _e('Minify/optimize merged CSS style sheet'); ?>
              </label>
              <span class="help-box"><?php _e('Comments and redundant whitespaces will be removed'); ?></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('CSS Exclude words'); ?></div>
          <div class="form-controls">
            <input type="text" name="css_banned_words" class="xlarge" value="<?php echo osc_esc_html(osc_css_banned_words()); ?>" />
            <span class="help-box"><?php _e('Banned keywords, in case CSS contains this word in link, will be excluded from optimization.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('CSS Exclude pages'); ?></div>
          <div class="form-controls">
            <input type="text" name="css_banned_pages" class="xlarge" value="<?php echo osc_esc_html(osc_css_banned_pages()); ?>" />
            <span class="help-box"><?php _e('Exclude optimization of CSS style sheets on particular pages. Example: home, search, item. Optimized scripts are printed before excluded, take it into consideration regarding script dependencies.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('JS scripts'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo ( osc_js_merge() ? 'checked="checked"' : '' ); ?> name="js_merge" value="1" />
                <?php _e('Merge internal JS scripts into one'); ?>
              </label>
              <span class="help-box"><?php _e('External scripts are not merged.'); ?></span>
            </div>

            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo ( osc_js_minify() ? 'checked="checked"' : '' ); ?> name="js_minify" value="1" />
                <?php _e('Minify/optimize merged JS scripts'); ?>
              </label>
              <span class="help-box"><?php _e('Comments and redundant whitespaces will be removed'); ?></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('JS Exclude words'); ?></div>
          <div class="form-controls">
            <input type="text" name="js_banned_words" class="xlarge" value="<?php echo osc_esc_html(osc_js_banned_words()); ?>" />
            <span class="help-box"><?php _e('Banned keywords, in case JS script contains this word in link, will be excluded from optimization.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('JS Exclude pages'); ?></div>
          <div class="form-controls">
            <input type="text" name="js_banned_pages" class="xlarge" value="<?php echo osc_esc_html(osc_js_banned_pages()); ?>" />
            <span class="help-box"><?php _e('Exclude optimization of JS scripts on particular pages. Example: home, search, item'); ?></span>
          </div>
        </div>

        <div class="form-actions">
          <input type="submit" id="save_changes" value="<?php echo osc_esc_html( __('Save changes') ); ?>" class="btn btn-submit" />
          <a class="btn" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=optimization_clean'; ?>"><?php echo sprintf(__('Remove optimization files (%s)'), osc_esc_html($optimizationFilesStats['size_formatted'])); ?></a>
        </div>
      </div>
    </fieldset>
  </form>
</div>

<div id="database-settings" style="margin-top:30px;">
  <ul id="database_error_list"></ul>
  <form name="database_optimization_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="settings" />
    <input type="hidden" name="action" value="optimization_database_post" />
    <fieldset>
      <div class="form-horizontal">
        <h2 class="render-title separate-top"><?php _e('Database optimization'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Automatic optimization'); ?></div>
          <div class="form-controls">
            <select name="database_optimization_frequency">
              <option value="none" <?php echo (osc_database_optimization_frequency() == 'none' ? 'selected="selected"' : ''); ?>><?php _e('None'); ?></option>
              <option value="daily" <?php echo (osc_database_optimization_frequency() == 'daily' ? 'selected="selected"' : ''); ?>><?php _e('Daily'); ?></option>
              <option value="weekly" <?php echo (osc_database_optimization_frequency() == 'weekly' ? 'selected="selected"' : ''); ?>><?php _e('Weekly'); ?></option>
              <option value="monthly" <?php echo (osc_database_optimization_frequency() == 'monthly' ? 'selected="selected"' : ''); ?>><?php _e('Monthly'); ?></option>
            </select>
            <span class="help-box"><?php _e('Database optimization runs using cron and only processes Osclass tables matching database table prefix. On shared hosting, weekly or monthly schedule is recommended.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Optimization actions'); ?></div>
          <div class="form-controls">
            <?php $dbOperations = osc_database_optimization_operations_array(); ?>
            <select name="database_optimization_operations[]" multiple="multiple" style="min-width:360px;height:115px;">
              <option value="check" <?php echo (in_array('check', $dbOperations, true) ? 'selected="selected"' : ''); ?>><?php _e('Check tables'); ?></option>
              <option value="analyze" <?php echo (in_array('analyze', $dbOperations, true) ? 'selected="selected"' : ''); ?>><?php _e('Analyze tables'); ?></option>
              <option value="optimize" <?php echo (in_array('optimize', $dbOperations, true) ? 'selected="selected"' : ''); ?>><?php _e('Optimize tables'); ?></option>
              <option value="flush" <?php echo (in_array('flush', $dbOperations, true) ? 'selected="selected"' : ''); ?>><?php _e('Flush tables (requires extra privileges)'); ?></option>
            </select>
            <span class="help-box"><?php _e('Hold Ctrl/Cmd to select multiple actions. Check, analyze and optimize work on MySQL 5.7+ and MariaDB. Flush tables is often blocked on shared hosting and will be reported if it fails.'); ?></span>
          </div>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Save changes') ); ?>" class="btn btn-submit" />
          <a class="btn" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=optimization_database_now&' . osc_csrf_token_url(); ?>"><?php _e('Run optimization now'); ?></a>
        </div>
      </div>
    </fieldset>
  </form>
</div>

<?php osc_current_admin_theme_path('parts/footer.php');

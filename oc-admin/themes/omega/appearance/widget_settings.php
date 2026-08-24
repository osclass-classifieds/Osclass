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
  echo '<p>' . __('These options apply to all widgets.') . '</p>';
  echo '<p><strong>' . __('Locale-strict') . '</strong></p>';
  echo '<p>' . __('When enabled, a widget shows HTML content only for the current locale. If that locale has no text, content is empty. Code is not localized and still prints when it is filled.') . '</p>';
  echo '<p>' . __('When disabled, missing locale text falls back to the site language, then to the first locale that has content. Single-locale widgets keep one content field for every language.') . '</p>';
  echo '<p><strong>' . __('Custom sections') . '</strong></p>';
  echo '<p>' . __('Separated by commas. One slug per line is also accepted (letters, numbers, hyphen, underscore). Extra spaces are ignored. Theme sections extracted from the active theme are listed under Custom sections and do not need to be added here.') . '</p>';
  echo '<p>' . sprintf(__('After you add a slug (for example xyz), create widgets for that section and print them in the theme with %s. You can also type a new slug on the add-widget form.'), '<code>&lt;?php osc_widget(\'xyz\'); ?&gt;</code>') . '</p>';
}
osc_add_hook('help_box','addHelp');


function customPageHeader() {
  ?>
  <h1><?php _e('Appearance'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Widget settings - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

$custom_sections = __get('custom_sections');
$theme_sections = __get('theme_sections');
$info = __get('info');
$theme_name = (is_array($info) && isset($info['name']) && $info['name'] != '') ? $info['name'] : osc_theme();

osc_current_admin_theme_path('parts/header.php');
?>

<h2 class="render-title"><?php _e('Widget settings'); ?></h2>
<form action="<?php echo osc_admin_base_url(true); ?>" method="post">
  <input type="hidden" name="page" value="appearance" />
  <input type="hidden" name="action" value="widgets_settings" />
  <fieldset>
    <div class="form-horizontal">
      <div class="form-row">
        <div class="form-label"><?php _e('Locale-strict'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox">
            <label>
              <input type="checkbox" name="widget_locale_strict" value="1" <?php if(osc_widget_locale_strict()) { echo 'checked="checked"'; } ?> />
              <?php _e('Show content only in the current locale. If that locale has no content, the widget content is empty. Code is not localized.'); ?>
            </label>
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-label"><?php _e('Custom sections'); ?></div>
        <div class="form-controls">
          <textarea name="widget_custom_sections" rows="6" class="xlarge"><?php echo osc_esc_html(implode(', ', (is_array($custom_sections) ? $custom_sections : array()))); ?></textarea>
          <span class="help-box"><?php _e('Separated by commas. One per line is also accepted (letters, numbers, hyphen, underscore). Extra spaces are ignored. Use in the theme as:'); ?> <code>&lt;?php osc_widget('xyz'); ?&gt;</code></span>
          <?php if(is_array($theme_sections) && count($theme_sections) > 0) {
            $theme_section_html = array();
            foreach($theme_sections as $section) {
              $theme_section_html[] = '<code>' . osc_esc_html($section) . '</code>';
            }
            ?>
            <span class="help-box"><?php echo sprintf(__('Theme sections extracted from %s: %s. They are already available and do not need to be listed here.'), osc_esc_html($theme_name), implode(', ', $theme_section_html)); ?></span>
          <?php } else { ?>
            <span class="help-box"><?php echo sprintf(__('The active theme (%s) does not declare widget sections.'), osc_esc_html($theme_name)); ?></span>
          <?php } ?>
        </div>
      </div>
      <div class="form-actions">
        <input type="submit" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
      </div>
    </div>
  </fieldset>
</form>
<?php osc_current_admin_theme_path('parts/footer.php');

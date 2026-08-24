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

function render_offset() {
  return 'row-offset';
}
osc_add_filter('render-wrapper', 'render_offset');

function addHelp() {
  echo '<p>' . __('Choose which listing statistics are collected and how charts are shown to sellers.') . '</p>';
  echo '<p>' . __('Essential covers views, premium views, phone clicks, engaged views, logged-in views, minutes viewed, alert emails, contact forms and reports. Keep user charts to 4 to 6 measures so they stay readable.') . '</p>';
}
osc_add_hook('help_box', 'addHelp');

function customHead() {
  ?>
  <script type="text/javascript">
  $(document).ready(function() {
    var presets = <?php echo json_encode(array(
      'essential' => osc_item_stats_preset_keys('essential'),
      'engagement' => osc_item_stats_preset_keys('engagement'),
      'commerce' => osc_item_stats_preset_keys('commerce'),
      'full' => osc_item_stats_preset_keys('full')
    )); ?>;
    $('.osc-stats-preset').on('click', function(e) {
      e.preventDefault();
      var key = $(this).data('preset');
      var list = presets[key] || [];
      $('#item_stats_preset').val(key);
      $('.osc-stats-measure').each(function() {
        $(this).prop('checked', $.inArray($(this).val(), list) !== -1);
      });
    });
    $('.osc-stats-measure').on('change', function() {
      $('#item_stats_preset').val('custom');
    });
  });
  </script>
  <?php
  osc_run_hook('admin_stats_header', 'settings');
}
osc_add_hook('admin_header', 'customHead', 10);

function customPageHeader() {
  ?>
  <h1><?php _e('Statistics'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}
osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Statistics settings - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');

$all = osc_item_stats_measures(false);
$admin_measures = osc_item_stats_parse_csv(osc_item_stats_admin_default_measures());
$user_measures = osc_item_stats_parse_csv(osc_item_stats_user_chart_measures());
$item_chart_measures = osc_item_stats_parse_csv(osc_item_stats_item_chart_measures());
$user_period_keys = osc_stats_period_keys('front');
$item_period_keys = osc_stats_period_keys('item');
$period_catalog = osc_stats_period_all_keys();
$enabled_keys = osc_item_stats_enabled_keys();
$chart_keys = osc_item_stats_chart_allowed_keys();
$groups = osc_item_stats_groups();
$by_group = array();
foreach($all as $row) {
  if(isset($row['source']) && $row['source'] == 'item') {
    continue;
  }
  $gid = (isset($row['group']) ? $row['group'] : 'custom');
  $by_group[$gid][] = $row;
  if(!isset($groups[$gid])) {
    $groups[$gid] = $gid;
  }
}
?>
<div id="general-settings">
  <form name="stats_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="stats" />
    <input type="hidden" name="action" value="settings_post" />
    <fieldset>
      <div class="form-horizontal">
        <h2 class="render-title"><?php _e('Statistics settings'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Collect views'); ?></div>
          <div class="form-controls">
            <select name="item_stats_method">
              <option value="SESSION" <?php if(osc_item_stats_method() == 'SESSION') { ?>selected="selected"<?php } ?>><?php _e('One view per session'); ?></option>
              <option value="PAGELOAD" <?php if(osc_item_stats_method() == 'PAGELOAD') { ?>selected="selected"<?php } ?>><?php _e('One view per page load'); ?></option>
            </select>
            <div class="help-box"><?php _e('Applies to page views and premium views. Other impression measures always count once per session.'); ?></div>
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" name="item_stats_logged_only" value="1" <?php if(osc_item_stats_logged_only()) { echo 'checked="checked"'; } ?> />
                <?php _e('Collect from logged-in visitors only'); ?>
              </label>
            </div>
            <div class="help-box"><?php _e('Does not apply to renewals, promotions or alert emails.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Presets'); ?></div>
          <div class="form-controls">
            <div class="osc-stats-presets">
              <a href="#" class="btn btn-mini osc-stats-preset" data-preset="essential"><?php _e('Essential'); ?></a>
              <a href="#" class="btn btn-mini osc-stats-preset" data-preset="engagement"><?php _e('Engagement'); ?></a>
              <a href="#" class="btn btn-mini osc-stats-preset" data-preset="commerce"><?php _e('Commerce'); ?></a>
              <a href="#" class="btn btn-mini osc-stats-preset" data-preset="full"><?php _e('Full'); ?></a>
            </div>
            <input type="hidden" name="item_stats_preset" id="item_stats_preset" value="<?php echo osc_esc_html(osc_item_stats_preset()); ?>" />
            <div class="help-box"><?php _e('Presets tick the collect checkboxes below. Change a checkbox to switch to a custom set.'); ?></div>
          </div>
        </div>

        <?php foreach($groups as $gid => $glabel) {
          if(empty($by_group[$gid])) {
            continue;
          }
        ?>
        <div class="form-row stats-settings-checks">
          <div class="form-label"><?php echo osc_esc_html($glabel); ?></div>
          <div class="form-controls">
            <?php foreach($by_group[$gid] as $row) { ?>
              <div class="form-label-checkbox">
                <label title="<?php echo osc_esc_html($row['help']); ?>">
                  <input type="checkbox" class="osc-stats-measure" name="item_stats_enabled[]" value="<?php echo osc_esc_html($row['key']); ?>"<?php if(in_array($row['key'], $enabled_keys, true)) { echo ' checked="checked"'; } ?> />
                  <?php echo osc_esc_html($row['label']); ?>
                </label>
              </div>
            <?php } ?>
          </div>
        </div>
        <?php } ?>

        <div class="form-row">
          <div class="form-label"><?php _e('Engaged view after'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-small" name="item_stats_engaged_seconds" value="<?php echo osc_esc_html((string)osc_item_stats_engaged_seconds()); ?>" />
            <div class="inpt-desc"><?php _e('seconds'); ?></div>
            <div class="help-box"><?php _e('Minimum 5, maximum 600. The visitor must keep the listing tab visible.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Phone click selectors'); ?></div>
          <div class="form-controls">
            <textarea name="item_stats_phone_selectors" rows="2" class="input-large"><?php echo osc_esc_html(osc_item_stats_phone_selectors()); ?></textarea>
            <div class="help-box"><?php _e('Comma-separated CSS selectors for the phone control. Add .mobile only if your theme uses that class on the phone control.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Other contact click selectors'); ?></div>
          <div class="form-controls">
            <textarea name="item_stats_contactother_selectors" rows="2" class="input-large"><?php echo osc_esc_html(osc_item_stats_contactother_selectors()); ?></textarea>
            <div class="help-box"><?php _e('Comma-separated CSS selectors for the other-contact field (WhatsApp, Viber or similar).'); ?></div>
          </div>
        </div>

        <h2 class="render-title separate-top"><?php _e('Admin Statistics'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Default period'); ?></div>
          <div class="form-controls">
            <select name="item_stats_admin_default_period">
              <?php foreach(osc_stats_period_keys('admin') as $key) { ?>
                <option value="<?php echo osc_esc_html($key); ?>" <?php if(osc_item_stats_admin_default_period() == $key) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html(osc_stats_period_label($key)); ?></option>
              <?php } ?>
            </select>
            <div class="help-box"><?php _e('Used on a first visit to admin Statistics pages.'); ?></div>
          </div>
        </div>

        <div class="form-row stats-settings-checks">
          <div class="form-label"><?php _e('Listing details measures'); ?></div>
          <div class="form-controls">
            <?php foreach($all as $row) {
              if(!in_array($row['key'], $chart_keys, true)) {
                continue;
              }
            ?>
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" name="item_stats_admin_default_measures[]" value="<?php echo osc_esc_html($row['key']); ?>" <?php if(in_array($row['key'], $admin_measures, true)) { echo 'checked="checked"'; } ?> />
                  <?php echo osc_esc_html($row['label']); ?>
                </label>
              </div>
            <?php } ?>
            <div class="help-box"><?php _e('Used on Listing details, Statistics Overview (Listing views) and the dashboard Listing views widget. Only collected measures are listed, plus listings published and listings live. Use 4 to 6 so the chart stays readable.'); ?></div>
          </div>
        </div>

        <h2 class="render-title separate-top"><?php _e('User summary chart'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('My listings'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" name="item_stats_user_chart_enabled" value="1" <?php if(osc_item_stats_user_chart_enabled()) { echo 'checked="checked"'; } ?> />
                <?php _e('Show the statistics block on My listings'); ?>
              </label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Show charts to'); ?></div>
          <div class="form-controls">
            <select name="item_stats_chart_audience">
              <?php foreach(osc_item_stats_chart_audience_options() as $aud_key => $aud_label) { ?>
                <option value="<?php echo osc_esc_html($aud_key); ?>" <?php if(osc_item_stats_chart_audience() == $aud_key) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html($aud_label); ?></option>
              <?php } ?>
            </select>
            <div class="help-box"><?php _e('Applies to both user charts. Company users are accounts registered as a company.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Hooks'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-large" name="item_stats_user_chart_hooks" value="<?php echo osc_esc_html(osc_item_stats_user_chart_hooks()); ?>" />
            <div class="help-box"><?php _e('Theme hooks, comma-separated. Default: user_items_top.'); ?></div>
          </div>
        </div>

        <div class="form-row stats-settings-checks">
          <div class="form-label"><?php _e('Chart measures'); ?></div>
          <div class="form-controls">
            <?php foreach($all as $row) {
              if(!in_array($row['key'], $chart_keys, true)) {
                continue;
              }
            ?>
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" name="item_stats_user_chart_measures[]" value="<?php echo osc_esc_html($row['key']); ?>" <?php if(in_array($row['key'], $user_measures, true)) { echo 'checked="checked"'; } ?> />
                  <?php echo osc_esc_html($row['label']); ?>
                </label>
              </div>
            <?php } ?>
            <div class="help-box"><?php _e('Use 4 to 6 measures. The same measures appear in the summary cards and on the chart.'); ?></div>
          </div>
        </div>

        <div class="form-row stats-settings-checks">
          <div class="form-label"><?php _e('Periods'); ?></div>
          <div class="form-controls">
            <?php foreach($period_catalog as $pkey) { ?>
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" name="item_stats_user_chart_periods[]" value="<?php echo osc_esc_html($pkey); ?>" <?php if(in_array($pkey, $user_period_keys, true)) { echo 'checked="checked"'; } ?> />
                  <?php echo osc_esc_html(osc_stats_period_label($pkey)); ?>
                </label>
              </div>
            <?php } ?>
            <div class="help-box"><?php _e('Tabs on the user summary chart. Pick at least one.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Default period'); ?></div>
          <div class="form-controls">
            <select name="item_stats_user_chart_period">
              <?php foreach($period_catalog as $key) { ?>
                <option value="<?php echo osc_esc_html($key); ?>" <?php if(osc_item_stats_user_chart_period() == $key) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html(osc_stats_period_label($key)); ?></option>
              <?php } ?>
            </select>
            <div class="help-box"><?php _e('Must be one of the periods above.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Chart type'); ?></div>
          <div class="form-controls">
            <select name="item_stats_user_chart_type">
              <?php foreach(osc_item_stats_chart_types() as $type_key => $type_label) { ?>
                <option value="<?php echo osc_esc_html($type_key); ?>" <?php if(osc_item_stats_user_chart_type() == $type_key) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html($type_label); ?></option>
              <?php } ?>
            </select>
            <div class="help-box"><?php _e('Default is line. Stacked types add each measure on top of the previous one.'); ?></div>
          </div>
        </div>

        <h2 class="render-title separate-top"><?php _e('User listing chart'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Listing page'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" name="item_stats_item_chart_enabled" value="1" <?php if(osc_item_stats_item_chart_enabled()) { echo 'checked="checked"'; } ?> />
                <?php _e('Show a statistics chart on the listing page'); ?>
              </label>
            </div>
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" name="item_stats_item_chart_admin" value="1" <?php if(osc_item_stats_item_chart_admin()) { echo 'checked="checked"'; } ?> />
                <?php _e('Also show to a logged-in administrator'); ?>
              </label>
            </div>
            <div class="help-box"><?php _e('The listing owner sees the chart when it is enabled and the audience setting allows it.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Hooks'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-large" name="item_stats_item_chart_hooks" value="<?php echo osc_esc_html(osc_item_stats_item_chart_hooks()); ?>" />
            <div class="help-box"><?php _e('Theme hooks, comma-separated. Default: item_top.'); ?></div>
          </div>
        </div>

        <div class="form-row stats-settings-checks">
          <div class="form-label"><?php _e('Chart measures'); ?></div>
          <div class="form-controls">
            <?php foreach($all as $row) {
              if(!in_array($row['key'], $chart_keys, true)) {
                continue;
              }
            ?>
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" name="item_stats_item_chart_measures[]" value="<?php echo osc_esc_html($row['key']); ?>" <?php if(in_array($row['key'], $item_chart_measures, true)) { echo 'checked="checked"'; } ?> />
                  <?php echo osc_esc_html($row['label']); ?>
                </label>
              </div>
            <?php } ?>
            <div class="help-box"><?php _e('Use 4 to 6 measures.'); ?></div>
          </div>
        </div>

        <div class="form-row stats-settings-checks">
          <div class="form-label"><?php _e('Periods'); ?></div>
          <div class="form-controls">
            <?php foreach($period_catalog as $pkey) { ?>
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" name="item_stats_item_chart_periods[]" value="<?php echo osc_esc_html($pkey); ?>" <?php if(in_array($pkey, $item_period_keys, true)) { echo 'checked="checked"'; } ?> />
                  <?php echo osc_esc_html(osc_stats_period_label($pkey)); ?>
                </label>
              </div>
            <?php } ?>
            <div class="help-box"><?php _e('Tabs on the user listing chart. Pick at least one.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Default period'); ?></div>
          <div class="form-controls">
            <select name="item_stats_item_chart_period">
              <?php foreach($period_catalog as $key) { ?>
                <option value="<?php echo osc_esc_html($key); ?>" <?php if(osc_item_stats_item_chart_period() == $key) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html(osc_stats_period_label($key)); ?></option>
              <?php } ?>
            </select>
            <div class="help-box"><?php _e('Must be one of the periods above.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Chart type'); ?></div>
          <div class="form-controls">
            <select name="item_stats_item_chart_type">
              <?php foreach(osc_item_stats_chart_types() as $type_key => $type_label) { ?>
                <option value="<?php echo osc_esc_html($type_key); ?>" <?php if(osc_item_stats_item_chart_type() == $type_key) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html($type_label); ?></option>
              <?php } ?>
            </select>
            <div class="help-box"><?php _e('Separate from the user summary chart. Default is line.'); ?></div>
          </div>
        </div>

        <h2 class="render-title separate-top"><?php _e('Custom measures'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Custom 1 label'); ?></div>
          <div class="form-controls">
            <input type="text" name="item_stats_custom1_label" value="<?php echo osc_esc_html(osc_item_stats_custom_label(1)); ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Custom 2 label'); ?></div>
          <div class="form-controls">
            <input type="text" name="item_stats_custom2_label" value="<?php echo osc_esc_html(osc_item_stats_custom_label(2)); ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Custom 3 label'); ?></div>
          <div class="form-controls">
            <input type="text" name="item_stats_custom3_label" value="<?php echo osc_esc_html(osc_item_stats_custom_label(3)); ?>" />
            <div class="help-box"><?php _e('Custom 1-3 stay off until enabled in Collect measures. Plugins write with osc_increase_item_stat().'); ?></div>
          </div>
        </div>

        <h2 class="render-title separate-top"><?php _e('Purge old statistics'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Automatic purge'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" name="item_stats_auto_cleanup_enabled" value="1" <?php if(osc_item_stats_auto_cleanup_enabled()) { echo 'checked="checked"'; } ?> />
                <?php _e('Remove old listing statistics once a day'); ?>
              </label>
            </div>
            <div class="help-box"><?php _e('Daily cron deletes listing statistic rows older than the retention period.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Keep statistics for'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-small" name="item_stats_cleanup_months" value="<?php echo osc_esc_html((string)osc_item_stats_cleanup_months()); ?>" />
            <div class="inpt-desc"><?php _e('months'); ?></div>
            <div class="help-box"><?php _e('Default 24. Minimum 1, maximum 120. Tools > Cleanup can run the same purge by hand.'); ?></div>
          </div>
        </div>

        <?php osc_run_hook('admin_stats_settings_form'); ?>

        <div class="clear"></div>
        <div class="form-actions">
          <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>
<?php osc_current_admin_theme_path('parts/footer.php');

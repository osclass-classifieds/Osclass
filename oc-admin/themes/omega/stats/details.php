<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');
/*
 * Copyright 2014 Osclass
 * Copyright 2026 Osclass by OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Do not edit or add to this file if you wish to upgrade Osclass to newer
 * versions in the future. Software is distributed on an "AS IS" basis, without
 * warranties or conditions of any kind, either express or implied. Do not remove
 * this NOTICE section as it contains license information and copyrights.
 */

$measures = __get('measures');
$rows = __get('rows');
$sums = __get('sums');
$prev_sum = __get('prev_sum');
$item_id = (int)__get('item_id');
$user_id = (int)__get('user_id');
$category_id = (int)__get('category_id');
$period = __get('stats_period');
$enabled = array();
foreach(osc_item_stats_measures(false) as $key => $row) {
  if(in_array($key, osc_item_stats_chart_allowed_keys(), true)) {
    $enabled[$key] = $row;
  }
}
$categories = Category::newInstance()->listEnabled();
$chart_color = omg_current_color_scheme_chart();

function render_offset() {
  return 'row-offset';
}
osc_add_filter('render-wrapper', 'render_offset');

function addHelp() {
  echo '<p>' . __('Daily listing measures for the selected period. Cards at the top are period totals for the measures on the chart. Use the checkboxes to compare measures. Filters limit the chart to one category, listing or seller.') . '</p>';
  echo '<p>' . __('Drag across the chart to zoom. Right-click to reset. Charts redraw when the window is resized or zoomed.') . '</p>';
  echo '<ul>';
  foreach($enabled as $row) {
    echo '<li><strong>' . osc_esc_html($row['label']) . ':</strong> ' . osc_esc_html($row['help']) . '</li>';
  }
  echo '</ul>';
}
osc_add_hook('help_box', 'addHelp');

function customPageHeader() {
  echo '<h1>' . __('Statistics') . ' <a href="#" class="btn ico ico-32 ico-help float-right"></a></h1>';
}
osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Listing details - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  $measures = __get('measures');
  $rows = __get('rows');
  $labels = array(__('Date'));
  $colors = array();
  $chart_rows = array();
  foreach((array)$measures as $key) {
    $def = osc_item_stats_measure($key);
    $labels[] = (is_array($def) ? $def['label'] : $key);
    $colors[] = osc_item_stats_color($key);
  }
  foreach((array)$rows as $row) {
    $vals = array();
    foreach((array)$measures as $key) {
      $vals[] = (int)$row[$key];
    }
    $chart_rows[$row['d_date']] = $vals;
  }
  echo '<link rel="stylesheet" href="' . osc_esc_html(osc_assets_url('css/item-stats.css')) . '" />';
  echo osc_admin_stats_chart_js(array(
    array('id' => 'placeholder-details', 'type' => 'area', 'labels' => $labels, 'rows' => $chart_rows, 'colors' => $colors, 'legendFontSize' => 10, 'chartAreaBottom' => 140)
  ), array('page' => 'details'));
  osc_run_hook('admin_stats_header', 'details');
}
osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path('parts/header.php');

$qs = array(
  'page' => 'stats',
  'action' => 'details',
  'stats_period' => $period,
  'measures' => implode(',', (array)$measures),
  'item_id' => $item_id,
  'user_id' => $user_id,
  'category_id' => $category_id
);
$csv = osc_admin_base_url(true) . '?' . http_build_query($qs) . '&action=details_csv&' . osc_csrf_token_url();
?>
<div class="grid-system" id="stats-page">
  <div class="grid-row grid-50">
    <div class="row-wrapper"><h2 class="render-title"><?php _e('Listing details'); ?></h2></div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper"><?php echo osc_admin_stats_period_links('details'); ?></div>
  </div>
  <div class="grid-row grid-100">
    <div class="row-wrapper osc-stats-kpi-cards">
      <?php foreach((array)$measures as $key) {
        $def = osc_item_stats_measure($key);
        $n = (int)(isset($sums[$key]) ? $sums[$key] : 0);
        $p = (int)(isset($prev_sum[$key]) ? $prev_sum[$key] : 0);
        $label = (is_array($def) ? $def['label'] : $key);
      ?>
      <span>
        <span class="k-label"><?php echo osc_esc_html($label); ?></span>
        <?php echo osc_stats_kpi_value_html($n, $p); ?>
      </span>
      <?php } ?>
      <?php osc_run_hook('admin_stats_kpi', 'details'); ?>
    </div>
  </div>
  <div class="grid-row grid-100">
    <div class="row-wrapper">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="form-horizontal">
        <input type="hidden" name="page" value="stats" />
        <input type="hidden" name="action" value="details" />
        <input type="hidden" name="stats_period" value="<?php echo osc_esc_html($period); ?>" />
        <div class="form-row">
          <div class="form-label"><?php _e('Filters'); ?></div>
          <div class="form-controls">
            <select name="category_id">
              <option value="0"><?php _e('All categories'); ?></option>
              <?php foreach((array)$categories as $cat) { ?>
                <option value="<?php echo (int)$cat['pk_i_id']; ?>" <?php if($category_id == (int)$cat['pk_i_id']) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html($cat['s_name']); ?></option>
              <?php } ?>
            </select>
            <input type="text" class="input-small" name="item_id" value="<?php echo ($item_id > 0 ? (int)$item_id : ''); ?>" placeholder="<?php echo osc_esc_html(__('Listing ID')); ?>" />
            <input type="text" class="input-small" name="user_id" value="<?php echo ($user_id > 0 ? (int)$user_id : ''); ?>" placeholder="<?php echo osc_esc_html(__('User ID')); ?>" />
            <button type="submit" class="btn"><?php _e('Apply'); ?></button>
            <a class="btn" href="<?php echo osc_esc_html($csv); ?>"><?php _e('Download CSV'); ?></a>
          </div>
        </div>
        <div class="form-row stats-measures">
          <div class="form-label"><?php _e('Measures'); ?></div>
          <div class="form-controls">
            <?php foreach($enabled as $row) { ?>
              <label class="form-label-checkbox"><input type="checkbox" name="measures[]" value="<?php echo osc_esc_html($row['key']); ?>" <?php if(in_array($row['key'], (array)$measures, true)) { echo 'checked="checked"'; } ?> /> <?php echo osc_esc_html($row['label']); ?></label>
            <?php } ?>
            <button type="submit" class="btn"><?php _e('Update chart'); ?></button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="grid-row grid-100">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Daily totals'); ?></h3></div>
        <div class="widget-box-content">
          <div id="placeholder-details" class="graph-placeholder"><?php if(array_sum((array)$sums) == 0) { _e("There're no statistics yet"); echo ' ' . __('Enable measures in Listing settings and wait for visitor activity.'); } ?></div>
          <table class="table" cellpadding="0" cellspacing="0">
            <thead>
              <tr>
                <th><?php _e('Measure'); ?></th>
                <th><?php _e('Period'); ?></th>
                <th><?php _e('Previous period'); ?></th>
                <th><?php _e('Change'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach((array)$measures as $key) {
                $def = osc_item_stats_measure($key);
                $cur = (int)(isset($sums[$key]) ? $sums[$key] : 0);
                $prev = (int)(isset($prev_sum[$key]) ? $prev_sum[$key] : 0);
                $delta = ($prev > 0 ? round((($cur - $prev) / $prev) * 100) : null);
                $views = (int)(isset($sums['views']) ? $sums['views'] : 0);
                $rate = '';
                if($key == 'contactforms' && $views > 0) {
                  $rate = ' (' . osc_esc_html(number_format(($cur / $views) * 100, 1)) . ' ' . osc_esc_html(__('per 100 views')) . ')';
                }
              ?>
              <tr>
                <th><?php echo osc_esc_html(is_array($def) ? $def['label'] : $key); ?></th>
                <td title="<?php echo osc_esc_html((string)$cur); ?>"><?php echo osc_esc_html(osc_item_stats_format($cur)); ?><?php echo $rate; ?></td>
                <td title="<?php echo osc_esc_html((string)$prev); ?>"><?php echo osc_esc_html(osc_item_stats_format($prev)); ?></td>
                <td><?php echo ($delta === null ? '-' : (($delta >= 0 ? '+' : '') . (int)$delta . '%')); ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_main', 'details'); ?>
    </div>
  </div>
</div>
<?php osc_run_hook('admin_stats_after', 'details'); ?>
<?php osc_current_admin_theme_path('parts/footer.php');

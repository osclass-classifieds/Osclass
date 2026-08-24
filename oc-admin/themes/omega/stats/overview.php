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

$items = __get('items');
$users = __get('users');
$comments = __get('comments');
$reports = __get('reports');
$alerts = __get('alerts');
$view_rows = __get('view_rows');
$view_keys = __get('view_keys');
$view_sums = __get('view_sums');
if(!is_array($view_sums)) {
  $view_sums = array();
}
$items_by_root_category = __get('items_by_root_category');
$users_by_status = __get('users_by_status');
$comments_by_status = __get('comments_by_status');
$period = __get('stats_period');
$prev_items = (int)__get('prev_items');
$prev_users = (int)__get('prev_users');
$prev_comments = (int)__get('prev_comments');
$prev_reports = (int)__get('prev_reports');
$prev_alerts = (int)__get('prev_alerts');
$prev_views = (int)__get('prev_views');
$prev_premium = (int)__get('prev_premium');
$chart_color = omg_current_color_scheme_chart();

function render_offset() {
  return 'row-offset';
}
osc_add_filter('render-wrapper', 'render_offset');

function addHelp() {
  echo '<p>' . __('Overview of new listings, listing views, users, comments, alerts and abuse reports for the selected period.') . '</p>';
  echo '<ul><li>' . __('Listing views uses the measures selected under Listing details measures in Statistics settings.') . '</li>';
  echo '<li>' . __('Open a card to see the matching statistics page.') . '</li>';
  echo '<li>' . __('The line and area charts and cards use the selected period. Listings by category, users by status and comments by status are current totals. Category is a bar of root categories.') . '</li></ul>';
}
osc_add_hook('help_box', 'addHelp');

function customPageHeader() {
  echo '<h1>' . __('Statistics') . ' <a href="#" class="btn ico ico-32 ico-help float-right"></a></h1>';
}
osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Statistics overview - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  $items = __get('items');
  $view_rows = __get('view_rows');
  $view_keys = __get('view_keys');
  $items_by_root_category = osc_admin_stats_limit_slices((array)__get('items_by_root_category'), 's_name', 'num', 10);
  $users_by_status = __get('users_by_status');
  if(!is_array($users_by_status)) {
    $users_by_status = array();
  }
  $users_by_status_rows = array(
    array('s_label' => __('Pending'), 'num' => (int)(isset($users_by_status['pending']) ? $users_by_status['pending'] : 0)),
    array('s_label' => __('Active'), 'num' => (int)(isset($users_by_status['active']) ? $users_by_status['active'] : 0)),
    array('s_label' => __('Blocked'), 'num' => (int)(isset($users_by_status['blocked']) ? $users_by_status['blocked'] : 0))
  );
  $comments_by_status = __get('comments_by_status');
  if(!is_array($comments_by_status)) {
    $comments_by_status = array();
  }
  $comments_by_status_rows = array(
    array('s_label' => __('Pending'), 'num' => (int)(isset($comments_by_status['pending']) ? $comments_by_status['pending'] : 0)),
    array('s_label' => __('Active'), 'num' => (int)(isset($comments_by_status['active']) ? $comments_by_status['active'] : 0)),
    array('s_label' => __('Blocked'), 'num' => (int)(isset($comments_by_status['blocked']) ? $comments_by_status['blocked'] : 0))
  );
  $chart_color = omg_current_color_scheme_chart();
  echo '<link rel="stylesheet" href="' . osc_esc_html(osc_assets_url('css/item-stats.css')) . '" />';
  $view_chart = array();
  $view_labels = array(__('Date'));
  $view_colors = array();
  foreach((array)$view_keys as $key) {
    $def = osc_item_stats_measure($key);
    $view_labels[] = (is_array($def) ? $def['label'] : $key);
    $view_colors[] = osc_item_stats_color($key);
  }
  foreach((array)$view_rows as $row) {
    $vals = array();
    foreach((array)$view_keys as $key) {
      $vals[] = (int)$row[$key];
    }
    $view_chart[$row['d_date']] = $vals;
  }
  echo osc_admin_stats_chart_js(array(
    array('id' => 'placeholder-items', 'type' => 'line', 'labels' => array(__('Date'), __('Listings')), 'rows' => $items, 'colors' => array($chart_color)),
    array('id' => 'placeholder-views', 'type' => 'area', 'labels' => $view_labels, 'rows' => $view_chart, 'colors' => $view_colors)
  ), array(
    'page' => 'overview',
    'mix' => array(
      osc_admin_stats_listings_by_category_mix('items_by_category', $items_by_root_category, 'simple'),
      array('id' => 'users_by_status', 'type' => 'pie', 'labels' => array(__('Status'), __('Users')), 'rows' => $users_by_status_rows),
      array('id' => 'comments_by_status', 'type' => 'donut', 'labels' => array(__('Status'), __('Comments')), 'rows' => $comments_by_status_rows)
    )
  ));
  osc_run_hook('admin_stats_header', 'overview');
}
osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path('parts/header.php');

$sum_items = array_sum((array)$items);
$sum_users = array_sum((array)$users);
$sum_comments = array_sum((array)$comments);
$sum_reports = array_sum((array)$reports);
$sum_alerts = array_sum((array)$alerts);
$sum_views = 0;
$sum_premium = 0;
foreach((array)$view_rows as $row) {
  $sum_views += (int)$row['views'];
  if(isset($row['premium_views'])) {
    $sum_premium += (int)$row['premium_views'];
  }
}
if(!is_array($items_by_root_category)) {
  $items_by_root_category = array();
}
$items_by_category_sum = 0;
foreach((array)$items_by_root_category as $v) {
  $items_by_category_sum += (int)(isset($v['num']) ? $v['num'] : 0);
}
if(!is_array($users_by_status)) {
  $users_by_status = array();
}
$users_by_status_sum = (int)(isset($users_by_status['pending']) ? $users_by_status['pending'] : 0) + (int)(isset($users_by_status['active']) ? $users_by_status['active'] : 0) + (int)(isset($users_by_status['blocked']) ? $users_by_status['blocked'] : 0);
if(!is_array($comments_by_status)) {
  $comments_by_status = array();
}
$comments_by_status_sum = (int)(isset($comments_by_status['pending']) ? $comments_by_status['pending'] : 0) + (int)(isset($comments_by_status['active']) ? $comments_by_status['active'] : 0) + (int)(isset($comments_by_status['blocked']) ? $comments_by_status['blocked'] : 0);
?>
<div class="grid-system" id="stats-page">
  <div class="grid-row grid-50">
    <div class="row-wrapper"><h2 class="render-title"><?php _e('Overview'); ?></h2></div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper"><?php echo osc_admin_stats_period_links('overview'); ?></div>
  </div>
  <div class="grid-row grid-100">
    <div class="row-wrapper osc-stats-kpi-cards">
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=items&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('New listings'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_items, $prev_items); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=details&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('Views'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_views, $prev_views); ?>
      </a>
      <?php if(osc_item_stats_enabled('premium_views')) { ?>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=details&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('Premium views'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_premium, $prev_premium); ?>
      </a>
      <?php } ?>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=users&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('New users'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_users, $prev_users); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=comments&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('Comments'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_comments, $prev_comments); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=alerts&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('Alerts'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_alerts, $prev_alerts); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=reports">
        <span class="k-label"><?php _e('Abuse reports'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_reports, $prev_reports); ?>
      </a>
      <?php osc_run_hook('admin_stats_kpi', 'overview'); ?>
    </div>
  </div>
  <div class="stats-band">
  <div class="grid-row grid-65 stats-main">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Listing views'); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_item_stats_admin_views_heading($view_keys, $view_sums, $period); ?>
          <div id="placeholder-views" class="graph-placeholder"><?php if(array_sum((array)$view_sums) == 0) { _e("There're no statistics yet"); echo ' ' . __('Open a listing page to start collecting views.'); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('New listings'); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('New listings'), $sum_items, $period); ?>
          <div id="placeholder-items" class="graph-placeholder"><?php if(array_sum((array)$items) == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_main', 'overview'); ?>
    </div>
  </div>
  <div class="grid-row grid-35 stats-side">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Listings by category'); ?></h3></div>
        <div class="widget-box-content">
          <div id="items_by_category" class="graph-placeholder"><?php if($items_by_category_sum == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Users by status'); ?></h3></div>
        <div class="widget-box-content">
          <div id="users_by_status" class="graph-placeholder"><?php if($users_by_status_sum == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Comments by status'); ?></h3></div>
        <div class="widget-box-content">
          <div id="comments_by_status" class="graph-placeholder"><?php if($comments_by_status_sum == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_side', 'overview'); ?>
    </div>
  </div>
  </div>
  <div class="clear"></div>
  <?php osc_run_hook('admin_stats_after', 'overview'); ?>
</div>
<?php osc_current_admin_theme_path('parts/footer.php');

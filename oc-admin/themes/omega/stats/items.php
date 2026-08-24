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
$views = __get('views');
$alerts = __get('alerts');
$subscribers = __get('subscribers');
$latest_items = __get('latest_items');
$items_by_country = __get('items_by_country');
$items_by_region = __get('items_by_region');
$items_by_category_levels = __get('items_by_category_levels');
$items_by_price_type = __get('items_by_price_type');
$items_by_phone = __get('items_by_phone');
$period = __get('stats_period');
$prev_items = (int)__get('prev_items');
$prev_views = (int)__get('prev_views');
$prev_alerts = (int)__get('prev_alerts');
$prev_subscribers = (int)__get('prev_subscribers');

function render_offset() {
  return 'row-offset';
}
osc_add_filter('render-wrapper', 'render_offset');

function addHelp() {
  echo '<p>' . __('New listings and listing page views for the selected period.') . '</p>';
  echo '<ul><li>' . __('Views are counted from listing statistics, not abuse reports.') . '</li>';
  echo '<li>' . __('Saved-search alerts have their own statistics page.') . '</li>';
  echo '<li>' . __('Listings by country and region are all-time totals. Ranked bars are colored by count. The rest of those lists are grouped as Other.') . '</li>';
  echo '<li>' . __('Listings by category is a stacked bar: parent categories on the axis and child categories as stacks. Smaller groups are grouped as Other. Price type is free (0), check with seller (empty) or standard price (greater than 0).') . '</li>';
  echo '<li>' . __('Listings by phone split listings with a contact phone from those without.') . '</li></ul>';
}
osc_add_hook('help_box', 'addHelp');

function customPageHeader() {
  echo '<h1>' . __('Statistics') . ' <a href="#" class="btn ico ico-32 ico-help float-right"></a></h1>';
}
osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Listing statistics - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  $items = __get('items');
  $views = __get('views');
  $alerts = __get('alerts');
  $subscribers = __get('subscribers');
  $items_by_country = osc_admin_stats_limit_slices((array)__get('items_by_country'), 's_country');
  $items_by_region = osc_admin_stats_limit_slices((array)__get('items_by_region'), 's_region');
  $items_by_category_levels = __get('items_by_category_levels');
  $items_by_price_type = __get('items_by_price_type');
  $items_by_phone = __get('items_by_phone');
  if(!is_array($items_by_price_type)) {
    $items_by_price_type = array();
  }
  $items_by_price_rows = array(
    array('s_label' => __('Free'), 'num' => (int)(isset($items_by_price_type['free']) ? $items_by_price_type['free'] : 0)),
    array('s_label' => __('Ask seller'), 'num' => (int)(isset($items_by_price_type['ask']) ? $items_by_price_type['ask'] : 0)),
    array('s_label' => __('Priced'), 'num' => (int)(isset($items_by_price_type['priced']) ? $items_by_price_type['priced'] : 0))
  );
  if(!is_array($items_by_phone)) {
    $items_by_phone = array();
  }
  $items_by_phone_rows = array(
    array('s_label' => __('Phone'), 'num' => (int)(isset($items_by_phone['filled']) ? $items_by_phone['filled'] : 0)),
    array('s_label' => __('No phone'), 'num' => (int)(isset($items_by_phone['empty']) ? $items_by_phone['empty'] : 0))
  );
  $chart_color = omg_current_color_scheme_chart();
  echo osc_admin_stats_chart_js(array(
    array('id' => 'placeholder', 'type' => 'area', 'labels' => array(__('Date'), __('Items')), 'rows' => $items, 'colors' => array($chart_color)),
    array('id' => 'placeholder_total', 'type' => 'scatter', 'labels' => array(__('Date'), __('Views')), 'rows' => $views, 'colors' => array($chart_color))
  ), array(
    'page' => 'items',
    'mix' => array(
      array('id' => 'items_by_country', 'type' => 'bar', 'labels' => array(__('Country'), __('Listings')), 'rows' => $items_by_country, 'colors' => array($chart_color)),
      array('id' => 'items_by_region', 'type' => 'bar', 'labels' => array(__('Region'), __('Listings')), 'rows' => $items_by_region, 'colors' => array($chart_color)),
      array('id' => 'items_by_price', 'type' => 'pie', 'labels' => array(__('Price'), __('Listings')), 'rows' => $items_by_price_rows),
      array('id' => 'items_by_phone', 'type' => 'donut', 'labels' => array(__('Phone'), __('Listings')), 'rows' => $items_by_phone_rows),
      osc_admin_stats_listings_by_category_mix('items_by_category', $items_by_category_levels)
    )
  ));
  osc_run_hook('admin_stats_header', 'items');
}
osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path('parts/header.php');
if(!is_array($items_by_price_type)) {
  $items_by_price_type = array();
}
$items_by_price_sum = (int)(isset($items_by_price_type['free']) ? $items_by_price_type['free'] : 0) + (int)(isset($items_by_price_type['ask']) ? $items_by_price_type['ask'] : 0) + (int)(isset($items_by_price_type['priced']) ? $items_by_price_type['priced'] : 0);
if(!is_array($items_by_phone)) {
  $items_by_phone = array();
}
$items_by_phone_sum = (int)(isset($items_by_phone['filled']) ? $items_by_phone['filled'] : 0) + (int)(isset($items_by_phone['empty']) ? $items_by_phone['empty'] : 0);
$recent_items = array();
foreach((array)$latest_items as $it) {
  $label = (isset($it['s_title']) && $it['s_title'] != '' ? $it['s_title'] : '#' . (int)$it['pk_i_id']);
  $meta_parts = array();
  if(isset($it['s_contact_name']) && $it['s_contact_name'] != '') {
    $meta_parts[] = $it['s_contact_name'];
  }
  $place = '';
  if(isset($it['s_city']) && $it['s_city'] != '') {
    $place = $it['s_city'];
  } else if(isset($it['s_region']) && $it['s_region'] != '') {
    $place = $it['s_region'];
  }
  if($place != '') {
    $meta_parts[] = $place;
  }
  $recent_items[] = array(
    'label' => $label,
    'href' => osc_admin_base_url(true) . '?page=items&action=item_edit&id=' . (int)$it['pk_i_id'],
    'meta' => implode(', ', $meta_parts),
    'date' => osc_admin_stats_recent_date(isset($it['dt_pub_date']) ? $it['dt_pub_date'] : '')
  );
}
$sum_items = array_sum((array)$items);
$sum_views = array_sum((array)$views);
$sum_alerts = array_sum((array)$alerts);
$sum_subscribers = array_sum((array)$subscribers);
?>
<div class="grid-system" id="stats-page">
  <div class="grid-row grid-50">
    <div class="row-wrapper"><h2 class="render-title"><?php _e('Listing statistics'); ?></h2></div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper"><?php echo osc_admin_stats_period_links('items'); ?></div>
  </div>
  <div class="grid-row grid-100">
    <div class="row-wrapper osc-stats-kpi-cards">
      <a href="<?php echo osc_admin_base_url(true); ?>?page=items">
        <span class="k-label"><?php _e('New listings'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_items, $prev_items); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=details&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('Views'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_views, $prev_views); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=alerts&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('New alerts'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_alerts, $prev_alerts); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=alerts&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('New subscribers'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_subscribers, $prev_subscribers); ?>
      </a>
      <?php osc_run_hook('admin_stats_kpi', 'items'); ?>
    </div>
  </div>
  <div class="stats-band">
  <div class="grid-row grid-65 stats-main">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('New listings'); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('New listings'), $sum_items, $period); ?>
          <div id="placeholder" class="graph-placeholder"><?php if(array_sum((array)$items) == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e("Listings' views"); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__("Listings' views"), $sum_views, $period); ?>
          <div id="placeholder_total" class="graph-placeholder"><?php if(array_sum((array)$views) == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_main', 'items'); ?>
    </div>
  </div>
  <div class="grid-row grid-35 stats-side">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Listings per country'); ?></h3></div>
        <div class="widget-box-content">
          <div id="items_by_country" class="graph-placeholder"><?php if(!is_array($items_by_country) || count($items_by_country) == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Listings per region'); ?></h3></div>
        <div class="widget-box-content">
          <div id="items_by_region" class="graph-placeholder"><?php if(!is_array($items_by_region) || count($items_by_region) == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Listings by price'); ?></h3></div>
        <div class="widget-box-content">
          <div id="items_by_price" class="graph-placeholder"><?php
            if($items_by_price_sum == 0) {
              _e("There're no statistics yet");
            }
          ?></div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_side', 'items'); ?>
    </div>
  </div>
  </div>
  <div class="stats-band">
  <div class="grid-row grid-65 stats-main">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Listings by category'); ?></h3></div>
        <div class="widget-box-content">
          <div id="items_by_category" class="graph-placeholder"><?php if(!is_array($items_by_category_levels) || count($items_by_category_levels) == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="grid-row grid-35 stats-side">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Listings by phone'); ?></h3></div>
        <div class="widget-box-content">
          <div id="items_by_phone" class="graph-placeholder"><?php
            if($items_by_phone_sum == 0) {
              _e("There're no statistics yet");
            }
          ?></div>
        </div>
      </div>
      <div class="widget-box stats-span-2">
        <div class="widget-box-title"><h3><?php _e('Latest listings'); ?></h3></div>
        <div class="widget-box-content"><?php echo osc_admin_stats_recent_table($recent_items); ?></div>
      </div>
    </div>
  </div>
  </div>
  <div class="clear"></div>
  <?php osc_run_hook('admin_stats_after', 'items'); ?>
</div>
<?php osc_current_admin_theme_path('parts/footer.php');

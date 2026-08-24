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

$alerts = __get('alerts');
$subscribers = __get('subscribers');
$sent = __get('sent');
$matched = __get('matched');
$sent_rows = __get('sent_rows');
$active_by_day = __get('active_by_day');
$expired = __get('expired');
$active_rows = __get('active_rows');
$alerts_by_kind = __get('alerts_by_kind');
$alerts_by_frequency = __get('alerts_by_frequency');
$alerts_by_status = __get('alerts_by_status');
$alerts_by_country = __get('alerts_by_country');
$latest_alerts = __get('latest_alerts');
$period = __get('stats_period');
$prev_alerts = (int)__get('prev_alerts');
$prev_subscribers = (int)__get('prev_subscribers');
$prev_sent = (int)__get('prev_sent');
$prev_matched = (int)__get('prev_matched');
$prev_expired = (int)__get('prev_expired');
$prev_active = (int)__get('prev_active');

function render_offset() {
  return 'row-offset';
}
osc_add_filter('render-wrapper', 'render_offset');

function addHelp() {
  echo '<p>' . __('New saved-search alerts and subscribers for the selected period, plus emails sent and listings matched.') . '</p>';
  echo '<ul><li>' . __('New alerts counts saved searches. New subscribers counts distinct email addresses.') . '</li>';
  echo '<li>' . __('Emails sent is how many alert emails went out. Matched listings is how many listings were included in those emails.') . '</li>';
  echo '<li>' . __('Active alerts is how many confirmed, still-subscribed alerts existed at the end of each day. Expired on that chart is how many alerts reached their expire date that day.') . '</li>';
  echo '<li>' . __('Expired counts alerts that reached their expire date in the selected period. Alerts with no expire date are not counted.') . '</li>';
  echo '<li>' . __('Account type splits guests, personal accounts and company accounts. The period chart shows each as a share of that day. Frequency uses Instant, Hourly, Daily, Weekly and Custom.') . '</li>';
  echo '<li>' . __('Status and country are totals for subscribed alerts in the selected period (country is unknown for guests). Unsubscribed alerts are not counted. Status Expired is subscribed alerts whose expire date has passed.') . '</li></ul>';
}
osc_add_hook('help_box', 'addHelp');

function customPageHeader() {
  echo '<h1>' . __('Statistics') . ' <a href="#" class="btn ico ico-32 ico-help float-right"></a></h1>';
}
osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Alert statistics - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  $alerts = __get('alerts');
  $subscribers = __get('subscribers');
  $sent_rows = __get('sent_rows');
  $active_rows = __get('active_rows');
  $alerts_by_kind = __get('alerts_by_kind');
  $alerts_by_frequency = __get('alerts_by_frequency');
  $alerts_by_status = __get('alerts_by_status');
  $alerts_by_country = osc_admin_stats_limit_slices((array)__get('alerts_by_country'), 's_country');
  if(!is_array($alerts_by_frequency)) {
    $alerts_by_frequency = array();
  }
  if(!is_array($alerts_by_status)) {
    $alerts_by_status = array();
  }
  $alerts_freq_rows = array();
  foreach(array('INSTANT', 'HOURLY', 'DAILY', 'WEEKLY', 'CUSTOM') as $ft) {
    $alerts_freq_rows[] = array('s_label' => osc_alert_type_label($ft), 'num' => (int)(isset($alerts_by_frequency[$ft]) ? $alerts_by_frequency[$ft] : 0));
  }
  $alerts_status_rows = array(
    array('s_label' => __('Active'), 'num' => (int)(isset($alerts_by_status['active']) ? $alerts_by_status['active'] : 0)),
    array('s_label' => __('Inactive'), 'num' => (int)(isset($alerts_by_status['inactive']) ? $alerts_by_status['inactive'] : 0)),
    array('s_label' => __('Expired'), 'num' => (int)(isset($alerts_by_status['expired']) ? $alerts_by_status['expired'] : 0))
  );
  $kind_guest = 0;
  $kind_personal = 0;
  $kind_company = 0;
  foreach((array)$alerts_by_kind as $vals) {
    if(!is_array($vals)) {
      continue;
    }
    $kind_guest += (int)(isset($vals[0]) ? $vals[0] : 0);
    $kind_personal += (int)(isset($vals[1]) ? $vals[1] : 0);
    $kind_company += (int)(isset($vals[2]) ? $vals[2] : 0);
  }
  $alerts_kind_rows = array(
    array('s_label' => __('Guest'), 'num' => $kind_guest),
    array('s_label' => __('Personal'), 'num' => $kind_personal),
    array('s_label' => __('Company'), 'num' => $kind_company)
  );
  $chart_color = omg_current_color_scheme_chart();
  echo osc_admin_stats_chart_js(array(
    array('id' => 'placeholder-alerts', 'type' => 'area', 'labels' => array(__('Date'), __('New alerts')), 'rows' => $alerts, 'colors' => array($chart_color)),
    array('id' => 'placeholder-subscribers', 'type' => 'line', 'labels' => array(__('Date'), __('New subscribers')), 'rows' => $subscribers, 'colors' => array(osc_item_stats_palette_color(6))),
    array('id' => 'placeholder-sent', 'type' => 'combo', 'labels' => array(__('Date'), __('Emails sent'), __('Matched listings')), 'rows' => $sent_rows, 'colors' => array($chart_color, osc_item_stats_palette_color(6))),
    array('id' => 'placeholder-active', 'type' => 'combo', 'combo' => 'area_bars', 'labels' => array(__('Date'), __('Active alerts'), __('Expired')), 'rows' => $active_rows, 'colors' => array('#0ea5e9', '#f97316')),
    array('id' => 'placeholder-kind', 'type' => 'stacked_percent', 'labels' => array(__('Date'), __('Guest'), __('Personal'), __('Company')), 'rows' => $alerts_by_kind, 'colors' => array(osc_item_stats_palette_color(9), $chart_color, osc_item_stats_palette_color(6)))
  ), array(
    'page' => 'alerts',
    'mix' => array(
      array('id' => 'by_kind', 'type' => 'pie', 'labels' => array(__('Account'), __('Alerts')), 'rows' => $alerts_kind_rows),
      array('id' => 'by_freq', 'type' => 'pie', 'labels' => array(__('Frequency'), __('Alerts')), 'rows' => $alerts_freq_rows),
      array('id' => 'by_status', 'type' => 'donut', 'labels' => array(__('Status'), __('Alerts')), 'rows' => $alerts_status_rows),
      array('id' => 'by_country', 'type' => 'bar', 'labels' => array(__('Country'), __('Alerts')), 'rows' => $alerts_by_country, 'colors' => array($chart_color))
    )
  ));
  osc_run_hook('admin_stats_header', 'alerts');
}
osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path('parts/header.php');

$sum_alerts = array_sum((array)$alerts);
$sum_subscribers = array_sum((array)$subscribers);
$sum_sent = array_sum((array)$sent);
$sum_matched = array_sum((array)$matched);
$sum_expired = array_sum((array)$expired);
$active_now = 0;
if(is_array($active_by_day) && !empty($active_by_day)) {
  $active_now = (int)end($active_by_day);
}
if(!is_array($alerts_by_frequency)) {
  $alerts_by_frequency = array();
}
$alerts_freq_sum = 0;
foreach($alerts_by_frequency as $n) {
  $alerts_freq_sum += (int)$n;
}
if(!is_array($alerts_by_status)) {
  $alerts_by_status = array();
}
$alerts_status_sum = (int)(isset($alerts_by_status['active']) ? $alerts_by_status['active'] : 0) + (int)(isset($alerts_by_status['inactive']) ? $alerts_by_status['inactive'] : 0) + (int)(isset($alerts_by_status['expired']) ? $alerts_by_status['expired'] : 0);
$kind_guest = 0;
$kind_personal = 0;
$kind_company = 0;
foreach((array)$alerts_by_kind as $vals) {
  if(!is_array($vals)) {
    continue;
  }
  $kind_guest += (int)(isset($vals[0]) ? $vals[0] : 0);
  $kind_personal += (int)(isset($vals[1]) ? $vals[1] : 0);
  $kind_company += (int)(isset($vals[2]) ? $vals[2] : 0);
}
$kind_sum = $kind_guest + $kind_personal + $kind_company;
$recent_alerts = array();
foreach((array)$latest_alerts as $a) {
  $email = (isset($a['s_email']) ? (string)$a['s_email'] : '');
  $meta_parts = array();
  $expired_alert = (!empty($a['dt_expire_date']) && strtotime($a['dt_expire_date']) !== false && strtotime($a['dt_expire_date']) <= time());
  if($expired_alert) {
    $meta_parts[] = __('Expired');
  } else if(!empty($a['b_active'])) {
    $meta_parts[] = __('Active');
  } else {
    $meta_parts[] = __('Inactive');
  }
  if(isset($a['e_type']) && $a['e_type'] != '') {
    $meta_parts[] = osc_alert_type_label($a['e_type']);
  }
  if(!isset($a['fk_i_user_id']) || (int)$a['fk_i_user_id'] <= 0) {
    $meta_parts[] = __('Guest');
  } else if(isset($a['b_company']) && (int)$a['b_company'] == 1) {
    $meta_parts[] = __('Company');
  } else {
    $meta_parts[] = __('Personal');
  }
  $recent_alerts[] = array(
    'label' => ($email != '' ? $email : '#' . (int)$a['pk_i_id']),
    'href' => osc_admin_base_url(true) . '?page=users&action=alerts&alertEmail=' . rawurlencode($email),
    'meta' => implode(', ', $meta_parts),
    'date' => osc_admin_stats_recent_date(isset($a['dt_date']) ? $a['dt_date'] : '')
  );
}
?>
<div class="grid-system" id="stats-page">
  <div class="grid-row grid-50">
    <div class="row-wrapper"><h2 class="render-title"><?php _e('Alert statistics'); ?></h2></div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper"><?php echo osc_admin_stats_period_links('alerts'); ?></div>
  </div>
  <div class="grid-row grid-100">
    <div class="row-wrapper osc-stats-kpi-cards">
      <a href="<?php echo osc_admin_base_url(true); ?>?page=users&amp;action=alerts">
        <span class="k-label"><?php _e('New alerts'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_alerts, $prev_alerts); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=users&amp;action=alerts">
        <span class="k-label"><?php _e('New subscribers'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_subscribers, $prev_subscribers); ?>
      </a>
      <span>
        <span class="k-label"><?php _e('Emails sent'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_sent, $prev_sent); ?>
      </span>
      <span>
        <span class="k-label"><?php _e('Matched listings'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_matched, $prev_matched); ?>
      </span>
      <span>
        <span class="k-label"><?php _e('Active alerts'); ?></span>
        <?php echo osc_stats_kpi_value_html($active_now, $prev_active); ?>
      </span>
      <span>
        <span class="k-label"><?php _e('Expired'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_expired, $prev_expired); ?>
      </span>
      <?php osc_run_hook('admin_stats_kpi', 'alerts'); ?>
    </div>
  </div>
  <div class="stats-band">
  <div class="grid-row grid-65 stats-main">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('New alerts'); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('New alerts'), $sum_alerts, $period); ?>
          <div id="placeholder-alerts" class="graph-placeholder"><?php if($sum_alerts == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('New subscribers'); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('New subscribers'), $sum_subscribers, $period); ?>
          <div id="placeholder-subscribers" class="graph-placeholder"><?php if($sum_subscribers == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_main', 'alerts'); ?>
    </div>
  </div>
  <div class="grid-row grid-35 stats-side">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Alerts by account'); ?></h3></div>
        <div class="widget-box-content">
          <div id="by_kind" class="graph-placeholder"><?php if($kind_sum == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Alerts by frequency'); ?></h3></div>
        <div class="widget-box-content">
          <div id="by_freq" class="graph-placeholder"><?php if($alerts_freq_sum == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Alerts by status'); ?></h3></div>
        <div class="widget-box-content">
          <div id="by_status" class="graph-placeholder"><?php if($alerts_status_sum == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_side', 'alerts'); ?>
    </div>
  </div>
  </div>
  <div class="stats-band">
  <div class="grid-row grid-65 stats-main">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Emails sent'); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('Emails sent'), $sum_sent, $period, array(__('Matched listings') => $sum_matched)); ?>
          <div id="placeholder-sent" class="graph-placeholder"><?php if($sum_sent == 0 && $sum_matched == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Active alerts'); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('Expired'), $sum_expired, $period); ?>
          <div id="placeholder-active" class="graph-placeholder"><?php if($active_now == 0 && $sum_expired == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('New alerts by account'); ?></h3></div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('New alerts'), $kind_sum, $period, array(__('Guest') => $kind_guest, __('Personal') => $kind_personal, __('Company') => $kind_company)); ?>
          <div id="placeholder-kind" class="graph-placeholder"><?php if($kind_sum == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="grid-row grid-35 stats-side">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Alerts by country'); ?></h3></div>
        <div class="widget-box-content">
          <div id="by_country" class="graph-placeholder"><?php if(!is_array($alerts_by_country) || count($alerts_by_country) == 0) { _e("There're no statistics yet"); } ?></div>
        </div>
      </div>
      <div class="widget-box stats-span-2">
        <div class="widget-box-title"><h3><?php _e('Latest alerts'); ?></h3></div>
        <div class="widget-box-content"><?php echo osc_admin_stats_recent_table($recent_alerts); ?></div>
      </div>
    </div>
  </div>
  </div>
  <div class="clear"></div>
  <?php osc_run_hook('admin_stats_after', 'alerts'); ?>
</div>
<?php osc_current_admin_theme_path('parts/footer.php');

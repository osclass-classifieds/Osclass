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


/**
 * Helper Item statistics
 * @package Osclass
 * @subpackage Helpers
 * @author Osclass
 */


// 16-color categorical palette (Tableau 10/20, cyan from Paul Tol for alert emails)
function osc_item_stats_palette() {
  static $colors = null;
  if($colors === null) {
    $colors = array(
      '#4E79A7', '#F28E2B', '#E15759', '#76B7B2', '#59A14F',
      '#EDC948', '#B07AA1', '#33BBEE', '#9C755F', '#BAB0AC',
      '#A0CBE8', '#FFBE7D', '#8CD17D', '#B6992D', '#D4A6C8', '#FF9DA7'
    );
    $colors = array_values((array)osc_apply_filter('osc_item_stats_palette', $colors));
  }
  return $colors;
}


// Hex for one palette slot
function osc_item_stats_palette_color($index) {
  $p = osc_item_stats_palette();
  $n = count($p);
  if($n < 1) {
    return '#4E79A7';
  }
  return $p[((int)$index) % $n];
}


// Measure key => hex; plugins may remap with osc_item_stats_colors
function osc_item_stats_colors() {
  static $map = null;
  if($map === null) {
    $map = array(
      'views' => osc_item_stats_palette_color(0),
      'contactforms' => osc_item_stats_palette_color(1),
      'reports' => osc_item_stats_palette_color(2),
      'views_engaged' => osc_item_stats_palette_color(3),
      'phone_clicks' => osc_item_stats_palette_color(4),
      'premium_views' => osc_item_stats_palette_color(5),
      'views_logged' => osc_item_stats_palette_color(6),
      'alerts_sent' => osc_item_stats_palette_color(7),
      'view_minutes' => osc_item_stats_palette_color(8),
      'comments' => osc_item_stats_palette_color(9),
      'views_search' => osc_item_stats_palette_color(10),
      'contactother_clicks' => osc_item_stats_palette_color(11),
      'views_home' => osc_item_stats_palette_color(12),
      'shares' => osc_item_stats_palette_color(13),
      'promotions' => osc_item_stats_palette_color(14),
      'favorites' => osc_item_stats_palette_color(15),
      'contacts' => osc_item_stats_palette_color(11),
      'orders' => osc_item_stats_palette_color(13),
      'offers' => osc_item_stats_palette_color(14),
      'tops' => osc_item_stats_palette_color(5),
      'renews' => osc_item_stats_palette_color(12),
      'repubs' => osc_item_stats_palette_color(3),
      'rated_comments' => osc_item_stats_palette_color(15),
      'custom1' => osc_item_stats_palette_color(9),
      'custom2' => osc_item_stats_palette_color(10),
      'custom3' => osc_item_stats_palette_color(8),
      'items_published' => osc_item_stats_palette_color(12),
      'items_active' => osc_item_stats_palette_color(4)
    );
    $map = osc_apply_filter('osc_item_stats_colors', $map, osc_item_stats_palette());
  }
  return $map;
}


// Color for one measure (front and back office)
function osc_item_stats_color($key) {
  $key = osc_item_stats_normalize_key($key);
  $def = osc_item_stats_measure($key);
  $color = (is_array($def) && isset($def['color']) && $def['color'] != '' ? $def['color'] : osc_item_stats_palette_color(0));
  return osc_apply_filter('osc_item_stats_color', $color, $key);
}


// Return the full measure registry (plugins may extend via filter)
function osc_item_stats_measures($only_enabled = false) {
  static $all = null;
  if($all === null) {
    $all = array(
      'views' => array('key' => 'views', 'column' => 'i_num_views', 'group' => 'traffic', 'label' => __('Standard views'), 'help' => __('Times the listing page was opened.'), 'default_enabled' => true, 'session_guard' => 'method', 'logged_only' => true, 'collect_guest' => true),
      'premium_views' => array('key' => 'premium_views', 'column' => 'i_num_premium_views', 'group' => 'traffic', 'label' => __('Premium views'), 'help' => __('Times the listing was shown as a premium result.'), 'default_enabled' => true, 'session_guard' => 'method', 'logged_only' => true, 'collect_guest' => true),
      'views_engaged' => array('key' => 'views_engaged', 'column' => 'i_num_views_engaged', 'group' => 'traffic', 'label' => __('Engaged views'), 'help' => __('Visits that stayed on the listing for the configured number of seconds.'), 'default_enabled' => true, 'session_guard' => 'always', 'logged_only' => true, 'collect_guest' => true),
      'views_search' => array('key' => 'views_search', 'column' => 'i_num_views_search', 'group' => 'traffic', 'label' => __('Search impressions'), 'help' => __('Times the listing appeared in search results. Writes one row per result per session when enabled.'), 'default_enabled' => false, 'session_guard' => 'always', 'logged_only' => true, 'collect_guest' => true),
      'views_logged' => array('key' => 'views_logged', 'column' => 'i_num_views_logged', 'group' => 'traffic', 'label' => __('Logged-in views'), 'help' => __('Listing page opens by signed-in visitors.'), 'default_enabled' => true, 'session_guard' => 'method', 'logged_only' => true, 'collect_guest' => false),
      'views_home' => array('key' => 'views_home', 'column' => 'i_num_views_home', 'group' => 'traffic', 'label' => __('Home impressions'), 'help' => __('Times the listing appeared in latest listings on the home page. Writes one row per result per session when enabled.'), 'default_enabled' => false, 'session_guard' => 'always', 'logged_only' => true, 'collect_guest' => true),
      'view_minutes' => array('key' => 'view_minutes', 'column' => 'i_num_view_minutes', 'group' => 'engagement', 'label' => __('Minutes viewed'), 'help' => __('Full minutes a visitor kept the listing tab visible.'), 'default_enabled' => true, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'phone_clicks' => array('key' => 'phone_clicks', 'column' => 'i_num_phone_clicks', 'group' => 'contact', 'label' => __('Phone clicks'), 'help' => __('Clicks on a telephone control on the listing page.'), 'default_enabled' => true, 'session_guard' => 'always', 'logged_only' => true, 'collect_guest' => true),
      'contactother_clicks' => array('key' => 'contactother_clicks', 'column' => 'i_num_contactother_clicks', 'group' => 'contact', 'label' => __('Other contact clicks'), 'help' => __('Clicks on the other-contact control (WhatsApp, Viber or similar).'), 'default_enabled' => false, 'session_guard' => 'always', 'logged_only' => true, 'collect_guest' => true),
      'favorites' => array('key' => 'favorites', 'column' => 'i_num_favorites', 'group' => 'engagement', 'label' => __('Favorites'), 'help' => __('Times someone saved the listing. Removing a favorite does not subtract.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'contactforms' => array('key' => 'contactforms', 'column' => 'i_num_contactforms', 'group' => 'contact', 'label' => __('Contact forms'), 'help' => __('Contact messages sent from the listing page.'), 'default_enabled' => true, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'contacts' => array('key' => 'contacts', 'column' => 'i_num_contacts', 'group' => 'contact', 'label' => __('Messages'), 'help' => __('Messenger conversations started about this listing.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'orders' => array('key' => 'orders', 'column' => 'i_num_orders', 'group' => 'commerce', 'label' => __('Orders'), 'help' => __('Orders created for this listing.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'offers' => array('key' => 'offers', 'column' => 'i_num_offers', 'group' => 'commerce', 'label' => __('Offers'), 'help' => __('Price offers submitted on this listing.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'promotions' => array('key' => 'promotions', 'column' => 'i_num_promotions', 'group' => 'commerce', 'label' => __('Promotions'), 'help' => __('Paid promotions applied to this listing.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => false, 'collect_guest' => true),
      'reports' => array('key' => 'reports', 'column' => 'i_num_reports', 'group' => 'lifecycle', 'label' => __('Reports'), 'help' => __('Abuse reports filed against this listing.'), 'default_enabled' => true, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'tops' => array('key' => 'tops', 'column' => 'i_num_tops', 'group' => 'commerce', 'label' => __('Move to top'), 'help' => __('Times the listing was moved to the top.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => false, 'collect_guest' => true),
      'renews' => array('key' => 'renews', 'column' => 'i_num_renews', 'group' => 'lifecycle', 'label' => __('Renewals'), 'help' => __('Times the seller renewed this listing.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => false, 'collect_guest' => true),
      'repubs' => array('key' => 'repubs', 'column' => 'i_num_repubs', 'group' => 'lifecycle', 'label' => __('Republish'), 'help' => __('Times the listing was republished.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => false, 'collect_guest' => true),
      'alerts_sent' => array('key' => 'alerts_sent', 'column' => 'i_num_alerts_sent', 'group' => 'engagement', 'label' => __('Alert emails'), 'help' => __('Times the listing was included in a sent alert email.'), 'default_enabled' => true, 'session_guard' => 'never', 'logged_only' => false, 'collect_guest' => true),
      'shares' => array('key' => 'shares', 'column' => 'i_num_shares', 'group' => 'engagement', 'label' => __('Shares'), 'help' => __('Send-to-friend submissions and share clicks.'), 'default_enabled' => false, 'session_guard' => 'always', 'logged_only' => true, 'collect_guest' => true),
      'comments' => array('key' => 'comments', 'column' => 'i_num_comments', 'group' => 'engagement', 'label' => __('Comments'), 'help' => __('Active comments on the listing.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'rated_comments' => array('key' => 'rated_comments', 'column' => 'i_num_rated_comments', 'group' => 'engagement', 'label' => __('Rated comments'), 'help' => __('Active comments that include a rating.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'custom1' => array('key' => 'custom1', 'column' => 'i_num_custom1', 'group' => 'custom', 'label' => osc_item_stats_custom_label(1), 'help' => __('Reserved for plugins.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'custom2' => array('key' => 'custom2', 'column' => 'i_num_custom2', 'group' => 'custom', 'label' => osc_item_stats_custom_label(2), 'help' => __('Reserved for plugins.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'custom3' => array('key' => 'custom3', 'column' => 'i_num_custom3', 'group' => 'custom', 'label' => osc_item_stats_custom_label(3), 'help' => __('Reserved for plugins.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => true, 'collect_guest' => true),
      'items_published' => array('key' => 'items_published', 'column' => '', 'source' => 'item', 'group' => 'lifecycle', 'label' => __('Listings published'), 'help' => __('Listings with a publish date on that day. Counted from listing records, not from daily stats rows.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => false, 'collect_guest' => true),
      'items_active' => array('key' => 'items_active', 'column' => '', 'source' => 'item', 'group' => 'lifecycle', 'label' => __('Listings live'), 'help' => __('How many listings were live on that day (published and not expired). Counted from listing records, not from daily stats rows.'), 'default_enabled' => false, 'session_guard' => 'never', 'logged_only' => false, 'collect_guest' => true)
    );
    $colors = osc_item_stats_colors();
    foreach($all as $key => $row) {
      if(isset($colors[$key]) && $colors[$key] != '') {
        $all[$key]['color'] = $colors[$key];
      }
    }
    $all = osc_apply_filter('osc_item_stats_measures', $all);
    $p = osc_item_stats_palette();
    $pn = count($p);
    $pi = 0;
    foreach($all as $key => $row) {
      if(!isset($row['color']) || $row['color'] == '') {
        $all[$key]['color'] = ($pn > 0 ? $p[$pi % $pn] : '#4E79A7');
      }
      $pi++;
    }
  }

  if(!$only_enabled) {
    return $all;
  }

  $out = array();
  foreach($all as $key => $row) {
    if(osc_item_stats_enabled($key)) {
      $out[$key] = $row;
    }
  }
  return $out;
}


// Return one registry row or false
function osc_item_stats_measure($key) {
  $key = osc_item_stats_normalize_key($key);
  $all = osc_item_stats_measures(false);
  if($key != '' && isset($all[$key])) {
    return $all[$key];
  }
  return false;
}


// Return registry row by SQL column name
function osc_item_stats_measure_by_column($column) {
  $column = trim((string)$column);
  if($column == '') {
    return false;
  }
  foreach(osc_item_stats_measures(false) as $row) {
    if(isset($row['column']) && $row['column'] === $column) {
      return $row;
    }
  }
  return false;
}


// Map alias or key to column name
function osc_item_stats_column($key) {
  $def = osc_item_stats_measure($key);
  if(!is_array($def) || !isset($def['column'])) {
    return '';
  }
  return (string)$def['column'];
}


// Normalize key or column name to registry key
function osc_item_stats_normalize_key($key) {
  $key = trim((string)$key);
  if($key == '') {
    return '';
  }
  $aliases = array(
    'phone_views' => 'phone_clicks',
    'whatsapp_views' => 'contactother_clicks',
    'i_num_phone_views' => 'phone_clicks',
    'i_num_whatsapp_views' => 'contactother_clicks',
    'i_sum_view_minutes' => 'view_minutes'
  );
  if(isset($aliases[$key])) {
    $key = $aliases[$key];
  }
  $all = osc_item_stats_measures(false);
  if(isset($all[$key])) {
    return $key;
  }
  foreach($all as $row) {
    if($row['column'] === $key) {
      return $row['key'];
    }
  }
  return '';
}


// Whether a measure is currently collected
function osc_item_stats_enabled($measure) {
  $key = osc_item_stats_normalize_key($measure);
  if($key == '') {
    return false;
  }
  $enabled = osc_item_stats_enabled_keys();
  return in_array($key, $enabled, true);
}


// Measures the public item-page AJAX endpoint may write
function osc_item_stats_ajax_measures() {
  $keys = array('views_engaged', 'view_minutes', 'phone_clicks', 'contactother_clicks', 'shares', 'custom1', 'custom2', 'custom3');
  $keys = osc_apply_filter('osc_item_stats_ajax_measures', $keys);
  $blocked = array('views', 'premium_views', 'views_search', 'views_logged', 'views_home', 'favorites', 'contactforms', 'contacts', 'orders', 'offers', 'promotions', 'reports', 'tops', 'renews', 'repubs', 'alerts_sent', 'comments', 'rated_comments');
  $out = array();
  foreach((array)$keys as $key) {
    $key = osc_item_stats_normalize_key($key);
    if($key == '' || in_array($key, $blocked, true)) {
      continue;
    }
    if(osc_item_stats_column($key) != '' && osc_item_stats_enabled($key)) {
      $out[] = $key;
    }
  }
  return array_values(array_unique($out));
}


// Whether the public AJAX endpoint may write this measure
function osc_item_stats_ajax_allowed($measure) {
  $key = osc_item_stats_normalize_key($measure);
  return ($key != '' && in_array($key, osc_item_stats_ajax_measures(), true));
}


// Default collect/report measure CSV
function osc_item_stats_default_enabled_csv() {
  return 'views,premium_views,views_engaged,views_logged,view_minutes,phone_clicks,contactforms,reports,alerts_sent';
}


// Default user summary and listing chart measure CSV
function osc_item_stats_default_chart_csv() {
  return 'views,premium_views,views_engaged,phone_clicks,contactforms,reports';
}


// Default user summary and listing chart period tabs (4 periods)
function osc_item_stats_default_periods_csv() {
  return '30d,90d,12m,all';
}


// Enabled measure keys from preference
function osc_item_stats_enabled_keys() {
  static $keys = null;
  if($keys === null) {
    $preset = osc_item_stats_preset();
    if(in_array($preset, array('essential', 'engagement', 'commerce', 'full'), true)) {
      $keys = osc_item_stats_preset_keys($preset);
    } else {
      $keys = osc_item_stats_parse_csv(osc_item_stats_enabled_pref());
    }
    if(empty($keys)) {
      $keys = osc_item_stats_parse_csv(osc_item_stats_default_enabled_csv());
    }
    $valid = array();
    foreach(osc_item_stats_measures(false) as $key => $row) {
      if(!isset($row['source']) || $row['source'] != 'item') {
        $valid[] = $key;
      }
    }
    $keys = array_values(array_intersect($keys, $valid));
    if(empty($keys)) {
      $keys = osc_item_stats_parse_csv(osc_item_stats_default_enabled_csv());
    }
  }
  return $keys;
}


// Keep only known collectable measure keys; empty input falls back to the default set
function osc_item_stats_sanitize_enabled($raw) {
  $keys = osc_item_stats_parse_csv($raw);
  $valid = array();
  foreach(osc_item_stats_measures(false) as $key => $row) {
    if(!osc_item_stats_is_virtual($key)) {
      $valid[] = $key;
    }
  }
  $keys = array_values(array_intersect($keys, $valid));
  if(empty($keys)) {
    $keys = osc_item_stats_parse_csv(osc_item_stats_default_enabled_csv());
  }
  return implode(',', $keys);
}


// Whether a measure is computed from t_item instead of t_item_stats
function osc_item_stats_is_virtual($measure) {
  $def = osc_item_stats_measure($measure);
  return (is_array($def) && isset($def['source']) && $def['source'] == 'item');
}


// Virtual measure keys
function osc_item_stats_virtual_keys() {
  $out = array();
  foreach(osc_item_stats_measures(false) as $key => $row) {
    if(isset($row['source']) && $row['source'] == 'item') {
      $out[] = $key;
    }
  }
  return $out;
}


// Keys allowed on charts: collected measures plus virtual listing counts
function osc_item_stats_chart_allowed_keys() {
  return array_values(array_unique(array_merge(osc_item_stats_enabled_keys(), osc_item_stats_virtual_keys())));
}


// Listing details measures that can actually plot (collected plus virtual)
function osc_item_stats_admin_plot_measures() {
  $keys = osc_item_stats_parse_csv(osc_item_stats_admin_default_measures());
  $keys = array_values(array_intersect($keys, osc_item_stats_chart_allowed_keys()));
  if(empty($keys)) {
    $keys = array('views');
    if(osc_item_stats_enabled('premium_views')) {
      $keys[] = 'premium_views';
    }
  }
  return $keys;
}


// Overview/dashboard listing-views subtitle (period total plus extra selected measures)
function osc_item_stats_admin_views_heading($measures, $sums, $period) {
  $measures = array_values((array)$measures);
  if(empty($measures)) {
    $measures = array('views');
  }
  $main = (in_array('views', $measures, true) ? 'views' : $measures[0]);
  $label = __('Views');
  if($main != 'views') {
    $def = osc_item_stats_measure($main);
    $label = (is_array($def) ? $def['label'] : $main);
  }
  $more = array();
  foreach($measures as $key) {
    if($key === $main) {
      continue;
    }
    $def = osc_item_stats_measure($key);
    $more[(is_array($def) ? $def['label'] : $key)] = (int)(isset($sums[$key]) ? $sums[$key] : 0);
  }
  return osc_admin_stats_chart_total($label, (int)(isset($sums[$main]) ? $sums[$main] : 0), $period, $more);
}


// Google Chart payload for listing-views series
function osc_item_stats_admin_views_chart($placeholder, $measures, $rows) {
  $labels = array(__('Date'));
  $colors = array();
  foreach((array)$measures as $key) {
    $def = osc_item_stats_measure($key);
    $labels[] = (is_array($def) ? $def['label'] : $key);
    $colors[] = osc_item_stats_color($key);
  }
  $chart_rows = array();
  foreach((array)$rows as $row) {
    if(!isset($row['d_date'])) {
      continue;
    }
    $vals = array();
    foreach((array)$measures as $key) {
      $vals[] = (int)(isset($row[$key]) ? $row[$key] : 0);
    }
    $chart_rows[$row['d_date']] = $vals;
  }
  $chart = array('id' => $placeholder, 'type' => 'area', 'labels' => $labels, 'rows' => $chart_rows, 'colors' => $colors);
  if(count((array)$measures) > 2) {
    $chart['legendFontSize'] = 10;
    $chart['chartAreaBottom'] = 100;
  }
  return $chart;
}


// Keep known period keys in catalog order
function osc_item_stats_parse_period_csv($raw) {
  if(is_array($raw)) {
    $raw = implode(',', $raw);
  }
  $valid = osc_stats_period_all_keys();
  $out = array();
  foreach(explode(',', (string)$raw) as $part) {
    $part = trim((string)$part);
    if($part != '' && in_array($part, $valid, true) && !in_array($part, $out, true)) {
      $out[] = $part;
    }
  }
  $ordered = array();
  foreach($valid as $key) {
    if(in_array($key, $out, true)) {
      $ordered[] = $key;
    }
  }
  return $ordered;
}


// Keep known period keys in catalog order
function osc_item_stats_sanitize_periods($raw, $fallback = '') {
  $def = ($fallback != '' ? $fallback : osc_item_stats_default_periods_csv());
  $keys = osc_item_stats_parse_period_csv($raw);
  if(empty($keys)) {
    $keys = osc_item_stats_parse_period_csv($def);
  }
  if(empty($keys)) {
    $keys = array('30d', '90d', '12m', 'all');
  }
  return implode(',', $keys);
}


// Keep known chart measure keys (collected or virtual)
function osc_item_stats_sanitize_chart_measures($raw, $fallback = '') {
  $keys = osc_item_stats_parse_csv($raw);
  $valid = array_keys(osc_item_stats_measures(false));
  $keys = array_values(array_intersect($keys, $valid));
  if(empty($keys)) {
    $keys = osc_item_stats_parse_csv($fallback != '' ? $fallback : osc_item_stats_default_enabled_csv());
  }
  return implode(',', $keys);
}


// Sanitize comma-separated hook names
function osc_item_stats_parse_hooks($raw, $fallback = 'user_items_top') {
  $out = array();
  foreach(explode(',', (string)$raw) as $part) {
    $part = strtolower(trim($part));
    if($part != '' && preg_match('/^[a-z0-9_]+$/', $part) && !in_array($part, $out, true)) {
      $out[] = $part;
    }
  }
  if(empty($out) && $fallback != '') {
    $out[] = $fallback;
  }
  return $out;
}


// CSV of sanitized hook names
function osc_item_stats_sanitize_hooks($raw, $fallback = 'user_items_top') {
  return implode(',', osc_item_stats_parse_hooks($raw, $fallback));
}


// Parse CSV preference into unique keys
function osc_item_stats_parse_csv($raw) {
  if(is_array($raw)) {
    $raw = implode(',', $raw);
  }
  $out = array();
  foreach(explode(',', (string)$raw) as $part) {
    $part = osc_item_stats_normalize_key(trim($part));
    if($part != '' && !in_array($part, $out, true)) {
      $out[] = $part;
    }
  }
  return $out;
}


// Preset measure keys
function osc_item_stats_preset_keys($preset) {
  $essential = osc_item_stats_parse_csv(osc_item_stats_default_enabled_csv());
  $engagement = array_merge($essential, array('views_search', 'views_home', 'contactother_clicks', 'shares', 'comments'));
  $commerce = array_merge($engagement, array('favorites', 'contacts', 'offers', 'orders'));
  if($preset == 'engagement') {
    return $engagement;
  }
  if($preset == 'commerce') {
    return $commerce;
  }
  if($preset == 'full') {
    $keys = array();
    foreach(osc_item_stats_measures(false) as $key => $row) {
      if(!isset($row['source']) || $row['source'] != 'item') {
        $keys[] = $key;
      }
    }
    return $keys;
  }
  return $essential;
}


// Measure group labels
function osc_item_stats_groups() {
  $groups = array(
    'traffic' => __('Traffic'),
    'engagement' => __('Engagement'),
    'contact' => __('Contact'),
    'commerce' => __('Commerce'),
    'lifecycle' => __('Listing life'),
    'custom' => __('Custom')
  );
  return osc_apply_filter('osc_item_stats_groups', $groups);
}


// SUM of one measure for an item
function osc_item_stat($measure, $item_id = null) {
  if($item_id === null) {
    $item_id = osc_item_id();
  }
  $item_id = (int)$item_id;
  $column = osc_item_stats_column($measure);
  $value = 0;
  if($item_id > 0 && $column != '') {
    $value = ItemStats::newInstance()->getStat($item_id, $column);
  }
  return (int)osc_apply_filter('osc_item_stat', $value, $measure, $item_id);
}


// Increase one measure for an item
function osc_increase_item_stat($measure, $item_id, $num = 1) {
  $item_id = (int)$item_id;
  $column = osc_item_stats_column($measure);
  if($item_id <= 0 || $column == '') {
    return false;
  }
  return ItemStats::newInstance()->increase($column, $item_id, (int)$num);
}


// Increase one measure for many items in one query
function osc_increase_item_stats($measure, $item_ids, $num = 1) {
  $column = osc_item_stats_column($measure);
  if($column == '' || !is_array($item_ids) || empty($item_ids)) {
    return false;
  }
  return ItemStats::newInstance()->increaseForItems($column, $item_ids, (int)$num);
}


// Record impressions for a list of items (search, home, premium)
function osc_item_stats_record_impressions($items, $measure) {
  if(!osc_item_stats_enabled($measure) || !is_array($items) || empty($items)) {
    return;
  }
  if(osc_is_admin_user_logged_in() || !osc_visitor_is_real_user()) {
    return;
  }
  $logged_id = (osc_is_web_user_logged_in() ? (int)osc_logged_user_id() : 0);
  $ids = array();
  foreach($items as $item) {
    if(!isset($item['pk_i_id'])) {
      continue;
    }
    if(isset($item['b_spam']) && (int)$item['b_spam'] == 1) {
      continue;
    }
    if($logged_id > 0 && isset($item['fk_i_user_id']) && (int)$item['fk_i_user_id'] == $logged_id) {
      continue;
    }
    $ids[] = (int)$item['pk_i_id'];
  }
  if(!empty($ids)) {
    osc_increase_item_stats($measure, $ids);
  }
}


// Home latest impressions once per request
function osc_item_stats_record_home_impressions($items) {
  static $done = false;
  if($done) {
    return;
  }
  $done = true;
  osc_item_stats_record_impressions($items, 'views_home');
}


// Daily series for measure keys
function osc_item_stats_series($measures, $from_date, $item_id = null, $category_id = null, $user_id = null) {
  $keys = array();
  foreach((array)$measures as $measure) {
    $key = osc_item_stats_normalize_key($measure);
    if($key != '' && !in_array($key, $keys, true)) {
      $keys[] = $key;
    }
  }
  if(empty($keys)) {
    return array();
  }

  $item_id = ($item_id === null ? null : (int)$item_id);
  $category_id = ($category_id === null ? null : (int)$category_id);
  $user_id = ($user_id === null ? null : (int)$user_id);
  $from_date = (string)$from_date;
  $cache_key = 'item_stats_series_' . md5(implode(',', $keys) . '|' . $from_date . '|' . (int)$item_id . '|' . (int)$category_id . '|' . (int)$user_id);
  $view = View::newInstance();
  if($view->_exists($cache_key)) {
    $cached = $view->_get($cache_key);
    if(is_array($cached)) {
      return $cached;
    }
  }

  $stat_keys = array();
  $columns = array();
  $virtual_keys = array();
  foreach($keys as $key) {
    if(osc_item_stats_is_virtual($key)) {
      $virtual_keys[] = $key;
      continue;
    }
    $column = osc_item_stats_column($key);
    if($column != '') {
      $stat_keys[] = $key;
      $columns[] = $column;
    }
  }

  $by_date = array();
  if(!empty($columns)) {
    $rows = ItemStats::newInstance()->getSeries($columns, $from_date, $item_id, $category_id, $user_id);
    foreach($rows as $row) {
      $d = (isset($row['d_date']) ? $row['d_date'] : '');
      if($d == '') {
        continue;
      }
      if(!isset($by_date[$d])) {
        $by_date[$d] = array();
      }
      foreach($stat_keys as $i => $key) {
        $by_date[$d][$key] = (int)(isset($row[$columns[$i]]) ? $row[$columns[$i]] : 0);
      }
    }
  }
  if(!empty($virtual_keys)) {
    $vrows = osc_item_stats_item_count_series($virtual_keys, $from_date, $item_id, $category_id, $user_id);
    foreach($vrows as $d => $vals) {
      if(!isset($by_date[$d])) {
        $by_date[$d] = array();
      }
      foreach((array)$vals as $key => $n) {
        $by_date[$d][$key] = (int)$n;
      }
    }
  }

  ksort($by_date);
  $out = array();
  foreach($by_date as $d => $vals) {
    $line = array('d_date' => $d);
    foreach($keys as $key) {
      $line[$key] = (int)(isset($vals[$key]) ? $vals[$key] : 0);
    }
    $out[] = $line;
  }
  $view->_exportVariableToView($cache_key, $out);
  return $out;
}


// Listing-count series from t_item (published per day and live inventory)
function osc_item_stats_item_count_series($measures, $from_date, $item_id = null, $category_id = null, $user_id = null) {
  $want_pub = in_array('items_published', (array)$measures, true);
  $want_live = in_array('items_active', (array)$measures, true);
  if(!$want_pub && !$want_live) {
    return array();
  }

  $from = date('Y-m-d', strtotime((string)$from_date));
  if($from == '' || strtotime($from) === false) {
    $from = date('Y-m-d');
  }
  $to = date('Y-m-d');
  $table = Item::newInstance()->getTableName();
  $where = 'b_active = 1 AND b_enabled = 1 AND b_spam = 0';
  if((int)$item_id > 0) {
    $where .= ' AND pk_i_id = ' . (int)$item_id;
  }
  if((int)$user_id > 0) {
    $where .= ' AND fk_i_user_id = ' . (int)$user_id;
  }
  if((int)$category_id > 0) {
    $where .= ' AND fk_i_category_id = ' . (int)$category_id;
  }

  $published = array();
  $expired = array();
  $mItem = Item::newInstance();
  $res = $mItem->dao->query('SELECT DATE(dt_pub_date) AS d_date, COUNT(*) AS num FROM ' . $table . ' WHERE ' . $where . ' AND dt_pub_date >= \'' . $from . ' 00:00:00\' GROUP BY DATE(dt_pub_date)');
  if($res) {
    foreach($res->result() as $row) {
      if(isset($row['d_date'])) {
        $published[$row['d_date']] = (int)$row['num'];
      }
    }
  }
  if($want_live) {
    $res = $mItem->dao->query('SELECT DATE(dt_expiration) AS d_date, COUNT(*) AS num FROM ' . $table . ' WHERE ' . $where . ' AND dt_expiration >= \'' . $from . ' 00:00:00\' AND dt_expiration < \'9999-12-31 00:00:00\' GROUP BY DATE(dt_expiration)');
    if($res) {
      foreach($res->result() as $row) {
        if(isset($row['d_date'])) {
          $expired[$row['d_date']] = (int)$row['num'];
        }
      }
    }
    $running = 0;
    $res = $mItem->dao->query('SELECT COUNT(*) AS num FROM ' . $table . ' WHERE ' . $where . ' AND dt_pub_date < \'' . $from . ' 00:00:00\' AND dt_expiration > \'' . $from . ' 00:00:00\'');
    if($res) {
      $row = $res->row();
      $running = (int)(isset($row['num']) ? $row['num'] : 0);
    }
  }

  $out = array();
  $ts = strtotime($from);
  $end = strtotime($to);
  if($ts === false || $end === false) {
    return array();
  }
  while($ts <= $end) {
    $d = date('Y-m-d', $ts);
    $pub = (int)(isset($published[$d]) ? $published[$d] : 0);
    $line = array();
    if($want_pub) {
      $line['items_published'] = $pub;
    }
    if($want_live) {
      $running += $pub;
      $line['items_active'] = $running;
      $running -= (int)(isset($expired[$d]) ? $expired[$d] : 0);
      if($running < 0) {
        $running = 0;
      }
    }
    $out[$d] = $line;
    $ts = strtotime('+1 day', $ts);
  }
  return $out;
}


// Batch totals keyed by item id
function osc_item_stats_totals($item_ids, $measures) {
  $columns = array();
  $map = array();
  foreach((array)$measures as $measure) {
    $key = osc_item_stats_normalize_key($measure);
    $column = osc_item_stats_column($key);
    if($key != '' && $column != '') {
      $map[$column] = $key;
      $columns[] = $column;
    }
  }
  $ids = array();
  foreach((array)$item_ids as $id) {
    $id = (int)$id;
    if($id > 0 && !in_array($id, $ids, true)) {
      $ids[] = $id;
    }
  }
  $cache_key = 'item_stats_totals_' . md5(implode(',', $ids) . '|' . implode(',', array_keys($map)));
  $view = View::newInstance();
  if($view->_exists($cache_key)) {
    $cached = $view->_get($cache_key);
    if(is_array($cached)) {
      return $cached;
    }
  }
  $rows = ItemStats::newInstance()->getTotalsByItems($ids, $columns);
  $out = array();
  foreach($rows as $item_id => $row) {
    $line = array();
    foreach($map as $column => $key) {
      $line[$key] = (int)(isset($row[$column]) ? $row[$column] : 0);
    }
    $out[(int)$item_id] = $line;
  }
  $view->_exportVariableToView($cache_key, $out);
  $view->_exportVariableToView('item_stats_totals', $out);
  return $out;
}


// Top listings for one measure
function osc_item_stats_top($measure, $from_date, $limit = 10, $filters = array()) {
  $column = osc_item_stats_column($measure);
  if($column == '') {
    return array();
  }
  return ItemStats::newInstance()->getTopByMeasure($column, $from_date, $limit, $filters);
}


// KPI cards for a seller
function osc_item_stats_kpis($user_id, $period, $cur_rows = null) {
  $user_id = (int)$user_id;
  $r = osc_stats_period_range($period);
  $keys = osc_item_stats_parse_csv(osc_item_stats_user_chart_measures());
  $allowed = osc_item_stats_chart_allowed_keys();
  $keys = array_values(array_intersect($keys, $allowed));
  if(empty($keys) || $user_id <= 0) {
    return array();
  }
  if(is_array($cur_rows)) {
    $cur_sum = osc_item_stats_sum_series($cur_rows, $keys);
  } else {
    $cur_sum = osc_item_stats_sum_series(osc_item_stats_series($keys, $r['from'], null, null, $user_id), $keys);
  }
  $prev_sum = osc_item_stats_sum_series(osc_item_stats_series($keys, $r['prev_from'], null, null, $user_id), $keys, $r['prev_from'], $r['prev_to']);
  $out = array();
  foreach($keys as $key) {
    $def = osc_item_stats_measure($key);
    $value = (int)(isset($cur_sum[$key]) ? $cur_sum[$key] : 0);
    $previous = (int)(isset($prev_sum[$key]) ? $prev_sum[$key] : 0);
    $out[] = array(
      'key' => $key,
      'label' => (is_array($def) ? $def['label'] : $key),
      'color' => osc_item_stats_color($key),
      'value' => $value,
      'previous' => $previous,
      'delta' => osc_stats_period_delta($value, $previous)
    );
  }
  return $out;
}


// Sum series rows by measure key, optionally clipped to from/to
function osc_item_stats_sum_series($rows, $keys, $from = '', $to = '') {
  $sum = array();
  foreach((array)$keys as $key) {
    $sum[$key] = 0;
  }
  foreach((array)$rows as $row) {
    if($from != '' || $to != '') {
      $d = (isset($row['d_date']) ? (string)$row['d_date'] : '');
      if($from != '' && $d < $from) {
        continue;
      }
      if($to != '' && $d > $to) {
        continue;
      }
    }
    foreach((array)$keys as $key) {
      $sum[$key] += (int)(isset($row[$key]) ? $row[$key] : 0);
    }
  }
  return $sum;
}


// Compact number for KPI (exact value stays in title)
function osc_item_stats_format($n) {
  $n = (int)$n;
  if($n >= 1000000) {
    $v = $n / 1000000;
    return (abs($v - round($v)) < 0.05 ? round($v) . 'm' : number_format($v, 1) . 'm');
  }
  if($n >= 1000) {
    $v = $n / 1000;
    return (abs($v - round($v)) < 0.05 ? round($v) . 'k' : number_format($v, 1) . 'k');
  }
  return (string)$n;
}


// Fill missing days with zeros
function osc_item_stats_zero_fill($from, $to, $rows, $measures) {
  $map = array();
  foreach((array)$rows as $row) {
    if(isset($row['d_date'])) {
      $map[$row['d_date']] = $row;
    }
  }
  $keys = array();
  foreach((array)$measures as $measure) {
    $key = osc_item_stats_normalize_key($measure);
    if($key != '') {
      $keys[] = $key;
    }
  }
  $out = array();
  $ts = strtotime($from);
  $end = strtotime($to);
  if($ts === false || $end === false || $ts > $end) {
    return array();
  }
  while($ts <= $end) {
    $day = date('Y-m-d', $ts);
    $line = array('d_date' => $day);
    foreach($keys as $key) {
      $line[$key] = (isset($map[$day][$key]) ? (int)$map[$day][$key] : 0);
    }
    $out[] = $line;
    $ts = strtotime('+1 day', $ts);
  }
  return $out;
}


// Period start date
function osc_stats_period_from($key) {
  $key = osc_stats_period_normalize($key);
  $today = date('Y-m-d');
  if($key == '7d') {
    return date('Y-m-d', strtotime($today . ' -6 days'));
  }
  if($key == '14d') {
    return date('Y-m-d', strtotime($today . ' -13 days'));
  }
  if($key == '90d') {
    return date('Y-m-d', strtotime($today . ' -89 days'));
  }
  if($key == '3m') {
    return date('Y-m-d', strtotime($today . ' -3 months'));
  }
  if($key == '6m') {
    return date('Y-m-d', strtotime($today . ' -6 months'));
  }
  if($key == '12m') {
    return date('Y-m-d', strtotime($today . ' -12 months'));
  }
  if($key == '24m') {
    return date('Y-m-d', strtotime($today . ' -24 months'));
  }
  if($key == 'all') {
    $months = (int)osc_item_stats_cleanup_months();
    if($months < 1) {
      $months = 24;
    }
    return date('Y-m-d', strtotime($today . ' -' . $months . ' months'));
  }
  return date('Y-m-d', strtotime($today . ' -29 days'));
}


// Previous-period start (same length)
function osc_stats_period_prev_from($key) {
  $from = osc_stats_period_from($key);
  $days = osc_stats_period_days($key);
  return date('Y-m-d', strtotime($from . ' -' . $days . ' days'));
}


// Inclusive day count for a period
function osc_stats_period_days($key) {
  $from = osc_stats_period_from($key);
  $today = date('Y-m-d');
  $d = (int)round((strtotime($today) - strtotime($from)) / 86400) + 1;
  return ($d > 0 ? $d : 1);
}


// Current and previous date range for a period key
function osc_stats_period_range($period) {
  $from = osc_stats_period_from($period);
  $prev_from = osc_stats_period_prev_from($period);
  return array(
    'from' => $from,
    'to' => date('Y-m-d'),
    'prev_from' => $prev_from,
    'prev_to' => date('Y-m-d', strtotime($from . ' -1 day'))
  );
}


// Sum a date => count map, optionally clipped to from/to
function osc_stats_period_sum_map($map, $from = '', $to = '') {
  $n = 0;
  foreach((array)$map as $d => $v) {
    $d = (string)$d;
    if($from != '' && $d < $from) {
      continue;
    }
    if($to != '' && $d > $to) {
      continue;
    }
    if(is_array($v)) {
      $n += (int)array_sum($v);
    } else {
      $n += (int)$v;
    }
  }
  return $n;
}


// Percent change versus the previous period, or null when there is nothing to compare
function osc_stats_period_delta($current, $previous) {
  $previous = (int)$previous;
  if($previous <= 0) {
    return null;
  }
  return (int)round((((int)$current) - $previous) / $previous * 100);
}


// KPI percent change markup (same as front-office summary cards)
function osc_stats_period_delta_html($current, $previous) {
  $delta = osc_stats_period_delta($current, $previous);
  if($delta === null) {
    return '';
  }
  $cls = ($delta >= 0 ? 'up' : 'down');
  return '<span class="osc-stats-delta ' . $cls . '">' . ($delta >= 0 ? '+' : '') . (int)$delta . '%</span>';
}


// Admin KPI number plus optional previous-period percent
function osc_stats_kpi_value_html($current, $previous = null) {
  $current = (int)$current;
  $html = '<span class="k-value" title="' . osc_esc_html((string)$current) . '">' . osc_esc_html(osc_item_stats_format($current)) . '</span>';
  if($previous === null) {
    return $html;
  }
  $delta_html = osc_stats_period_delta_html($current, $previous);
  if($delta_html == '') {
    $delta_html = '<span class="osc-stats-delta up">+0%</span>';
  }
  $html .= $delta_html;
  return $html;
}


// All period keys (catalog order)
function osc_stats_period_all_keys() {
  return array('7d', '14d', '30d', '90d', '3m', '6m', '12m', '24m', 'all');
}


// Enabled period keys for a chart
function osc_stats_period_keys($scope = 'admin') {
  if($scope == 'front' || $scope == 'user') {
    return osc_item_stats_parse_period_csv(osc_item_stats_user_chart_periods());
  }
  if($scope == 'item') {
    return osc_item_stats_parse_period_csv(osc_item_stats_item_chart_periods());
  }
  return array('30d', '3m', '12m', 'all');
}


// Map old type_stat and validate
function osc_stats_period_normalize($key) {
  $key = trim((string)$key);
  if($key == 'day') {
    return '30d';
  }
  if($key == 'week') {
    return '3m';
  }
  if($key == 'month') {
    return '12m';
  }
  if(in_array($key, osc_stats_period_all_keys(), true)) {
    return $key;
  }
  return '30d';
}


// Current period from request, cookie (admin), session (front) or default
function osc_stats_period_current($scope = 'admin') {
  $allowed = osc_stats_period_keys($scope);
  $raw = Params::getParam('stats_period');
  if($raw == '') {
    $raw = Params::getParam('type_stat');
  }
  $from_request = ($raw != '');
  $p = ($from_request ? osc_stats_period_normalize($raw) : '');
  if($from_request && !in_array($p, osc_stats_period_all_keys(), true)) {
    $from_request = false;
    $p = '';
  }
  if($scope == 'admin') {
    if($from_request && in_array($p, $allowed, true)) {
      Cookie::newInstance()->push('osc_admin_stats_period', $p);
      Cookie::newInstance()->set();
    } else {
      $saved = Cookie::newInstance()->get_value('osc_admin_stats_period');
      if($saved != '') {
        $p = osc_stats_period_normalize($saved);
      }
    }
  } else {
    if($from_request) {
      Session::newInstance()->_set('osc_user_stats_period', $p);
    }
    if($p == '' || !in_array($p, $allowed, true)) {
      $saved = Session::newInstance()->_get('osc_user_stats_period');
      if($saved != '') {
        $p = osc_stats_period_normalize($saved);
      }
    }
  }
  if($p == '' || !in_array($p, $allowed, true)) {
    if($scope == 'item') {
      $p = osc_stats_period_normalize(osc_item_stats_item_chart_period());
    } else if($scope == 'front' || $scope == 'user') {
      $p = osc_stats_period_normalize(osc_item_stats_user_chart_period());
    } else {
      $p = osc_stats_period_normalize(osc_item_stats_admin_default_period());
    }
  }
  if(!in_array($p, $allowed, true)) {
    $p = (isset($allowed[0]) ? $allowed[0] : '30d');
  }
  if($scope == 'admin' && Params::getParam('stats_period') == '') {
    Params::setParam('stats_period', $p);
  }
  return $p;
}


// Period button labels
function osc_stats_period_label($key) {
  $key = osc_stats_period_normalize($key);
  $labels = array(
    '7d' => __('Last 7 days'),
    '14d' => __('Last 14 days'),
    '30d' => __('Last 30 days'),
    '90d' => __('Last 90 days'),
    '3m' => __('Last 3 months'),
    '6m' => __('Last 6 months'),
    '12m' => __('Last 12 months'),
    '24m' => __('Last 24 months'),
    'all' => __('All time')
  );
  return (isset($labels[$key]) ? $labels[$key] : $labels['30d']);
}


// Period button titles
function osc_stats_period_title($key) {
  $key = osc_stats_period_normalize($key);
  $titles = array(
    '7d' => __('Daily totals for the last 7 days'),
    '14d' => __('Daily totals for the last 14 days'),
    '30d' => __('Daily totals for the last 30 days'),
    '90d' => __('Daily totals for the last 90 days'),
    '3m' => __('Daily totals for the last 3 months'),
    '6m' => __('Daily totals for the last 6 months'),
    '12m' => __('Daily totals for the last 12 months'),
    '24m' => __('Daily totals for the last 24 months'),
    'all' => __('Daily totals for all stored days')
  );
  return (isset($titles[$key]) ? $titles[$key] : $titles['30d']);
}


// Chart subtitle with the selected period total
function osc_admin_stats_chart_total($label, $total, $period = '', $more = array()) {
  if($period == '') {
    $period = osc_stats_period_current('admin');
  }
  $period = osc_stats_period_normalize($period);
  $n = osc_item_stats_format($total);
  if($period == 'all') {
    $text = sprintf(__('%s, all time: %s'), $label, $n);
  } else {
    $in = array(
      '7d' => __('the last 7 days'),
      '14d' => __('the last 14 days'),
      '30d' => __('the last 30 days'),
      '90d' => __('the last 90 days'),
      '3m' => __('the last 3 months'),
      '6m' => __('the last 6 months'),
      '12m' => __('the last 12 months'),
      '24m' => __('the last 24 months')
    );
    $span = (isset($in[$period]) ? $in[$period] : $in['30d']);
    $text = sprintf(__('%s in %s: %s'), $label, $span, $n);
  }
  if(is_array($more)) {
    foreach($more as $mlabel => $mval) {
      $text .= '. ' . sprintf(__('%s: %s'), $mlabel, osc_item_stats_format($mval));
    }
  }
  return '<b class="stats-title">' . osc_esc_html($text) . '</b>';
}


// Shared admin period toolbar (smallest to largest: 30d, 3m, 12m, all)
function osc_admin_stats_period_links($action) {
  $current = osc_stats_period_current('admin');
  $html = '<div class="btn-group float-right">';
  foreach(osc_stats_period_keys('admin') as $key) {
    $url = osc_admin_base_url(true) . '?page=stats&action=' . rawurlencode($action) . '&stats_period=' . $key;
    $keep = array('measures', 'item_id', 'user_id', 'category_id');
    foreach($keep as $param) {
      $val = Params::getParam($param);
      if(is_array($val)) {
        $val = implode(',', $val);
      }
      if($val != '') {
        $url .= '&' . $param . '=' . rawurlencode($val);
      }
    }
    $cls = 'btn' . ($current == $key ? ' btn-green' : '');
    $html .= '<a href="' . osc_esc_html($url) . '" class="' . $cls . '" title="' . osc_esc_html(osc_stats_period_title($key)) . '">' . osc_esc_html(osc_stats_period_label($key)) . '</a>';
  }
  $html .= '</div>';
  return $html;
}


// Front-office period tabs: POST so the page URL stays clean
function osc_item_stats_period_links($base_url, $current, $scope = 'front') {
  $html = '<form method="post" action="' . osc_esc_html($base_url) . '" class="alert-frequency stats-actions">';
  $item = (int)Params::getParam('stats_item');
  if($item > 0) {
    $html .= '<input type="hidden" name="stats_item" value="' . $item . '" />';
  }
  foreach(osc_stats_period_keys($scope) as $key) {
    $cls = ($current == $key ? 'active' : '');
    $html .= '<button type="submit" name="stats_period" value="' . osc_esc_html($key) . '" class="' . $cls . '" title="' . osc_esc_html(osc_stats_period_title($key)) . '">' . osc_esc_html(osc_stats_period_label($key)) . '</button>';
  }
  $html .= '</form>';
  return $html;
}


// Clean URL after a front-office period change
function osc_item_stats_period_return_url() {
  $page = Params::getParam('page');
  $action = Params::getParam('action');
  if($page == 'user' && ($action == 'items' || $action == '')) {
    $url = osc_user_list_items_url();
    $item = (int)Params::getParam('stats_item');
    if($item > 0) {
      $url .= (strpos($url, '?') === false ? '?' : '&') . 'stats_item=' . $item;
    }
    return $url;
  }
  if($page == 'item' && (int)Params::getParam('id') > 0 && function_exists('osc_item_url_from_item')) {
    $item = osc_get_item_row((int)Params::getParam('id'));
    if(is_array($item) && !empty($item)) {
      $url = osc_item_url_from_item($item);
      if($url != '') {
        return $url;
      }
    }
  }
  $ref = osc_get_http_referer();
  if($ref != '') {
    $ref = preg_replace('#/stats_period,[^/?#]+#', '', $ref);
    $ref = preg_replace('/([?&])stats_period=[^&]*/', '$1', $ref);
    $ref = preg_replace('/[?&]$/', '', $ref);
    return $ref;
  }
  return osc_base_url();
}


// Store the chosen front-office period in session and drop it from the URL
function osc_item_stats_capture_period() {
  if(defined('OC_ADMIN') && OC_ADMIN) {
    return;
  }
  $raw = Params::getParam('stats_period');
  if($raw == '') {
    $raw = Params::getParam('type_stat');
  }
  if($raw == '') {
    return;
  }
  $p = osc_stats_period_normalize($raw);
  if(!in_array($p, osc_stats_period_all_keys(), true)) {
    return;
  }
  Session::newInstance()->_set('osc_user_stats_period', $p);
  $url = osc_item_stats_period_return_url();
  if($url != '') {
    osc_redirect_to($url);
  }
}


// Compact inline counters for one listing
function osc_item_stats_inline($item_id, $measures = null) {
  $item_id = (int)$item_id;
  if($item_id <= 0) {
    return '';
  }
  if($measures === null) {
    $measures = array_values(array_intersect(array('views', 'premium_views', 'contactforms', 'favorites'), osc_item_stats_enabled_keys()));
  }
  $parts = array();
  $titles = array();
  foreach((array)$measures as $measure) {
    $def = osc_item_stats_measure($measure);
    if(!is_array($def) || !osc_item_stats_enabled($measure)) {
      continue;
    }
    $n = osc_item_stat($measure, $item_id);
    $parts[] = sprintf('%s %s', osc_item_stats_format($n), osc_esc_html($def['label']));
    $titles[] = $n . ' ' . $def['label'];
  }
  if(empty($parts)) {
    return '';
  }
  return '<span class="osc-stats-inline" title="' . osc_esc_html(implode(' · ', $titles)) . '">' . implode(' · ', $parts) . '</span>';
}


// User summary / listing chart types (HTML/SVG, same shapes as Google Charts bar, line, area, stacked)
function osc_item_stats_chart_types() {
  return array(
    'bar' => __('Bar'),
    'line' => __('Line'),
    'area' => __('Area'),
    'stacked_bar' => __('Stacked bar'),
    'stacked_area' => __('Stacked area')
  );
}


// HTML + SVG chart
function osc_item_stats_chart($item_id = null, $category_id = null, $user_id = null, $options = array()) {
  $options = osc_apply_filter('osc_item_stats_chart', (array)$options, $item_id, $category_id, $user_id);
  $scope = (isset($options['period_scope']) ? (string)$options['period_scope'] : 'front');
  if($scope != 'item') {
    $scope = 'front';
  }
  $period = (isset($options['period']) && $options['period'] != '' ? osc_stats_period_normalize($options['period']) : osc_stats_period_current($scope));
  if(!in_array($period, osc_stats_period_keys($scope), true)) {
    $period = osc_stats_period_current($scope);
  }
  $type = (isset($options['type']) ? (string)$options['type'] : osc_item_stats_user_chart_type());
  $chart_types = osc_item_stats_chart_types();
  if(!isset($chart_types[$type])) {
    $type = 'line';
  }
  $height = (isset($options['height']) ? (int)$options['height'] : 360);
  if($height < 120) {
    $height = 360;
  }
  $show_links = (!isset($options['show_period_links']) || $options['show_period_links']);
  $show_kpis = (!empty($options['show_kpis']));
  $show_table = (!empty($options['show_table']));
  $title = (isset($options['title']) ? (string)$options['title'] : __('Listing statistics'));
  $empty_hint = (isset($options['empty_hint']) ? (string)$options['empty_hint'] : __('No views yet. Stats appear when people open your listings.'));
  $base_url = (isset($options['base_url']) ? $options['base_url'] : osc_user_list_items_url());

  $measures = array();
  if(isset($options['measures'])) {
    $measures = osc_item_stats_parse_csv(is_array($options['measures']) ? implode(',', $options['measures']) : $options['measures']);
  } else {
    $measures = osc_item_stats_parse_csv(osc_item_stats_user_chart_measures());
  }
  $measures = array_values(array_intersect($measures, osc_item_stats_chart_allowed_keys()));
  if(empty($measures)) {
    $measures = array('views');
  }

  $from = osc_stats_period_from($period);
  $to = date('Y-m-d');
  $rows = osc_item_stats_series($measures, $from, $item_id, $category_id, $user_id);
  $rows = osc_item_stats_zero_fill($from, $to, $rows, $measures);
  $sums = osc_item_stats_sum_series($rows, $measures);
  $has_data = false;
  foreach($sums as $n) {
    if($n > 0) {
      $has_data = true;
      break;
    }
  }

  $html = '<div class="osc-stats">';
  if($show_kpis && (int)$user_id > 0) {
    $html .= osc_item_stats_kpis_html($user_id, $period, $rows);
  }
  if($show_links) {
    $html .= osc_item_stats_period_links($base_url, $period, $scope);
  }
  $html .= osc_item_stats_svg($rows, $measures, $type, $height, $title);
  if(!$has_data) {
    $html .= '<p class="osc-stats-empty">' . osc_esc_html($empty_hint) . '</p>';
  }
  if($show_table) {
    $html .= osc_item_stats_table_html($measures, $sums, $title);
  }
  $html .= '</div>';

  return osc_apply_filter('osc_item_stats_chart_html', $html, $item_id, $category_id, $user_id, $options);
}


// KPI row markup
function osc_item_stats_kpis_html($user_id, $period, $cur_rows = null) {
  $cards = osc_item_stats_kpis($user_id, $period, $cur_rows);
  if(empty($cards)) {
    return '';
  }
  $html = '<div class="osc-stats-kpis">';
  foreach($cards as $card) {
    $delta = osc_stats_period_delta_html($card['value'], $card['previous']);
    $html .= '<div class="osc-stats-kpi">';
    $html .= '<span class="osc-stats-kpi-label">' . osc_esc_html($card['label']) . '</span>';
    $html .= '<span class="osc-stats-kpi-value" title="' . osc_esc_html((string)$card['value']) . '">' . osc_esc_html(osc_item_stats_format($card['value'])) . '</span>';
    $html .= $delta;
    $html .= '</div>';
  }
  $html .= '</div>';
  return $html;
}


// Totals table markup
function osc_item_stats_table_html($measures, $sums, $caption) {
  $html = '<table class="osc-stats-table"><caption>' . osc_esc_html($caption) . '</caption><tbody>';
  foreach((array)$measures as $measure) {
    $def = osc_item_stats_measure($measure);
    if(!is_array($def)) {
      continue;
    }
    $n = (int)(isset($sums[$measure]) ? $sums[$measure] : 0);
    $html .= '<tr><th>' . osc_esc_html($def['label']) . '</th><td title="' . osc_esc_html((string)$n) . '">' . osc_esc_html(osc_item_stats_format($n)) . '</td></tr>';
  }
  $html .= '</tbody></table>';
  return $html;
}


// Inline SVG (bar, line, area, stacked bar, stacked area)
function osc_item_stats_svg($rows, $measures, $type, $height, $title) {
  $n = count($rows);
  if($n < 1) {
    return '';
  }
  $stacked = ($type == 'stacked_bar' || $type == 'stacked_area');
  $is_line = ($type == 'line' || $type == 'area' || $type == 'stacked_area');
  $max = 1;
  foreach($rows as $row) {
    if($stacked) {
      $sum = 0;
      foreach($measures as $measure) {
        $sum += (int)(isset($row[$measure]) ? $row[$measure] : 0);
      }
      if($sum > $max) {
        $max = $sum;
      }
    } else {
      foreach($measures as $measure) {
        $v = (int)(isset($row[$measure]) ? $row[$measure] : 0);
        if($v > $max) {
          $max = $v;
        }
      }
    }
  }
  if($max < 1) {
    $max = 1;
  }
  $steps = 4;
  if($max <= 4) {
    $steps = $max;
  } else {
    $max = (int)ceil($max / 4) * 4;
  }
  $pad_l = 36;
  $pad_r = 8;
  $pad_t = 12;
  $pad_b = 24;
  $vb_w = 800;
  $vb_h = 360;
  $plot_w = $vb_w - $pad_l - $pad_r;
  $plot_h = $vb_h - $pad_t - $pad_b;
  $series_n = count($measures);
  $base_y = $pad_t + $plot_h;
  $svg = '<svg class="osc-stats-svg" role="img" aria-label="' . osc_esc_html($title) . '" viewBox="0 0 ' . $vb_w . ' ' . $vb_h . '" preserveAspectRatio="xMidYMid meet" style="height:' . (int)$height . 'px">';
  $svg .= '<title>' . osc_esc_html($title) . '</title>';
  for($g = 0; $g <= $steps; $g++) {
    $gy = $pad_t + ($plot_h * $g / $steps);
    $gv = (int)round($max * (1 - ($g / $steps)));
    $svg .= '<line class="osc-stats-grid" x1="' . $pad_l . '" y1="' . round($gy, 2) . '" x2="' . ($pad_l + $plot_w) . '" y2="' . round($gy, 2) . '" />';
    $svg .= '<text x="' . ($pad_l - 4) . '" y="' . round($gy + 3, 2) . '" text-anchor="end" class="osc-stats-axis">' . osc_esc_html((string)$gv) . '</text>';
  }

  if($is_line) {
    $gap = ($n > 1 ? $plot_w / ($n - 1) : $plot_w);
    $cum = array_fill(0, $n, 0);
    foreach($measures as $measure) {
      $def = osc_item_stats_measure($measure);
      $color = osc_item_stats_color($measure);
      $label = (is_array($def) ? $def['label'] : $measure);
      $top = array();
      $bot = array();
      $i = 0;
      foreach($rows as $row) {
        $v = (int)(isset($row[$measure]) ? $row[$measure] : 0);
        $x = $pad_l + ($n > 1 ? $i * $gap : $plot_w / 2);
        if($stacked) {
          $y0 = $base_y - (($cum[$i] / $max) * $plot_h);
          $cum[$i] += $v;
          $y1 = $base_y - (($cum[$i] / $max) * $plot_h);
          $bot[] = round($x, 2) . ',' . round($y0, 2);
          $top[] = round($x, 2) . ',' . round($y1, 2);
        } else {
          $y = $base_y - (($v / $max) * $plot_h);
          $top[] = round($x, 2) . ',' . round($y, 2);
        }
        $i++;
      }
      if($type == 'area' || $type == 'stacked_area') {
        $poly = $top;
        if($stacked) {
          for($j = $n - 1; $j >= 0; $j--) {
            $poly[] = $bot[$j];
          }
        } else {
          $x_last = $pad_l + ($n > 1 ? ($n - 1) * $gap : $plot_w / 2);
          $x_first = $pad_l + ($n > 1 ? 0 : $plot_w / 2);
          $poly[] = round($x_last, 2) . ',' . round($base_y, 2);
          $poly[] = round($x_first, 2) . ',' . round($base_y, 2);
        }
        $op = ($stacked ? '0.75' : '0.28');
        $svg .= '<polygon fill="' . osc_esc_html($color) . '" fill-opacity="' . $op . '" stroke="none" points="' . implode(' ', $poly) . '" />';
      }
      $svg .= '<polyline fill="none" stroke="' . osc_esc_html($color) . '" stroke-width="2" points="' . implode(' ', $top) . '"><title>' . osc_esc_html($label) . '</title></polyline>';
    }
  } else {
    $slot = $plot_w / $n;
    if($stacked) {
      $bar_w = max(5, $slot * 0.8);
      $x0 = ($slot - $bar_w) / 2;
      foreach($rows as $i => $row) {
        $acc = 0;
        foreach($measures as $measure) {
          $def = osc_item_stats_measure($measure);
          $color = osc_item_stats_color($measure);
          $label = (is_array($def) ? $def['label'] : $measure);
          $v = (int)(isset($row[$measure]) ? $row[$measure] : 0);
          $h = ($v / $max) * $plot_h;
          $x = $pad_l + ($i * $slot) + $x0;
          $y = $base_y - ((($acc + $v) / $max) * $plot_h);
          $acc += $v;
          $svg .= '<rect x="' . round($x, 2) . '" y="' . round($y, 2) . '" width="' . round($bar_w, 2) . '" height="' . round($h, 2) . '" fill="' . osc_esc_html($color) . '"><title>' . osc_esc_html($row['d_date'] . ': ' . $v . ' ' . $label) . '</title></rect>';
        }
      }
    } else {
      $bar_w = max(5, ($slot * 0.9) / max(1, $series_n));
      $group_w = $bar_w * $series_n;
      $x0 = ($slot - $group_w) / 2;
      foreach($rows as $i => $row) {
        foreach($measures as $si => $measure) {
          $def = osc_item_stats_measure($measure);
          $color = osc_item_stats_color($measure);
          $v = (int)(isset($row[$measure]) ? $row[$measure] : 0);
          $h = ($v / $max) * $plot_h;
          $x = $pad_l + ($i * $slot) + $x0 + ($si * $bar_w);
          $y = $base_y - $h;
          $label = (is_array($def) ? $def['label'] : $measure);
          $svg .= '<rect x="' . round($x, 2) . '" y="' . round($y, 2) . '" width="' . round($bar_w, 2) . '" height="' . round($h, 2) . '" fill="' . osc_esc_html($color) . '"><title>' . osc_esc_html($row['d_date'] . ': ' . $v . ' ' . $label) . '</title></rect>';
        }
      }
    }
  }

  $step = ($n > 12 ? (int)ceil($n / 6) : 1);
  for($i = 0; $i < $n; $i += $step) {
    if($is_line) {
      $x = $pad_l + ($n > 1 ? ($i / ($n - 1)) * $plot_w : $plot_w / 2);
    } else {
      $slot = $plot_w / $n;
      $x = $pad_l + ($i * $slot) + ($slot / 2);
    }
    $svg .= '<text x="' . round($x, 2) . '" y="' . ($vb_h - 6) . '" text-anchor="middle" class="osc-stats-axis">' . osc_esc_html(substr($rows[$i]['d_date'], 5)) . '</text>';
  }
  $svg .= '</svg>';

  $legend = '<div class="osc-stats-legend">';
  foreach($measures as $measure) {
    $def = osc_item_stats_measure($measure);
    if(!is_array($def)) {
      continue;
    }
    $legend .= '<span class="osc-stats-legend-item"><i style="background:' . osc_esc_html(osc_item_stats_color($measure)) . '"></i>' . osc_esc_html($def['label']) . '</span>';
  }
  $legend .= '</div>';

  return '<div class="osc-stats-chart">' . $svg . $legend . '</div>';
}


// Audience options for front-office charts (plugins may add keys)
function osc_item_stats_chart_audience_options() {
  $options = array(
    'all' => __('All users'),
    'company' => __('Company users')
  );
  return osc_apply_filter('osc_item_stats_chart_audience_options', $options);
}


// Whether the given (or logged-in) user may see user summary and listing charts
function osc_item_stats_user_can_see_charts($user = null) {
  if($user === null) {
    if(!osc_is_web_user_logged_in()) {
      return false;
    }
    $user = osc_logged_user();
  }
  if(!is_array($user) || empty($user)) {
    return false;
  }
  $audience = osc_item_stats_chart_audience();
  $allowed = true;
  if($audience == 'company') {
    $allowed = (isset($user['b_company']) && (int)$user['b_company'] == 1);
  }
  return (bool)osc_apply_filter('osc_item_stats_user_can_see_charts', $allowed, $user, $audience);
}


// User summary block on My listings
function osc_user_items_stats_block() {
  if(!osc_is_web_user_logged_in() || !osc_item_stats_user_chart_enabled() || !osc_item_stats_user_can_see_charts()) {
    return;
  }
  $user_id = (int)osc_logged_user_id();
  if($user_id <= 0 || Item::newInstance()->countByUserID($user_id) <= 0) {
    return;
  }
  osc_item_stats_enqueue_assets();
  $item_id = (int)Params::getParam('stats_item');
  if($item_id > 0) {
    $item = osc_get_item_row($item_id);
    if(!$item || (int)$item['fk_i_user_id'] != $user_id) {
      $item_id = 0;
    }
  }
  echo osc_item_stats_chart(($item_id > 0 ? $item_id : null), null, $user_id, array(
    'type' => osc_item_stats_user_chart_type(),
    'period' => osc_stats_period_current('front'),
    'period_scope' => 'front',
    'show_kpis' => true,
    'show_table' => false,
    'base_url' => osc_user_list_items_url()
  ));
}


// Listing-page chart for the owner or a logged-in administrator
function osc_item_page_stats_block() {
  if(!osc_item_stats_item_chart_enabled() || !osc_is_ad_page()) {
    return;
  }
  $item_id = (int)osc_item_id();
  if($item_id <= 0) {
    return;
  }
  $owner = (osc_is_web_user_logged_in() && (int)osc_item_user_id() === (int)osc_logged_user_id() && osc_item_stats_user_can_see_charts());
  $admin = (osc_item_stats_item_chart_admin() && osc_is_admin_user_logged_in());
  if(!$owner && !$admin) {
    return;
  }
  osc_item_stats_enqueue_assets();
  echo osc_item_stats_chart($item_id, null, null, array(
    'type' => osc_item_stats_item_chart_type(),
    'period' => osc_stats_period_current('item'),
    'period_scope' => 'item',
    'measures' => osc_item_stats_item_chart_measures(),
    'show_kpis' => false,
    'show_table' => true,
    'base_url' => osc_item_url(),
    'title' => __('Listing statistics')
  ));
}


// Attach user summary and listing charts to configured hooks
function osc_item_stats_register_chart_hooks() {
  foreach(osc_item_stats_parse_hooks(osc_item_stats_user_chart_hooks(), 'user_items_top') as $hook) {
    osc_add_hook($hook, 'osc_user_items_stats_block');
  }
  foreach(osc_item_stats_parse_hooks(osc_item_stats_item_chart_hooks(), 'item_top') as $hook) {
    osc_add_hook($hook, 'osc_item_page_stats_block');
  }
}


// Per-listing inline counters
function osc_user_item_stats_inline($item_id = null) {
  if(!osc_is_web_user_logged_in() || !osc_item_stats_user_chart_enabled() || !osc_item_stats_user_can_see_charts()) {
    return;
  }
  static $batch = null;
  static $measures = null;
  if($measures === null) {
    $measures = array_values(array_intersect(array('views', 'premium_views', 'contactforms', 'favorites'), osc_item_stats_enabled_keys()));
  }
  if($item_id === null) {
    $item_id = osc_item_id();
  }
  $item_id = (int)$item_id;
  if($item_id <= 0 || empty($measures)) {
    return;
  }
  if($batch === null) {
    $ids = array();
    $items = View::newInstance()->_get('items');
    if(is_array($items)) {
      foreach($items as $it) {
        if(isset($it['pk_i_id'])) {
          $ids[] = (int)$it['pk_i_id'];
        }
      }
    }
    if(!in_array($item_id, $ids, true)) {
      $ids[] = $item_id;
    }
    $batch = osc_item_stats_totals($ids, $measures);
  }
  $parts = array();
  $titles = array();
  $row = (isset($batch[$item_id]) ? $batch[$item_id] : array());
  foreach($measures as $measure) {
    $def = osc_item_stats_measure($measure);
    if(!is_array($def)) {
      continue;
    }
    $n = (int)(isset($row[$measure]) ? $row[$measure] : 0);
    $parts[] = sprintf('%s %s', osc_item_stats_format($n), osc_esc_html($def['label']));
    $titles[] = $n . ' ' . $def['label'];
  }
  if(empty($parts)) {
    return;
  }
  echo '<span class="osc-stats-inline" title="' . osc_esc_html(implode(' · ', $titles)) . '">' . implode(' · ', $parts) . '</span>';
}


// Enqueue item-page JS/CSS
function osc_item_stats_enqueue() {
  if(osc_is_ad_page() || osc_is_list_items() || osc_is_user_dashboard()) {
    osc_item_stats_enqueue_assets();
  }
  if(!osc_is_ad_page()) {
    return;
  }
  if(empty(osc_item_stats_ajax_measures())) {
    return;
  }
  $js = osc_assets_url('js/item-stats.js');
  $js_file = osc_lib_path() . 'osclass/assets/js/item-stats.js';
  if(file_exists($js_file)) {
    $js .= '?v=' . filemtime($js_file);
  }
  osc_register_script('osc-item-stats', $js, array('jquery'));
  osc_enqueue_script('osc-item-stats');
  osc_add_hook('header', 'osc_item_stats_print_vars', 9);
  osc_add_hook('footer', 'osc_item_stats_print_vars');
}


// Print localized JS config on the item page
function osc_item_stats_print_vars() {
  static $printed = false;
  if($printed) {
    return;
  }
  $item_id = (int)osc_item_id();
  if($item_id <= 0) {
    return;
  }
  $printed = true;
  $token = osc_csrf_token_url();
  $vars = array(
    'ajaxUrl' => osc_base_url(true) . '?page=ajax&action=item_stats&' . $token,
    'octoken' => str_replace('octoken=', '', $token),
    'itemId' => $item_id,
    'ajaxMeasures' => osc_item_stats_ajax_measures(),
    'engagedSeconds' => osc_item_stats_engaged_seconds(),
    'phoneSelectors' => osc_item_stats_phone_selectors(),
    'otherSelectors' => osc_item_stats_contactother_selectors()
  );
  echo '<script type="text/javascript">window.oscItemStats = ' . json_encode($vars) . ';</script>';
}


// Shared CSS for seller block and item page
function osc_item_stats_enqueue_assets() {
  static $done = false;
  if($done) {
    return;
  }
  $done = true;
  $css = osc_assets_url('css/item-stats.css');
  $css_file = osc_lib_path() . 'osclass/assets/css/item-stats.css';
  if(file_exists($css_file)) {
    $css .= '?v=' . filemtime($css_file);
  }
  osc_enqueue_style('osc-item-stats', $css);
}


// Keep comment counters in sync with active comments on the listing
function osc_item_stats_sync_item_comments($item_id) {
  $item_id = (int)$item_id;
  if($item_id <= 0) {
    return;
  }
  if(!osc_item_stats_enabled('comments') && !osc_item_stats_enabled('rated_comments')) {
    return;
  }

  $mComments = ItemComment::newInstance();
  $mComments->dao->select('COUNT(*) AS total, SUM(CASE WHEN i_rating > 0 THEN 1 ELSE 0 END) AS rated');
  $mComments->dao->from($mComments->getTableName());
  $mComments->dao->where('fk_i_item_id', $item_id);
  $mComments->dao->where('b_active', 1);
  $mComments->dao->where('b_enabled', 1);
  $mComments->dao->where('b_spam', 0);
  $result = $mComments->dao->get();
  $row = ($result ? $result->row() : array());
  $total = (int)(isset($row['total']) ? $row['total'] : 0);
  $rated = (int)(isset($row['rated']) ? $row['rated'] : 0);

  $mStats = ItemStats::newInstance();
  if(osc_item_stats_enabled('comments')) {
    $sum = $mStats->getStat($item_id, 'i_num_comments');
    $mStats->applyDelta('i_num_comments', $item_id, $total - $sum);
  }
  if(osc_item_stats_enabled('rated_comments')) {
    $sum = $mStats->getStat($item_id, 'i_num_rated_comments');
    $mStats->applyDelta('i_num_rated_comments', $item_id, $rated - $sum);
  }
}


// Sync listing comment stats after a comment id changes
function osc_item_stats_on_comment_changed($comment_id) {
  $comment_id = (int)$comment_id;
  if($comment_id <= 0) {
    return;
  }
  $comment = ItemComment::newInstance()->findByPrimaryKey($comment_id);
  if(!is_array($comment) || !isset($comment['fk_i_item_id'])) {
    return;
  }
  osc_item_stats_sync_item_comments((int)$comment['fk_i_item_id']);
}


// Daily repair for comment stats from t_item_comment only (never scans all listings)
function osc_item_stats_cron_recalc() {
  $do_comments = osc_item_stats_enabled('comments');
  $do_rated = osc_item_stats_enabled('rated_comments');
  if(!$do_comments && !$do_rated) {
    return;
  }

  $limit = (int)osc_apply_filter('osc_item_stats_cron_recalc_limit', 200);
  if($limit < 1) {
    return;
  }
  if($limit > 1000) {
    $limit = 1000;
  }

  $having = array();
  if($do_comments) {
    $having[] = 'x.actual_comments <> COALESCE(SUM(s.i_num_comments), 0)';
  }
  if($do_rated) {
    $having[] = 'x.actual_rated <> COALESCE(SUM(s.i_num_rated_comments), 0)';
  }

  $sql = sprintf(
    'SELECT x.fk_i_item_id FROM (SELECT fk_i_item_id, SUM(CASE WHEN b_active = 1 AND b_enabled = 1 AND b_spam = 0 THEN 1 ELSE 0 END) AS actual_comments, SUM(CASE WHEN b_active = 1 AND b_enabled = 1 AND b_spam = 0 AND i_rating > 0 THEN 1 ELSE 0 END) AS actual_rated FROM %s GROUP BY fk_i_item_id) x LEFT JOIN %s s ON s.fk_i_item_id = x.fk_i_item_id GROUP BY x.fk_i_item_id, x.actual_comments, x.actual_rated HAVING %s LIMIT %d',
    ItemComment::newInstance()->getTableName(),
    ItemStats::newInstance()->getTableName(),
    implode(' OR ', $having),
    $limit
  );
  $result = ItemStats::newInstance()->dao->query($sql);
  if(!$result) {
    return;
  }
  foreach($result->result() as $row) {
    if(isset($row['fk_i_item_id'])) {
      osc_item_stats_sync_item_comments((int)$row['fk_i_item_id']);
    }
  }
}


// Google Charts loader snippet for Omega
function osc_admin_stats_google_loader() {
  return '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
}


// Zero-fill daily count rows from Stats::* methods
function osc_admin_stats_count_map($from_date, $rows, $value_key = 'num') {
  $map = array();
  $ts = strtotime($from_date);
  $end = strtotime(date('Y-m-d'));
  if($ts === false || $end === false) {
    return array();
  }
  while($ts <= $end) {
    $map[date('Y-m-d', $ts)] = 0;
    $ts = strtotime('+1 day', $ts);
  }
  foreach((array)$rows as $row) {
    if(!isset($row['d_date'])) {
      continue;
    }
    $map[$row['d_date']] = (int)(isset($row[$value_key]) ? $row[$value_key] : 0);
  }
  ksort($map);
  return $map;
}


// Selected-period daily map plus previous-period total. Pass rows that start at prev_from.
function osc_admin_stats_period_counts($period, $rows, $value_key = 'num') {
  $r = osc_stats_period_range($period);
  $all = osc_admin_stats_count_map($r['prev_from'], $rows, $value_key);
  $map = array();
  foreach($all as $d => $n) {
    if($d >= $r['from']) {
      $map[$d] = $n;
    }
  }
  return array($map, osc_stats_period_sum_map($all, $r['prev_from'], $r['prev_to']));
}


// Selected-period listing-stat rows, current totals and previous-period totals
function osc_item_stats_period_series($period, $measures, $item_id = null, $category_id = null, $user_id = null) {
  $r = osc_stats_period_range($period);
  $raw = osc_item_stats_series($measures, $r['prev_from'], $item_id, $category_id, $user_id);
  $rows = osc_item_stats_zero_fill($r['from'], $r['to'], $raw, $measures);
  return array(
    'rows' => $rows,
    'sums' => osc_item_stats_sum_series($rows, $measures),
    'prev' => osc_item_stats_sum_series($raw, $measures, $r['prev_from'], $r['prev_to'])
  );
}


// Previous-window totals from an open-ended from-date count (wider starts at prev_from)
function osc_admin_stats_open_prev($from_prev, $current) {
  $out = array();
  foreach((array)$from_prev as $k => $v) {
    $out[$k] = max(0, (int)$v - (int)(isset($current[$k]) ? $current[$k] : 0));
  }
  return $out;
}


// Fill a date map with several series from rows that have d_date, s_type and num
function osc_admin_stats_kind_map($from_date, $rows, $kinds) {
  $kinds = array_values((array)$kinds);
  $blank = array();
  foreach($kinds as $k) {
    $blank[] = 0;
  }
  $map = array();
  $ts = strtotime($from_date);
  $end = strtotime(date('Y-m-d'));
  if($ts === false || $end === false) {
    return array();
  }
  while($ts <= $end) {
    $map[date('Y-m-d', $ts)] = $blank;
    $ts = strtotime('+1 day', $ts);
  }
  $index = array_flip($kinds);
  foreach((array)$rows as $row) {
    if(!isset($row['d_date']) || !isset($row['s_type'])) {
      continue;
    }
    $d = $row['d_date'];
    $k = $row['s_type'];
    if(isset($map[$d]) && isset($index[$k])) {
      $vals = $map[$d];
      $vals[$index[$k]] = (int)(isset($row['num']) ? $row['num'] : 0);
      $map[$d] = $vals;
    }
  }
  ksort($map);
  return $map;
}


// Dashboard widget column (1-3)
function osc_admin_dash_widget_column($id, $default = 2) {
  $v = (int)osc_get_preference('admindash_widget_column_' . $id, 'osclass');
  if($v < 1 || $v > 3) {
    return (int)$default;
  }
  return $v;
}


// Dashboard statistics chart widgets (period charts default to column 2, mix charts to column 3)
function osc_admin_dash_stats_widgets() {
  $w = array(
    'chart-listing-views' => array('label' => __('Listing views'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => false, 'ph' => 'placeholder-listing-views'),
    'chart-items' => array('label' => __('Listing statistics'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => false, 'ph' => 'placeholder-item'),
    'chart-comments' => array('label' => __('Comments statistics'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => false, 'ph' => 'placeholder-comment'),
    'chart-reports' => array('label' => __('Report statistics'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => false, 'ph' => 'placeholder-report'),
    'chart-users' => array('label' => __('User statistics'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => false, 'ph' => 'placeholder-user'),
    'chart-alerts' => array('label' => __('New alerts'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-alerts'),
    'chart-subscribers' => array('label' => __('New subscribers'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-subscribers'),
    'chart-alerts-sent' => array('label' => __('Emails sent'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-alerts-sent'),
    'chart-alerts-active' => array('label' => __('Active alerts'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-alerts-active'),
    'chart-comments-kind' => array('label' => __('Comments: registered vs guest'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-comments-kind'),
    'chart-alerts-kind' => array('label' => __('New alerts by account'), 'group' => 'main', 'plot' => 'series', 'default_col' => 2, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-alerts-kind'),
    'chart-users-country' => array('label' => __('Users per country'), 'group' => 'main', 'plot' => 'mix', 'default_col' => 2, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-users-country'),
    'chart-users-region' => array('label' => __('Users per region'), 'group' => 'main', 'plot' => 'mix', 'default_col' => 2, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-users-region'),
    'chart-items-category' => array('label' => __('Listings by category'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-items-category'),
    'chart-users-status' => array('label' => __('Users by status'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-users-status'),
    'chart-comments-status' => array('label' => __('Comments by status'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-comments-status'),
    'chart-items-country' => array('label' => __('Listings per country'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-items-country'),
    'chart-items-region' => array('label' => __('Listings per region'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-items-region'),
    'chart-items-price' => array('label' => __('Listings by price'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-items-price'),
    'chart-items-phone' => array('label' => __('Listings by phone'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-items-phone'),
    'chart-items-user-type' => array('label' => __('Listings by user type'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-items-user-type'),
    'chart-users-type' => array('label' => __('Users by type'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-users-type'),
    'chart-comments-account' => array('label' => __('Comments by account'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-comments-account'),
    'chart-comments-rating' => array('label' => __('Comments by rating'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-comments-rating'),
    'chart-comments-reply' => array('label' => __('Comments vs replies'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-comments-reply'),
    'chart-alerts-account' => array('label' => __('Alerts by account'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-alerts-account'),
    'chart-alerts-freq' => array('label' => __('Alerts by frequency'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-alerts-freq'),
    'chart-alerts-status' => array('label' => __('Alerts by status'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => true, 'opt_in' => true, 'ph' => 'placeholder-dash-alerts-status'),
    'chart-alerts-country' => array('label' => __('Alerts by country'), 'group' => 'mix', 'plot' => 'mix', 'default_col' => 3, 'period' => false, 'opt_in' => true, 'ph' => 'placeholder-dash-alerts-country')
  );
  return osc_apply_filter('admin_dash_stats_widgets', $w);
}


// Opt-in dashboard stats widget ids (hidden until enabled in widget settings)
function osc_admin_dash_stats_opt_in_ids() {
  $ids = array();
  foreach(osc_admin_dash_stats_widgets() as $id => $w) {
    if(!empty($w['opt_in'])) {
      $ids[] = $id;
    }
  }
  return $ids;
}


// Hide extra stats charts once (install/upgrade or first dashboard/settings visit)
function osc_admin_dash_stats_seed_hidden() {
  static $done = false;
  if($done) {
    return;
  }
  $done = true;
  $seed = (string)osc_get_preference('admindash_stats_opt_in_seeded', 'osclass');
  if($seed === '5') {
    return;
  }
  $hidden = array_filter(array_unique(array_map('trim', explode(',', (string)osc_get_preference('admindash_widgets_hidden', 'osclass')))));
  foreach(osc_admin_dash_stats_opt_in_ids() as $oid) {
    if(!in_array($oid, $hidden)) {
      $hidden[] = $oid;
    }
  }
  $show = array('chart-listing-views', 'chart-items', 'chart-comments', 'chart-reports', 'chart-users');
  $keep = array();
  foreach($hidden as $hid) {
    if(!in_array($hid, $show, true)) {
      $keep[] = $hid;
    }
  }
  osc_set_preference('admindash_widgets_hidden', implode(',', $keep));
  osc_set_preference('admindash_stats_opt_in_seeded', '5');
}


// Whether a dashboard widget should load and render
function osc_admin_dash_widget_on($id) {
  osc_admin_dash_stats_seed_hidden();
  $hidden = array_filter(array_map('trim', explode(',', (string)osc_get_preference('admindash_widgets_hidden', 'osclass'))));
  if(in_array($id, $hidden)) {
    return false;
  }
  $cat = osc_admin_dash_stats_widgets();
  $def = (isset($cat[$id]['default_col']) ? (int)$cat[$id]['default_col'] : 2);
  $col = osc_admin_dash_widget_column($id, $def);
  $cols_hidden = array_filter(array_map('trim', explode(',', (string)osc_get_preference('admindash_columns_hidden', 'osclass'))));
  if(in_array((string)$col, $cols_hidden)) {
    return false;
  }
  return true;
}


// Label/value rows for mix charts
function osc_admin_dash_mix_rows($map, $order) {
  $out = array();
  foreach($order as $key => $label) {
    $out[] = array('s_label' => $label, 'num' => (int)(isset($map[$key]) ? $map[$key] : 0));
  }
  return $out;
}


// Sum chart rows (date=>n, date=>array, or mix num)
function osc_admin_dash_rows_sum($rows, $idx = 0) {
  $n = 0;
  foreach((array)$rows as $v) {
    if(is_array($v)) {
      if(isset($v['num'])) {
        $n += (int)$v['num'];
      } else {
        $n += (int)(isset($v[$idx]) ? $v[$idx] : 0);
      }
    } else {
      $n += (int)$v;
    }
  }
  return $n;
}


// All-time + period totals on one line
function osc_admin_dash_heading_pair($noun, $all, $period = null, $days = 30, $all_extra = '') {
  $all_txt = sprintf(__('Total number of %s: %s'), $noun, osc_item_stats_format($all));
  if($all_extra != '') {
    $all_txt .= ' ' . $all_extra;
  }
  $out = array('all' => $all_txt);
  if($period !== null) {
    $out['period'] = sprintf(__('Last %s days: %s'), (int)$days, osc_item_stats_format($period));
  }
  return $out;
}


// Print dashboard chart heading
function osc_admin_dash_heading_html($h) {
  if(is_string($h) && $h != '') {
    return $h;
  }
  if(!is_array($h)) {
    return '';
  }
  $all = (isset($h['all']) ? (string)$h['all'] : '');
  $period = (isset($h['period']) ? (string)$h['period'] : '');
  $text = $all;
  if($period != '') {
    $text .= ($text != '' ? '. ' : '') . $period;
  }
  if($text == '') {
    return '';
  }
  return '<h4>' . osc_esc_html($text) . '</h4>';
}


// All-time count for a dashboard heading entity
function osc_admin_dash_entity_total($key) {
  static $tot = array();
  if(isset($tot[$key])) {
    return $tot[$key];
  }
  $n = 0;
  if($key == 'listings') {
    $n = (int)Item::newInstance()->count();
  } else if($key == 'comments') {
    $n = (int)ItemComment::newInstance()->count();
  } else if($key == 'users') {
    $n = (int)User::newInstance()->count();
  } else if($key == 'reports') {
    $n = ((function_exists('osc_reports_tables_ready') && !osc_reports_tables_ready()) ? 0 : (int)Report::newInstance()->countSearch());
  } else if($key == 'reports_open') {
    $n = ((function_exists('osc_reports_tables_ready') && !osc_reports_tables_ready()) ? 0 : (int)Report::newInstance()->countSearch('open'));
  } else if($key == 'views') {
    $n = (int)ItemStats::newInstance()->getAllViews();
  } else if($key == 'alerts') {
    $n = (int)Alerts::newInstance()->count();
  } else if($key == 'subscribers') {
    $m = Alerts::newInstance();
    $m->dao->select('COUNT(DISTINCT s_email) AS count');
    $m->dao->from($m->getTableName());
    $m->dao->where('dt_unsub_date IS NULL');
    $r = $m->dao->get();
    $row = ($r ? $r->row() : array());
    $n = (int)(isset($row['count']) ? $row['count'] : 0);
  } else if($key == 'emails') {
    $m = AlertsStats::newInstance();
    $m->dao->select('SUM(i_num_alerts_sent) AS count');
    $m->dao->from($m->getTableName());
    $r = $m->dao->get();
    $row = ($r ? $r->row() : array());
    $n = (int)(isset($row['count']) ? $row['count'] : 0);
  }
  $tot[$key] = $n;
  return $n;
}


// Load enabled dashboard stats charts (last 30 days for period series)
function osc_admin_dash_stats_load() {
  $from = date('Y-m-d', strtotime('-29 days'));
  $on = array();
  foreach(osc_admin_dash_stats_widgets() as $id => $w) {
    if(osc_admin_dash_widget_on($id)) {
      $on[$id] = $w;
    }
  }
  $series = array();
  $mix = array();
  $headings = array();
  $days = 30;
  if(empty($on)) {
    return array('series' => $series, 'mix' => $mix, 'headings' => $headings);
  }
  $need = function($ids) use ($on) {
    foreach((array)$ids as $id) {
      if(isset($on[$id])) {
        return true;
      }
    }
    return false;
  };
  $setHead = function($id, $noun, $all, $period, $extra = '') use (&$headings, $days) {
    $headings[$id] = osc_admin_dash_heading_pair($noun, $all, $period, $days, $extra);
  };
  $color = (function_exists('omg_current_color_scheme_chart') ? omg_current_color_scheme_chart() : '#0073aa');
  $stats = Stats::newInstance();
  if($need('chart-items')) {
    $rows = osc_admin_stats_count_map($from, $stats->new_items_count($from, 'day'));
    $series[] = array('id' => $on['chart-items']['ph'], 'type' => 'area', 'labels' => array(__('Date'), __('Listings')), 'colors' => array($color), 'rows' => $rows);
    $setHead('chart-items', __('listings'), osc_admin_dash_entity_total('listings'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-listing-views')) {
    $view_keys = osc_item_stats_admin_plot_measures();
    $view_pack = osc_item_stats_period_series('30d', $view_keys);
    $series[] = osc_item_stats_admin_views_chart($on['chart-listing-views']['ph'], $view_keys, $view_pack['rows']);
    $headings['chart-listing-views'] = osc_item_stats_admin_views_heading($view_keys, $view_pack['sums'], '30d');
  }
  if($need('chart-comments')) {
    $rows = osc_admin_stats_count_map($from, $stats->new_comments_count($from, 'day'));
    $series[] = array('id' => $on['chart-comments']['ph'], 'type' => 'stepped_area', 'labels' => array(__('Date'), __('Comments')), 'colors' => array($color), 'rows' => $rows);
    $setHead('chart-comments', __('comments'), osc_admin_dash_entity_total('comments'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-reports')) {
    $rows = osc_admin_stats_count_map($from, $stats->new_reports_count($from, 'day'));
    $series[] = array('id' => $on['chart-reports']['ph'], 'type' => 'column', 'labels' => array(__('Date'), __('Reports')), 'colors' => array($color), 'rows' => $rows);
    $open = sprintf(__('(%s open)'), osc_item_stats_format(osc_admin_dash_entity_total('reports_open')));
    $setHead('chart-reports', __('reports'), osc_admin_dash_entity_total('reports'), osc_admin_dash_rows_sum($rows), $open);
  }
  if($need('chart-users')) {
    $rows = osc_admin_stats_count_map($from, $stats->new_users_count($from, 'day'));
    $series[] = array('id' => $on['chart-users']['ph'], 'type' => 'line', 'labels' => array(__('Date'), __('Users')), 'colors' => array($color), 'rows' => $rows);
    $setHead('chart-users', __('users'), osc_admin_dash_entity_total('users'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-alerts')) {
    $rows = osc_admin_stats_count_map($from, $stats->new_alerts_count($from, 'day'));
    $series[] = array('id' => $on['chart-alerts']['ph'], 'type' => 'area', 'labels' => array(__('Date'), __('New alerts')), 'colors' => array($color), 'rows' => $rows);
    $setHead('chart-alerts', __('alerts'), osc_admin_dash_entity_total('alerts'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-subscribers')) {
    $rows = osc_admin_stats_count_map($from, $stats->new_subscribers_count($from, 'day'));
    $series[] = array('id' => $on['chart-subscribers']['ph'], 'type' => 'line', 'labels' => array(__('Date'), __('New subscribers')), 'colors' => array(osc_item_stats_palette_color(6)), 'rows' => $rows);
    $setHead('chart-subscribers', __('subscribers'), osc_admin_dash_entity_total('subscribers'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-alerts-sent')) {
    $sent = osc_admin_stats_count_map($from, $stats->alerts_sent_count($from));
    $matched = osc_admin_stats_count_map($from, $stats->alerts_matched_count($from));
    $sent_rows = array();
    foreach($sent as $d => $n) {
      $sent_rows[$d] = array($n, (int)(isset($matched[$d]) ? $matched[$d] : 0));
    }
    $series[] = array('id' => $on['chart-alerts-sent']['ph'], 'type' => 'combo', 'labels' => array(__('Date'), __('Emails sent'), __('Matched listings')), 'colors' => array($color, osc_item_stats_palette_color(6)), 'rows' => $sent_rows);
    $setHead('chart-alerts-sent', __('emails'), osc_admin_dash_entity_total('emails'), osc_admin_dash_rows_sum($sent_rows, 0));
  }
  if($need('chart-alerts-active')) {
    $active_by_day = $stats->alerts_active_by_day($from);
    $expired = osc_admin_stats_count_map($from, $stats->alerts_expired_count($from));
    $active_rows = array();
    foreach($active_by_day as $d => $n) {
      $active_rows[$d] = array($n, (int)(isset($expired[$d]) ? $expired[$d] : 0));
    }
    $series[] = array('id' => $on['chart-alerts-active']['ph'], 'type' => 'combo', 'combo' => 'area_bars', 'labels' => array(__('Date'), __('Active alerts'), __('Expired')), 'colors' => array('#0ea5e9', '#f97316'), 'rows' => $active_rows);
    $active_now = 0;
    if(!empty($active_by_day)) {
      $active_now = (int)end($active_by_day);
    }
    $setHead('chart-alerts-active', __('alerts'), $active_now, null);
  }
  if($need(array('chart-comments-kind', 'chart-comments-account'))) {
    $comments_by_kind = osc_admin_stats_kind_map($from, $stats->new_comments_by_user_kind($from), array('user', 'guest'));
    if($need('chart-comments-kind')) {
      $series[] = array('id' => $on['chart-comments-kind']['ph'], 'type' => 'stacked_percent', 'labels' => array(__('Date'), __('Registered'), __('Guest')), 'colors' => array($color, osc_item_stats_palette_color(6)), 'rows' => $comments_by_kind);
      $setHead('chart-comments-kind', __('comments'), osc_admin_dash_entity_total('comments'), osc_admin_dash_rows_sum($comments_by_kind, 0) + osc_admin_dash_rows_sum($comments_by_kind, 1));
    }
    if($need('chart-comments-account') && !$need('chart-comments-kind')) {
      $ku = 0;
      $kg = 0;
      foreach($comments_by_kind as $vals) {
        if(!is_array($vals)) {
          continue;
        }
        $ku += (int)(isset($vals[0]) ? $vals[0] : 0);
        $kg += (int)(isset($vals[1]) ? $vals[1] : 0);
      }
      $mix[] = array('id' => $on['chart-comments-account']['ph'], 'type' => 'donut', 'labels' => array(__('Account'), __('Comments')), 'rows' => array(array('s_label' => __('Registered'), 'num' => $ku), array('s_label' => __('Guest'), 'num' => $kg)));
      $setHead('chart-comments-account', __('comments'), osc_admin_dash_entity_total('comments'), $ku + $kg);
    }
  }
  if($need(array('chart-alerts-kind', 'chart-alerts-account'))) {
    $alerts_by_kind = osc_admin_stats_kind_map($from, $stats->new_alerts_by_user_kind($from), array('guest', 'personal', 'company'));
    if($need('chart-alerts-kind')) {
      $series[] = array('id' => $on['chart-alerts-kind']['ph'], 'type' => 'stacked_percent', 'labels' => array(__('Date'), __('Guest'), __('Personal'), __('Company')), 'colors' => array(osc_item_stats_palette_color(9), $color, osc_item_stats_palette_color(6)), 'rows' => $alerts_by_kind);
      $setHead('chart-alerts-kind', __('alerts'), osc_admin_dash_entity_total('alerts'), osc_admin_dash_rows_sum($alerts_by_kind, 0) + osc_admin_dash_rows_sum($alerts_by_kind, 1) + osc_admin_dash_rows_sum($alerts_by_kind, 2));
    }
    if($need('chart-alerts-account') && !$need('chart-alerts-kind')) {
      $g = 0;
      $p = 0;
      $c = 0;
      foreach($alerts_by_kind as $vals) {
        if(!is_array($vals)) {
          continue;
        }
        $g += (int)(isset($vals[0]) ? $vals[0] : 0);
        $p += (int)(isset($vals[1]) ? $vals[1] : 0);
        $c += (int)(isset($vals[2]) ? $vals[2] : 0);
      }
      $mix[] = array('id' => $on['chart-alerts-account']['ph'], 'type' => 'pie', 'labels' => array(__('Account'), __('Alerts')), 'rows' => array(array('s_label' => __('Guest'), 'num' => $g), array('s_label' => __('Personal'), 'num' => $p), array('s_label' => __('Company'), 'num' => $c)));
      $setHead('chart-alerts-account', __('alerts'), osc_admin_dash_entity_total('alerts'), $g + $p + $c);
    }
  }
  if($need('chart-users-country')) {
    $rows = osc_admin_stats_limit_slices((array)$stats->users_by_country(), 's_country');
    $mix[] = array('id' => $on['chart-users-country']['ph'], 'type' => 'bar', 'labels' => array(__('Country'), __('Users')), 'rows' => $rows, 'colors' => array($color));
    $setHead('chart-users-country', __('users'), osc_admin_dash_entity_total('users'), null);
  }
  if($need('chart-users-region')) {
    $rows = osc_admin_stats_limit_slices((array)$stats->users_by_region(), 's_region');
    $mix[] = array('id' => $on['chart-users-region']['ph'], 'type' => 'bar', 'labels' => array(__('Region'), __('Users')), 'rows' => $rows, 'colors' => array($color));
    $setHead('chart-users-region', __('users'), osc_admin_dash_entity_total('users'), null);
  }
  if($need('chart-items-category') && !osc_admin_dash_widget_on('items-category')) {
    $cat_col = osc_admin_dash_widget_column('chart-items-category', (int)$on['chart-items-category']['default_col']);
    $mix[] = osc_admin_stats_listings_by_category_mix($on['chart-items-category']['ph'], null, ($cat_col == 1 ? 'advanced' : 'simple'));
    $setHead('chart-items-category', __('listings'), osc_admin_dash_entity_total('listings'), null);
  }
  if($need('chart-users-status')) {
    $rows = osc_admin_dash_mix_rows($stats->users_by_status(), array('pending' => __('Pending'), 'active' => __('Active'), 'blocked' => __('Blocked')));
    $mix[] = array('id' => $on['chart-users-status']['ph'], 'type' => 'pie', 'labels' => array(__('Status'), __('Users')), 'rows' => $rows);
    $setHead('chart-users-status', __('users'), osc_admin_dash_entity_total('users'), null);
  }
  if($need('chart-comments-status')) {
    $rows = osc_admin_dash_mix_rows($stats->comments_by_status($from), array('pending' => __('Pending'), 'active' => __('Active'), 'blocked' => __('Blocked')));
    $mix[] = array('id' => $on['chart-comments-status']['ph'], 'type' => 'donut', 'labels' => array(__('Status'), __('Comments')), 'rows' => $rows);
    $setHead('chart-comments-status', __('comments'), osc_admin_dash_entity_total('comments'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-items-country')) {
    $rows = osc_admin_stats_limit_slices((array)$stats->items_by_country(), 's_country');
    $mix[] = array('id' => $on['chart-items-country']['ph'], 'type' => 'bar', 'labels' => array(__('Country'), __('Listings')), 'rows' => $rows, 'colors' => array($color));
    $setHead('chart-items-country', __('listings'), osc_admin_dash_entity_total('listings'), null);
  }
  if($need('chart-items-region')) {
    $rows = osc_admin_stats_limit_slices((array)$stats->items_by_region(), 's_region');
    $mix[] = array('id' => $on['chart-items-region']['ph'], 'type' => 'bar', 'labels' => array(__('Region'), __('Listings')), 'rows' => $rows, 'colors' => array($color));
    $setHead('chart-items-region', __('listings'), osc_admin_dash_entity_total('listings'), null);
  }
  if($need('chart-items-price')) {
    $rows = osc_admin_dash_mix_rows($stats->items_by_price_type(), array('free' => __('Free'), 'ask' => __('Ask seller'), 'priced' => __('Priced')));
    $mix[] = array('id' => $on['chart-items-price']['ph'], 'type' => 'pie', 'labels' => array(__('Price'), __('Listings')), 'rows' => $rows);
    $setHead('chart-items-price', __('listings'), osc_admin_dash_entity_total('listings'), null);
  }
  if($need('chart-items-phone')) {
    $rows = osc_admin_dash_mix_rows($stats->items_by_phone(), array('filled' => __('Phone'), 'empty' => __('No phone')));
    $mix[] = array('id' => $on['chart-items-phone']['ph'], 'type' => 'donut', 'labels' => array(__('Phone'), __('Listings')), 'rows' => $rows);
    $setHead('chart-items-phone', __('listings'), osc_admin_dash_entity_total('listings'), null);
  }
  if($need('chart-items-user-type')) {
    $rows = osc_admin_dash_mix_rows($stats->items_by_user_type(), array('guest' => __('Guest'), 'personal' => __('Personal'), 'business' => __('Business')));
    $mix[] = array('id' => $on['chart-items-user-type']['ph'], 'type' => 'pie', 'labels' => array(__('User type'), __('Listings')), 'rows' => $rows);
    $setHead('chart-items-user-type', __('listings'), osc_admin_dash_entity_total('listings'), null);
  }
  if($need('chart-users-type')) {
    $uc = $stats->users_by_company();
    $order = array('personal' => __('Personal'), 'company' => __('Company'));
    if(isset($uc['other']) && (int)$uc['other'] > 0) {
      $order['other'] = __('Other');
    }
    $rows = osc_admin_dash_mix_rows($uc, $order);
    $mix[] = array('id' => $on['chart-users-type']['ph'], 'type' => 'donut', 'labels' => array(__('User type'), __('Users')), 'rows' => $rows);
    $setHead('chart-users-type', __('users'), osc_admin_dash_entity_total('users'), null);
  }
  if($need('chart-comments-rating')) {
    $rows = osc_admin_dash_mix_rows($stats->comments_by_rating($from), array('rated' => __('Rated'), 'unrated' => __('Unrated')));
    $mix[] = array('id' => $on['chart-comments-rating']['ph'], 'type' => 'donut', 'labels' => array(__('Rating'), __('Comments')), 'rows' => $rows);
    $setHead('chart-comments-rating', __('comments'), osc_admin_dash_entity_total('comments'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-comments-reply')) {
    $rows = osc_admin_dash_mix_rows($stats->comments_by_reply($from), array('comment' => __('Comments'), 'reply' => __('Replies')));
    $mix[] = array('id' => $on['chart-comments-reply']['ph'], 'type' => 'pie', 'labels' => array(__('Type'), __('Comments')), 'rows' => $rows);
    $setHead('chart-comments-reply', __('comments'), osc_admin_dash_entity_total('comments'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-alerts-freq')) {
    $freq = $stats->alerts_by_frequency($from);
    $freq_rows = array();
    foreach(array('INSTANT', 'HOURLY', 'DAILY', 'WEEKLY', 'CUSTOM') as $ft) {
      $freq_rows[] = array('s_label' => osc_alert_type_label($ft), 'num' => (int)(isset($freq[$ft]) ? $freq[$ft] : 0));
    }
    $mix[] = array('id' => $on['chart-alerts-freq']['ph'], 'type' => 'pie', 'labels' => array(__('Frequency'), __('Alerts')), 'rows' => $freq_rows);
    $setHead('chart-alerts-freq', __('alerts'), osc_admin_dash_entity_total('alerts'), osc_admin_dash_rows_sum($freq_rows));
  }
  if($need('chart-alerts-status')) {
    $rows = osc_admin_dash_mix_rows($stats->alerts_by_status($from), array('active' => __('Active'), 'inactive' => __('Inactive'), 'expired' => __('Expired')));
    $mix[] = array('id' => $on['chart-alerts-status']['ph'], 'type' => 'donut', 'labels' => array(__('Status'), __('Alerts')), 'rows' => $rows);
    $setHead('chart-alerts-status', __('alerts'), osc_admin_dash_entity_total('alerts'), osc_admin_dash_rows_sum($rows));
  }
  if($need('chart-alerts-country')) {
    $rows = osc_admin_stats_limit_slices((array)$stats->alerts_by_country(), 's_country');
    $mix[] = array('id' => $on['chart-alerts-country']['ph'], 'type' => 'bar', 'labels' => array(__('Country'), __('Alerts')), 'rows' => $rows, 'colors' => array($color));
    $setHead('chart-alerts-country', __('alerts'), osc_admin_dash_entity_total('alerts'), null);
  }
  return array('series' => $series, 'mix' => $mix, 'headings' => $headings);
}


// Dashboard chart widget title
function osc_admin_dash_chart_title($id) {
  $cat = osc_admin_dash_stats_widgets();
  if(!isset($cat[$id])) {
    return $id;
  }
  return $cat[$id]['label'];
}


// One dashboard settings row: hide checkbox plus column select
function osc_admin_dash_settings_chart_row($sid, $sw, $widgets_hidden, $show_label, $help = '') {
  $sid_esc = osc_esc_html($sid);
  $col = osc_admin_dash_widget_column($sid, (isset($sw['default_col']) ? (int)$sw['default_col'] : 2));
  echo '<div class="form-row' . ($show_label ? '' : ' has-blank-label') . ' dash-chart-row">';
  if($show_label) {
    echo '<div class="form-label">' . __('Charts') . '</div>';
  } else {
    echo '<div class="form-label blank">&nbsp;</div>';
  }
  echo '<div class="form-controls">';
  echo '<label id="widget_' . $sid_esc . '" class="form-label-checkbox">';
  echo '<input type="checkbox" id="widget_' . $sid_esc . '" name="widget_' . $sid_esc . '"' . (in_array($sid, $widgets_hidden) ? ' checked="checked"' : '') . ' value="1" />';
  echo sprintf(__('Hide "%s" widget'), $sw['label']);
  echo '</label>';
  echo '<select name="widgetcol_' . $sid_esc . '">';
  for($c = 1; $c <= 3; $c++) {
    echo '<option value="' . $c . '"' . ($col == $c ? ' selected="selected"' : '') . '>' . sprintf(__('Column %d'), $c) . '</option>';
  }
  echo '</select>';
  if($help != '') {
    echo '<div class="help-box">' . $help . '</div>';
  }
  echo '</div></div>';
}


// Print enabled stats chart widgets for one dashboard column
function osc_admin_dash_render_chart_widgets($col) {
  osc_admin_dash_stats_seed_hidden();
  $col = (int)$col;
  $hidden = array_filter(array_map('trim', explode(',', (string)osc_get_preference('admindash_widgets_hidden', 'osclass'))));
  $headings = __get('dash_chart_headings');
  if(!is_array($headings)) {
    $headings = array();
  }
  foreach(osc_admin_dash_stats_widgets() as $id => $w) {
    if(in_array($id, $hidden)) {
      continue;
    }
    $def = (isset($w['default_col']) ? (int)$w['default_col'] : 2);
    if(osc_admin_dash_widget_column($id, $def) != $col) {
      continue;
    }
    if($id == 'chart-items-category' && osc_admin_dash_widget_on('items-category')) {
      continue;
    }
    if($id == 'chart-comments-account' && osc_admin_dash_widget_on('chart-comments-kind')) {
      continue;
    }
    if($id == 'chart-alerts-account' && osc_admin_dash_widget_on('chart-alerts-kind')) {
      continue;
    }
    $cls = 'widget-box widget-chart';
    if(isset($w['plot']) && $w['plot'] == 'mix') {
      $cls .= ' widget-chart-mix';
    }
    echo '<div class="' . $cls . '" id="widget-' . osc_esc_html($id) . '" data-id="' . osc_esc_html($id) . '">';
    echo '<div class="widget-box-title"><h3><span>' . osc_esc_html(osc_admin_dash_chart_title($id)) . '</span>';
    if(function_exists('osc_admin_widget_title_controls')) {
      osc_admin_widget_title_controls($id);
    }
    echo '</h3></div>';
    $collapsed = (function_exists('osc_admin_widget_collapsed') && osc_admin_widget_collapsed($id));
    echo '<div class="widget-box-content"' . ($collapsed ? ' style="display:none;"' : '') . '>';
    if(isset($headings[$id])) {
      echo osc_admin_dash_heading_html($headings[$id]);
    }
    echo '<div id="' . osc_esc_html($w['ph']) . '" class="graph-placeholder"></div>';
    echo '</div></div>';
  }
}


// JS that draws queued pie/bar/column/stacked-bar charts inside oscDrawAdminStats
function osc_admin_stats_extra_chart_js($charts) {
  $js = '';
  if(!is_array($charts) || empty($charts)) {
    return $js;
  }
  $js .= 'var pieOpts={title:"",chartArea:{left:8,top:8,width:"90%",height:"80%"},legend:{position:"right",textStyle:{fontSize:11}},pieSliceText:"percentage"};';
  $js .= 'var donutOpts={title:"",chartArea:{left:8,top:8,width:"90%",height:"80%"},legend:{position:"right",textStyle:{fontSize:11}},pieHole:0.45,pieSliceText:"percentage"};';
  $js .= 'var barOpts={title:"",legend:{position:"none"},chartArea:{left:120,top:8,width:"52%",height:"78%"},hAxis:{minValue:0,format:"0",viewWindow:{min:0},textStyle:{color:"#8C8C8C",fontSize:11}},vAxis:{textStyle:{color:"#8C8C8C",fontSize:10}}};';
  $js .= 'var colOpts={title:"",legend:{position:"none"},chartArea:{left:72,top:12,width:"74%",height:"50%"},hAxis:{slantedText:true,slantedTextAngle:45,maxAlternation:1,maxTextLines:2,textStyle:{color:"#8C8C8C",fontSize:10}},vAxis:{minValue:0,viewWindow:{min:0},format:"0",textStyle:{color:"#8C8C8C",fontSize:9},gridlines:{color:"#ddd",count:4}}};';
  $js .= 'var stackBarOpts={title:"",isStacked:true,legend:{position:"right",textStyle:{fontSize:10}},chartArea:{left:120,top:8,width:"58%",height:"78%"},hAxis:{minValue:0,format:"0",viewWindow:{min:0},textStyle:{color:"#8C8C8C",fontSize:11}},vAxis:{textStyle:{color:"#8C8C8C",fontSize:10}}};';
  foreach($charts as $i => $chart) {
    if(!is_array($chart) || !isset($chart['id']) || $chart['id'] == '') {
      continue;
    }
    $sid = osc_esc_js($chart['id']);
    $ctype = (isset($chart['type']) ? $chart['type'] : 'pie');
    if($ctype == 'treemap') {
      $ctype = 'bar';
    }
    if($ctype == 'stacked_bar') {
      $series = (isset($chart['series']) ? (array)$chart['series'] : array());
      $srows = (isset($chart['rows']) ? (array)$chart['rows'] : array());
      $labels = (isset($chart['labels']) ? (array)$chart['labels'] : array(__('Category')));
      $col1 = (isset($labels[0]) ? $labels[0] : __('Category'));
      if(empty($series) || empty($srows)) {
        continue;
      }
      $palette = osc_item_stats_palette();
      $safe = array();
      foreach($palette as $c) {
        $safe[] = "'" . osc_esc_js($c) . "'";
      }
      $js .= 'var ep'.$i.'=document.getElementById("'.$sid.'");';
      $js .= 'if(ep'.$i.'&&ep'.$i.'.offsetWidth>0&&ep'.$i.'.offsetHeight>0){';
      $js .= 'var ed'.$i.'=new google.visualization.DataTable();';
      $js .= 'ed'.$i.'.addColumn("string","'.osc_esc_js($col1).'");';
      foreach($series as $sname) {
        $js .= 'ed'.$i.'.addColumn("number","'.osc_esc_js((string)$sname).'");';
      }
      $js .= 'ed'.$i.'.addRows('.count($srows).');';
      $sum_all = 0;
      foreach($srows as $ri => $srow) {
        $lab = (isset($srow['label']) ? $srow['label'] : '');
        $vals = (isset($srow['values']) && is_array($srow['values']) ? $srow['values'] : array());
        $js .= 'ed'.$i.'.setValue('.$ri.',0,"'.osc_esc_js((string)$lab).'");';
        $ci = 1;
        foreach($series as $sname) {
          $n = (int)(isset($vals[$sname]) ? $vals[$sname] : 0);
          $sum_all += $n;
          $js .= 'ed'.$i.'.setValue('.$ri.','.$ci.','.$n.');';
          $ci++;
        }
      }
      $js .= 'if('.($sum_all > 0 ? 'true' : 'false').'){';
      $js .= 'var eopts'.$i.'=oscAdminStatsFit(ep'.$i.',stackBarOpts);';
      $js .= 'if(eopts'.$i.'){';
      if(!empty($safe)) {
        $js .= 'eopts'.$i.'.colors=[' . implode(',', $safe) . '];';
      }
      $js .= 'if(!window.oscAdminStatsCharts["'.$sid.'"]){window.oscAdminStatsCharts["'.$sid.'"]=new google.visualization.BarChart(ep'.$i.');}';
      $js .= 'window.oscAdminStatsCharts["'.$sid.'"].draw(ed'.$i.',eopts'.$i.');}}}';
      continue;
    }
    $pairs = array();
    foreach((array)(isset($chart['rows']) ? $chart['rows'] : array()) as $rk => $rv) {
      if(is_array($rv)) {
        $lab = '';
        if(isset($rv['label'])) {
          $lab = $rv['label'];
        } else if(isset($rv['s_label'])) {
          $lab = $rv['s_label'];
        } else if(isset($rv['s_name'])) {
          $lab = $rv['s_name'];
        } else if(isset($rv['s_country'])) {
          $lab = $rv['s_country'];
        } else if(isset($rv['s_region'])) {
          $lab = $rv['s_region'];
        }
        if($lab === null || $lab === '') {
          $lab = __('Unknown');
        }
        $pairs[] = array((string)$lab, (int)(isset($rv['num']) ? $rv['num'] : 0));
      } else {
        $pairs[] = array((string)$rk, (int)$rv);
      }
    }
    $n_pairs = count($pairs);
    $labels = (isset($chart['labels']) ? (array)$chart['labels'] : array(__('Label'), __('Value')));
    $col1 = (isset($labels[0]) ? $labels[0] : __('Label'));
    $col2 = (isset($labels[1]) ? $labels[1] : __('Value'));
    if($ctype == 'auto') {
      $ctype = ($n_pairs > 0 && $n_pairs < 8 ? 'pie' : 'bar');
    }
    $colors = (isset($chart['colors']) ? (array)$chart['colors'] : array());
    $palette = (!empty($colors) ? array_values($colors) : osc_item_stats_palette());
    $base = (isset($palette[0]) && $palette[0] != '' ? $palette[0] : osc_item_stats_palette_color(0));
    $color_js = '';
    $safe = array();
    foreach($palette as $c) {
      $safe[] = "'" . osc_esc_js($c) . "'";
    }
    if(!empty($safe)) {
      $color_js = 'colors:[' . implode(',', $safe) . '],';
    }
    $color_by_value = !isset($chart['color_by_value']) ? ($ctype == 'bar') : !empty($chart['color_by_value']);
    if($ctype == 'bar') {
      $gtype = 'BarChart';
      $opts = 'barOpts';
    } else if($ctype == 'column') {
      $gtype = 'ColumnChart';
      $opts = 'colOpts';
    } else if($ctype == 'donut') {
      $gtype = 'PieChart';
      $opts = 'donutOpts';
    } else {
      $gtype = 'PieChart';
      $opts = 'pieOpts';
    }
    $js .= 'var ep'.$i.'=document.getElementById("'.$sid.'");';
    $js .= 'if(ep'.$i.'&&ep'.$i.'.offsetWidth>0&&ep'.$i.'.offsetHeight>0){';
    $js .= 'var ed'.$i.'=new google.visualization.DataTable();';
    $js .= 'ed'.$i.'.addColumn("string","'.osc_esc_js($col1).'");';
    $js .= 'ed'.$i.'.addColumn("number","'.osc_esc_js($col2).'");';
    if($color_by_value && ($gtype == 'BarChart' || $gtype == 'ColumnChart')) {
      $js .= 'ed'.$i.'.addColumn({type:"string",role:"style"});';
    }
    $js .= 'ed'.$i.'.addRows('.$n_pairs.');';
    foreach($pairs as $pi => $pair) {
      $js .= 'ed'.$i.'.setValue('.$pi.',0,"'.osc_esc_js($pair[0]).'");';
      $js .= 'ed'.$i.'.setValue('.$pi.',1,'.(int)$pair[1].');';
    }
    if($color_by_value && ($gtype == 'BarChart' || $gtype == 'ColumnChart')) {
      $js .= 'var emx'.$i.'=0;for(var er=0;er<ed'.$i.'.getNumberOfRows();er++){var ev=ed'.$i.'.getValue(er,1);if(ev>emx'.$i.')emx'.$i.'=ev;}';
      $js .= 'for(var er=0;er<ed'.$i.'.getNumberOfRows();er++){ed'.$i.'.setValue(er,2,oscAdminStatsTint("'.osc_esc_js($base).'",(emx'.$i.'>0?ed'.$i.'.getValue(er,1)/emx'.$i.':0)));}';
    }
    $js .= 'var esum'.$i.'=0;for(var er=0;er<ed'.$i.'.getNumberOfRows();er++){esum'.$i.'+=ed'.$i.'.getValue(er,1);}';
    $js .= 'if(esum'.$i.'>0){';
    $js .= 'var eopts'.$i.'=oscAdminStatsFit(ep'.$i.','.$opts.');';
    $js .= 'if(eopts'.$i.'){';
    if($color_js != '' && !($color_by_value && ($gtype == 'BarChart' || $gtype == 'ColumnChart'))) {
      $js .= 'eopts'.$i.'.colors=[' . implode(',', $safe) . '];';
    }
    $js .= 'if(!window.oscAdminStatsCharts["'.$sid.'"]){window.oscAdminStatsCharts["'.$sid.'"]=new google.visualization.'.$gtype.'(ep'.$i.');}';
    $js .= 'window.oscAdminStatsCharts["'.$sid.'"].draw(ed'.$i.',eopts'.$i.');}}}';
  }
  return $js;
}


// Draw one or more Google charts; keeps the table visible if the loader is blocked
function osc_admin_stats_chart_js($charts, $options = array()) {
  $charts = (array)$charts;
  $page = (isset($options['page']) ? (string)$options['page'] : '');
  $charts = osc_apply_filter('admin_stats_charts', $charts, $page);
  $extra = array();
  if($page != '') {
    $plugin_charts = osc_apply_filter('admin_stats_plugin_charts', osc_admin_stats_plugin_charts(), $page);
    foreach((array)$plugin_charts as $pc) {
      if(!is_array($pc) || !isset($pc['id']) || $pc['id'] == '') {
        continue;
      }
      $ptype = (isset($pc['type']) ? $pc['type'] : 'line');
      if($ptype == 'pie' || $ptype == 'donut' || $ptype == 'bar' || $ptype == 'column' || $ptype == 'auto' || $ptype == 'stacked_bar') {
        $extra[] = $pc;
      } else {
        $charts[] = $pc;
      }
    }
  }
  if(isset($options['mix']) && is_array($options['mix'])) {
    foreach($options['mix'] as $mc) {
      if(is_array($mc) && isset($mc['id']) && $mc['id'] != '') {
        $extra[] = $mc;
      }
    }
  }
  $loader = (!isset($options['loader']) || $options['loader']);
  $locale = osc_current_admin_locale();
  $lang = (is_string($locale) && strlen($locale) >= 2 ? substr($locale, 0, 2) : 'en');
  $lang = preg_replace('/[^a-zA-Z]/', '', $lang);
  if($lang == '') {
    $lang = 'en';
  }
  $js = '<link rel="stylesheet" href="' . osc_esc_html(osc_assets_url('css/item-stats.css')) . '" />' . "\n";
  $js .= ($loader ? osc_admin_stats_google_loader() . "\n" : '');
  $js .= '<script type="text/javascript">';
  $js .= 'if(!window.oscAdminStatsCharts){window.oscAdminStatsCharts={};}';
  $js .= 'if(!window.oscAdminStatsExtraFns){window.oscAdminStatsExtraFns=[];}';
  $js .= 'window.oscAdminStatsFit=function(el,opts){var o={},k;if(opts){for(k in opts){if(Object.prototype.hasOwnProperty.call(opts,k)){o[k]=opts[k];}}}if(!el){return null;}var w=el.clientWidth||el.offsetWidth||0;var h=el.clientHeight||el.offsetHeight||0;if(h<80){h=parseInt((window.getComputedStyle(el).height||"0"),10)||240;el.style.height=h+"px";}if(w<40){return null;}o.width=w;o.height=h;return o;};';
  $js .= 'window.oscAdminStatsTint=function(hex,t){hex=String(hex||"#4E79A7").replace("#","");if(hex.length===3){hex=hex.charAt(0)+hex.charAt(0)+hex.charAt(1)+hex.charAt(1)+hex.charAt(2)+hex.charAt(2);}var r=parseInt(hex.substr(0,2),16),g=parseInt(hex.substr(2,2),16),b=parseInt(hex.substr(4,2),16);if(isNaN(r)||isNaN(g)||isNaN(b)){r=78;g=121;b=167;}t=Math.max(0.28,Math.min(1,t));r=Math.round(255-(255-r)*t);g=Math.round(255-(255-g)*t);b=Math.round(255-(255-b)*t);return"rgb("+r+","+g+","+b+")";};';
  $js .= 'function oscDrawAdminStats(){if(typeof google==="undefined"||!google.visualization){return;}';
  foreach($charts as $i => $chart) {
    $id = (isset($chart['id']) ? $chart['id'] : '');
    if($id == '') {
      continue;
    }
    $sid = osc_esc_js($id);
    $labels = (isset($chart['labels']) ? (array)$chart['labels'] : array(__('Date'), __('Value')));
    $ncol = count($labels);
    $colors = (isset($chart['colors']) ? (array)$chart['colors'] : array());
    $rows = (isset($chart['rows']) ? $chart['rows'] : array());
    $ctype = (isset($chart['type']) ? $chart['type'] : 'line');
    $stacked = !empty($chart['stacked']);
    $is_percent = false;
    if($ctype == 'stacked_percent') {
      $stacked = true;
      $is_percent = true;
      $gtype = 'AreaChart';
    } else if($ctype == 'stacked_area') {
      $stacked = true;
      $gtype = 'AreaChart';
    } else if($ctype == 'stepped_area' || $ctype == 'stepped') {
      $gtype = 'SteppedAreaChart';
    } else if($ctype == 'scatter') {
      $gtype = 'ScatterChart';
    } else if($ctype == 'combo') {
      $gtype = 'ComboChart';
    } else if($ctype == 'column') {
      $gtype = 'ColumnChart';
    } else if($ctype == 'area') {
      $gtype = 'AreaChart';
    } else {
      $gtype = 'LineChart';
    }
    $n_rows = count($rows);
    $point_size = ($n_rows > 60 ? 3 : 6);
    $use_date = ($n_rows > 0);
    foreach($rows as $date => $vals) {
      if(!preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$date)) {
        $use_date = false;
        break;
      }
    }
    $js .= 'var d'.$i.'=new google.visualization.DataTable();';
    foreach($labels as $li => $label) {
      if($li == 0) {
        $coltype = ($use_date ? 'date' : 'string');
      } else {
        $coltype = 'number';
      }
      $js .= 'd'.$i.'.addColumn("'.$coltype.'","'.osc_esc_js($label).'");';
    }
    $js .= 'd'.$i.'.addRows('.$n_rows.');';
    $k = 0;
    foreach($rows as $date => $vals) {
      if($use_date && preg_match('/^(\d{4})-(\d{2})-(\d{2})/', (string)$date, $dm)) {
        $js .= 'd'.$i.'.setValue('.$k.',0,new Date('.(int)$dm[1].','.((int)$dm[2] - 1).','.(int)$dm[3].'));';
      } else {
        $js .= 'd'.$i.'.setValue('.$k.',0,"'.osc_esc_js(osc_admin_stats_chart_tick($date, $n_rows)).'");';
      }
      if(!is_array($vals)) {
        $vals = array($vals);
      }
      $ci = 1;
      foreach($vals as $v) {
        if($ci >= $ncol) {
          break;
        }
        $js .= 'd'.$i.'.setValue('.$k.','.$ci.','.(int)$v.');';
        $ci++;
      }
      $k++;
    }
    $color_js = '';
    if(!empty($colors)) {
      $safe = array();
      foreach($colors as $c) {
        $safe[] = "'".osc_esc_js($c)."'";
      }
      $color_js = 'colors:['.implode(',', $safe).'],';
    }
    $area_js = '';
    if($gtype == 'AreaChart' || $gtype == 'SteppedAreaChart') {
      $area_js = ($stacked ? 'areaOpacity:0.65,' : 'areaOpacity:0.2,');
    }
    $stack_js = ($is_percent ? 'isStacked:"percent",' : ($stacked ? 'isStacked:true,' : ''));
    $combo_js = '';
    if($gtype == 'ComboChart') {
      $mode = (isset($chart['combo']) ? (string)$chart['combo'] : 'bars_line');
      if($mode == 'area_bars') {
        $combo_js = 'seriesType:"area",areaOpacity:0.2,series:{1:{type:"bars"}},';
      } else if($mode == 'line_bars') {
        $combo_js = 'seriesType:"line",series:{1:{type:"bars"}},';
      } else {
        $combo_js = 'seriesType:"bars",series:{1:{type:"line"}},';
      }
    }
    $legend_pos = (count($labels) > 2 ? 'bottom' : 'none');
    $legend_font = (isset($chart['legendFontSize']) ? (int)$chart['legendFontSize'] : 0);
    if($legend_pos != 'none' && $legend_font <= 0) {
      $legend_font = 10;
    }
    $legend_js = 'legend:{position:"'.$legend_pos.'"';
    if($legend_pos != 'none' && $legend_font > 0) {
      $legend_js .= ',textStyle:{fontSize:'.$legend_font.'}';
    }
    $legend_js .= '}';
    $ca_top = (isset($chart['chartAreaTop']) ? max(0, (int)$chart['chartAreaTop']) : 10);
    if(isset($chart['chartAreaBottom'])) {
      $ca_js = 'chartArea:{left:48,top:'.$ca_top.',width:"88%",bottom:'.max(0, (int)$chart['chartAreaBottom']).'}';
    } else {
      $ca_height = ($legend_pos != 'none' ? '68%' : '75%');
      if(isset($chart['chartAreaHeight']) && preg_match('/^\d{1,3}%$/', (string)$chart['chartAreaHeight'])) {
        $ca_height = $chart['chartAreaHeight'];
      }
      $ca_js = 'chartArea:{left:48,top:'.$ca_top.',width:"88%",height:"'.$ca_height.'"}';
    }
    $pt = $point_size;
    $line_w = 2;
    if($gtype == 'ScatterChart') {
      $line_w = 0;
      $pt = ($n_rows > 60 ? 3 : 5);
    } else if(($gtype == 'AreaChart' || $gtype == 'SteppedAreaChart') && $n_rows > 30) {
      $pt = 0;
    } else if($gtype == 'ColumnChart' || $gtype == 'ComboChart') {
      $pt = 0;
    }
    if($use_date) {
      $hfmt = ($n_rows > 180 ? 'MMM yyyy' : 'd MMM');
      $tfmt = ($n_rows > 180 ? 'MMM yyyy' : 'd MMM yyyy');
      $js .= 'var df'.$i.'=new google.visualization.DateFormat({pattern:"'.$tfmt.'"});df'.$i.'.format(d'.$i.',0);';
      $haxis_js = 'hAxis:{format:"'.$hfmt.'",maxAlternation:1,maxTextLines:1,minTextSpacing:28,slantedText:false,textStyle:{color:"#8C8C8C",fontSize:11}}';
    } else {
      $show_every = ($n_rows > 10 ? (int)ceil($n_rows / 10) : 1);
      $haxis_js = 'hAxis:{showTextEvery:'.$show_every.',maxAlternation:1,maxTextLines:1,slantedText:false,textStyle:{color:"#8C8C8C",fontSize:11}}';
    }
    $explore_js = '';
    if($use_date) {
      $explore_js = 'explorer:{actions:["dragToZoom","rightClickToReset"],axis:"horizontal",keepInBounds:true,maxZoomIn:0.15},crosshair:{trigger:"both",orientation:"vertical"},focusTarget:"category",';
    }
    if($is_percent) {
      $vaxis_js = 'vAxis:{minValue:0,viewWindow:{min:0},textStyle:{color:"#8C8C8C",fontSize:12},gridlines:{color:"#ddd",count:4}}';
    } else {
      $vaxis_js = 'vAxis:{minValue:0,viewWindow:{min:0},format:"0",textStyle:{color:"#8C8C8C",fontSize:12},gridlines:{color:"#ddd",count:4}}';
    }
    $js .= 'var e'.$i.'=document.getElementById("'.$sid.'");';
    $js .= 'if(e'.$i.'&&d'.$i.'.getNumberOfRows()>0){';
    $js .= 'var o'.$i.'=oscAdminStatsFit(e'.$i.',{'.$color_js.$area_js.$stack_js.$combo_js.$explore_js.$legend_js.',lineWidth:'.$line_w.',pointSize:'.$pt.','.$ca_js.','.$haxis_js.','.$vaxis_js.',animation:{duration:(window.oscAdminStatsCharts["'.$sid.'"]&&window.oscAdminStatsCharts["'.$sid.'"].oscDrawn?0:400),easing:"out",startup:!(window.oscAdminStatsCharts["'.$sid.'"]&&window.oscAdminStatsCharts["'.$sid.'"].oscDrawn)}});';
    $js .= 'if(o'.$i.'){';
    $js .= 'if(!window.oscAdminStatsCharts["'.$sid.'"]){window.oscAdminStatsCharts["'.$sid.'"]=new google.visualization.'.$gtype.'(e'.$i.');}';
    $js .= 'var mx'.$i.'=0;for(var r=0;r<d'.$i.'.getNumberOfRows();r++){for(var c=1;c<d'.$i.'.getNumberOfColumns();c++){var vv=d'.$i.'.getValue(r,c);if(vv>mx'.$i.')mx'.$i.'=vv;}}';
    $js .= 'if(mx'.$i.'<4&&o'.$i.'.isStacked!=="percent"){o'.$i.'.vAxis=o'.$i.'.vAxis||{};o'.$i.'.vAxis.viewWindowMode="explicit";o'.$i.'.vAxis.viewWindow={min:0,max:4};}';
    $js .= 'window.oscAdminStatsCharts["'.$sid.'"].draw(d'.$i.',o'.$i.');window.oscAdminStatsCharts["'.$sid.'"].oscDrawn=true;}}';
  }
  $js .= osc_admin_stats_extra_chart_js($extra);
  $js .= 'if(!window.oscAdminStatsExtraFns){window.oscAdminStatsExtraFns=[];}';
  $js .= 'for(var ei=0;ei<window.oscAdminStatsExtraFns.length;ei++){if(typeof window.oscAdminStatsExtraFns[ei]==="function"){window.oscAdminStatsExtraFns[ei]();}}';
  $js .= 'if(typeof window.oscDrawAdminStatsExtra==="function"){window.oscDrawAdminStatsExtra();}';
  $js .= '}';
  $js .= 'window.oscDrawAdminStats=oscDrawAdminStats;';
  $js .= 'if(typeof google!=="undefined"&&google.charts){';
  $js .= 'if(!window.oscChartsPkgLoaded){window.oscChartsPkgLoaded=true;google.charts.load("current",{packages:["corechart"],language:"'.osc_esc_js($lang).'",callback:oscDrawAdminStats});}';
  $js .= 'else if(google.visualization){oscDrawAdminStats();}else{google.charts.setOnLoadCallback(oscDrawAdminStats);}';
  $js .= '}';
  $js .= 'if(!window.oscStatsResizeBound){window.oscStatsResizeBound=true;window.oscStatsResizeT=null;window.oscStatsResizeRun=function(){clearTimeout(window.oscStatsResizeT);window.oscStatsResizeT=setTimeout(function(){if(typeof window.oscDrawAdminStats==="function"){window.oscDrawAdminStats();}},200);};window.addEventListener("resize",window.oscStatsResizeRun);window.addEventListener("orientationchange",window.oscStatsResizeRun);if(window.visualViewport){window.visualViewport.addEventListener("resize",window.oscStatsResizeRun);}}';
  $js .= '</script>';
  return $js;
}


// Fallback x-axis label when the series is not a YYYY-MM-DD date
function osc_admin_stats_chart_tick($date, $count) {
  $raw = (string)$date;
  $ts = strtotime($raw);
  if($ts === false || !preg_match('/^\d{4}-\d{2}-\d{2}/', $raw)) {
    return $raw;
  }
  if((int)$count > 180) {
    return date('M Y', $ts);
  }
  return date('j M', $ts);
}


// Format a datetime for compact stats lists
function osc_admin_stats_recent_date($raw) {
  $ts = strtotime((string)$raw);
  if($ts === false) {
    return (string)$raw;
  }
  return date('Y-m-d H:i', $ts);
}


// Short plain text for stats recent-activity rows
function osc_admin_stats_recent_excerpt($raw, $len = 90) {
  $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string)$raw), ENT_QUOTES, 'UTF-8')));
  if($text == '') {
    return '';
  }
  $len = (int)$len;
  if($len < 20) {
    $len = 20;
  }
  if(function_exists('mb_strlen') && function_exists('mb_substr')) {
    if(mb_strlen($text) > $len) {
      return rtrim(mb_substr($text, 0, $len)) . '...';
    }
    return $text;
  }
  if(strlen($text) > $len) {
    return rtrim(substr($text, 0, $len)) . '...';
  }
  return $text;
}


// Compact recent-activity table: label, optional meta/detail/rating, date
function osc_admin_stats_recent_table($rows) {
  if(!is_array($rows) || empty($rows)) {
    return '<p>' . osc_esc_html(__("There're no statistics yet")) . '</p>';
  }
  $html = '<table class="table stats-recent" cellpadding="0" cellspacing="0"><tbody>';
  foreach($rows as $row) {
    $label = (isset($row['label']) ? (string)$row['label'] : '');
    $href = (isset($row['href']) ? (string)$row['href'] : '');
    $date = (isset($row['date']) ? (string)$row['date'] : '');
    $meta = (isset($row['meta']) ? trim((string)$row['meta']) : '');
    $detail = (isset($row['detail']) ? trim((string)$row['detail']) : '');
    $rating = (isset($row['rating']) ? (int)$row['rating'] : 0);
    $html .= '<tr><td>';
    if($href != '') {
      $html .= '<a class="stats-recent-label" href="' . osc_esc_html($href) . '" title="' . osc_esc_html($label) . '">' . osc_esc_html($label) . '</a>';
    } else {
      $html .= '<span class="stats-recent-label" title="' . osc_esc_html($label) . '">' . osc_esc_html($label) . '</span>';
    }
    if($meta != '') {
      $html .= '<span class="stats-recent-meta">' . osc_esc_html($meta) . '</span>';
    }
    if($detail != '') {
      $html .= '<span class="stats-recent-detail" title="' . osc_esc_html($detail) . '">' . osc_esc_html($detail) . '</span>';
    }
    if($rating > 0) {
      $html .= '<span class="stats-recent-rating">' . osc_esc_html(sprintf(__('Rating: %d of 5'), $rating)) . '</span>';
    }
    $html .= '</td><td class="stats-recent-date">' . osc_esc_html($date) . '</td></tr>';
  }
  $html .= '</tbody></table>';
  return $html;
}


// Queue a plugin chart for the current Statistics page (call from admin_stats_load)
function osc_admin_stats_add_chart($chart) {
  if(!is_array($chart) || !isset($chart['id']) || trim((string)$chart['id']) == '') {
    return;
  }
  $list = View::newInstance()->_get('admin_stats_plugin_charts');
  if(!is_array($list)) {
    $list = array();
  }
  $list[] = $chart;
  View::newInstance()->_exportVariableToView('admin_stats_plugin_charts', $list);
}


// Charts queued by osc_admin_stats_add_chart()
function osc_admin_stats_plugin_charts() {
  $list = View::newInstance()->_get('admin_stats_plugin_charts');
  return is_array($list) ? $list : array();
}


// Keep pie/bar charts readable: top N plus Other
function osc_admin_stats_limit_slices($rows, $label_key, $num_key = 'num', $limit = 10) {
  $rows = array_values((array)$rows);
  $limit = (int)$limit;
  if($limit < 1) {
    $limit = 10;
  }
  if(count($rows) <= $limit) {
    return $rows;
  }
  $top = array_slice($rows, 0, $limit);
  $rest = 0;
  foreach(array_slice($rows, $limit) as $row) {
    $rest += (int)(isset($row[$num_key]) ? $row[$num_key] : 0);
  }
  if($rest > 0) {
    $top[] = array($label_key => __('Other'), $num_key => $rest);
  }
  return $top;
}

// Stacked bar: parent categories on the axis, child categories as series
function osc_admin_stats_category_stacked($rows, $parent_limit = 8, $series_limit = 12) {
  $parents = array();
  foreach((array)$rows as $row) {
    $parent = trim((string)(isset($row['root_name']) ? $row['root_name'] : ''));
    $child = trim((string)(isset($row['cat_name']) ? $row['cat_name'] : ''));
    $num = (int)(isset($row['num']) ? $row['num'] : 0);
    $root_id = (int)(isset($row['root_id']) ? $row['root_id'] : 0);
    $cat_id = (int)(isset($row['cat_id']) ? $row['cat_id'] : 0);
    if($parent == '' || $num < 1) {
      continue;
    }
    if($child == '' || $cat_id == $root_id) {
      $child = $parent;
    }
    if(!isset($parents[$parent])) {
      $parents[$parent] = array('total' => 0, 'children' => array());
    }
    $parents[$parent]['total'] += $num;
    if(!isset($parents[$parent]['children'][$child])) {
      $parents[$parent]['children'][$child] = 0;
    }
    $parents[$parent]['children'][$child] += $num;
  }
  if($parents == array()) {
    return array('series' => array(), 'rows' => array());
  }
  uasort($parents, function($a, $b) {
    if($a['total'] == $b['total']) {
      return 0;
    }
    return ($a['total'] > $b['total']) ? -1 : 1;
  });
  $parent_limit = (int)$parent_limit;
  if($parent_limit < 1) {
    $parent_limit = 8;
  }
  $keep_parents = array_slice($parents, 0, $parent_limit, true);
  $other_parents = array_slice($parents, $parent_limit, null, true);
  if($other_parents != array()) {
    $keep_parents[__('Other')] = array('total' => 0, 'children' => array());
    foreach($other_parents as $pdata) {
      $keep_parents[__('Other')]['total'] += (int)$pdata['total'];
      foreach($pdata['children'] as $cname => $cnum) {
        if(!isset($keep_parents[__('Other')]['children'][$cname])) {
          $keep_parents[__('Other')]['children'][$cname] = 0;
        }
        $keep_parents[__('Other')]['children'][$cname] += (int)$cnum;
      }
    }
  }
  $series_tot = array();
  foreach($keep_parents as $pdata) {
    foreach($pdata['children'] as $cname => $cnum) {
      if(!isset($series_tot[$cname])) {
        $series_tot[$cname] = 0;
      }
      $series_tot[$cname] += (int)$cnum;
    }
  }
  arsort($series_tot);
  $series_limit = (int)$series_limit;
  if($series_limit < 1) {
    $series_limit = 12;
  }
  $keep_series = array_keys(array_slice($series_tot, 0, $series_limit, true));
  $has_other_series = count($series_tot) > $series_limit;
  if($has_other_series) {
    $keep_series[] = __('Other');
  }
  $out_rows = array();
  foreach($keep_parents as $pname => $pdata) {
    $vals = array();
    $other_n = 0;
    foreach($keep_series as $sname) {
      $vals[$sname] = 0;
    }
    foreach($pdata['children'] as $cname => $cnum) {
      if(isset($vals[$cname])) {
        $vals[$cname] += (int)$cnum;
      } else {
        $other_n += (int)$cnum;
      }
    }
    if($has_other_series) {
      $vals[__('Other')] += $other_n;
    }
    $out_rows[] = array('label' => $pname, 'values' => $vals);
  }
  return array('series' => $keep_series, 'rows' => $out_rows);
}

// Mix chart payload for Listings by category (simple root bar, or stacked parent + child)
function osc_admin_stats_listings_by_category_mix($placeholder_id, $rows = null, $mode = 'advanced') {
  if($mode != 'advanced') {
    if($rows === null) {
      $rows = Stats::newInstance()->items_by_root_category();
    }
    $rows = osc_admin_stats_limit_slices((array)$rows, 's_name', 'num', 10);
    return array(
      'id' => $placeholder_id,
      'type' => 'bar',
      'labels' => array(__('Category'), __('Listings')),
      'rows' => $rows,
      'colors' => array(osc_item_stats_palette_color(0))
    );
  }
  if($rows === null) {
    $rows = Stats::newInstance()->items_by_category_levels();
  }
  $data = osc_admin_stats_category_stacked((array)$rows);
  $has_child = false;
  foreach((array)$data['rows'] as $r) {
    $lab = (isset($r['label']) ? (string)$r['label'] : '');
    foreach((array)(isset($r['values']) ? $r['values'] : array()) as $sname => $v) {
      if((int)$v > 0 && (string)$sname !== $lab) {
        $has_child = true;
        break 2;
      }
    }
  }
  if(!$has_child) {
    $bar_rows = array();
    foreach((array)$data['rows'] as $r) {
      $n = 0;
      foreach((array)(isset($r['values']) ? $r['values'] : array()) as $v) {
        $n += (int)$v;
      }
      $bar_rows[] = array('s_name' => (isset($r['label']) ? $r['label'] : ''), 'num' => $n);
    }
    return array(
      'id' => $placeholder_id,
      'type' => 'bar',
      'labels' => array(__('Category'), __('Listings')),
      'rows' => $bar_rows,
      'colors' => array(osc_item_stats_palette_color(0))
    );
  }
  return array(
    'id' => $placeholder_id,
    'type' => 'stacked_bar',
    'labels' => array(__('Category')),
    'series' => $data['series'],
    'rows' => $data['rows']
  );
}

/* file end: ./oc-includes/osclass/helpers/hItemStats.php */

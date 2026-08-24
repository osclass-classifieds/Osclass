<?php
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
* Helper Preferences
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/


/**
 * True when Osclass can generate logs
 *
 * @return boolean
 */
function osc_logging_enabled() {
  return getBoolPreference('logging_enabled');
}


/**
 * True when Osclass logs can be automatically cleaned
 *
 * @return boolean
 */
function osc_logging_auto_cleanup() {
  return getBoolPreference('logging_auto_cleanup');
}


/**
 * Get retention number of months for logs
 *
 * @return string
 */
function osc_logging_months() {
  return (int)getPreference('logging_months');
}

/**
 * Get cleanup threshold in days
 *
 * @return int
 */
function osc_cleanup_threshold_days() {
  $raw = getPreference('cleanup_threshold_days');

  if($raw === '' || $raw === null) {
    return 14;
  }

  return (int)$raw;
}

/**
 * Get list of cleanup types to execute automatically
 *
 * @return string
 */
function osc_cleanup_auto_types() {
  return (string)getPreference('cleanup_auto_types');
}

/**
 * Get auto cleanup types as array
 *
 * @return array
 */
function osc_cleanup_auto_types_array() {
  $raw = trim((string)osc_cleanup_auto_types());

  if($raw == '') {
    return array();
  }

  $parts = explode(',', $raw);
  $types = array();

  foreach($parts as $part) {
    $part = trim((string)$part);

    if($part != '') {
      $types[] = $part;
    }
  }

  return $types;
}

/**
 * True when old item stats should be auto-cleaned by cron
 *
 * @return boolean
 */
function osc_item_stats_auto_cleanup_enabled() {
  $raw = getPreference('item_stats_auto_cleanup_enabled');

  if($raw === '' || $raw === null) {
    $raw = getPreference('item_stats_auto_cleanup');
  }

  if($raw === '' || $raw === null) {
    return false;
  }

  return ($raw == 1 || $raw == '1' || $raw === true);
}

/**
 * Get retention number of months for item stats cleanup
 *
 * @return int
 */
function osc_item_stats_cleanup_months() {
  $raw = getPreference('item_stats_cleanup_months');
  if($raw === '' || $raw === null) {
    return 24;
  }
  return (int)$raw;
}

/**
 * Legacy alias for older code paths
 *
 * @return boolean
 */
function osc_item_stats_auto_cleanup() {
  return osc_item_stats_auto_cleanup_enabled();
}

/**
 * True when alerts feature is enabled
 *
 * @return boolean
 */
function osc_alerts_enabled() {
  $raw = getPreference('alerts_enabled');
  if($raw === '' || $raw === null) {
    return true;
  }
  return ($raw == 1 || $raw == '1' || $raw === true);
}

/**
 * Get default expiration in months for logged user alerts
 *
 * @return int
 */
function osc_alerts_expiration_months_user() {
  $raw = getPreference('alerts_expiration_months_user');
  if($raw === '' || $raw === null) {
    $raw_legacy = getPreference('alerts_expiration_days_user');
    if($raw_legacy !== '' && $raw_legacy !== null) {
      return (int)floor(((int)$raw_legacy) / 30);
    }
    return 3;
  }
  return (int)$raw;
}

/**
 * Get default expiration in months for guest alerts
 *
 * @return int
 */
function osc_alerts_expiration_months_guest() {
  $raw = getPreference('alerts_expiration_months_guest');
  if($raw === '' || $raw === null) {
    $raw_legacy = getPreference('alerts_expiration_days_guest');
    if($raw_legacy !== '' && $raw_legacy !== null) {
      return (int)floor(((int)$raw_legacy) / 30);
    }
    return 3;
  }
  return (int)$raw;
}

/**
 * Legacy alias for older code paths
 *
 * @return int
 */
function osc_alerts_expiration_days_user() {
  return (int)osc_alerts_expiration_months_user() * 30;
}

/**
 * Legacy alias for older code paths
 *
 * @return int
 */
function osc_alerts_expiration_days_guest() {
  return (int)osc_alerts_expiration_months_guest() * 30;
}

/**
 * True when user can set alerts as non-expiring
 *
 * @return boolean
 */
function osc_alerts_allow_non_expiring() {
  $raw = getPreference('alerts_allow_non_expiring');
  if($raw === '' || $raw === null) {
    return true;
  }
  return ($raw == 1 || $raw == '1' || $raw === true);
}

/**
 * True when users can change alert expiration in user menu
 *
 * @return boolean
 */
function osc_alerts_allow_user_expiration_change() {
  $raw = getPreference('alerts_allow_user_expiration_change');
  if($raw === '' || $raw === null) {
    return true;
  }
  return ($raw == 1 || $raw == '1' || $raw === true);
}

/**
 * True when new alerts should default to non-expiring
 *
 * @return boolean
 */
function osc_alerts_default_non_expiring() {
  $raw = getPreference('alerts_default_non_expiring');
  if($raw === '' || $raw === null) {
    return false;
  }
  return ($raw == 1 || $raw == '1' || $raw === true);
}

/**
 * Number of months without login after which alerts are unsubscribed (0 disables)
 *
 * @return int
 */
function osc_alerts_unsub_inactive_months() {
  $raw = getPreference('alerts_unsub_inactive_months');
  if($raw === '' || $raw === null) {
    return 3;
  }
  return (int)$raw;
}


/**
 * True when hreflang tags should be generated (only for multilingual sites)
 *
 * @return boolean
 */
function osc_generate_hreflang_tags_enabled() {
  return getBoolPreference('gen_hreflang_tags');
}


/**
 * True when canonical URL is always generated
 *
 * @return boolean
 */
function osc_always_generate_canonical_enabled() {
  return getBoolPreference('always_gen_canonical');
}


/**
 * True when canonical URL should be enhanced with various rules
 *
 * @return boolean
 */
function osc_rewrite_search_custom_rules_enabled() {
  return getBoolPreference('rewrite_search_custom_rules_enabled');
}


/**
 * True when canonical URL should be enhanced with various rules
 *
 * @return boolean
 */
function osc_rewrite_search_custom_rules_strict() {
  return getBoolPreference('rewrite_search_custom_rules_strict');
}


/**
 * True when canonical URL should be enhanced with various rules
 *
 * @return boolean
 */
function osc_enhance_canonical_url_enabled() {
  return getBoolPreference('enhance_canonical_url_enabled');
}


/**
 * True when site indexing should be disabled
 *
 * @return boolean
 */
function osc_block_indexing_enabled() {
  return getBoolPreference('block_indexing_enabled');
}


/**
 * True when language locale should be added to base URL
 *
 * @return boolean
 */
function osc_locale_to_base_url_enabled() {
  return getBoolPreference('locale_to_base_url_enabled');
}


/**
 * Get for what users is public profile enabled
 *
 * @return string
 */
function osc_user_public_profile_enabled() {
  return getPreference('user_public_profile_enabled');
}


/**
 * Get minimum number of listings user must have to get public profile page enabled
 *
 * @return string
 */
function osc_user_public_profile_min_items() {
  return (int)getPreference('user_public_profile_min_items');
}


/**
 * Get type of locale to be added into base URL
 * Supported values are "" (short: xx) and "LONG" (long: xx-yy)
 *
 * @return string
 */
function osc_locale_to_base_url_type() {
  return getPreference('locale_to_base_url_type');
}

/**
 * Get method for item statistics collection
 *
 * @return string
 */
function osc_item_stats_method() {
  return getPreference('item_stats_method');
}

// Collect listing stats from logged-in visitors only
function osc_item_stats_logged_only() {
  return getBoolPreference('item_stats_logged_only');
}

// CSV of enabled listing-stat measure keys
function osc_item_stats_enabled_pref() {
  $raw = getPreference('item_stats_enabled');
  $def = (function_exists('osc_item_stats_default_enabled_csv') ? osc_item_stats_default_enabled_csv() : 'views,premium_views,views_engaged,views_logged,view_minutes,phone_clicks,contactforms,reports,alerts_sent');
  return ($raw == '' ? $def : (string)$raw);
}

// User summary chart on My listings
function osc_item_stats_user_chart_enabled() {
  $raw = getPreference('item_stats_user_chart_enabled');
  if($raw === '' || $raw === null) {
    return true;
  }
  return getBoolPreference('item_stats_user_chart_enabled');
}

// User summary chart measures
function osc_item_stats_user_chart_measures() {
  $raw = getPreference('item_stats_user_chart_measures');
  $def = (function_exists('osc_item_stats_default_chart_csv') ? osc_item_stats_default_chart_csv() : 'views,premium_views,views_engaged,phone_clicks,contactforms,reports');
  if($raw == '' || $raw == 'views,premium_views') {
    return $def;
  }
  return (string)$raw;
}

// User summary chart default period
function osc_item_stats_user_chart_period() {
  $raw = getPreference('item_stats_user_chart_period');
  return ($raw == '' ? '30d' : (string)$raw);
}

// User summary chart period tabs
function osc_item_stats_user_chart_periods() {
  $raw = getPreference('item_stats_user_chart_periods');
  if($raw == '') {
    $raw = osc_item_stats_chart_periods();
  }
  if(function_exists('osc_item_stats_sanitize_periods')) {
    return osc_item_stats_sanitize_periods($raw);
  }
  return ($raw == '' ? '30d,90d,12m,all' : (string)$raw);
}

// User summary chart type (line|bar|area|stacked)
function osc_item_stats_user_chart_type() {
  $raw = getPreference('item_stats_user_chart_type');
  $ok = array('bar', 'line', 'area', 'stacked_bar', 'stacked_area');
  if($raw == '' || !in_array($raw, $ok, true)) {
    return 'line';
  }
  return $raw;
}

// User summary KPI measures follow the chart measures
function osc_item_stats_user_chart_kpis() {
  return osc_item_stats_user_chart_measures();
}

// User summary chart hook names (comma-separated)
function osc_item_stats_user_chart_hooks() {
  $raw = trim((string)getPreference('item_stats_user_chart_hooks'));
  return ($raw == '' ? 'user_items_top' : $raw);
}

// Who can see user summary and listing charts (all|company, plugins may add keys)
function osc_item_stats_chart_audience() {
  $raw = trim((string)getPreference('item_stats_chart_audience'));
  if($raw == '') {
    $raw = 'all';
  }
  if(function_exists('osc_item_stats_chart_audience_options')) {
    $options = osc_item_stats_chart_audience_options();
    if(!is_array($options) || !isset($options[$raw])) {
      $raw = 'all';
    }
  }
  return (string)osc_apply_filter('osc_item_stats_chart_audience', $raw);
}

// User listing chart
function osc_item_stats_item_chart_enabled() {
  $raw = getPreference('item_stats_item_chart_enabled');
  if($raw === '' || $raw === null) {
    return false;
  }
  return getBoolPreference('item_stats_item_chart_enabled');
}

// Show user listing chart to a logged-in administrator
function osc_item_stats_item_chart_admin() {
  $raw = getPreference('item_stats_item_chart_admin');
  if($raw === '' || $raw === null) {
    return true;
  }
  return getBoolPreference('item_stats_item_chart_admin');
}

// User listing chart measures
function osc_item_stats_item_chart_measures() {
  $raw = getPreference('item_stats_item_chart_measures');
  $def = (function_exists('osc_item_stats_default_chart_csv') ? osc_item_stats_default_chart_csv() : 'views,premium_views,views_engaged,phone_clicks,contactforms,reports');
  if($raw == '' || $raw == 'views,premium_views') {
    return $def;
  }
  return (string)$raw;
}

// User listing chart type (line|bar|area|stacked)
function osc_item_stats_item_chart_type() {
  $raw = getPreference('item_stats_item_chart_type');
  $ok = array('bar', 'line', 'area', 'stacked_bar', 'stacked_area');
  if($raw == '' || !in_array($raw, $ok, true)) {
    return osc_item_stats_user_chart_type();
  }
  return $raw;
}

// User listing chart hook names (comma-separated)
function osc_item_stats_item_chart_hooks() {
  $raw = trim((string)getPreference('item_stats_item_chart_hooks'));
  return ($raw == '' ? 'item_top' : $raw);
}

// User listing chart default period
function osc_item_stats_item_chart_period() {
  $raw = getPreference('item_stats_item_chart_period');
  return ($raw == '' ? '30d' : (string)$raw);
}

// User listing chart period tabs
function osc_item_stats_item_chart_periods() {
  $raw = getPreference('item_stats_item_chart_periods');
  if($raw == '') {
    $raw = osc_item_stats_chart_periods();
  }
  if(function_exists('osc_item_stats_sanitize_periods')) {
    return osc_item_stats_sanitize_periods($raw);
  }
  return ($raw == '' ? '30d,90d,12m,all' : (string)$raw);
}

// Default admin statistics period
function osc_item_stats_admin_default_period() {
  $raw = getPreference('item_stats_admin_default_period');
  return ($raw == '' ? '30d' : (string)$raw);
}

// Default Listing-details measures
function osc_item_stats_admin_default_measures() {
  $raw = getPreference('item_stats_admin_default_measures');
  $def = (function_exists('osc_item_stats_default_enabled_csv') ? osc_item_stats_default_enabled_csv() : 'views,premium_views,views_engaged,views_logged,view_minutes,phone_clicks,contactforms,reports,alerts_sent');
  return ($raw == '' || $raw == 'views,premium_views' ? $def : (string)$raw);
}

// Allowed chart period keys
function osc_item_stats_chart_periods() {
  $raw = getPreference('item_stats_chart_periods');
  if(function_exists('osc_item_stats_sanitize_periods')) {
    return osc_item_stats_sanitize_periods($raw);
  }
  return ($raw == '' ? '30d,90d,12m,all' : (string)$raw);
}

// Collection preset name
function osc_item_stats_preset() {
  $raw = getPreference('item_stats_preset');
  return ($raw == '' ? 'essential' : (string)$raw);
}

// Custom measure label 1-3
function osc_item_stats_custom_label($n) {
  $n = (int)$n;
  if($n < 1 || $n > 3) {
    return '';
  }
  $raw = getPreference('item_stats_custom' . $n . '_label');
  if($raw == '') {
    return sprintf(__('Custom %d'), $n);
  }
  return (string)$raw;
}

// Seconds on the listing page before an engaged view is counted
function osc_item_stats_engaged_seconds() {
  static $seeded = false;
  if(!$seeded) {
    $seeded = true;
    if((string)getPreference('item_stats_engaged_seeded') !== '1') {
      $cur = (int)getPreference('item_stats_engaged_seconds');
      if($cur == 60 || $cur < 5) {
        osc_set_preference('item_stats_engaged_seconds', 15, 'osclass', 'INTEGER');
      }
      osc_set_preference('item_stats_engaged_seeded', '1', 'osclass', 'BOOLEAN');
    }
  }
  $n = (int)getPreference('item_stats_engaged_seconds');
  if($n < 5) {
    $n = 15;
  }
  if($n > 600) {
    $n = 600;
  }
  return $n;
}

// CSS selectors for phone clicks on the listing page
function osc_item_stats_phone_selectors() {
  $raw = trim((string)getPreference('item_stats_phone_selectors'));
  if($raw == '') {
    return '.phone, div.phone, .show-phone, .show-mobile, a[href^="tel:"]';
  }
  return $raw;
}

// CSS selectors for other-contact clicks on the listing page
function osc_item_stats_contactother_selectors() {
  $raw = trim((string)getPreference('item_stats_contactother_selectors'));
  if($raw == '') {
    return '.other, .contact-other, .show-other, [data-osc-stat="contactother_clicks"]';
  }
  return $raw;
}

/**
 * Get image canvas background
 *
 * @return string
 */
function osc_canvas_background() {
  $bg = getPreference('canvas_background');
  return ($bg == '' ? 'white' : $bg);
}


/**
 * Check if item contact form is enabled
 *
 * @return boolean
 */
function osc_item_contact_form_disabled() {
  return getBoolPreference('item_contact_form_disabled');
}


/**
 * Check if item send to friend form is enabled
 *
 * @return boolean
 */
function osc_item_send_friend_form_disabled() {
  return getBoolPreference('item_send_friend_form_disabled');
}


/**
 * Legacy "Mark as" is retired. Always disabled so old themes hide those links.
 *
 * @return boolean
 */
function osc_item_mark_disable() {
  return true;
}


/**
 * Check if web contact form is enabled
 *
 * @return boolean
 */
function osc_web_contact_form_disabled() {
  return getBoolPreference('web_contact_form_disabled');
}


/**
 * Check if website contact form creates a report instead of sending email
 *
 * @return boolean
 */
function osc_web_contact_create_report() {
  return getBoolPreference('web_contact_create_report');
}


/**
 * Check if oc-content folder should be updated
 *
 * @return boolean
 */
function osc_update_occontent() {
  return getBoolPreference('update_include_occontent');
}

/**
 * Check if country is enabled on listings page in breadcrumbs
 *
 * @return string
 */
function osc_item_breadcrumbs_country() {
  return getBoolPreference('breadcrumbs_item_country');
}

/**
 * Check if region is enabled on listings page in breadcrumbs
 *
 * @return boolean
 */
function osc_item_breadcrumbs_region() {
  return getBoolPreference('breadcrumbs_item_region');
}

/**
 * Check if city is enabled on listings page in breadcrumbs
 *
 * @return boolean
 */
function osc_item_breadcrumbs_city() {
  return getBoolPreference('breadcrumbs_item_city');
}

/**
 * Check if parent categories is enabled on listings page in breadcrumbs
 *
 * @return boolean
 */
function osc_item_breadcrumbs_parent_categories() {
  return getBoolPreference('breadcrumbs_item_parent_categories');
}

/**
 * Check if category is enabled on listings page in breadcrumbs
 *
 * @return boolean
 */
function osc_item_breadcrumbs_category() {
  return getBoolPreference('breadcrumbs_item_category');
}

/**
 * Get list of custom breadcrumb codes to hide
 *
 * @return string
 */
function osc_breadcrumbs_hide_custom_pref() {
  return getPreference('breadcrumbs_hide_custom');
}


/**
 * Check if breadcrumbs are hidden on specific page
 *
 * @return boolean
 */
function osc_breadcrumbs_hide($location = '', $section = '') {
  $data = explode(',', getPreference('breadcrumbs_hide'));

  $loc_sec = trim((string)$location) . '-' . trim((string)$section);

  if(in_array($loc_sec, $data)) {
    return true;
  }

  return false;
}


/**
 * Check if breadcrumbs are hidden on specific page
 *
 * @return boolean
 */
function osc_breadcrumbs_hide_custom($location = '', $section = '') {
  if(trim((string)$location) == '') {
    return false;
  }

  $data = explode(',', getPreference('breadcrumbs_hide_custom'));
  $loc_sec = trim((string)$location) . '-' . trim((string)$section);

  if(in_array($loc_sec, $data)) {
    return true;
  }

  return false;
}


/**
 * Check if page title is enabled on listings page in breadcrumbs
 *
 * @return string
 */
function osc_item_breadcrumbs_page_title() {
  return getPreference('breadcrumbs_item_page_title');
}


/**
 * Gets cookie's life
 *
 * @return int
 */
function osc_time_cookie() {
  return 86400 * 365 * 3; // 3 years in seconds
}

/**
 * Gets if comments are enabled or not
 *
 * @return boolean
 */
function osc_comments_enabled() {
  return getBoolPreference('enabled_comments');
}

/**
 * Check if CSS has banned keywords
 *
 * @return string
 */
function osc_css_banned_words() {
  return getPreference('css_banned_words');
}

/**
 * Check if CSS has banned pages
 *
 * @return string
 */
function osc_css_banned_pages() {
  return getPreference('css_banned_pages');
}


/**
 * Check if CSS merge is enabled
 *
 * @return boolean
 */
function osc_css_merge() {
  return getBoolPreference('css_merge');
}

/**
 * Check if CSS minify is enabled
 *
 * @return boolean
 */
function osc_css_minify() {
  return getBoolPreference('css_minify');
}

/**
 * Check if JS merge is enabled
 *
 * @return boolean
 */
function osc_js_merge() {
  return getBoolPreference('js_merge');
}

/**
 * Check if JS minify is enabled
 *
 * @return boolean
 */
function osc_js_minify() {
  return getBoolPreference('js_minify');
}

/**
 * Check if JS has banned keywords
 *
 * @return string
 */
function osc_js_banned_words() {
  return getPreference('js_banned_words');
}

/**
 * Check if JS has banned pages
 *
 * @return string
 */
function osc_js_banned_pages() {
  return getPreference('js_banned_pages');
}

/**
 * Get optimization files automatic cleanup frequency
 *
 * @return string
 */
function osc_optimization_cleanup_frequency() {
  $frequency = getPreference('optimization_cleanup_frequency');
  if(!in_array($frequency, array('none', 'weekly', 'monthly'), true)) {
    return 'weekly';
  }

  return $frequency;
}

/**
 * Get database optimization frequency
 *
 * @return string
 */
function osc_database_optimization_frequency() {
  $frequency = getPreference('database_optimization_frequency');
  if(!in_array($frequency, array('none', 'daily', 'weekly', 'monthly'), true)) {
    return 'none';
  }

  return $frequency;
}

/**
 * Get database optimization operations
 *
 * @return string
 */
function osc_database_optimization_operations() {
  $operations = getPreference('database_optimization_operations');
  if(trim((string)$operations) == '') {
    return 'check,analyze,optimize';
  }

  return $operations;
}

/**
 * Get database optimization operations as array
 *
 * @return array
 */
function osc_database_optimization_operations_array() {
  $available = array('check', 'analyze', 'optimize', 'flush');
  $operations = explode(',', osc_database_optimization_operations());
  $output = array();

  foreach($operations as $operation) {
    $operation = trim(strtolower((string)$operation));

    if(in_array($operation, $available, true) && !in_array($operation, $output, true)) {
      $output[] = $operation;
    }
  }

  return $output;
}

/**
 * Force uploaded images to be JPEG
 *
 * @return boolean
 */
function osc_force_jpeg() {
  return getBoolPreference('force_jpeg');
}

/**
 * Gets comments per page
 *
 * @return int
 */
function osc_comments_per_page() {
  return (int)(getPreference('comments_per_page'));
}

/**
 * Gets auto update settings
 *
 * @return string
 */
function osc_auto_update() {
  return getPreference('auto_update');
}


function osc_update_api_key() {
  return getPreference('osclasspoint_api_key');
}

/**
 * Gets renewal if enabled
 *
 * @return boolean
 */
function osc_renewal_items_enabled() {
  return getBoolPreference('enabled_renewal_items');
}

/**
 * Gets if renewal updates publish date
 *
 * @return boolean
 */
function osc_renewal_update_publish_date() {
  return getBoolPreference('renewal_update_pub_date');
}


/**
 * Gets renewal limit
 *
 * @return int
 */
function osc_renewal_limit() {
  return (int)getPreference('renewal_limit');
}


/**
 * Gets if structured data are enabled
 *
 * @return boolean
 */
function osc_structured_data_enabled() {
  return getBoolPreference('structured_data');
}


/**
 * Gets if hide generator is enabled
 *
 * @return boolean
 */
function osc_hide_generator_enabled() {
  return getBoolPreference('hide_generator');
}


/**
 * Gets search pattern filter method
 *
 * @return string
 */
function osc_search_pattern_method() {
  return getPreference('search_pattern_method');
}

/**
 * Gets auto update settings
 *
 * @return string
 */
function osc_search_pattern_current_locale_only() {
  return getBoolPreference('search_pattern_locale');
}

/**
 * Gets latest search filtering word method
 *
 * @return int
 */
function osc_latest_searches_restriction() {
  return getPreference('latest_searches_restriction');
}

/**
 * Gets latest search words to be filtered
 *
 * @return string
 */
function osc_latest_searches_words() {
  return getPreference('latest_searches_words');
}

/**
 * Gets minimum latest search word length
 *
 * @return int
 */
function osc_latest_searches_min_length() {
  $value = (int)getPreference('latest_searches_min_length');
  return ($value > 0 ? $value : 3);
}

/**
 * Gets maximum latest search word length
 *
 * @return int
 */
function osc_latest_searches_max_length() {
  $value = (int)getPreference('latest_searches_max_length');
  return ($value > 0 ? $value : 15);
}

/**
 * Gets number of days to warn about an ad being expired
 *
 * @return int
 */
function osc_warn_expiration() {
  return (int)(getPreference('warn_expiration'));
}

/**
 * Gets timezone
 *
 * @return string
 */
function osc_timezone() {
  return getPreference('timezone');
}

/**
 * Gets csrf session name
 *
 * @return int
 */
function osc_csrf_name() {
  return getPreference('csrf_name');
}

/**
 * Gets if only users can post comments
 *
 * @return boolean
 */
function osc_reg_user_post_comments() {
  return getBoolPreference('reg_user_post_comments');
}

/**
 * Gets if only users can contact to seller
 *
 * @return boolean
 */
function osc_reg_user_can_contact() {
  return getPreference('reg_user_can_contact');
}

/**
 * Gets if only users can contact to seller
 *
 * @return boolean
 */
function osc_reg_user_can_see_phone() {
  return getPreference('reg_user_can_see_phone');
}

/**
 * Gets list of blacklsited terms for usernames
 *
 * @return string
 */
function osc_username_blacklist() {
  return getPreference('username_blacklist');
}

/**
 * Gets username generator method
 *
 * @return string
 */
function osc_username_generator() {
  return getPreference('username_generator');
}

/**
 * Gets if users are enabled or not
 *
 * @return boolean
 */
function osc_users_enabled() {
  return getBoolPreference('enabled_users');
}

/**
 * Gets if user registration is enabled
 *
 * @return boolean
 */
function osc_user_registration_enabled() {
  return getBoolPreference('enabled_user_registration');
}

/**
 * Gets is user validation is enabled or not
 *
 * @return boolean
 */
function osc_user_validation_enabled() {
  return getBoolPreference('enabled_user_validation');
}

/**
 * Gets if validation for logged users is required or not
 *
 * @return boolean
 */
function osc_logged_user_item_validation() {
  return getBoolPreference('logged_user_item_validation');
}

/**
 * Gets if rating is enabled on comments or not
 *
 * @return boolean
 */
function osc_enable_comment_rating() {
  return getBoolPreference('enable_comment_rating');
}

/**
 * Gets limit of how many ratings can leave one user on one listing
 *
 * @return int
 */
function osc_comment_rating_limit() {
  return (int)(getPreference('comment_rating_limit'));
}

/**
 * Gets if comment replies are enabled
 *
 * @return boolean
 */
function osc_enable_comment_reply() {
  return getBoolPreference('enable_comment_reply');
}

/**
 * Gets if rating is enabled on comments or not
 *
 * @return boolean
 */
function osc_enable_comment_reply_rating() {
  return getBoolPreference('enable_comment_reply_rating');
}

/**
 * Gets if type of user that can reply to comment
 * EMPTY - anyone can reply to comments
 * LOGGED - only logged in user can reply to comments
 * OWNER - only listing owner can reply to comments
 * ADMIN - only logged in admin can reply to comments
 *
 * @return boolean
 */
function osc_comment_reply_user_type() {
  return getPreference('comment_reply_user_type');
}

/**
 * Gets if notification of new comments is enabled or not to users on reply
 *
 * @return boolean
 */
function osc_notify_new_comment_reply_user() {
  return getBoolPreference('notify_new_comment_reply_user');
}

/**
 * Gets if notification of new comment reply is enabled or not to admin
 *
 * @return boolean
 */
function osc_notify_new_comment_reply() {
  return getBoolPreference('notify_new_comment_reply');
}


/**
 * Gets how many comments should be posted before auto-moderation
 *
 * @return int
 */
function osc_moderate_comments() {
  return (int)(getPreference('moderate_comments'));
}

/**
 * Gets if notification of new comments is enabled or not to admin
 *
 * @return boolean
 */
function osc_notify_new_comment() {
  return getBoolPreference('notify_new_comment');
}

/**
 * Gets if notification of new comments is enabled or not to users
 *
 * @return boolean
 */
function osc_notify_new_comment_user() {
  return getBoolPreference('notify_new_comment_user');
}

/**
 * Gets if nice urls are enabled or not
 *
 * @return boolean
 */
function osc_rewrite_enabled() {
  return getBoolPreference('rewriteEnabled');
}

/**
 * Gets if mod rewrite is loaded or not (if apache runs on cgi mode, mod rewrite will not be detected)
 *
 * @return boolean
 */
function osc_mod_rewrite_loaded() {
  return getBoolPreference('mod_rewrite_loaded');
}

/**
 * Gets type of image uploader library to use
 *
 * @return string
 */

function osc_image_upload_library() {
  return getPreference('image_upload_library');
}

/**
 * Returns true if reordering of images on item publish/edit page is enabled
 *
 * @return boolean
 */

function osc_image_upload_reorder() {
  return getBoolPreference('image_upload_reorder');
}

/**
 * Returns true if reordering of images on item publish/edit page is enabled
 *
 * @return boolean
 */

function osc_image_upload_lib_force_replace() {
  return getBoolPreference('image_upload_lib_force_replace');
}


/**
 * Gets if original images should be kept
 *
 * @return boolean
 */
function osc_keep_original_image() {
  return getBoolPreference('keep_original_image');
}

/**
 * Force image aspect
 *
 * @return boolean
 */
function osc_force_aspect_image() {
  return getBoolPreference('force_aspect_image');
}

/**
 * Optimize uploaded images
 *
 * @return boolean
 */
function osc_optimize_uploaded_images() {
  return getBoolPreference('optimize_uploaded_images');
}


/**
 * Gets if best fit method should be used on images
 *
 * @return boolean
 */
function osc_best_fit_image() {
  return getBoolPreference('best_fit_image');
}

/**
 * Gets if autocron is enabled
 *
 * @return boolean
 */
function osc_auto_cron() {
  return getBoolPreference('auto_cron');
}

/**
 * Gets if recaptcha for items is enabled or not
 *
 * @return boolean
 */
function osc_recaptcha_items_enabled() {
  return getBoolPreference('enabled_recaptcha_items');
}

/**
 * Gets if TinyMCE for items is enabled or not
 *
 * @return boolean
 */
function osc_tinymce_items_enabled() {
  return getBoolPreference('enabled_tinymce_items');
}

/**
 * Gets if TinyMCE for users is enabled or not
 *
 * @return boolean
 */
function osc_tinymce_users_enabled() {
  return getBoolPreference('enabled_tinymce_users');
}

/**
 * Widget locale-strict mode: current locale only, no fallback
 *
 * @return boolean
 */
function osc_widget_locale_strict() {
  return getBoolPreference('widget_locale_strict');
}

/**
 * Custom widget section slugs from preference
 *
 * @return array
 */
function osc_widget_custom_sections() {
  $raw = getPreference('widget_custom_sections');
  $out = array();

  if($raw != '') {
    $parts = preg_split('/[\s,]+/', (string)$raw, -1, PREG_SPLIT_NO_EMPTY);
    foreach($parts as $part) {
      $slug = osc_widget_sanitize_slug($part);
      if($slug != '') {
        $out[] = $slug;
      }
    }
  }

  return osc_apply_filter('widget_custom_sections', array_values(array_unique($out)));
}


/**
 * Gets if admin toolbar is enabled in front
 *
 * @return boolean
 */
function osc_admin_toolbar_front_enabled() {
  return getBoolPreference('admin_toolbar_front');
}


/**
 * Gets if profile pictures for users are enabled or not
 *
 * @return boolean
 */
function osc_profile_img_users_enabled() {
  return getBoolPreference('enable_profile_img');
}

/**
 * Gets profile picture image uploader library
 *
 * @return string
 */
function osc_profile_picture_library() {
  return getPreference('profile_picture_library');
}


/**
 * Gets how many seconds should an user wait to post a second item (0 for no waiting)
 *
 * @return int
 */
function osc_items_wait_time() {
  return (int)(getPreference('items_wait_time'));
}

/**
 * Gets how many items should be moderated to enable auto-moderation
 *
 * @return int
 */
function osc_moderate_items() {
  return (int)(getPreference('moderate_items'));
}

/**
 * Returns if user can disable/deactivate listing
 *
 * @return bool
 */
function osc_can_deactivate_items() {
  return getBoolPreference('can_deactivate_items');
}

/**
 * Gets if only registered users can publish new items or anyone could
 *
 * @return boolean
 */
function osc_reg_user_post() {
  return getBoolPreference('reg_user_post');
}

/**
 * Gets if the prices are o not enabled on the item's form
 *
 * @return boolean
 */
function osc_price_enabled_at_items() {
  return getBoolPreference('enableField#f_price@items');
}

/**
 * Gets if images are o not enabled in item's form
 *
 * @return boolean
 */
function osc_images_enabled_at_items() {
  return getBoolPreference('enableField#images@items');
}

/**
 * Gets how many images are allowed per item (o for unlimited)
 *
 * @return int
 */
function osc_max_images_per_item() {
  return (int)(getPreference('numImages@items'));
}

/**
 * Gets redirect priority after publishing listing
 *
 * @return int
 */
function osc_get_redirect_after_publish() {
  return getPreference('item_post_redirect');
}

/**
 * Gets how many characters are allowed for the listings title
 *
 * @return int
 */
function osc_max_characters_per_title() {
  $value = getPreference('title_character_length');
  return ( !empty($value) ? (int)$value : 128);
}

/**
 * Gets how many characters are allowed for the listings description
 *
 * @return int
 */
function osc_max_characters_per_description() {
  $value = getPreference('description_character_length');
  return ( !empty($value) ? (int)$value : 4096);
}

/**
 * Gets if notification are sent to admin when a send-a-friend message is sent
 *
 * @return boolean
 */
function osc_notify_contact_friends() {
  return getBoolPreference('notify_contact_friends');
}

/**
 * Gets if notification are sent to admin when a contact message is sent
 *
 * @return boolean
 */
function osc_notify_contact_item() {
  return getBoolPreference('notify_contact_item');
}

/**
 * Gets item attachment is enabled
 *
 * @return boolean
 */
function osc_item_attachment() {
  return getBoolPreference('item_attachment');
}

/**
 * Gets if contact attachment is enabled
 *
 * @return boolean
 */
function osc_contact_attachment() {
  return getBoolPreference('contact_attachment');
}

/**
 * Gets if notification are sent to admin with new item
 *
 * @return boolean
 */
function osc_notify_new_item() {
  return getBoolPreference('notify_new_item');
}

/**
 * Gets if notification are sent to admin with new user
 *
 * @return boolean
 */
function osc_notify_new_user() {
  return getBoolPreference('notify_new_user');
}

/**
 * Gets if the mailserver requires authetification
 *
 * @return boolean
 */
function osc_mailserver_auth() {
  return getBoolPreference('mailserver_auth');
}

/**
 * Gets if the mailserver requires authetification
 *
 * @return boolean
 */
function osc_mailserver_pop() {
  return getBoolPreference('mailserver_pop');
}


//OTHER FUNCTIONS TO GET INFORMATION OF PREFERENCES
/**
 * Gets the rewrite rules (generated via generate_rules.php at root folder)
 *
 * @return string
 */
function osc_rewrite_rules() {
  return getPreference('rewrite_rules');
}

/**
 * Gets max kb of uploads
 *
 * @return int
 */
function osc_max_size_kb() {
  return (int)(getPreference('maxSizeKb'));
}

/**
 * List of image extensions Osclass can handle for item uploads.
 *
 * @return array
 */
function osc_available_image_upload_extensions() {
  $extensions = array('png', 'gif', 'jpg', 'jpeg', 'heif', 'webp', 'avif');

  return osc_apply_filter('available_image_upload_extensions', $extensions);
}

/**
 * Raw allowedExt preference (comma-separated), without server filtering.
 *
 * @return string
 */
function osc_allowed_extension_preference() {
  $stored = trim((string)getPreference('allowedExt'));

  if($stored === '') {
    return 'png,gif,jpg,jpeg';
  }

  return $stored;
}

/**
 * Parse allowed image extensions from comma-separated string or array input.
 *
 * @param string|array $extensions
 *
 * @return array
 */
function osc_parse_allowed_image_extensions($extensions) {
  if(is_array($extensions)) {
    $list = $extensions;
  } else {
    $list = explode(',', (string)$extensions);
  }

  $available = osc_available_image_upload_extensions();
  $parsed = array();

  foreach($list as $ext) {
    $ext = strtolower(trim((string)$ext));

    if($ext === 'heic') {
      $ext = 'heif';
    }

    if($ext !== '' && in_array($ext, $available, true) && !in_array($ext, $parsed, true)) {
      $parsed[] = $ext;
    }
  }

  return $parsed;
}

/**
 * Check if ImageMagick supports a given image format on this server.
 *
 * @param string $format
 *
 * @return bool
 */
function osc_imagick_supports_image_format($format) {
  if(!extension_loaded('imagick') || !class_exists('Imagick')) {
    return false;
  }

  $format = strtoupper(trim((string)$format));
  $formats = @Imagick::queryFormats($format);

  if(!is_array($formats) || count($formats) === 0) {
    return false;
  }

  foreach($formats as $f) {
    if(stripos((string)$f, $format) === 0) {
      return true;
    }
  }

  return false;
}

/**
 * Check if GD can read/write a given image extension.
 *
 * @param string $ext
 *
 * @return bool
 */
function osc_gd_supports_image_extension($ext) {
  $ext = strtolower(trim((string)$ext));

  if($ext === 'heic') {
    $ext = 'heif';
  }

  switch($ext) {
    case 'png':
    case 'gif':
    case 'jpg':
    case 'jpeg':
      return function_exists('imagecreatefromstring') && function_exists('imagepng');

    case 'webp':
      return function_exists('imagecreatefromwebp') && function_exists('imagewebp');

    case 'avif':
      return function_exists('imagecreatefromavif') && function_exists('imageavif');

    case 'heif':
      return false;

    default:
      return false;
  }
}

/**
 * Check if current server stack can process an image extension for uploads.
 *
 * @param string $ext
 *
 * @return bool
 */
function osc_server_supports_image_extension($ext) {
  $ext = strtolower(trim((string)$ext));

  if($ext === 'heic') {
    $ext = 'heif';
  }

  if(!in_array($ext, osc_available_image_upload_extensions(), true)) {
    return false;
  }

  if(in_array($ext, array('png', 'gif', 'jpg', 'jpeg'), true)) {
    return true;
  }

  if(extension_loaded('imagick') && osc_use_imagick()) {
    if($ext === 'webp') {
      return osc_imagick_supports_image_format('WEBP');
    }

    if($ext === 'avif') {
      return osc_imagick_supports_image_format('AVIF');
    }

    if($ext === 'heif') {
      return (osc_imagick_supports_image_format('HEIC') || osc_imagick_supports_image_format('HEIF'));
    }

    return false;
  }

  return osc_gd_supports_image_extension($ext);
}

/**
 * Human-readable reason why an extension is unavailable on this server.
 *
 * @param string $ext
 *
 * @return string
 */
function osc_server_unsupported_image_extension_reason($ext) {
  $ext = strtolower(trim((string)$ext));

  if($ext === 'heic') {
    $ext = 'heif';
  }

  if(osc_server_supports_image_extension($ext)) {
    return '';
  }

  if(extension_loaded('imagick') && osc_use_imagick()) {
    return __('ImageMagick is active in Media Settings, but this format is not supported by your ImageMagick build.');
  }

  if($ext === 'heif') {
    return __('HEIF/HEIC requires ImageMagick with HEIC/HEIF support enabled in Media Settings.');
  }

  if($ext === 'avif') {
    return __('AVIF requires PHP 8.1+ GD (imageavif) or ImageMagick with AVIF support.');
  }

  if($ext === 'webp') {
    return __('WebP requires GD (imagewebp) or ImageMagick with WebP support.');
  }

  return __('This image format is not supported by the active image library on this server.');
}

/**
 * Normalize and validate allowed upload extensions for storage.
 *
 * @param string|array $extensions
 *
 * @return string
 */
function osc_prepare_allowed_image_extensions($extensions) {
  $selected = osc_parse_allowed_image_extensions($extensions);
  $supported = array();
  $base = array('png', 'gif', 'jpg', 'jpeg');

  foreach($selected as $ext) {
    if(osc_server_supports_image_extension($ext)) {
      $supported[] = $ext;
    }
  }

  $has_base = false;

  foreach($supported as $ext) {
    if(in_array($ext, $base, true)) {
      $has_base = true;
      break;
    }
  }

  if(!$has_base) {
    $supported = array_values(array_unique(array_merge($base, $supported)));
  }

  if(count($supported) === 0) {
    $supported = $base;
  }

  return implode(',', $supported);
}

/**
 * Gets allowed extensions of uploads (effective list, server-capability aware).
 *
 * @return string
 */
function osc_allowed_extension() {
  return osc_prepare_allowed_image_extensions(osc_allowed_extension_preference());
}

/**
 * Gets if use of imagick is enabled or not
 *
 * @return string
 */
function osc_use_imagick() {
  return getBoolPreference('use_imagick');
}

/**
 * Gets thumbnails' dimensions
 *
 * @return string
 */
function osc_thumbnail_dimensions() {
  return getPreference('dimThumbnail');
}

/**
 * Gets profile image dimension
 *
 * @return string
 */
function osc_profile_img_dimensions() {
  return getPreference('dimProfileImg');
}

/**
 * Gets preview images' dimensions
 *
 * @return string
 */
function osc_preview_dimensions() {
  return getPreference('dimPreview');
}

/**
 * Gets normal size images' dimensions
 *
 * @return string
 */
function osc_normal_dimensions() {
  return getPreference('dimNormal');
}

/**
 * Gets when was the last version check
 *
 * @return int
 */
function osc_last_version_check() {
  return (int)(getPreference('last_version_check'));
}

/**
 * Gets when selected stats were last recalculated
 *
 * @param string $type
 * @return int
 */
function osc_stats_last_recalc($type = '') {
  $type = strtolower(trim((string)$type));

  if(!in_array($type, array('location', 'category', 'user'))) {
    return 0;
  }

  return (int)(getPreference($type . '_stats_last_recalc'));
}

/**
 * Gets when was the last version check
 *
 * @return int
 */
function osc_themes_last_version_check() {
  return (int)(getPreference('themes_last_version_check'));
}

/**
 * Gets when was the last version check
 *
 * @return int
 */
function osc_plugins_last_version_check() {
  return (int)(getPreference('plugins_last_version_check'));
}

/**
 * Gets when was the last version check
 *
 * @return int
 */
function osc_languages_last_version_check() {
  return (int)(getPreference('languages_last_version_check'));
}

/**
 * Gets json response when checking if there is available a new version
 *
 * @return string
 */
function osc_update_core_json() {
  return getPreference('update_core_json');
}

/**
 * Gets current version
 *
 * @return int
 */
function osc_version($with_dots = false) {
  $version = getPreference('version');

  if($with_dots) {
    return implode('.', str_split($version));
  } else {
    return (int)$version;
  }
}

/**
 * Gets current jquery version
 *
 * @return string
 */
function osc_jquery_version() {
  $version = trim(getPreference('jquery_version'));
  return ($version <> '' ? $version : '1');
}

/**
 * Gets website's title
 *
 * @return string
 */
function osc_page_title() {
  return getPreference('pageTitle');
}

/**
 * Gets website's default language
 *
 * @return string
 */
function osc_language() {
  return (getPreference('language'));
}

/**
 * Gets website's admin default language
 *
 * @return string
 */
function osc_admin_language() {
  return (getPreference('admin_language'));
}

/**
 * Gets current theme
 *
 * @return string
 */
function osc_theme() {
  return (getPreference('theme'));
}

/**
 * Gets current admin theme
 *
 * @return string
 */
function osc_admin_theme() {
  return (getPreference('admin_theme'));
}

/**
 * Gets website description
 *
 * @return string
 */
function osc_page_description() {
  return (getPreference('pageDesc'));
}

/**
 * Gets contact email
 *
 * @return string
 */
function osc_contact_email() {
  return (getPreference('contactEmail'));
}

/**
 * Gets date format
 *
 * @return string
 */
function osc_date_format() {
  return (getPreference('dateFormat'));
}

/**
 * Gets time format
 *
 * @return string
 */
function osc_time_format() {
  return (getPreference('timeFormat'));
}

/**
 * Gets week start day
 *
 * @return string
 */
function osc_week_starts_at() {
  return (getPreference('weekStart'));
}

/**
 * Get if RSS feed is enabled
 *
 * @return boolean
 */
function osc_rss_enabled() {
  return getBoolPreference('enable_rss');
}


/**
 * Gets number of items to display on RSS
 *
 * @return int
 */
function osc_num_rss_items() {
  return (int)(getPreference('num_rss_items'));
}

/**
 * Gets number of category levels
 *
 * @return int
 */
function osc_num_category_levels() {
  return (int)(getPreference('num_category_levels'));
}


/**
 * Gets default currency
 *
 * @return string
 */
function osc_currency() {
  return (getPreference('currency'));
}

/**
 * Gets default currency row
 *
 * @return array
 */
function osc_currency_row() {
  $data = osc_get_currency_row(osc_currency());
  return $data;
}

/**
 * Gets default currency symbol
 *
 * @return string
 */
function osc_currency_symbol() {
  $data = osc_currency_row();

  if(isset($data['s_description'])) {
    return $data['s_description'];
  }

  return osc_currency();
}


/**
 * Gets akismet key
 *
 * @return string
 */
function osc_akismet_key() {
  return (getPreference('akismetKey'));
}

/**
 * Gets if recaptcha for items is enabled or not
 *
 * @return boolean
 */
function osc_recaptcha_enabled() {
  return getBoolPreference('recaptchaEnabled');
}

/**
 * Gets recaptcha public key
 *
 * @return string
 */
function osc_recaptcha_public_key($force = false) {
  if(!osc_recaptcha_enabled() && $force === false) {
    return '';
  }

  return (getPreference('recaptchaPubKey'));
}

/**
 * Gets recaptcha private key
 *
 * @return string
 */
function osc_recaptcha_private_key($force = false) {
  if(!osc_recaptcha_enabled() && $force === false) {
    return '';
  }

  return (getPreference('recaptchaPrivKey'));
}

/**
 * Gets if third party sources are allowed to install new plugins and themes
 *
 * @return int
 */
function osc_market_external_sources() {
  return true;
}


function osc_osclass_url($action = '') {
  $url = 'https://osclass-classifieds.com/api/latest_version.php';
  $params = array();

  if($action != '') {
    $params['action'] = $action;
  }

  if(defined('ALPHA_TEST') && ALPHA_TEST === true) {
    $params['alpha'] = 1;
  }

  if(defined('BETA_TEST') && BETA_TEST === true) {
    $params['beta'] = 1;
  }

  $p = http_build_query($params);

  if($p != '') {
    $url .= '?' . $p;
  }

  return $url;
}


function osc_share_translation_url($language, $type, $plugin = '', $theme = '') {
  $url = 'https://osclass-classifieds.com/api/share_translation.php';
  $params = array();

  if($language != '') {
    $params['language'] = $language;
  }

  if($type != '') {
    $params['type'] = $type;
  }

  if($plugin != '') {
    $params['plugin'] = $plugin;
  }

  if($theme != '') {
    $params['theme'] = $theme;
  }

  $p = http_build_query($params);

  if($p != '') {
    $url .= '?' . $p;
  }

  return $url;
}


function osc_language_url($code = '', $pattern = '', $sort = '', $type = 'osclass') {
  $api_url = 'https://osclass-classifieds.com/api/language.php';
  $osclass_version = trim(str_replace('.', '', osc_version()));

  if($code == '' && $pattern == '') {
    $api_url .= '?osclassVersion=' . $osclass_version . '&type=' . $type . '&action=list&sort=' . $sort;
  } else if($code <> '') {
    $api_url .= '?osclassVersion=' . $osclass_version . '&type=' . $type . '&action=find&code=' . $code;
  } else if($pattern <> '') {
    $api_url .= '?osclassVersion=' . $osclass_version . '&type=' . $type . '&action=search&pattern=' . $pattern . '&sort=' . $sort;
  }

  if(defined('ALPHA_TEST') && ALPHA_TEST === true) {
    $api_url .= '&alpha=1';
  }

  if(defined('BETA_TEST') && BETA_TEST === true) {
    $api_url .= '&beta=1';
  }

  return $api_url;
}


function osc_location_url($code = '', $pattern = '', $sort = '') {
  $api_url = 'https://osclass-classifieds.com/api/location.php';
  $api_version = '3';

  if($code == '' && $pattern == '') {
    $api_url .= '?action=list&apiVersion=' . $api_version . '&sort=' . $sort;
  } else if($code <> '') {
    $api_url .= '?action=find&apiVersion=' . $api_version . '&code=' . $code;
  } else if($pattern <> '') {
    $api_url .= '?action=search&apiVersion=' . $api_version . '&pattern=' . $pattern . '&sort=' . $sort;
  }

  if(defined('ALPHA_TEST') && ALPHA_TEST === true) {
    $api_url .= '&alpha=1';
  }

  if(defined('BETA_TEST') && BETA_TEST === true) {
    $api_url .= '&beta=1';
  }

  return $api_url;
}


function osc_market_url($type = '', $code = '') {
  $url = 'https://osclasspoint.com/oc-content/plugins/market/api/v3/';
  $key = osc_update_api_key();
  $domain = osc_get_parent_domain();

  if($type == 'download') {
    $url .= 'download.php';
  } else if($type == 'product') {
    $url .= 'product.php';
  } else if($type == 'products_version') {
    $url .= 'products_version.json';
    return $url;
  } else if($type == 'product_updates') {
    $url .= 'product_updates.json';
    return $url;
  } elseif($type == 'validate_api_key') {
    $url .= 'validate_api_key.php';
  } elseif($type == 'blog') {
    $url .= 'blog.php';
    return $url;
  } else {
    $url .= 'check_version.php';
  }

  $url .= '?apiKey=' . $key;
  $url .= '&domain=' . $domain;

  if(defined('ALPHA_TEST') && ALPHA_TEST === true) {
    $url .= '&alpha=1';
  }

  if(defined('BETA_TEST') && BETA_TEST === true) {
    $url .= '&beta=1';
  }

  if($type <> 'validate_api_key') {
    $url .= '&productKey=' . $code;
  }

  return $url;
}


/**
 * Gets mailserver's type
 *
 * @return string
 */
function osc_mailserver_type() {
  return (getPreference('mailserver_type'));
}

/**
 * Gets mailserver's host
 *
 * @return string
 */
function osc_mailserver_host() {
  return (getPreference('mailserver_host'));
}

/**
 * Gets mailserver's port
 *
 * @return int
 */
function osc_mailserver_port() {
  return (int)(getPreference('mailserver_port'));
}

/**
* Gets mail from
*
* @return string
*/
function osc_mailserver_mail_from() {
  return (getPreference('mailserver_mail_from'));
}

/**
* Gets name from
*
* @return string
*/
function osc_mailserver_name_from() {
  return (getPreference('mailserver_name_from'));
}

/**
 * Gets mailserver's username
 *
 * @return string
 */
function osc_mailserver_username() {
  return (getPreference('mailserver_username'));
}

/**
 * Gets mailserver's password
 *
 * @return string
 */
function osc_mailserver_password() {
  return (getPreference('mailserver_password'));
}

/**
 * Gets if use SSL on the mailserver
 *
 * @return boolean
 */
function osc_mailserver_ssl() {
  return (getPreference('mailserver_ssl'));
}

/**
 * Gets list of active plugins
 *
 * @return string
 */
function osc_active_plugins() {
  return (getPreference('active_plugins'));
}

/**
 * Gets list of installed plugins
 *
 * @return string
 */
function osc_installed_plugins() {
  return (getPreference('installed_plugins'));
}

/**
 * Gets default order field at search
 *
 * @return string
 */
function osc_default_order_field_at_search() {
  return (osc_apply_filter('osc_default_order_field_at_search', getPreference('defaultOrderField@search')));
}

/**
 * Gets default order type at search
 *
 * @return string
 */
function osc_default_order_type_at_search() {
  return (osc_apply_filter('osc_default_order_type_at_search', getPreference('defaultOrderType@search')));
}

/**
 * Gets default show as at search
 *
 * @return string
 */
function osc_default_show_as_at_search() {
  return (osc_apply_filter('osc_default_show_as_at_search', getPreference('defaultShowAs@search')));
}

/**
 * Gets max results per page at search
 *
 * @return int
 */
function osc_max_results_per_page_at_search() {
  return (int)(getPreference('maxResultsPerPage@search'));
}

/**
 * Gets default results per page at search
 *
 * @return int
 */
function osc_default_results_per_page_at_search() {
  return (int)(getPreference('defaultResultsPerPage@search'));
}

/**
 * Gets max latest items
 *
 * @return int
 */
function osc_max_latest_items() {
  return (int)(getPreference('maxLatestItems@home'));
}

/**
 * Gets if save searches is enabled or not
 *
 * @return boolean
 */
function osc_save_latest_searches() {
  return getBoolPreference('save_latest_searches');
}


/**
* @return string
*/
function osc_purge_latest_searches() {
  return getPreference('purge_latest_searches');
}

/**
 * Gets how many seconds between item post to not consider it SPAM
 *
 * @return int
 */
function osc_item_spam_delay() {
  return (int)60; // need to be changed
}

/**
 * Gets how many seconds between comment post to not consider it SPAM
 *
 * @return int
 */
function osc_comment_spam_delay() {
  return (int)60; // need to be changed
}

/**
 * Gets if parent categories are enabled or not
 *
 * @return boolean
 */
function osc_selectable_parent_categories() {
  return getPreference('selectable_parent_categories');
}

/**
 * Return max. number of latest items displayed at home index
 *
 * @return int
 */
function osc_max_latest_items_at_home() {
  return (int)(getPreference('maxLatestItems@home'));
}

/**
 * generic function to retrieve preferences
 *
 * @param string $key
 * @param string $section
 *
 * @return mixed
 */
function osc_get_preference($key, $section = 'osclass') {
  return getPreference($key, $section);
}

/**
 * generic function to retrieve preferences as bool
 *
 * @param string $key
 * @param string $section
 * @return string
 */
function osc_get_bool_preference($key, $section = 'osclass') {
  $var = getPreference($key, $section);
  if($var==1 || $var=="1" || $var=="true" || $var==true) {
  return true;
  }
  return false;
}


/**
* generic function to retrieve preferences
*
* @param string $section
* @return array
*/
function osc_get_preference_section($section = 'osclass') {
  $_P = Preference::newInstance();
  return $_P->getSection($section);
}

/**
 * generic function to insert/update preferences
 *
 * @param string $key
 * @param mixed $value
 * @param string $section
 * @param string $type
 * @return boolean
 */
function osc_set_preference($key, $value = '', $section = 'osclass', $type = 'STRING') {
  return Preference::newInstance()->replace($key, $value, $section, $type);
}

/**
 * generic function to delete preferences
 *
 * @param string $key
 * @param string $section
 * @return boolean
 */
function osc_delete_preference($key = '', $section = 'osclass') {
  return Preference::newInstance()->delete(array('s_name' => $key, 's_section' => $section));
}


/**
* Reload preferences
*
* @return bool <array>
*/
function osc_reset_preferences() {
  return Preference::newInstance()->toArray();
}

/**
 * Return if need mark images with text
 *
 * @return boolean
 */
function osc_is_watermark_text() {
   $text = getPreference('watermark_text');

  return $text != '';
}

/**
 * Return if need mark images with image
 *
 * @return boolean
 */
function osc_is_watermark_image() {
  $image = getPreference('watermark_image');

  return $image != '';
}

/**
 * Return watermark text color
 *
 * @return string
 */
function osc_watermark_text_color() {
  return getPreference('watermark_text_color');
}

/**
 * Return watermark text
 *
 * @return string
 */
function osc_watermark_text() {
  return getPreference('watermark_text');
}

/**
 * Return watermark place
 *
 * @return string
 */
function osc_watermark_place() {
  return getPreference('watermark_place');
}

/**
 * Return subdomain type
 *
 * @return string
 */
function osc_subdomain_type() {
  return getPreference('subdomain_type');
}

/**
 * Return subdomain host
 *
 * @return string
 */
function osc_subdomain_host() {
  return getPreference('subdomain_host');
}

/**
 * Return true if base domain is turned into landing page
 *
 * @return string
 */
function osc_subdomain_landing_enabled() {
  return getBoolPreference('subdomain_landing');
}

/**
 * Return true if user should be automatically redirected to subdomain
 * Only available for country subdomain type
 *
 * @return string
 */
function osc_subdomain_redirect_enabled() {
  return getBoolPreference('subdomain_redirect');
}

/**
 * Return subdomain restricted IDs/codes
 *
 * @return string
 */
function osc_subdomain_restricted_ids() {
  return getPreference('subdomain_restricted_ids');
}

/**
 * Return slug type for language subdomain
 *
 * @return string
 */
function osc_subdomain_language_slug_type() {
  return getPreference('subdomain_language_slug_type');
}

/**
 * Return version of recaptcha
 *
 * @return string
 */
function osc_recaptcha_version() {
  return getPreference('recaptcha_version');
}

//PRIVATE FUNCTION (if there was a class :P)
/**
 * Gets preference
 *
 * @param string $key
 * @return boolean
 */
function getBoolPreference($key) {
  $_P = Preference::newInstance();

  if($_P->get($key)) {
    return true;
  } else {
    return false;
  }
}

// PRIVATE FUNCTION FOR GETTING NO BOOLEAN INFORMATION (if there was a class :P)
/**
 * Gets preference
 *
 * @param string $key
 * @param string $section
 * @return string
 */
function getPreference($key, $section = 'osclass') {
  $_P = Preference::newInstance();
  return $_P->get($key, $section);
}

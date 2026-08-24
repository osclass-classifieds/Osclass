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


class CAdminStats extends AdminSecBaseModel {
  function __construct() {
    parent::__construct();
  }

  // Business layer
  function doModel() {
    parent::doModel();

    if(osc_is_moderator() && ($this->action == 'settings' || $this->action == 'settings_post')) {
      osc_add_flash_error_message(_m("You don't have enough permissions"), 'admin');
      $this->redirectTo(osc_admin_base_url());
    }

    $period = osc_stats_period_current('admin');
    $range = osc_stats_period_range($period);
    $from = $range['from'];
    $stats_page = $this->action;
    if($stats_page == '' || $stats_page == 'overview') {
      $stats_page = 'overview';
    }
    $this->_exportVariableToView('stats_period', $period);
    $this->_exportVariableToView('stats_from', $from);
    $this->_exportVariableToView('stats_range', $range);
    $this->_exportVariableToView('stats_page', $stats_page);

    switch($this->action) {
      case('reports'):
        $this->redirectTo(osc_admin_base_url(true) . '?page=stats&action=details');
        break;

      case('settings_post'):
        osc_csrf_check();
        $iUpdated = 0;
        $admin_period = osc_stats_period_normalize(Params::getParam('item_stats_admin_default_period'));
        if(!in_array($admin_period, osc_stats_period_keys('admin'), true)) {
          $admin_period = '30d';
        }
        $iUpdated += osc_set_preference('item_stats_admin_default_period', $admin_period);
        $measures = Params::getParam('item_stats_admin_default_measures');
        if(is_array($measures)) {
          $measures = implode(',', $measures);
        }
        $iUpdated += osc_set_preference('item_stats_admin_default_measures', osc_item_stats_sanitize_chart_measures($measures, osc_item_stats_default_enabled_csv()));
        $iUpdated += osc_set_preference('item_stats_user_chart_enabled', (Params::getParam('item_stats_user_chart_enabled') != '' ? '1' : '0'));
        $chart_audience = Params::getParam('item_stats_chart_audience');
        $chart_audience_options = osc_item_stats_chart_audience_options();
        if(!is_array($chart_audience_options) || !isset($chart_audience_options[$chart_audience])) {
          $chart_audience = 'all';
        }
        $iUpdated += osc_set_preference('item_stats_chart_audience', $chart_audience);
        $user_measures = Params::getParam('item_stats_user_chart_measures');
        if(is_array($user_measures)) {
          $user_measures = implode(',', $user_measures);
        }
        $iUpdated += osc_set_preference('item_stats_user_chart_measures', osc_item_stats_sanitize_chart_measures($user_measures, osc_item_stats_default_chart_csv()));
        $user_periods = Params::getParam('item_stats_user_chart_periods');
        if(is_array($user_periods)) {
          $user_periods = implode(',', $user_periods);
        }
        $user_periods = osc_item_stats_sanitize_periods($user_periods);
        $iUpdated += osc_set_preference('item_stats_user_chart_periods', $user_periods);
        $user_period = osc_stats_period_normalize(Params::getParam('item_stats_user_chart_period'));
        $user_period_keys = osc_item_stats_parse_period_csv($user_periods);
        if(!in_array($user_period, $user_period_keys, true)) {
          $user_period = (isset($user_period_keys[0]) ? $user_period_keys[0] : '30d');
        }
        $iUpdated += osc_set_preference('item_stats_user_chart_period', $user_period);
        $chart_type = Params::getParam('item_stats_user_chart_type');
        $chart_types = array('bar', 'line', 'area', 'stacked_bar', 'stacked_area');
        if(!in_array($chart_type, $chart_types, true)) {
          $chart_type = 'line';
        }
        $iUpdated += osc_set_preference('item_stats_user_chart_type', $chart_type);
        $iUpdated += osc_set_preference('item_stats_user_chart_hooks', osc_item_stats_sanitize_hooks(Params::getParam('item_stats_user_chart_hooks'), 'user_items_top'));
        $iUpdated += osc_set_preference('item_stats_item_chart_enabled', (Params::getParam('item_stats_item_chart_enabled') != '' ? '1' : '0'));
        $iUpdated += osc_set_preference('item_stats_item_chart_admin', (Params::getParam('item_stats_item_chart_admin') != '' ? '1' : '0'));
        $item_chart_measures = Params::getParam('item_stats_item_chart_measures');
        if(is_array($item_chart_measures)) {
          $item_chart_measures = implode(',', $item_chart_measures);
        }
        $iUpdated += osc_set_preference('item_stats_item_chart_measures', osc_item_stats_sanitize_chart_measures($item_chart_measures, osc_item_stats_default_chart_csv()));
        $item_periods = Params::getParam('item_stats_item_chart_periods');
        if(is_array($item_periods)) {
          $item_periods = implode(',', $item_periods);
        }
        $item_periods = osc_item_stats_sanitize_periods($item_periods);
        $iUpdated += osc_set_preference('item_stats_item_chart_periods', $item_periods);
        $item_period = osc_stats_period_normalize(Params::getParam('item_stats_item_chart_period'));
        $item_period_keys = osc_item_stats_parse_period_csv($item_periods);
        if(!in_array($item_period, $item_period_keys, true)) {
          $item_period = (isset($item_period_keys[0]) ? $item_period_keys[0] : '30d');
        }
        $iUpdated += osc_set_preference('item_stats_item_chart_period', $item_period);
        $item_chart_type = Params::getParam('item_stats_item_chart_type');
        if(!in_array($item_chart_type, $chart_types, true)) {
          $item_chart_type = 'line';
        }
        $iUpdated += osc_set_preference('item_stats_item_chart_type', $item_chart_type);
        $iUpdated += osc_set_preference('item_stats_item_chart_hooks', osc_item_stats_sanitize_hooks(Params::getParam('item_stats_item_chart_hooks'), 'item_top'));
        for($ci = 1; $ci <= 3; $ci++) {
          $clabel = trim(strip_tags((string)Params::getParam('item_stats_custom' . $ci . '_label')));
          if(osc_strlen($clabel) > 60) {
            $clabel = osc_substr($clabel, 0, 60);
          }
          $iUpdated += osc_set_preference('item_stats_custom' . $ci . '_label', $clabel);
        }
        $iUpdated += osc_set_preference('item_stats_auto_cleanup_enabled', (Params::getParam('item_stats_auto_cleanup_enabled') != '' ? '1' : '0'));
        $cleanup_months = (int)Params::getParam('item_stats_cleanup_months');
        if($cleanup_months < 1) {
          $cleanup_months = 24;
        }
        if($cleanup_months > 120) {
          $cleanup_months = 120;
        }
        $iUpdated += osc_set_preference('item_stats_cleanup_months', $cleanup_months, 'osclass', 'INTEGER');
        $engaged = (int)Params::getParam('item_stats_engaged_seconds');
        if($engaged < 5) {
          $engaged = 15;
        }
        if($engaged > 600) {
          $engaged = 600;
        }
        $iUpdated += osc_set_preference('item_stats_engaged_seconds', $engaged, 'osclass', 'INTEGER');
        $phone_sel = trim((string)Params::getParam('item_stats_phone_selectors', false, false));
        $phone_sel = strip_tags($phone_sel);
        if(osc_strlen($phone_sel) > 500) {
          $phone_sel = osc_substr($phone_sel, 0, 500);
        }
        $iUpdated += osc_set_preference('item_stats_phone_selectors', $phone_sel);
        $other_sel = trim((string)Params::getParam('item_stats_contactother_selectors', false, false));
        $other_sel = strip_tags($other_sel);
        if(osc_strlen($other_sel) > 500) {
          $other_sel = osc_substr($other_sel, 0, 500);
        }
        $iUpdated += osc_set_preference('item_stats_contactother_selectors', $other_sel);
        $itemStatsMethod = Params::getParam('item_stats_method');
        if($itemStatsMethod != 'PAGELOAD') {
          $itemStatsMethod = 'SESSION';
        }
        $iUpdated += osc_set_preference('item_stats_method', $itemStatsMethod);
        $iUpdated += osc_set_preference('item_stats_logged_only', (Params::getParam('item_stats_logged_only') != '' ? '1' : '0'));
        $itemStatsEnabled = osc_item_stats_sanitize_enabled(Params::getParam('item_stats_enabled'));
        $iUpdated += osc_set_preference('item_stats_enabled', $itemStatsEnabled);
        $itemStatsPreset = Params::getParam('item_stats_preset');
        if(!in_array($itemStatsPreset, array('essential', 'engagement', 'commerce', 'full', 'custom'), true)) {
          $itemStatsPreset = 'custom';
        }
        if($itemStatsPreset != 'custom') {
          $preset_keys = osc_item_stats_preset_keys($itemStatsPreset);
          $saved_keys = osc_item_stats_parse_csv($itemStatsEnabled);
          sort($preset_keys);
          sort($saved_keys);
          if($preset_keys !== $saved_keys) {
            $itemStatsPreset = 'custom';
          }
        }
        $iUpdated += osc_set_preference('item_stats_preset', $itemStatsPreset);
        osc_run_hook('admin_stats_settings_post');
        if($iUpdated > 0) {
          osc_add_flash_ok_message(_m('Statistics settings have been updated'), 'admin');
        }
        $this->redirectTo(osc_admin_base_url(true) . '?page=stats&action=settings');
        break;

      case('settings'):
        $this->doView('stats/settings.php');
        break;

      case('details_csv'):
        osc_csrf_check();
        $data = $this->detailsData($period);
        while(ob_get_level() > 0) {
          ob_end_clean();
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="listing-details-' . $period . '.csv"');
        $out = fopen('php://output', 'w');
        $header = array_merge(array('date'), $data['measures']);
        fwrite($out, implode(',', $header) . "\r\n");
        foreach($data['rows'] as $row) {
          $line = array($row['d_date']);
          foreach($data['measures'] as $measure) {
            $line[] = (int)(isset($row[$measure]) ? $row[$measure] : 0);
          }
          fwrite($out, implode(',', $line) . "\r\n");
        }
        fclose($out);
        exit;

      case('details'):
        $data = $this->detailsData($period);
        foreach($data as $k => $v) {
          $this->_exportVariableToView($k, $v);
        }
        $this->doView('stats/details.php');
        break;

      case('alerts'):
        list($alerts, $prev_alerts) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_alerts_count($range['prev_from'], 'day'));
        list($subscribers, $prev_subscribers) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_subscribers_count($range['prev_from'], 'day'));
        list($sent, $prev_sent) = osc_admin_stats_period_counts($period, Stats::newInstance()->alerts_sent_count($range['prev_from']));
        list($matched, $prev_matched) = osc_admin_stats_period_counts($period, Stats::newInstance()->alerts_matched_count($range['prev_from']));
        $sent_rows = array();
        foreach($sent as $d => $n) {
          $sent_rows[$d] = array($n, (int)(isset($matched[$d]) ? $matched[$d] : 0));
        }
        $this->_exportVariableToView('alerts', $alerts);
        $this->_exportVariableToView('subscribers', $subscribers);
        $this->_exportVariableToView('sent', $sent);
        $this->_exportVariableToView('matched', $matched);
        $this->_exportVariableToView('sent_rows', $sent_rows);
        $this->_exportVariableToView('prev_alerts', $prev_alerts);
        $this->_exportVariableToView('prev_subscribers', $prev_subscribers);
        $this->_exportVariableToView('prev_sent', $prev_sent);
        $this->_exportVariableToView('prev_matched', $prev_matched);
        $active_all = Stats::newInstance()->alerts_active_by_day($range['prev_from']);
        $active_by_day = array();
        foreach($active_all as $d => $n) {
          if($d >= $from) {
            $active_by_day[$d] = $n;
          }
        }
        list($expired, $prev_expired) = osc_admin_stats_period_counts($period, Stats::newInstance()->alerts_expired_count($range['prev_from']));
        $active_rows = array();
        foreach($active_by_day as $d => $n) {
          $active_rows[$d] = array($n, (int)(isset($expired[$d]) ? $expired[$d] : 0));
        }
        $this->_exportVariableToView('active_by_day', $active_by_day);
        $this->_exportVariableToView('expired', $expired);
        $this->_exportVariableToView('active_rows', $active_rows);
        $this->_exportVariableToView('prev_expired', $prev_expired);
        $this->_exportVariableToView('prev_active', (int)(isset($active_all[$range['prev_to']]) ? $active_all[$range['prev_to']] : 0));
        $this->_exportVariableToView('alerts_by_kind', osc_admin_stats_kind_map($from, Stats::newInstance()->new_alerts_by_user_kind($from), array('guest', 'personal', 'company')));
        $this->_exportVariableToView('alerts_by_frequency', Stats::newInstance()->alerts_by_frequency($from));
        $this->_exportVariableToView('alerts_by_status', Stats::newInstance()->alerts_by_status($from));
        $this->_exportVariableToView('alerts_by_country', Stats::newInstance()->alerts_by_country());
        $this->_exportVariableToView('latest_alerts', Stats::newInstance()->latest_alerts());
        $this->doView('stats/alerts.php');
        break;

      case('comments'):
        list($comments, $prev_comments) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_comments_count($range['prev_from'], 'day'));
        $comments_by_rating = Stats::newInstance()->comments_by_rating($from);
        $comments_by_reply = Stats::newInstance()->comments_by_reply($from);
        $comments_by_status = Stats::newInstance()->comments_by_status($from);
        $prev_rating = osc_admin_stats_open_prev(Stats::newInstance()->comments_by_rating($range['prev_from']), $comments_by_rating);
        $prev_reply = osc_admin_stats_open_prev(Stats::newInstance()->comments_by_reply($range['prev_from']), $comments_by_reply);
        $prev_status = osc_admin_stats_open_prev(Stats::newInstance()->comments_by_status($range['prev_from']), $comments_by_status);
        $this->_exportVariableToView('comments', $comments);
        $this->_exportVariableToView('comments_by_rating', $comments_by_rating);
        $this->_exportVariableToView('comments_by_reply', $comments_by_reply);
        $this->_exportVariableToView('comments_by_status', $comments_by_status);
        $this->_exportVariableToView('prev_comments', $prev_comments);
        $this->_exportVariableToView('prev_rated', (int)(isset($prev_rating['rated']) ? $prev_rating['rated'] : 0));
        $this->_exportVariableToView('prev_replies', (int)(isset($prev_reply['reply']) ? $prev_reply['reply'] : 0));
        $this->_exportVariableToView('prev_pending', (int)(isset($prev_status['pending']) ? $prev_status['pending'] : 0));
        $this->_exportVariableToView('latest_comments', Stats::newInstance()->latest_comments());
        $this->doView('stats/comments.php');
        break;

      case('users'):
        list($users, $prev_users) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_users_count($range['prev_from'], 'day'));
        list($listings, $prev_listings) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_items_count($range['prev_from'], 'day'));
        $item = Stats::newInstance()->items_by_user();
        $this->_exportVariableToView('users', $users);
        $this->_exportVariableToView('listings', $listings);
        $this->_exportVariableToView('prev_users', $prev_users);
        $this->_exportVariableToView('prev_listings', $prev_listings);
        $this->_exportVariableToView('users_by_country', Stats::newInstance()->users_by_country());
        $this->_exportVariableToView('users_by_region', Stats::newInstance()->users_by_region());
        $this->_exportVariableToView('users_by_company', Stats::newInstance()->users_by_company());
        $this->_exportVariableToView('users_by_status', Stats::newInstance()->users_by_status());
        $this->_exportVariableToView('items_by_user_type', Stats::newInstance()->items_by_user_type());
        $this->_exportVariableToView('item', (!isset($item[0]['avg']) || !is_numeric($item[0]['avg'])) ? 0 : $item[0]['avg']);
        $this->_exportVariableToView('latest_users', Stats::newInstance()->latest_users());
        $this->_exportVariableToView('comments_by_kind', osc_admin_stats_kind_map($from, Stats::newInstance()->new_comments_by_user_kind($from), array('user', 'guest')));
        $this->doView('stats/users.php');
        break;

      case('items'):
        list($items, $prev_items) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_items_count($range['prev_from'], 'day'));
        $views_pack = osc_item_stats_period_series($period, array('views'));
        $views = array();
        foreach($views_pack['rows'] as $row) {
          $views[$row['d_date']] = (int)$row['views'];
        }
        list($alerts, $prev_alerts) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_alerts_count($range['prev_from'], 'day'));
        list($subscribers, $prev_subscribers) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_subscribers_count($range['prev_from'], 'day'));
        $this->_exportVariableToView('items', $items);
        $this->_exportVariableToView('views', $views);
        $this->_exportVariableToView('alerts', $alerts);
        $this->_exportVariableToView('subscribers', $subscribers);
        $this->_exportVariableToView('prev_items', $prev_items);
        $this->_exportVariableToView('prev_views', (int)(isset($views_pack['prev']['views']) ? $views_pack['prev']['views'] : 0));
        $this->_exportVariableToView('prev_alerts', $prev_alerts);
        $this->_exportVariableToView('prev_subscribers', $prev_subscribers);
        $this->_exportVariableToView('items_by_country', Stats::newInstance()->items_by_country());
        $this->_exportVariableToView('items_by_region', Stats::newInstance()->items_by_region());
        $this->_exportVariableToView('items_by_category_levels', Stats::newInstance()->items_by_category_levels());
        $this->_exportVariableToView('items_by_price_type', Stats::newInstance()->items_by_price_type());
        $this->_exportVariableToView('items_by_phone', Stats::newInstance()->items_by_phone());
        $this->_exportVariableToView('latest_items', Stats::newInstance()->latest_items());
        $this->doView('stats/items.php');
        break;

      case('overview'):
      default:
        list($items, $prev_items) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_items_count($range['prev_from'], 'day'));
        list($users, $prev_users) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_users_count($range['prev_from'], 'day'));
        list($comments, $prev_comments) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_comments_count($range['prev_from'], 'day'));
        list($reports, $prev_reports) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_reports_count($range['prev_from'], 'day'));
        list($alerts, $prev_alerts) = osc_admin_stats_period_counts($period, Stats::newInstance()->new_alerts_count($range['prev_from'], 'day'));
        $view_keys = osc_item_stats_admin_plot_measures();
        $load_keys = $view_keys;
        if(!in_array('views', $load_keys, true)) {
          array_unshift($load_keys, 'views');
        }
        if(osc_item_stats_enabled('premium_views') && !in_array('premium_views', $load_keys, true)) {
          $load_keys[] = 'premium_views';
        }
        $views_pack = osc_item_stats_period_series($period, $load_keys);
        $this->_exportVariableToView('items', $items);
        $this->_exportVariableToView('users', $users);
        $this->_exportVariableToView('comments', $comments);
        $this->_exportVariableToView('reports', $reports);
        $this->_exportVariableToView('alerts', $alerts);
        $this->_exportVariableToView('view_rows', $views_pack['rows']);
        $this->_exportVariableToView('view_keys', $view_keys);
        $this->_exportVariableToView('view_sums', $views_pack['sums']);
        $this->_exportVariableToView('prev_items', $prev_items);
        $this->_exportVariableToView('prev_users', $prev_users);
        $this->_exportVariableToView('prev_comments', $prev_comments);
        $this->_exportVariableToView('prev_reports', $prev_reports);
        $this->_exportVariableToView('prev_alerts', $prev_alerts);
        $this->_exportVariableToView('prev_views', (int)(isset($views_pack['prev']['views']) ? $views_pack['prev']['views'] : 0));
        $this->_exportVariableToView('prev_premium', (int)(isset($views_pack['prev']['premium_views']) ? $views_pack['prev']['premium_views'] : 0));
        $this->_exportVariableToView('items_by_root_category', Stats::newInstance()->items_by_root_category());
        $this->_exportVariableToView('users_by_status', Stats::newInstance()->users_by_status());
        $this->_exportVariableToView('comments_by_status', Stats::newInstance()->comments_by_status());
        $this->doView('stats/overview.php');
        break;
    }
  }

  // Listing details series and period totals
  protected function detailsData($period) {
    $measures = osc_item_stats_parse_csv(Params::getParam('measures'));
    if(empty($measures)) {
      $measures = osc_item_stats_parse_csv(osc_item_stats_admin_default_measures());
    }
    $measures = array_values(array_intersect($measures, osc_item_stats_chart_allowed_keys()));
    if(empty($measures)) {
      $measures = array('views');
    }
    $item_id = (int)Params::getParam('item_id');
    $user_id = (int)Params::getParam('user_id');
    $category_id = (int)Params::getParam('category_id');
    $pack = osc_item_stats_period_series($period, $measures, ($item_id > 0 ? $item_id : null), ($category_id > 0 ? $category_id : null), ($user_id > 0 ? $user_id : null));
    return osc_apply_filter('admin_stats_details_data', array(
      'measures' => $measures,
      'rows' => $pack['rows'],
      'sums' => $pack['sums'],
      'prev_sum' => $pack['prev'],
      'item_id' => $item_id,
      'user_id' => $user_id,
      'category_id' => $category_id
    ));
  }

  function doView($file) {
    $page = View::newInstance()->_get('stats_page');
    if($page == '') {
      $page = 'overview';
    }
    osc_run_hook('admin_stats_load', $page);
    osc_run_hook('before_admin_html');
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook('after_admin_html');
  }
}

/* file end: ./oc-admin/stats.php */

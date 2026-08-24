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


// FUNCTION THAT DRIVES SENDING OF ALERTS
// Added into cron in cron.php
function osc_runAlert($type = '', $last_exec = null){
  $type = strtoupper(trim((string)$type));
  $hook_name = 'hook_alert_email_' . strtolower($type);
  $items_limit_per_alert = ((defined('MAX_ITEMS_PER_ALERT') && MAX_ITEMS_PER_ALERT > 0) ? (int)MAX_ITEMS_PER_ALERT : 200);

  if(!osc_alerts_enabled()) {
    return false;
  }

  if(!in_array($type, array('HOURLY', 'DAILY', 'WEEKLY', 'INSTANT'))) {
    return false;
  }

  // Get last execution time of cron - this will be filter on pub date on items
  if($last_exec === null) {
    $cron = Cron::newInstance()->getCronByType($type);
    $last_exec = (is_array($cron) && !empty($cron['d_last_exec']) ? $cron['d_last_exec'] : '0000-00-00 00:00:00');
  }

  $last_exec_comment = sprintf(__('Type: %s, Exec: %s'), ucwords(strtolower($type)), $last_exec);


  // Get active subscribed alerts by type - daily, hourly, ...
  $alerts = Alerts::newInstance()->findByType($type, true, false);

  if(is_array($alerts) && count($alerts) > 0) {
    foreach($alerts as $alert) {
      $alert_id = $alert['pk_i_id'];
      $user_invalid = false;

      // Skip expired alerts
      if(isset($alert['dt_expire_date']) && trim((string)$alert['dt_expire_date']) != '' && strtotime($alert['dt_expire_date']) <= time()) {
        continue;
      }

      // First identify user and prepare user object for hook
      $user_id = (int)($alert['fk_i_user_id'] > 0 ? $alert['fk_i_user_id'] : 0);
      $user_email = $alert['s_email'];

      if($user_id > 0) {
        $user = osc_get_user_row($user_id);
      } else {
        $user = osc_get_user_row_by_email($user_email);
      }

      if($user === false || !isset($user['pk_i_id'])) {
        $user_invalid = ($user_id > 0 ? true : $user_invalid);    // User was removed

        $user = array(
          'pk_i_id' => 0,
          's_name' => $user_email,
          's_email' => $user_email
        );

      } else {
        $user_invalid = (($user['b_active'] == 0 || $user['b_enabled'] == 0) ? true : $user_invalid);    // User is not in well state
        $user_email = ($user['s_email'] ?? $user_email);  // prefer email on user record
      }


      // User that subscribed to alert does not exists, is removed, blocked or inactive
      if($user_invalid) {
        Alert::newInstance()->deactivate($alert_id);
        Log::newInstance()->insertLog('alerts', 'userInvalid', $user_id, sprintf(__('Alert ID %d (%s) for user %s (ID %d) triggered, but was not sent because the user\'s user state is invalid (removed, inactive or blocked). This alert has been deactivated.'), $alert_id, strtolower($type), $user_email, $user_id), 'cron', 0, $last_exec_comment);
        continue;
      }

      // Check if user is not banned
      if(osc_is_email_banned($user_email) !== false) {
        Alert::newInstance()->deactivate($alert_id);
        Log::newInstance()->insertLog('alerts', 'userBanned', $user_id, sprintf(__('Alert ID %d (%s) for user %s (ID %d) triggered, but was not sent because the user\'s email is banned. This alert has been deactivated.'), $alert_id, strtolower($type), $user_email, $user_id), 'cron', 0, $last_exec_comment);
        continue;
      }


      // Collect search conditions (for items) and perform search
      $conditions_param = ($alert['s_param'] ?? null);
      $conditions_json = $alert['s_search'];
      $conditions = json_decode($conditions_json, true) ?: [];

      $mSearch = Search::newInstance();
      $mSearch->setJsonAlert($conditions, $user_email, $user_id);
      $mSearch->addConditions(sprintf('%st_item.dt_pub_date > "%s"', DB_TABLE_PREFIX, $last_exec));
      $mSearch->limit(0, $items_limit_per_alert);   // Avoid excessive email with hundreds of listings

      $items = $mSearch->doSearch();
      $items_count = $mSearch->count();
      $items_limitted = ($items_count >= $items_limit_per_alert ? true : false);

      // Check if any listing match search criteria
      if(!is_array($items) || empty($items)) {
        Log::newInstance()->insertLog('alerts', 'notifyUserEmpty', $user_id, sprintf(__('Alert ID %d (%s) for user %s (ID %d) triggered, but was not sent because 0 listings match search criteria.'), $alert_id, strtolower($type), $user_email, $user_id), 'cron', 0, $last_exec_comment, $conditions_param);
        continue;
      }

      Log::newInstance()->insertLog('alerts', 'notifyUser', $user_id, sprintf(__('Alert ID %d (%s) for user %s (ID %d) triggered - %d listings matched criteria (limit: %d).'), $alert_id, strtolower($type), $user_email, $user_id, $items_count, $items_limit_per_alert), 'cron', 0, $last_exec_comment, $conditions_param);

      Alerts::newInstance()->increaseTrigger($alert['pk_i_id']);
      AlertsStats::newInstance()->increase(date('Y-m-d'));

      $items_table = '<table id="alert-items" cellspacing="0" cellpadding="8">';

      foreach($items as $item) {
        $resource = ItemResource::newInstance()->getResource($item['pk_i_id']);

        if(isset($resource['pk_i_id']) && $resource['pk_i_id'] > 0) {
          $path = osc_apply_filter('resource_path', osc_base_url().$resource['s_path']);
          $img_link = osc_apply_filter('resource_thumbnail_url', $path . $resource['pk_i_id'] . '_thumbnail.' . $resource['s_extension']);
        } else {
          $img_link = osc_base_url() . 'oc-includes/osclass/gui/images/no_photo.gif';
        }

        $items_table .= '<tr>';
        $items_table .= '<td width="80" style="border-top:1px solid #ddd"><img src="' . $img_link . '" width="80"/></td>';
        $items_table .= '<td align="left" style="border-top:1px solid #ddd"><a href="' . osc_item_url_ns($item['pk_i_id']) . '">' . $item['s_title'] . '</a><br/><span>' . osc_highlight($item['s_description'], 115) . '</span></td>';
        $items_table .= '</tr>';
      }

      $items_table .= '</table>';


      // Trigger email
      // hook_alert_email_hourly, hook_alert_email_daily, hook_alert_email_weekly, hook_alert_email_instant
      osc_run_hook($hook_name, $user, $items_table, $alert, $items, $items_count);
      $sent_ids = array();
      foreach($items as $item) {
        if(isset($item['pk_i_id'])) {
          $sent_ids[] = (int)$item['pk_i_id'];
        }
      }
      osc_increase_item_stats('alerts_sent', $sent_ids);
    }
  }
}

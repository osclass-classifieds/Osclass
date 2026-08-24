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
 * @param null $type
 * @param null $last_exec
 */
function osc_runAlert($type = null, $last_exec = null){
  $mUser = User::newInstance();
  if(!in_array($type, array('HOURLY', 'DAILY', 'WEEKLY', 'INSTANT'))) {
    return;
  }

  if($last_exec == null) {
    $cron = Cron::newInstance()->getCronByType($type);
    $last_exec = '0000-00-00 00:00:00';
    
    if(is_array($cron)) {
      $last_exec = $cron['d_last_exec'];
    }
  }

  $internal_name = 'alert_email_hourly';
  
  switch($type) {
    case 'HOURLY':
      $internal_name = 'alert_email_hourly';
      break;
    case 'DAILY':
      $internal_name = 'alert_email_daily';
      break;
    case 'WEEKLY':
      $internal_name = 'alert_email_weekly';
      break;
    case 'INSTANT':
      $internal_name = 'alert_email_instant';
      break;
  }

  $active = true;
  $searches = Alerts::newInstance()->findByTypeGroup($type, $active);

  if(is_array($searches) && count($searches) > 0) {
    foreach($searches as $s_search) {
      // Get if there're new ads on this search
      $json = $s_search['s_search'];
      $array_conditions = (array)@json_decode($json, true);

      $new_search = Search::newInstance();
      $new_search->setJsonAlert($array_conditions, $s_search['s_email'], $s_search['fk_i_user_id']);
      $new_search->addConditions(sprintf(" %st_item.dt_pub_date > '%s' ", DB_TABLE_PREFIX, $last_exec));

      $items = $new_search->doSearch();
      $totalItems = $new_search->count();

      if($totalItems > 0 && is_array($items) && count($items) > 0) {
        Log::newInstance()->insertLog(
          'alerts',
          'notifyUser',
          $s_search['fk_i_user_id'],
          sprintf(__('%d listings matched alert ID %d for user %s (ID %d)'), $totalItems, $s_search['pk_i_id'], $s_search['s_email'], $s_search['fk_i_user_id']),
          'cron',
          0
        );
    
        Alerts::newInstance()->increaseTrigger($s_search['pk_i_id']);     // it's alert ID

        // If we have new items from last check
        // Catch the user subscribed to this search
        $alerts = Alerts::newInstance()->findUsersBySearchAndType($s_search['s_search'], $type, $active);

        if(is_array($alerts) && count($alerts) > 0) {
          $ads = '<table id="alert-items" cellspacing="0" cellpadding="8">';
          
          foreach($items as $item) {
            $ads .= '<tr>';
            $resource = ItemResource::newInstance()->getResource($item['pk_i_id']);

            if(isset($resource['pk_i_id']) && $resource['pk_i_id'] > 0) {
              $path = osc_apply_filter('resource_path', osc_base_url().$resource['s_path']);
              $img_link = osc_apply_filter('resource_thumbnail_url', $path.$resource['pk_i_id']."_thumbnail.".$resource['s_extension']);
            } else {
              $img_link = osc_base_url() . 'oc-includes/osclass/gui/images/no_photo.gif';
            }

            $ads .= '<td width="80" style="border-top:1px solid #ddd"><img src="' . $img_link . '" width="80"/></td>';
            $ads .= '<td align="left" style="border-top:1px solid #ddd"><a href="' . osc_item_url_ns($item['pk_i_id']) . '">' . $item['s_title'] . '</a><br/><span>' . osc_highlight($item['s_description'], 115) . '</span></td>';

            $ads .= '</tr>';
          }

          $ads .= '</table>';

          foreach($alerts as $alert) {
            if(!isset($alert['pk_i_id']) || !isset($alert['s_email'])) {
              continue;
            }
            
            $user = array();
            
            // Find user record
            if($alert['fk_i_user_id'] > 0) {
              $user = $mUser->findByPrimaryKey($alert['fk_i_user_id']);
            } else {
              $user = $mUser->findByEmail($alert['s_email']);
            }
            
            // User not found
            if($user === false || !isset($user['pk_i_id'])) {
              $user = array(
                'pk_i_id'  => 0,
                's_name'  => $alert['s_email'],
                's_email' => $alert['s_email']
              );
            }
            
            // Only trigger alert to non-banned users
            if(osc_is_email_banned($user['s_email']) !== false) {
              osc_run_hook('hook_' . $internal_name, $user, $ads, $alert, $items, $totalItems);
              AlertsStats::newInstance()->increase(date('Y-m-d'));
            }
          }
        }
      }
    }
  }
}
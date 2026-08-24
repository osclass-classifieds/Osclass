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
 * Helper Users
 * @package Osclass
 * @subpackage Helpers
 * @author Osclass
 */

/**
 * Gets a specific field from current user
 *
 * @param string $field
 * @param string $locale
 * @return mixed
 */
function osc_user_field($field, $locale = "") {
  if(View::newInstance()->_exists('users')) {
    $user = View::newInstance()->_current('users');
  } else {
    $user = View::newInstance()->_get('user');
  }
  return osc_field($user, $field, $locale);
}

/**
 * Gets user array from view
 *
 * @return array
 */
function osc_user() {
  if(View::newInstance()->_exists('users')) {
    $user = View::newInstance()->_current('users');
  } else {
    $user = View::newInstance()->_get('user');
  }

  return($user);
}


/**
 * Gets user array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_user_row($id, $cache = true) {
  if($id <= 0) {
    return false;
  }
  
  if($cache === true && View::newInstance()->_exists('user_' . $id)) {
    return View::newInstance()->_get('user_' . $id);
  }
  
  $user = User::newInstance()->findByPrimaryKey((int)$id);
  View::newInstance()->_exportVariableToView('user_' . $id, $user);
  
  return $user;
}


/**
 * Gets user array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_user_row_by_username($username) {
  $username = trim((string)$username);
  
  if($username == '') {
    return false;
  }

  if(View::newInstance()->_exists('user_' . $username)) {
    return View::newInstance()->_get('user_' . $username);
  }
  
  $user = User::newInstance()->findByUsername($username);
  View::newInstance()->_exportVariableToView('user_' . $username, $user);
  
  return $user;
}


/**
 * Gets true if user is logged in web
 *
 * @return boolean
 */
function osc_is_web_user_logged_in() {
  if(View::newInstance()->_exists('_loggedUser')) {
    $user = View::newInstance()->_get('_loggedUser');
    if(isset($user['b_enabled']) && $user['b_enabled']==1) {
      return true;
    } else {
      return false;
    }
  }

  if(Session::newInstance()->_get("userId") > 0) {
    $user = osc_get_user_row(Session::newInstance()->_get("userId"));
    
    View::newInstance()->_exportVariableToView('_loggedUser', $user);
    
    if(isset($user['b_enabled']) && $user['b_enabled']==1) {
      return true;
    } else {
      return false;
    }
  }

  //can already be a logged user or not, we'll take a look into the cookie
  if(Cookie::newInstance()->get_value('oc_userId') != '' && Cookie::newInstance()->get_value('oc_userSecret') != '') {
    $user = User::newInstance()->findByIdSecret(Cookie::newInstance()->get_value('oc_userId'), Cookie::newInstance()->get_value('oc_userSecret'));
    View::newInstance()->_exportVariableToView('_loggedUser', $user);
    if(isset($user['b_enabled']) && $user['b_enabled']==1) {
      Session::newInstance()->_set('userId', $user['pk_i_id']);
      Session::newInstance()->_set('userName', $user['s_name']);
      Session::newInstance()->_set('userEmail', $user['s_email']);
      $phone = osc_esc_html($user['s_phone_mobile'] ? $user['s_phone_mobile'] : $user['s_phone_land']);
      Session::newInstance()->_set('userPhone', $phone);

      return true;
    } else {
      return false;
    }
  }

  return false;
}

/**
 * Gets logged user array/record
 *
 * @return array
 */
function osc_logged_user() {
  if(osc_is_web_user_logged_in()) {
    if(View::newInstance()->_exists('_loggedUser')) {
      return View::newInstance()->_get('_loggedUser');
    }
  }
  
  return false;
}

/**
 * Gets logged user id
 *
 * @return int
 */
function osc_logged_user_id() {
  return (int) Session::newInstance()->_get("userId");
}

/**
 * Gets logged user mail
 *
 * @return string
 */
function osc_logged_user_email() {
  return (string) Session::newInstance()->_get('userEmail');
}

/**
 * Gets logged user name
 *
 * @return string
 */
function osc_logged_user_name() {
  return (string) Session::newInstance()->_get('userName');
}

/**
 * Gets logged user phone
 *
 * @return string
 */
function osc_logged_user_phone() {
  return (string) Session::newInstance()->_get('userPhone');
}

/**
 * Check if user public profile is enabled
 *
 * @return boolean
 */
function osc_user_public_profile_is_enabled($user) {
  if($user === false || !isset($user['pk_i_id']) || $user['b_enabled'] = 0 || $user['b_active'] = 0) {
    return osc_apply_filter('user_public_profile_is_enabled', false, $user);
  
  } else if(osc_user_public_profile_min_items() > 0 && $user['i_items'] < osc_user_public_profile_min_items()) {
    return osc_apply_filter('user_public_profile_is_enabled', false, $user);
    
  } else if(osc_user_public_profile_enabled() == 'COMPANY' && $user['b_company'] == 0) {
    return osc_apply_filter('user_public_profile_is_enabled', false, $user);
  }
  
  return osc_apply_filter('user_public_profile_is_enabled', true, $user);
}


/**
 * Gets true if admin user is logged in
 *
 * @return boolean
 */
function osc_is_admin_user_logged_in() {
  if(Session::newInstance()->_get("adminId") > 0 && class_exists('Admin')) {
    $admin = Admin::newInstance()->findByPrimaryKey(Session::newInstance()->_get("adminId"));
    if(isset($admin['pk_i_id'])) {
      return true;
    } else {
      return false;
    }
  }

  //can already be a logged user or not, we'll take a look into the cookie
  if(class_exists('Cookie') && class_exists('Admin')) { // update 420 - installation fix
    if(Cookie::newInstance()->get_value('oc_adminId') > 0 && Cookie::newInstance()->get_value('oc_adminSecret') != '') {
      $admin = Admin::newInstance()->findByIdSecret(Cookie::newInstance()->get_value('oc_adminId'), Cookie::newInstance()->get_value('oc_adminSecret'));
      if(isset($admin['pk_i_id'])) {
        Session::newInstance()->_set('adminId', $admin['pk_i_id']);
        Session::newInstance()->_set('adminUserName', $admin['s_username']);
        Session::newInstance()->_set('adminName', $admin['s_name']);
        Session::newInstance()->_set('adminEmail', $admin['s_email']);
        Session::newInstance()->_set('adminLocale', Cookie::newInstance()->get_value('oc_adminLocale'));

        return true;
      } else {
        return false;
      }
    }
  }

  return false;
}

/**
 * Gets logged admin id
 *
 * @return int
 */
function osc_logged_admin_id() {
  return (int) Session::newInstance()->_get("adminId");
}

/**
 * Gets logged admin username
 *
 * @return string
 */
function osc_logged_admin_username() {
  return (string) Session::newInstance()->_get('adminUserName');
}

/**
 * Gets logged admin name
 * @return string
 */
function osc_logged_admin_name() {
  return (string) Session::newInstance()->_get('adminName');
}

/**
 * Gets logged admin email
 *
 * @return string
 */
function osc_logged_admin_email() {
  return (string) Session::newInstance()->_get('adminEmail');
}

/**
 * Gets name of current user
 *
 * @return string
 */
function osc_user_name() {
  return (string) osc_user_field("s_name");
}

/**
 * Gets email of current user
 *
 * @return string
 */
function osc_user_email() {
  return (string) osc_user_field("s_email");
}

/**
 * Gets username of current user
 *
 * @return string
 */
function osc_user_username() {
  return (string) osc_user_field("s_username");
}

/**
 * Gets registration date of current user
 *
 * @return string
 */
function osc_user_regdate() {
  return (string) osc_user_field("dt_reg_date");
}

/**
 * Gets id of current user
 *
 * @return int
 */
function osc_user_id() {
  return (int) osc_user_field("pk_i_id");
}


/**
 * Gets profile picture of current user
 *
 * @return string
 */
function osc_user_profile_img($id = null) {
  if($id === 0) {
    $img = 'default-user-image.png';
  } else if($id !== null) {
    $user = osc_get_user_row($id);
    $img = (isset($user['s_profile_img']) ? $user['s_profile_img'] : NULL);
  } else {
    $img = osc_user_field("s_profile_img");
  }

  if($img === NULL || trim($img) == '') {
    $img = 'default-user-image.png';
  }

  return (string) $img;
}


/**
 * Gets profile picture url of current user
 *
 * @return string
 */
function osc_user_profile_img_url($id = null) {
  if(file_exists(osc_user_profile_img_path($id))) {
    return (string) osc_apply_filter('user_profile_img_url', osc_base_url(). OC_CONTENT_FOLDER . '/uploads/user-images/' . osc_user_profile_img($id));
  }
  
  return (string) osc_apply_filter('user_profile_img_url', osc_base_url(). OC_CONTENT_FOLDER . '/uploads/user-images/default-user-image.png');
}


/**
 * Gets profile picture path of current user
 *
 * @return string
 */
function osc_user_profile_img_path($id = null) {
  return (string) osc_apply_filter('user_profile_img_path', osc_base_path(). OC_CONTENT_FOLDER . '/uploads/user-images/' . osc_user_profile_img($id));
}


/**
 * Gets profile picture upload button
 *
 * @return string
 */
function osc_user_profile_img_button($id = null) {
  $img = osc_user_profile_img($id);

  return '--button will be there--';
}

/**
 * Gets last access date
 *
 * @return string
 */
function osc_user_access_date() {
  return (int) osc_user_field("dt_access_date");
}

/**
 * Returns true if user has been active in last XY seconds
 *
 * @return boolean
 */
function osc_user_is_online($user_id = '') {
  if($user_id <= 0) {
    $date = osc_user_field("dt_access_date");
  } else {
    $user = osc_get_user_row($user_id);

    if(isset($user['pk_i_id']) && $user['pk_i_id'] > 0) {
      $date = $user["dt_access_date"];
    } else {
      return false;
    }
  }
  
  $limit_seconds = 300; // 5 minutes
  $last_access_date = date('Y-m-d H:i:s', strtotime($date));
  $threshold = date('Y-m-d H:i:s', strtotime(' -' . $limit_seconds . ' seconds', time()));

  if(isset($last_access_date) && $last_access_date <> '' && $last_access_date <> null && $last_access_date >= $threshold) {
    return true;
  }

  return false;
}


/**
 * Gets default locale of user
 *
 * @return string
 */
function osc_user_def_locale_code($user_id = '', $email = '') {
  if($user_id <= 0 && $email == '') {
    return (string) osc_user_field("fk_c_locale_code");
  } else {
    if($user_id > 0) {
      $user = osc_get_user_row($user_id);

      if(isset($user['pk_i_id']) && $user['pk_i_id'] > 0) {
        return (string) $user["fk_c_locale_code"];
      }
    }
    
    if($email != '') { 
      $user = User::newInstance()->findByEmail($email);

      if(isset($user['pk_i_id']) && $user['pk_i_id'] > 0) {
        return (string) $user["fk_c_locale_code"];
      }
    }
  }
  
  return '';
}


/**
 * Gets last access ip
 *
 * @return string
 */
function osc_user_access_ip() {
  return (int) osc_user_field("s_access_ip");
}

/**
 * Gets website of current user
 *
 * @return string
 */
function osc_user_website() {
  return (string) osc_esc_html(osc_user_field("s_website"));
}

/**
 * Gets description/information of current user
 *
 * @return string
 */
function osc_user_info($locale = "") {
  $userId = osc_user_id();
  if($locale == "") {
    $locale = osc_current_user_locale();
  }
  $info = osc_user_field("s_info", $locale);
  $info = osc_apply_filter('user_info', $info, $userId, $locale);
  if($info == '') {
    $info = osc_user_field("s_info", osc_language());
    $info = osc_apply_filter('user_info', $info, $userId, osc_language());
    if($info=='') {
      $aLocales = osc_get_locales();
      foreach($aLocales as $locale) {
        $info = osc_user_field("s_info", $locale['pk_c_code']);
        $info = osc_apply_filter('user_info', $info, $userId, $locale['pk_c_code']);
        if($info!='') {
          break;
        }
      }
    }
  }
  return (string) $info;
}

/**
 * Gets phone of current user
 *
 * @return string
 */
function osc_user_phone_land() {
  return (string) osc_esc_html(osc_user_field("s_phone_land"));
}

/**
 * Gets cell phone of current user
 *
 * @return string
 */
function osc_user_phone_mobile() {
  return (string) osc_esc_html(osc_user_field("s_phone_mobile"));
}

/**
 * Gets phone_mobile if exist, else if exist return phone_land,
 * else return string blank
 * @return string
 */
function osc_user_phone() {
  if(osc_user_field("s_phone_mobile") != "") {
    return osc_esc_html(osc_user_field("s_phone_mobile"));
  } else if(osc_user_field("s_phone_land") != "") {
    return osc_esc_html(osc_user_field("s_phone_land"));
  }
  
  return "";
}

/**
 * Gets country of current user
 *
 * @return string
 */
function osc_user_country() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_user_field("s_country_native") <> '' ? osc_user_field("s_country_native") : osc_user_field("s_country"));
  }
  return (string) osc_user_field("s_country");
}

/**
 * Gets region of current user
 *
 * @return string
 */
function osc_user_region() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_user_field("s_region_native") <> '' ? osc_user_field("s_region_native") : osc_user_field("s_region"));
  }
  return (string) osc_user_field("s_region");
}

/**
 * Gets region id of current user
 *
 * @return string
 */
function osc_user_region_id() {
  return (string) osc_user_field("fk_i_region_id");
}

/**
 * Gets city of current user
 *
 * @return string
 */
function osc_user_city() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_user_field("s_city_native") <> '' ? osc_user_field("s_city_native") : osc_user_field("s_city"));
  }
  return (string) osc_user_field("s_city");
}

/**
 * Gets city id of current user
 *
 * @return string
 */
function osc_user_city_id() {
  return (string) osc_user_field("fk_i_city_id");
}

/**
 * Gets city area of current user
 *
 * @return string
 */
function osc_user_city_area() {
  return (string) osc_user_field("s_city_area");
}

/**
 * Gets city area id of current user
 *
 * @return string
 */
function osc_user_city_area_id() {
  return (string) osc_user_field("fk_i_city_area_id");
}

/**
 * Gets address of current user
 *
 * @return address
 */
function osc_user_address() {
  return (string) osc_user_field("s_address");
}

/**
 * Gets postal zip of current user
 *
 * @return string
 */
function osc_user_zip() {
  return (string) osc_user_field("s_zip");
}

/**
 * Gets latitude of current user
 *
 * @return float
 */
function osc_user_latitude() {
  return (float) osc_user_field("d_coord_lat");
}

/**
 * Gets longitude of current user
 *
 * @return float
 */
function osc_user_longitude() {
  return (float) osc_user_field("d_coord_long");
}

/**
 * Gets type (company/user) of current user
 *
 * @return float
 */
function osc_user_is_company() {
  return (bool) osc_user_field("b_company");
}

/**
 * Gets number of items validated of current user
 *
 * @return int
 */
function osc_user_items_validated() {
  return (int) osc_user_field("i_items");
}

/**
 * Gets number of comments validated of current user
 *
 * @return int
 */
function osc_user_comments_validated() {
  return osc_user_field("i_comments");
}

/**
 * Gets number of users
 *
 * @return int
 */
function osc_total_users($type = '', $condition = '') {
  switch($type) {
    case 'active':
      return User::newInstance()->countUsers('b_active = 1');
      break;
    case 'enabled':
      return User::newInstance()->countUsers('b_enabled = 1');
      break;
    case 'online':
      $limit_seconds = 300; // 5 minutes
      $threshold = date('Y-m-d H:i:s', strtotime(' -' . $limit_seconds . ' seconds', time()));
  
      return User::newInstance()->countUsers(sprintf('b_enabled = 1 AND b_active = 1 AND dt_access_date >= "%s"', $threshold));
      break;
    case 'custom':
      return User::newInstance()->countUsers($condition);
      break;
    default:
      return User::newInstance()->countUsers();
      break;
  }
}


/////////////
// ALERTS  //
/////////////


// Get list of alert types
function osc_alert_types($with_custom = false) {
  $list = array('INSTANT', 'HOURLY', 'DAILY', 'WEEKLY');
  
  if($with_custom) {
    $list[] = 'CUSTOM';
  }
  
  return osc_apply_filter('alert_types', $list, $with_custom);
}


// Create box to change frequency
function osc_alert_change_frequency($alert) {
  if(!isset($alert['pk_i_id'])) {
    return false;
  }
  
  $active_type = $alert['e_type'];
  $types = osc_alert_types();
  
  $html = '<div class="alert-frequency">';
  
  foreach($types as $to_type) { 
    $html .= osc_alert_change_frequency_url($alert, $to_type, '');
  }
  
  $html .= '</div>';
  
  return osc_apply_filter('alert_change_frequency', $html, $alert);
}


// Change alert frequency to specified value
function osc_alert_change_frequency_url($alert, $to_type, $label = '') {
  if(!isset($alert['pk_i_id'])) {
    return false;
  }
  
  $id = $alert['pk_i_id'];
  $secret = $alert['s_secret'];
  
  $label = ($label == '' ? osc_alert_type_label($to_type) : $label);
  $link = osc_base_url(true) . '?page=user&action=alert_change_freq&id='. $id . '&secret=' . $secret . '&type=' . $to_type;
  $active = ($to_type == $alert['e_type'] ? true : false);
  
  $url = '<a href="' . $link . '" class="' . ($active ? 'active' : '') . '">' . $label . '</a>';
  
  return osc_apply_filter('alert_change_frequency_url', $url, $alert, $to_type, $label);
}


// Generate search url of specific alert and take user to search page
function osc_search_alert_url($id = null, $secret = null) {
  if($id === null || $secret === null) {
    $id = osc_alert_id();
    $secret = osc_alert_secret();
  }
  
  if($id > 0 && $secret != '') {
    $url = osc_search_url(array('page' => 'search', 'iAlertId' => $id, 'sAlertSecret' => $secret));
    return osc_apply_filter('search_alert_url', $url, $id, $secret, osc_alert());
  }
  
  return false;
}

/**
 * Gets a specific field from current alert
 *
 * @param array $field
 * @return mixed
 */
function osc_alert_field($field) {
  return osc_field(View::newInstance()->_current('alerts'), $field, '');
}

/**
 * Gets next alert if there is, else return null
 *
 * @return array
 */
function osc_has_alerts() {
  $result = View::newInstance()->_next('alerts');
  $alert = osc_alert();
  View::newInstance()->_exportVariableToView("items", isset($alert['items'])?$alert['items']:array());
  return $result;
}

/**
 * Gets number of alerts in array alerts
 * @return int
 */
function osc_count_alerts() {
  return (int)View::newInstance()->_count('alerts');
}

/**
 * Gets current alert fomr view
 *
 * @return array
 */
function osc_alert() {
  return View::newInstance()->_current('alerts');
}

/**
 * Gets search field of current alert
 *
 * @return string
 */
function osc_alert_search() {
  return (string) osc_alert_field('s_search');
}

/**
 * Gets secret of current alert
 * @return string
 */
function osc_alert_secret() {
  return (string) osc_alert_field('s_secret');
}

/**
 * Gets id of current alert
 * @return string
 */
function osc_alert_id() {
  return (string) osc_alert_field('pk_i_id');
}

/**
 * Gets name of current alert
 * @return string
 */
function osc_alert_name() {
  return osc_apply_filter('user_alert_name', (string)osc_alert_field('s_name'), osc_alert());
}

/**
 * Gets aate of current alert
 * @return string
 */
function osc_alert_date() {
  return (string) osc_alert_field('dt_date');
}

/**
 * Gets unsub date of current alert
 * @return string
 */
function osc_alert_unsub_date() {
  return (string) osc_alert_field('dt_unsub_date');
}

/**
 * Gets type of current alert
 * @return string
 */
function osc_alert_type() {
  return (string) osc_alert_field('e_type');
}

/**
 * Gets type label of current alert
 * @return string
 */
function osc_alert_type_label($type = '') {
  $type = ($type == '' ? osc_alert_type() : $type);
  
  switch($type) {
    case 'INSTANT':
      return __('Instant');
    
    case 'HOURLY':
      return __('Hourly');
      
    case 'DAILY':
      return __('Daily');
      
    case 'WEEKLY':
      return __('Weekly');
      
    case 'CUSTOM':
      return __('Custom');
  }
}

/**
 * Gets active of current alert
 * @return boolean
 */
function osc_alert_is_active() {
  return (bool) osc_alert_field('b_active');
}

/**
 * Check if alert is unsubscribed
 * @return string
 */
function osc_alert_is_unsubscribed() {
  return osc_alert_unsub_date() <> '' ? true : false;
}

/**
 * Gets next user in users array
 *
 * @return <type>
 */
function osc_prepare_user_info() {
  if(!View::newInstance()->_exists('users')) {
    View::newInstance()->_exportVariableToView('users', array (osc_get_user_row(osc_item_user_id())));
  }
  return View::newInstance()->_next('users');
}
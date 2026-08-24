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



// Get param value
function osc_get_param($key, $htmlencode = false, $xss_check = true, $quotes_encode = true) {
  return Params::getParam($key, $htmlencode, $xss_check, $quotes_encode);
}


// Set param value
function osc_set_param($key, $value = '') {
  return Params::setParam($key, $value);
}


// Drop param value
function osc_drop_param($key) {
  return Params::unsetParam($key);
}


// Get cookie value
function osc_get_cookie($key) {
  return Cookie::newInstance()->_get($key);
}


// Set cookie value
function osc_set_cookie($key, $value = '', $httponly = NULL) {
  return Cookie::newInstance()->_set($key, $value, $httponly);
}


// Drop cookie
function osc_drop_cookie($key) {
  return Cookie::newInstance()->_drop($key);
}


// Get session value
function osc_get_session($key) {
  return Session::newInstance()->_get($key);
}


// Set session value
function osc_set_session($key, $value = '') {
  return Session::newInstance()->_set($key, $value);
}


// Drop session
function osc_drop_session($key) {
  return Session::newInstance()->_drop($key);
}


// Get view value
function osc_get_view($key) {
  return View::newInstance()->_get($key);
}


// Set view value
function osc_set_view($key, $value = '') {
  return View::newInstance()->_set($key, $value);
}


// Drop view
function osc_drop_view($key) {
  return View::newInstance()->_erase($key);
}


// Retro compatiblity, used mostly in backoffice
function __get($key) {
  return osc_get_view($key);
}


/**
 * Generic function for view layer, return the $field of $item
 * with specific $locale
 *
 * @param array $item
 * @param string $field
 * @param string $locale
 * @return string
 */
function osc_field($item, $field, $locale) {
  if($item !== null && is_array($item) && !empty($item)) {
    if($locale == '') {
      if(isset($item[$field])) {
        return $item[$field];
      }
    } else {
      //if(isset($item["locale"]) && !empty($item['locale']) && isset($item["locale"][$locale]) && isset($item["locale"][$locale][$field])) {
      if(isset($item['locale']) && isset($item['locale'][$locale]) && isset($item['locale'][$locale][$field]) && $item['locale'][$locale][$field] <> '') {
        return $item['locale'][$locale][$field];
      }else{
        if(isset($item['locale'])){
          foreach($item['locale'] as $locale => $data) {
            if(isset($item['locale'][$locale][$field])) {
              return $item['locale'][$locale][$field];
            }
          }
        }
      }
    }
  }
  
  return '';
}


/**
 * Show widget content
 *
 * @param string $location
 * @return void
 */
function osc_show_widget_content($widget) {
  if(is_array($widget) && isset($widget['s_content']) && trim((string)$widget['s_content']) != '') {
    echo osc_apply_filter('widget_content', $widget['s_content'], $widget);
  }
}


/**
 * Print all widgets belonging to $location
 *
 * @param string $location
 * @return void
 */
function osc_show_widgets($location) {
  // Just list all and save few queries
  // $widgets = Widget::newInstance()->findByLocation($location);
  $widgets = Widget::newInstance()->listAll();

  
  
  foreach($widgets as $widget) {
    if($widget['s_location'] == $location) {
      osc_show_widget_content($widget);
    }
  }
}

/**
 * Print all widgets named $description
 *
 * @param string $description
 * @return void
 */
function osc_show_widgets_by_description($description) {
  $widgets = Widget::newInstance()->findByDescription($description);
  
  foreach($widgets as $widget) {
    osc_show_widget_content($widget);
  }
}


/**
 * Print all widgets named $description
 *
 * @param string $description
 * @return void
 */
function osc_show_widgets_by_hook($hook) {
  $widgets = Widget::newInstance()->findByHook($hook);
  
  foreach($widgets as $w) {
    if(trim((string)$w['s_content']) != '') {
      echo osc_apply_filter('widget_content', $w['s_content'], $w);
    }
  }
}

/**
 * Print recaptcha html, if $section = "recover_password"
 * set 'recover_time' at session.
 *
 * @param  string $section
 * @return void
 */
function osc_show_recaptcha($section = '') {
  if(osc_recaptcha_public_key()) {
      switch($section) {
        case('recover_password'):
          Session::newInstance()->_set('recover_captcha_not_set',0);
          $time  = (int)Session::newInstance()->_get('recover_time');
          if((time()-$time)<=1200) {
            echo _osc_recaptcha_get_html(osc_recaptcha_public_key(), substr(osc_language(), 0, 2))."<br />";
          }
          else{
            Session::newInstance()->_set('recover_captcha_not_set',1);
          }
          break;

        default:
          echo _osc_recaptcha_get_html(osc_recaptcha_public_key(), substr(osc_language(), 0, 2))."<br />";
          break;
      }
  }
}

function _osc_recaptcha_get_html($siteKey, $lang) {
  echo '<div class="g-recaptcha" data-sitekey="' . $siteKey . '"></div>';
  echo '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=' . $lang . '"></script>';
}

/**
 * Formats the date using the appropiate format.
 *
 * @param string $date
 * @return string
 */
function osc_format_date($date, $dateformat = null) {
  if($dateformat==null) {
    $dateformat = osc_date_format();
  }

  $month = array('', __('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'));
  $month_short = array('', __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec'));
  $day = array('', __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday'));
  $day_short = array('', __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun'));
  $ampm = array('AM' => __('AM'), 'PM' => __('PM'), 'am' => __('am'), 'pm' => __('pm'));


  $time = strtotime($date);
  $dateformat = preg_replace('|(?<!\\\)F|', osc_escape_string($month[date('n', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)M|', osc_escape_string($month_short[date('n', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)l|', osc_escape_string($day[date('N', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)D|', osc_escape_string($day_short[date('N', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)A|', osc_escape_string($ampm[date('A', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)a|', osc_escape_string($ampm[date('a', $time)]), $dateformat);
  return date($dateformat, $time);
}


/**
 * Escapes letters and numbers of a string
 *
 * @since 2.4
 * @param string $string
 * @return string
 */
function osc_escape_string($string) {
  $string = preg_replace('/^([0-9])/', '\\\\\\\\\1', $string);
  $string = preg_replace('/([a-z])/i', '\\\\\1', $string);
  return $string;
}

/**
 * Prints the user's account menu
 *
 * @param array $options array with options of the form array('name' => 'display name', 'url' => 'url of link')
 * @return void
 */
function osc_private_user_menu($options = null) {
  if($options == null) {
    $options = array();
    $options[] = array('name' => __('Public Profile'), 'url' => osc_user_public_profile_url(osc_logged_user_id()), 'class' => 'opt_publicprofile');
    $options[] = array('name' => __('Dashboard'), 'url' => osc_user_dashboard_url(), 'class' => 'opt_dashboard');
    $options[] = array('name' => __('Manage your listings'), 'url' => osc_user_list_items_url(), 'class' => 'opt_items');
    $options[] = array('name' => __('Manage your alerts'), 'url' => osc_user_alerts_url(), 'class' => 'opt_alerts');
    $options[] = array('name' => __('My profile'), 'url' => osc_user_profile_url(), 'class' => 'opt_account');
    $options[] = array('name' => __('Logout'), 'url' => osc_user_logout_url(), 'class' => 'opt_logout');
  }

  $options = osc_apply_filter('user_menu_filter', $options);

  echo '<script type="text/javascript">';
  echo '$(".user_menu > :first-child").addClass("first");';
  echo '$(".user_menu > :last-child").addClass("last");';
  echo '</script>';
  
  osc_run_hook('user_menu_before');

  echo '<ul class="user_menu">';
  
  osc_run_hook('user_menu_top');

  $var_l = count($options);
  for($var_o = 0; $var_o < ($var_l-1); $var_o++) {
    $attr = '';
    
    if(isset($options[$var_o]['attr'])) {
      $attr = $options[$var_o]['attr'];
    }
    
    echo '<li class="' . $options[$var_o]['class'] . '" ' . $attr . '><a href="' . $options[$var_o]['url'] . '" >' . $options[$var_o]['name'] . '</a></li>';
  }

  osc_run_hook('user_menu');

  echo '<li class="' . $options[$var_l-1]['class'] . '"><a href="' . $options[$var_l-1]['url'] . '" >' . $options[$var_l-1]['name'] . '</a></li>';

  osc_run_hook('user_menu_bottom');
  
  echo '</ul>';
  
  osc_run_hook('user_menu_after');
}

/**
 * Gets prepared text, with:
 * - higlight search pattern and search city
 * - maxim length of text
 *
 * @param string $txt
 * @param int  $len
 * @param string $start_tag
 * @param string $end_tag
 * @return string
 */
function osc_highlight($txt, $len = 300, $start_tag = '<strong>', $end_tag = '</strong>') {
  $txt = strip_tags((string)$txt);
  $txt = str_replace(array("\n\r","\r\n","\n","\r","\t"), ' ', $txt);
  $txt = trim($txt);
  $txt = preg_replace('/\s+/', ' ', $txt);
  if( mb_strlen($txt, 'UTF-8') > $len ) {
    $txt = mb_substr($txt, 0, $len, 'UTF-8') . "...";
  }
  $query = osc_search_pattern();
  $query = str_replace(array('(',')','+','-','~','>','<'), array('','','','','','',''), $query);

  $query = str_replace(
    array('\\', '^', '$', '.', '[', '|', '?', '*', '{', '}', '/', ']'),
    array('\\\\', '\\^', '\\$', '\\.', '\\[', '\\|', '\\?', '\\*', '\\{', '\\}', '\\/', '\\]'),
    $query);

  $query = preg_replace('/\s+/', ' ', $query);

  $words = array();
  if(preg_match_all('/"([^"]*)"/', $query, $matches)) {
    $l = count($matches[1]);
    for($k=0;$k<$l;$k++) {
      $words[] = $matches[1][$k];
    }
  }

  $query = trim(preg_replace('/\s+/', ' ', preg_replace('/"([^"]*)"/', '', $query)));
  $words = array_merge($words, explode(" ", $query));

  foreach($words as $word) {
    if($word!='') {
      $txt = preg_replace("/(\PL|\s+|^)($word)(\PL|\s+|$)/i", "$01" . $start_tag . "$02". $end_tag . "$03", $txt);
    }
  }
  return $txt;
}


// Get referer url
function osc_get_http_referer() {
  if(Rewrite::newInstance()->get_http_referer() != '') {
    return Rewrite::newInstance()->get_http_referer();

  } else if (Cookie::newInstance()->_getTrueReferer() != '') {
    return Cookie::newInstance()->_getTrueReferer();
    
  } else if(Session::newInstance()->_getReferer() != '') {
    return Session::newInstance()->_getReferer();

  } else if(Params::existServerParam('HTTP_REFERER') && filter_var(Params::getServerParam('HTTP_REFERER', false, false), FILTER_VALIDATE_URL)){
    return Params::getServerParam('HTTP_REFERER', false, false);
  }
  
  return '';
}

function osc_add_route($id, $regexp, $url, $file, $user_menu = false, $location = "custom", $section = "custom", $title = "Custom") {
  Rewrite::newInstance()->addRoute($id, $regexp, $url, $file, $user_menu, $location, $section, $title);
}

/**
 * Set current subdomain parameter into array. There can only be 1 array key at same time!
 *
 * @return array
 */
function osc_get_subdomain_params() {
  $options = array();
  
  if(osc_subdomain_slug() != '') {
    if(osc_subdomain_type() == 'country' && Params::getParam('sCountry') != '') {
      $options['sCountry'] = Params::getParam('sCountry');
      
    } else if(osc_subdomain_type() == 'region' && Params::getParam('sRegion') != '') {
      $options['sRegion'] = Params::getParam('sRegion');
      
    } else if(osc_subdomain_type() == 'city' && Params::getParam('sCity') != '') {
      $options['sCity'] = Params::getParam('sCity');
      
    } else if(osc_subdomain_type() == 'category' && Params::getParam('sCategory') != '') {
      $options['sCategory'] = Params::getParam('sCategory');
      
    } else if(osc_subdomain_type() == 'user' && Params::getParam('sUser') != '') {
      $options['sUser'] = Params::getParam('sUser');
      
    } else if(osc_subdomain_type() == 'language' && Params::getParam('sLanguage') != '') {
      $options['sLanguage'] = Params::getParam('sLanguage');
    }
  }
  
  return $options;
}


/**
 * Generate different versions of filter conditions for SQL based on subdomains
 *
 * USAGE: 
 * $data = osc_subdomains_filter_conditions('oc_t_item');
 * 
 * if($data['usable'] === true) {
 * 
 *   A) $mSearch->addItemConditions('oc_t_item.pk_i_id IN (' . $data['sql_in'] . ')');   -- gives list of item ids matching criteria
 *
 *   B1) $mSearch->addItemConditions($data['where']);
 *   B2) $mSearch->addJoinTable($data['table_key'], $data['table'], $data['table_join'], 'INNER');
 *
 *   C1) $this->dao->where($data['where']);
 *   C2) $this->dao->join($data['table'], $data['table_join'], 'INNER');
 *
 * }
 * 
 * 
 
 * @return array
 */
function osc_subdomains_filter_conditions($item_table = '') {
  if($item_table == '') {
    $item_table = DB_TABLE_PREFIX.'t_item';
  }
  
  $output = array(
    'enabled' => osc_subdomain_enabled(),
    'is_subdomain' => osc_is_subdomain(),
    'type' => osc_subdomain_type(),
    'id' => osc_subdomain_id(),
    'name' => osc_subdomain_name(),
    'slug' => osc_subdomain_slug(),
    'param' => osc_subdomain_param(),
    'in_list' => '',                              // List of selected IDs those can be used in query as ... in (1,2,3,4,...)
    'sql_in' => '',                               // Generated query that can be used as nested query as ... in (select pk_i_id from ...)
    'table' => '',                                // Table required in join to use "where" key. Can be empty if not required
    'table_key' => '',                            // Table key - unique identifier of table, usually table_name.primary_column
    'table_join' => '',                           // Where statement required to join table
    'where' => '',                                // Where statement to filter data
    'usable' => false
  ); 

  $id = osc_subdomain_id();
  $type = osc_subdomain_type();

  $in_list = '';
  $sql_in = '';
  $table = '';
  $table_key = '';
  $table_join = '';
  
  if(osc_subdomain_enabled() && osc_is_subdomain() && $type != 'language') {
    // create list that can be place as: column in ( .. function .. )
    // create where statement in format t_table.column = "val"
    if($type == 'country' && $id <> '') {
      $in_list = osc_esc_html($id);
      $sql_in = sprintf('SELECT fk_i_item_id FROM %st_item_location WHERE fk_c_country_code = "%s"', DB_TABLE_PREFIX, osc_esc_html($id));

      $table = DB_TABLE_PREFIX.'t_item_location';
      $table_key = $table . '.fk_i_item_id';
      $table_join = $item_table . '.pk_i_id = ' . $table . '.fk_i_item_id';
      $where = sprintf('%st_item_location.fk_c_country_code = "%s"', DB_TABLE_PREFIX, osc_esc_html($id));

    } else if($type == 'region' && $id > 0) {
      $in_list = osc_esc_html($id);
      $sql_in = sprintf('SELECT fk_i_item_id FROM %st_item_location WHERE fk_i_region_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));

      $table = DB_TABLE_PREFIX.'t_item_location';
      $table_key = $table . '.fk_i_item_id';
      $table_join = $item_table . '.pk_i_id = ' . $table . '.fk_i_item_id';
      $where = sprintf('%st_item_location.fk_i_region_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));
      
    } else if($type == 'city' && $id > 0) {
      $in_list = osc_esc_html($id);
      $sql_in = sprintf('SELECT fk_i_item_id FROM %st_item_location WHERE fk_i_city_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));

      $table = DB_TABLE_PREFIX.'t_item_location';
      $table_key = $table . '.fk_i_item_id';
      $table_join = $item_table . '.pk_i_id = ' . $table . '.fk_i_item_id';
      $where = sprintf('%st_item_location.fk_i_city_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));

    } else if($type == 'category' && $id > 0) {
      // we need to prepare tree with cat ID and all it's subcategories
      $tree_details = Category::newInstance()->toSubTree(1);
      $tree = array();
      
      if(is_array($tree_details) && count($tree_details) > 0) {
        $tree = array_column($tree_details, 'pk_i_id');
      }
      
      $tree[] = osc_esc_html($id);
      $tree_in = implode(',', $tree);

      $in_list = osc_esc_html($tree_in);
      $sql_in = sprintf('SELECT pk_i_id FROM %st_item WHERE fk_i_category_id in (%s)', DB_TABLE_PREFIX, osc_esc_html($tree_in));

      $table = '';
      $table_key = '';
      $table_join = '';
      $where = sprintf('%s.fk_i_category_id in (%s)', $item_table, osc_esc_html($tree_in));
      
    } else if($type == 'user' && $id > 0) {
      $in_list = osc_esc_html($id);
      $sql_in = sprintf('SELECT pk_i_id FROM %st_item WHERE fk_i_user_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));

      $table = '';
      $table_key = '';
      $table_join = '';
      $where = sprintf('%s.fk_i_user_id in (%s)', $item_table, osc_esc_html($id));
    }

    $output['in_list'] = $in_list;
    $output['sql_in'] = $sql_in;
    $output['table'] = $table;
    $output['table_key'] = $table_key;
    $output['table_join'] = $table_join;
    $output['where'] = $where;
    
    // Make sure all is good and ready to use
    if(osc_subdomain_enabled() && osc_is_subdomain() && osc_subdomain_type() != '' && osc_subdomain_id() != '') {
      if($where != '' && $sql_in != '') {
        $output['usable'] = true;
      }
    }
  }
  
  return $output;
}


/**
 * Apply filter conditions for SQL based on subdomains into Search object
 *
 */
function osc_subdomains_filter_to_search($mSearch, $item_table = '') {
  $data = osc_subdomains_filter_conditions($item_table);
  
  if($data['usable'] === true) {
    $mSearch->addItemConditions($data['where']);
    
    if($data['table'] != '' && $data['table_join'] != '') {
      $mSearch->addJoinTable($data['table_key'], $data['table'], $data['table_join'], 'INNER');
    }
  }
  
  return $mSearch;
}


/**
 * Apply filter conditions for SQL based on subdomains into DAO object
 *
 */
function osc_subdomains_filter_to_dao($object, $item_table = '') {
  $data = osc_subdomains_filter_conditions($item_table);

  if($data['usable'] === true) {
    $object->dao->where($data['where']);
    
    if($data['table'] != '' && $data['table_join'] != '') {
      $object->dao->join($data['table'], $data['table_join'], 'INNER');
    }
  }
  
  return $object;
}



/**
 * Get URL of location files JSON.
 *
 * @return string
 */
function osc_get_locations_json_url() {
  return 'https://osclass-classifieds.com/extras/locations_v3/list.json';
}

/**
 * Get URL of location SQL.
 *
 * @param string $location
 * @return string
 */
function osc_get_locations_sql_url($location) {
  $location = rawurlencode($location);
  return 'https://osclass-classifieds.com/extras/locations_v3/'.$location;
}

/**
 * Get URL of language files JSON.
 *
 * @return string
 */
function osc_get_languages_json_url() {
  return 'https://osclass-classifieds.com/extras/languages/list.json';
}

/**
 * Get URL of language ZIP.
 *
 * @param string $language
 * @return string
 */
function osc_get_language_zip_url($language) {
  $location = rawurlencode($language);
  return 'https://osclass-classifieds.com/extras/languages/'.$language;
}

/**
 * Get hooks support defined in theme. This should be defined in functions.php via constant THEME_COMPATIBLE_WITH_OSCLASS_HOOKS.
 * If theme supports hooks added in Osclass 8.2.0 or later, it will have THEME_COMPATIBLE_WITH_OSCLASS_HOOKS = 820
 *
 * @return integer
 */
function osc_get_theme_hook_compatibility() {
  if(defined('THEME_COMPATIBLE_WITH_OSCLASS_HOOKS') && THEME_COMPATIBLE_WITH_OSCLASS_HOOKS > 810) {
    return THEME_COMPATIBLE_WITH_OSCLASS_HOOKS;
  }
  
  return 810;   // support hooks up to Osclass version 8.1.0
}


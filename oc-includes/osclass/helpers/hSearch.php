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
* Helper Search
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/

/**
 * Gets search object
 *
 * @return mixed
 */
function osc_search() {
  if(View::newInstance()->_exists('search')) {
    return View::newInstance()->_get('search');
    
  } else {
    $search = new Search();
    View::newInstance()->_exportVariableToView('search', $search);
    return $search;
  }
}


/**
 * Gets available search orders
 *
 * @return array
 */
function osc_list_orders() {
  $data = array();
  $data[__('Newly listed')] = array('sOrder' => 'dt_pub_date', 'iOrderType' => 'desc');
  
  // Relevance only if search pattern not empty and using "match"
  // if(in_array(osc_search_pattern_method(), array('','nlp')) && osc_search_pattern() != '') {
  if(osc_search_pattern() != '') {
    $data[__('Relevance')] = array('sOrder' => 'relevance', 'iOrderType' => 'desc');
  }

  if(osc_price_enabled_at_items()) {
    $data[__('Lower price first')] = array('sOrder' => 'i_price', 'iOrderType' => 'asc');
    $data[__('Higher price first')] = array('sOrder' => 'i_price', 'iOrderType' => 'desc');
  }
  
  
  return osc_apply_filter('search_list_orders', $data);
}


/**
 * Set param values those should not be added to alerts
 *
 * @return array
 */
function osc_alert_skip_params() {
  return osc_apply_filter('alert_skip_params', array('lang', 'iPage', 'page', 'iAlertId', 'sAlertSecret', 'sParams'));
}


/**
 * Gets current search page
 *
 * @return int
 */
function osc_search_alert_subscribed() {
  return View::newInstance()->_get('search_alert_subscribed') == 1;
}


/**
 * Gets current search page
 *
 * @return int
 */
function osc_search_page() {
  return (View::newInstance()->_get('search_page') > 0 ? View::newInstance()->_get('search_page') : 0);
}


/**
 * Gets total pages of search
 *
 * @return int
 */
function osc_search_total_pages() {
  return (View::newInstance()->_get('search_total_pages') > 0 ? View::newInstance()->_get('search_total_pages') : 0);
}


/**
 * Gets if "has pic" option is enabled or not in the search
 *
 * @return boolean
 */
function osc_search_has_pic() {
  return View::newInstance()->_get('search_has_pic');
}


/**
 * Gets if "only premium" option is enabled or not in the search
 *
 * @return boolean
 */
function osc_search_only_premium() {
  return View::newInstance()->_get('search_only_premium');
}


/**
 * Gets current search order
 *
 * @return string
 */
function osc_search_order() {
  return View::newInstance()->_get('search_order');
}


/**
 * Gets current search order type id (0, 1)
 *
 * @return string
 */
function osc_search_order_type() {
  return View::newInstance()->_get('search_order_type');
}


/**
 * Gets current search order type name (asc, desc)
 *
 * @return string
 */
function osc_search_order_type_name() {
  return View::newInstance()->_get('search_order_type_name');
}


/**
 * Gets current search pattern
 *
 * @return string
 */
function osc_search_pattern() {
  if(View::newInstance()->_exists('search_pattern')) {
    return View::newInstance()->_get('search_pattern');
  } else {
    return '';
  }
}


/**
 * Gets current search country
 *
 * @return string
 */
function osc_search_country() {
  return View::newInstance()->_get('search_country');
}


// Search country code
function osc_search_country_code() {
  return View::newInstance()->_get('search_country_code');
}

// Search country row
function osc_search_country_row() {
  return View::newInstance()->_get('search_country_row');
}


/**
 * Gets current search region
 *
 * @return string
 */
function osc_search_region() {
  return View::newInstance()->_get('search_region');
}


// Search region ID
function osc_search_region_id() {
  return View::newInstance()->_get('search_region_id');
}

// Search region row
function osc_search_region_row() {
  return View::newInstance()->_get('search_region_row');
}


/**
 * Gets current search city
 *
 * @return string
 */
function osc_search_city() {
  return View::newInstance()->_get('search_city');
}


// Search city ID
function osc_search_city_id() {
  return View::newInstance()->_get('search_city_id');
}


// Search city row
function osc_search_city_row() {
  return View::newInstance()->_get('search_city_row');
}


/**
 * Gets current search users
 *
 * @return string
 */
function osc_search_user() {
  if(is_array(View::newInstance()->_get('search_from_user') ) ){
    return View::newInstance()->_get('search_from_user');
  }
  
  return array();
}


/**
 * Gets current search max price
 *
 * @return float
 */
function osc_search_price_max() {
  return View::newInstance()->_get('search_price_max');
}


/**
 * Gets current search min price
 *
 * @return float
 */
function osc_search_price_min() {
  return View::newInstance()->_get('search_price_min');
}


/**
 * Gets current search total items
 *
 * @return int
 */
function osc_search_total_items() {
  return View::newInstance()->_get('search_total_items');
}


/**
 * Gets current search "show as" variable (show the items as a list or as a gallery)
 *
 * @return string
 */
function osc_search_show_as() {
  return View::newInstance()->_get('search_show_as');
}


/**
 * Gets current search start item record
 *
 * @return int
 */
function osc_search_start() {
  return View::newInstance()->_get('search_start');
}


/**
 * Gets current search end item record
 *
 * @return int
 */
function osc_search_end() {
  return View::newInstance()->_get('search_end');
}


/**
 * Gets current search category
 *
 * @return array
 */
function osc_search_category() {
  if(View::newInstance()->_exists('search_subcategories')) {
    $category = View::newInstance()->_current('search_subcategories');
    
  } else if(View::newInstance()->_exists('search_categories')) {
    $category = View::newInstance()->_current('search_categories');
    
  } else {
    $category = View::newInstance()->_get('search_category');
  }
  
  if(!is_array($category)) { 
    $category = array(); 
  }
  
  return($category);
}

/**
 * Gets current search category id
 *
 * @return int
 */
function osc_search_category_id($only_first = false) {
  $categories = osc_search_category();
  $category = array();
  $where = array();
  $mCat = Category::newInstance();

  foreach($categories as $cat) {
    if(is_numeric($cat)) {
      // $tmp = $mCat->findByPrimaryKey($cat);
      $tmp = osc_get_category_row($cat);
      
      if(isset($tmp['pk_i_id'])) { 
        $category[] = $tmp['pk_i_id'];
      }
      
    } else {
      $slug_cat = explode("/", trim($cat, "/"));
      // $tmp = $mCat->findBySlug($slug_cat[count($slug_cat)-1]);
      $tmp = osc_get_category_row_by_slug($slug_cat[count($slug_cat)-1]);
      
      if(isset($tmp['pk_i_id'])) { 
        $category[] = $tmp['pk_i_id']; 
      }
    }
  }
  
  // Get just first search category - usually there is just one
  if($only_first === true) {
    return isset($category[0]) ? (int)$category[0] : null;
  }

  return $category;
}

/**
 * Update the search url with new options
 *
 * @return string
 */
function osc_update_search_url($params = array(), $forced = false) {
  $request = Params::getParamsAsArray();
  unset($request['osclass']);
  
  if(isset($request['sCategory[0]'])) { 
    unset($request['sCategory']); 
  }
  
  unset($request['sCategory[]']);
  
  if(isset($request['sUser[0]'])) { 
    unset($request['sUser']); 
  }
  
  unset($request['sUser[]']);
  
  if(!$forced && View::newInstance()->_get('subdomain_slug') != '') {
    $subdomain_type = osc_subdomain_type();
    
    if($subdomain_type == 'category') {
      unset($request['sCategory']);
      
    } else if($subdomain_type == 'country') {
      unset($request['sCountry']);
      
    } else if($subdomain_type == 'region') {
      unset($request['sCountry']);
      unset($request['sRegion']);
      
    } else if($subdomain_type == 'city') {
      unset($request['sCountry']);
      unset($request['sRegion']);
      unset($request['sCity']);
      
    } else if($subdomain_type == 'user') {
      unset($request['sUser']);
    }
  }
  
  $merged = array_merge($request, $params);
  
  return osc_search_url($merged);
}


/**
 * Load the form for the alert subscription
 *
 * @return void
 */
function osc_alert_form() {
  osc_current_web_theme_path('alert-form.php');
}


/**
 * Gets alert of current search
 *
 * @return string
 */
function osc_search_alert() {
  return View::newInstance()->_get('search_alert');
}


/**
 * Gets for a default search (all categories, noother option)
 *
 * @return string
 */
function osc_search_show_all_url($params = array()) {
  $params['page'] = 'search';
  return osc_update_search_url($params);
}


/**
 * Gets search url given params
 *
 * @params array $params
 * @return string
 */
function osc_search_url($params = null, $lang_code = '') {
  if(is_array($params)) {
    osc_prune_array($params);
  }

  $countP = 0;
  if(is_array($params) && !empty($params)) {
    $countP = count($params);
  }
  
  if($countP == 0) {
    $params['page'] = 'search';
  }
  
  if(isset($params['iPage']) && $params['iPage'] <= 1) {
    unset($params['iPage']);
  }
  
  $base_url = osc_base_url(false, true, $lang_code);
  $http_url = osc_is_ssl() ? "https://" : "http://";
  $lang_slug = '';

  if(osc_locale_to_base_url_enabled() && osc_subdomain_type() != 'language') {
    $lang_slug = osc_base_url_locale_slug() . '/';
    unset($params['lang']);
  }

  if(!empty($params['sPattern'])) {
    $params['sPattern'] = osc_apply_filter('search_pattern', $params['sPattern']);
  }

  if(osc_subdomain_host() != '' && osc_subdomain_type() == 'category' && isset($params['sCategory'])) {
    if($params['sCategory'] != Params::getParam('sCategory')) {
      if(is_array($params['sCategory'])) {
        $params['sCategory'] = implode(',', $params['sCategory']);
      }
      
      if($params['sCategory'] != '' && strpos($params['sCategory'], ",") === false) {
        $category_slug = osc_search_param_value_to_slug('sCategory', $params['sCategory']);

        if($category_slug != '') {
          $base_url = $http_url . $category_slug . '.' . osc_subdomain_host() . REL_WEB_URL . $lang_slug;
          unset($params['sCategory']);
        }
      }
    } else if(osc_is_subdomain()) {
      unset($params['sCategory']);
    }
    
  } else if(osc_subdomain_host() != '' && osc_subdomain_type() == 'country' && isset($params['sCountry'])) {
    if($params['sCountry'] != Params::getParam('sCountry')) {
      if(is_array($params['sCountry'])) {
        $params['sCountry'] = implode(',', $params['sCountry']);
      }
      
      if($params['sCountry'] != '' && strpos($params['sCountry'], ",") === false) {
        $country_slug = osc_search_param_value_to_slug('sCountry', $params['sCountry']);
        
        if($country_slug != '') {
          $base_url = $http_url . $country_slug . '.' . osc_subdomain_host() . REL_WEB_URL . $lang_slug;
          unset($params['sCountry']);
        }
      }
    } else if(osc_is_subdomain()) {
      unset($params['sCountry']);
    }
    
  } else if(osc_subdomain_host() != '' && osc_subdomain_type() == 'region' && isset($params['sRegion'])) {
    if($params['sRegion'] != Params::getParam('sRegion')) {
      if(is_array($params['sRegion'])) {
        $params['sRegion'] = implode(',', $params['sRegion']);
      }
      
      if($params['sRegion'] != '' && strpos($params['sRegion'], ',') === false) {
        $region_slug = osc_search_param_value_to_slug('sRegion', $params['sRegion']);
        
        if($region_slug != '') {
          $base_url = $http_url . $region_slug . '.' . osc_subdomain_host() . REL_WEB_URL . $lang_slug;
          unset($params['sRegion']);
        }
      }
      
    } else if(osc_is_subdomain()) {
      unset($params['sRegion']);
    }
    
  } else if(osc_subdomain_host() != '' && osc_subdomain_type() == 'city' && isset($params['sCity'])) {
    if($params['sCity'] != Params::getParam('sCity')) {
      if(is_array($params['sCity'])) {
        $params['sCity'] = implode(',', $params['sCity']);
      }
      
      if($params['sCity'] != '' && strpos($params['sCity'], ',') === false) {
        $city_slug = osc_search_param_value_to_slug('sCity', $params['sCity']);
        
        if($city_slug != '') {
          $base_url = $http_url . $city_slug . '.' . osc_subdomain_host() . REL_WEB_URL . $lang_slug;
          unset($params['sCity']);
        }
      }
      
    } else if(osc_is_subdomain()) {
      unset($params['sCity']);
    }
    
  } else if(osc_subdomain_host() != '' && osc_subdomain_type() == 'user' && isset($params['sUser'])) {
    if($params['sUser']!=Params::getParam('sUser')) {
      if(is_array($params['sUser'])) {
        $params['sUser'] = implode(',', $params['sUser']);
      }
      
      if($params['sUser'] != '' && strpos($params['sUser'], ',') === false) {
        $user_slug = osc_search_param_value_to_slug('sUser', $params['sUser']);
        
        if($user_slug != '') {
          $base_url = $http_url . $user_slug . '.' . osc_subdomain_host() . REL_WEB_URL . $lang_slug;
          unset($params['sUser']);
        }
      }
      
    } else if(osc_is_subdomain()) {
      unset($params['sUser']);
    }
  }

  $countP = count($params);
  
  if($countP == 0) { 
    return $base_url; 
  }
  
  unset($params['page']);
  $countP = count($params);

  
  // FRIENDLY URLS ENABLED
  if(osc_rewrite_enabled()) {
    foreach($params as $kp => $vp) {
      $params[$kp] = osc_remove_slash($vp);
    }
    
    $url = $base_url . osc_get_preference('rewrite_search_url');

    
    // CUSTOM SEARCH RULES (Osclass 8.3)
    $strict_match = osc_rewrite_search_custom_rules_strict();
    $custom_rules = osc_custom_search_rules_list();
    
    if(osc_rewrite_search_custom_rules_enabled() && is_array($custom_rules) && count($custom_rules) > 0) {

      // Rule was set in controller/search.php
      if(Params::getParam('sCustomRuleId') != '' && isset($custom_rules[Params::getParam('sCustomRuleId')])) {
        
        // Double check that custom rule is OK
        $rule_id = Params::getParam('sCustomRuleId');
        $rule = $custom_rules[$rule_id];

        if(osc_custom_search_rule_check_match($rule, $params, $strict_match) === false) {
          $rule_id = '';
          Params::unsetParam('sCustomRuleId');
        }
        
      } else {
        $rule_id = '';
        
        // Starting with rule Z up to rule A
        foreach($custom_rules as $rid => $rule) {
          if(osc_custom_search_rule_check_match($rule, $params, $strict_match) === true) {
            $rule_id = $rid;
            break;
          }
        }
      }
      
      $rule_populated = false;
      
      if($rule_id != '') {
        $rule = $custom_rules[$rule_id];
        $rule_populated = osc_custom_search_rule_populate($rule, $params);
      }


      // Check if we were able to populate rule successfully
      // Means get {sCity}/{sCategory} into bremen/for-sale
      if($rule_populated !== false && $rule_populated != '') {
        if(osc_get_preference('seo_url_search_prefix') != '') {
          $url .= '/' . osc_get_preference('seo_url_search_prefix');
        }

        $url .= '/' . $rule_id . '/' . $rule_populated;

        // Other non-matching params
        if($strict_match === false) {
          $rule_params = osc_custom_search_rule_params($rule, $params);
          $other_params = osc_custom_search_rule_other_params($rule, $params);
          
          if(is_array($other_params) && count($other_params) > 0) {
            foreach($other_params as $o_key => $o_val) {
              
              // Custom fields
              if($o_key == 'meta') {
                if(is_array($o_val) && count($o_val) > 0) {
                  foreach($o_val as $meta_id => $meta_val) {
                    
                    // Check if this meta ID is not in custom rule definition
                    if(!in_array('meta' . $meta_id, $rule_params)) {
                      $url .= '/meta' . $meta_id . ',' . urlencode($meta_val); 
                    }
                  }
                }
                
              } else {
                $url .= '/' . $o_key . ',' . urlencode($o_val); 
              }
            }
          }
        }

        if(isset($params['sOrder']) && $params['sOrder'] != '' && $params['sOrder'] != 'dt_pub_date') { 
          $url .= '/' . osc_get_preference('rewrite_search_order') . ',' . osc_friendly_order_by_value($params['sOrder']); 
        }

        if(isset($params['iOrderType']) && $params['iOrderType'] != '' && $params['iOrderType'] != 'desc') { 
          $url .= '/' . osc_get_preference('rewrite_search_order_type') . ',' . $params['iOrderType']; 
        }

        if(isset($params['sShowAs']) && $params['sShowAs'] != '') { 
          $url .= '/' . osc_get_preference('rewrite_search_show_as') . ',' . $params['sShowAs']; 
        }
        
        if(isset($params['iPage']) && $params['iPage'] > 1) { 
          $url .= '/' . $params['iPage']; 
        }

        // echo 'Matched: ' . $rule . ' : ' . $url;
        // exit;

        return osc_apply_filter('search_url', str_replace('%2C', ',', $url), $params, $lang_code);

      } else {
        Params::unsetParam('sCustomRuleId');
      }

      
      // Remove sParams in case it's length is 1 char
      if(isset($params['sParams']) && strlen(trim((string)$params['sParams'], ' +0')) <= 1) {
        Params::unsetParam('sParams');
        $params = array();
      }
    }

    // Make sure custom rule is not used in URL
    unset($params['sCustomRuleId']);
    Params::unsetParam('sCustomRuleId');



    // CANONICAL URLS
    // Category canonical url : /search/for-sale/animals
    if(isset($params['sCategory']) && !is_array($params['sCategory']) && strpos($params['sCategory'], ',') === false && ($countP == 1 || ($countP == 2 && isset($params['iPage'])))) {
      if(osc_category_id() == $params['sCategory']) {
        $category['pk_i_id'] = osc_category_id();
        $category['s_slug'] = osc_category_slug();
        
      } else {
        if(is_numeric($params['sCategory'])) {
          $category = osc_get_category_row($params['sCategory']);
        } else {
          $category = osc_get_category_row_by_slug($params['sCategory']);
        }
      }
      
      if(isset($category['pk_i_id'])) {
        $url = osc_get_preference('rewrite_cat_url');
        
        if(preg_match('|{CATEGORIES}|', $url)) {
          $categories = Category::newInstance()->hierarchy($category['pk_i_id']);
          $sanitized_categories = array();
          $mCat = Category::newInstance();
          
          for($i = count($categories); $i > 0; $i--) {
            // $tmpcat = $mCat->findByPrimaryKey($categories[$i - 1]['pk_i_id']);
            $tmpcat = osc_get_category_row($categories[$i - 1]['pk_i_id']);
            $sanitized_categories[] = $tmpcat['s_slug'];
          }
          
          $url = str_replace('{CATEGORIES}', implode("/", $sanitized_categories), $url);
        }
        
        $seo_prefix = '';
        
        if(osc_get_preference('seo_url_search_prefix') != '') {
          $seo_prefix = osc_get_preference('seo_url_search_prefix') . '/';
        }
        
        $url = str_replace('{CATEGORY_NAME}', $category['s_slug'], $url);
        $url = str_replace('{CATEGORY_SLUG}', $category['s_slug'], $url);
        $url = str_replace('{CATEGORY_ID}', $category['pk_i_id'], $url);
        
      } else {
        // Search by a category which does not exists (by form)
        // Lang code in base URL cannot be used
        return osc_base_url(false, false) . 'index.php?page=search&sCategory=' . urlencode($params['sCategory']);
      }
      
      if(isset($params['iPage']) && $params['iPage'] != '' && $params['iPage']!=1) { 
        $url .= '/'.$params['iPage']; 
      }
      
      $url = $base_url . $seo_prefix . $url;


    // Region canonical url : /search/alabama-r123
    } else if(
      isset($params['sRegion']) 
      && is_string($params['sRegion']) 
      && strpos($params['sRegion'], ',') === false 
      && ($countP == 1 || ($countP == 2 && (isset($params['iPage']) || isset($params['sCategory']))) || ($countP == 3 && isset($params['iPage']) && isset($params['sCategory'])))
    ) {
      $url = $base_url;
      
      if(osc_get_preference('seo_url_search_prefix') != '') {
        $url .= osc_get_preference('seo_url_search_prefix') . '/';
      }
      
      if(isset($params['sCategory'])) {
        $_auxSlug = _aux_search_category_slug($params['sCategory']);
        
        if($_auxSlug != '') { 
          $url .= $_auxSlug . SEARCH_URL_CANONICAL_DELIMITER;
        }
      }

      if(isset($params['sRegion'])) {
        if(osc_list_region_id() == $params['sRegion']) {
          $url .= osc_sanitizeString(osc_list_region_slug()) . '-r' . osc_list_region_id();
          
        } else { 
          $region_slug = osc_search_param_value_to_slug('sRegion', $params['sRegion'], true, true);

          if($region_slug != '') {
            // $url .= $region_slug . '-r' . $region['pk_i_id'];
            $url .= $region_slug;     // It also contains -r{id}
            
          } else {
            // Search by a region which does not exists (by form)
            // Lang code in base URL cannot be used
            return osc_base_url(false, false) . 'index.php?page=search&sRegion=' . urlencode($params['sRegion']);
          }
        }
      }
      
      if(isset($params['iPage']) && $params['iPage'] != '' && $params['iPage'] != 1) { 
        $url .= '/' . $params['iPage']; 
      }


    // City canonical URL : /search/bremen-c123
    } else if(
      isset($params['sCity']) 
      && !is_array($params['sCity']) 
      && strpos($params['sCity'], ',') === false 
      && ($countP == 1 || ($countP == 2 && (isset($params['iPage']) || isset($params['sCategory']))) || ($countP == 3 && isset($params['iPage']) && isset($params['sCategory'])))
    ) {
      $url = $base_url;
      if(osc_get_preference('seo_url_search_prefix') != '') {
        $url .= osc_get_preference('seo_url_search_prefix') . '/';
      }
      
      if(isset($params['sCategory'])) {
        $_auxSlug = _aux_search_category_slug($params['sCategory']);
        if($_auxSlug != '') { 
          $url .= $_auxSlug . SEARCH_URL_CANONICAL_DELIMITER;
        }
      }
      
      if(isset($params['sCity'])) {
        if(osc_list_city_id()==$params['sCity']) {
          $url .= osc_sanitizeString(osc_list_city_slug()) . '-c' . osc_list_city_id();
          
        } else {
          $city_slug = osc_search_param_value_to_slug('sCity', $params['sCity'], true, true);
          
          if($city_slug != '') {
            // $url .= $city_slug . '-c' . $city['pk_i_id'];
            $url .= $city_slug;     // It also contains -c{id}

          } else {
            // Search by a city which does not exists (by form)
            // Lang code in base URL cannot be used
            return osc_base_url(false, false) . 'index.php?page=search&sCity=' . urlencode($params['sCity']);
          }
        }
      }
      
      if(isset($params['iPage']) && $params['iPage'] != '' && $params['iPage'] != 1) { 
        $url .= '/' . $params['iPage']; 
      }


    // NON-CANONICAL URL
    } else if($params !== null && is_array($params)) {
      foreach($params as $k => $v) {

        // Drop order in case it's default value = dt_pub_date
        if($k == 'sOrder' && $v == 'dt_pub_date') {
          continue;
        }

        // Drop order type in case it's default value = asc, or for pub date default value is desc
        if($k == 'iOrderType' && $v == 'desc') {
          continue;
        }


        // Update param names to SEO-Friendly variants, ie sCountry => country, sCategory => category, ...
        switch($k) {
          case 'sCountry':
            $k = osc_get_preference('rewrite_search_country');
            break;
            
          case 'sRegion':
            $k = osc_get_preference('rewrite_search_region');
            break;
            
          case 'sCity':
            $k = osc_get_preference('rewrite_search_city');
            break;
            
          case 'sCityArea':
            $k = osc_get_preference('rewrite_search_city_area');
            break;
            
          case 'sCategory':
            $k = osc_get_preference('rewrite_search_category');
            
            if(is_array($v)) {
              $v = implode(',', $v);
              
            } else {
              // For category ID search for slug
              if(is_numeric($v)) {
                $_auxSlug = _aux_search_category_slug($v);
                
                if($_auxSlug != '') { 
                  $v = $_auxSlug;
                }
              }
            }
           
            break;
            
          case 'sUser':
            $k = osc_get_preference('rewrite_search_user');
            if(is_array($v)) {
              $v = implode(',', $v);
            }
            break;
            
          case 'sPattern':
            $k = osc_get_preference('rewrite_search_pattern');
            break;

          case 'sPattern':
            $k = osc_get_preference('rewrite_search_pattern');
            break;

          case 'sOrder':
            $k = osc_get_preference('rewrite_search_order');
            $v = osc_friendly_order_by_value($v);
            break;

          case 'iOrderType':
            $k = osc_get_preference('rewrite_search_order_type');
            break;

          case 'sPriceMin':
            $k = osc_get_preference('rewrite_search_price_min');
            break;

          case 'sPriceMax':
            $k = osc_get_preference('rewrite_search_price_max');
            break;

          case 'bPic':
            $k = osc_get_preference('rewrite_search_with_picture');
            break;

          case 'bPremium':
            $k = osc_get_preference('rewrite_search_premium_only');
            break;
            
          case 'bPhone':
            $k = osc_get_preference('rewrite_search_with_phone');
            break;

          case 'sShowAs':
            $k = osc_get_preference('rewrite_search_show_as');
            break;

          case 'iPage':
            $k = osc_get_preference('rewrite_search_page_number');
            break;


          case 'meta':
            // meta(@id),value/meta(@id),value2/...

            if(is_array($v) && count($v) > 0) {
              foreach($v as $key => $value) {
                $key = osc_apply_filter('search_url_param_meta_name', $key, $value, $v, $params);
                $value = osc_apply_filter('search_url_param_meta_value', $value, $key, $v, $params);
          
                if(is_array($value)) {
                  foreach ($value as $_key => $_value) {
                    if($value != '') {
                      $url .= '/meta' . $key . '-' . $_key . ',' . urlencode($_value);
                    }
                  }
                } else {
                  if($value != '') {
                    $url .= '/meta' . $key . ',' . urlencode($value);
                  }
                }
              }
            }
            break;
            
          default:
            // No action
            break;
        }


        // Enable customization via filters: code -> friendly, ie sPriceMin -> price-min
        // There is reversal filter in controller/search.php
        $k = osc_apply_filter('search_url_param_name', $k, $v, $params);
        $v = osc_apply_filter('search_url_param_value', $v, $k, $params);


        // Add additional parameters to URL - also above except meta
        if(!is_array($v) && $v != '' && $k != 'sParams') { 
          $url .= '/' . $k . ',' . urlencode($v); 
        }
      }
    }


  // NO FRIENDLY URLS
  } else {

    // Lang code in base URL cannot be used
    $url = osc_base_url(false, false) . 'index.php?page=search';

    if($params != null && is_array($params)) {
      foreach($params as $k => $v) {
        if($k=='meta' || substr($k, 0, 5) == 'meta[') {
          if(is_array($v)) {
            foreach($v as $_k => $aux) {
              if(is_array($aux)) {
                foreach(array_keys($aux) as $aux_k) {
                  $url .= '&meta[' . $_k . '][' . $aux_k . ']=' . urlencode($aux[$aux_k]);
                }
              } else {
                $url .= '&meta[' . $_k . ']=' . urlencode($aux);
              }
            }
          }
          
        } else {
          if(is_array($v)) { 
            $v = implode(',', $v); 
          }
          
          $url .= '&' . $k . '=' . urlencode($v);
        }
      }
    }
  }
  
  return osc_apply_filter('search_url', str_replace('%2C', ',', $url), $params, $lang_code);
}


// Replace slash (/) with blank space in array or string
function osc_remove_slash($var) {
  if(is_array($var)) {
    foreach($var as $k => $v) {
      $var[$k] = osc_remove_slash($v);
    }
  } else {
    $var = str_ireplace('/', ' ', $var);
  }
  
  return $var;
}


// Get nice for of order by string
function osc_friendly_order_by_value($v) {
  // Update order 
  switch($v) {
    case 'dt_pub_date':
      $v = osc_get_preference('rewrite_search_order_by_pub_date');
      break;
      
    case 'i_price':
      $v = osc_get_preference('rewrite_search_order_by_price');
      break;
      
    case 'relevance':
      $v = osc_get_preference('rewrite_search_order_by_relevance');
      break;
      
    case 'i_rating':
      $v = osc_get_preference('rewrite_search_order_by_rating');
      break;
  }
  
  return $v;
}


// Update friendly url param back to original-raw. Ie category -> sCategory, view -> sShowAs...
function osc_friendly_param_to_raw($name, $value, $params = array()) {
  $original_name = $name;
  
  switch($name) {
    case osc_get_preference('rewrite_search_country'):
      $name = 'sCountry';
      break;
      
    case osc_get_preference('rewrite_search_region'):
      $name = 'sRegion';
      break;
      
    case osc_get_preference('rewrite_search_city'):
      $name = 'sCity';
      break;
      
    case osc_get_preference('rewrite_search_city_area'):
      $name = 'sCityArea';
      break;
      
    case osc_get_preference('rewrite_search_category'):
      $name = 'sCategory';
      break;
      
    case osc_get_preference('rewrite_search_user'):
      $name = 'sUser';
      break;
      
    case osc_get_preference('rewrite_search_pattern'):
      $name = 'sPattern';
      break;
      
    case osc_get_preference('rewrite_search_order'):
      $name = 'sOrder';
      break;
      
    case osc_get_preference('rewrite_search_order_type'):
      $name = 'iOrderType';
      break;

    case osc_get_preference('rewrite_search_price_min'):
      $name = 'sPriceMin';
      break;
      
    case osc_get_preference('rewrite_search_price_max'):
      $name = 'sPriceMax';
      break;
      
    case osc_get_preference('rewrite_search_with_picture'):
      $name = 'bPic';
      break;
        
    case osc_get_preference('rewrite_search_premium_only'):
      $name = 'bPremium';
      break;
        
    case osc_get_preference('rewrite_search_with_phone'):
      $name = 'bPhone';
      break;
        
    case osc_get_preference('rewrite_search_show_as'):
      $name = 'sShowAs';
      break;
        
    case osc_get_preference('rewrite_search_page_number'):
      $name = 'iPage';
      break;
       
  }

  // Custom fields
  // All custom fields are on Param "meta", not "meta14", "meta15" etc, but in URL they are split /meta14,val3/meta15/val1/...
  if(preg_match("/meta(\d+)-?(.*)?/", $name, $cmatch)) {
    $name = 'meta';
  }
  
  return osc_apply_filter('friendly_param_to_raw', $name, $original_name, $value, $params);
}


// Update friendly url param value back to original-raw. 
function osc_friendly_param_value_to_raw($name, $value, $params = array()) {
  $original_value = $value;
  
  // Custom fields
  if(preg_match("/meta(\d+)-?(.*)?/", $name, $results)) {
    $meta_key = $name;
    $meta_value = $value;
    $array_r = array();
    
    if(Params::existParam('meta')) {
      $array_r = Params::getParam('meta');
    }
    
    if($results[2] == '') {
      // meta[meta_id] = meta_value
      $meta_key = $results[1];
      $array_r[$meta_key] = $meta_value;
      
    } else {
      // meta[meta_id][meta_key] = meta_value
      $meta_key = $results[1];
      $meta_key2 = $results[2];
      $array_r[$meta_key][$meta_key2] = $meta_value;
    }
    
    $value = $array_r;
  }

  return osc_apply_filter('friendly_param_value_to_raw', $value, $original_value, $name, $params);
}


// Prepare non-empty custom search rules in correct priority - from highest to lowest (Z -> A)
function osc_custom_search_rules_list() {
  $output = array();
  
  foreach(range('z','a') as $search_rule_id) {
    $rule = trim((string)osc_get_preference('rewrite_search_rule_' . $search_rule_id));
    
    // Min rule length is 5 chars
    if($rule != '' && strlen($rule) >= 5) {
      $output[$search_rule_id] = $rule;
    }
  }
  
  return $output;
}


// Count params in URL for custom rules
function osc_custom_search_rules_count_params($params = array()) {
  if(!is_array($params) || count($params) <= 0) {
    return 0;
  }
  
  // Explode custom fields
  if(isset($params['meta']) && is_array($params['meta']) && !empty($params['meta'])) {
    foreach($params['meta'] as $meta_id => $meta_values) {
      $params['meta' . $meta_id] = (is_array($meta_values) ? implode(',', $meta_values) : $meta_values);
    }
  }
  
  // Do not count subdomain params as these are not used in custom rules
  if(osc_subdomain_type_to_raw_param() != '') {
    unset($params[osc_subdomain_type_to_raw_param()]);
  }

  unset($params['meta']);       // custom fields are exploded and counted above
  unset($params['lang']);
  unset($params['iPage']);
  unset($params['sShowAs']);
  unset($params['sOrder']);
  unset($params['iOrderType']);
  unset($params['page']);
  unset($params['sCustomRuleId']);
  unset($params['sParams']);

  return count($params);
}


// Check if page params match all to defined params and pattern
function osc_custom_search_rule_check_match($rule, $params = array(), $strict_match = false) {
  if(!is_array($params) || count($params) <= 0 || trim((string)$rule) == '') {
    return 0;
  }
  
  $missing_found = false;
  $rule_params = osc_custom_search_rule_params($rule);

  if(is_array($rule_params) && count($rule_params) > 0) {
    foreach($rule_params as $rule_param) {
      // Custom fields
      if(substr($rule_param, 0, 4) == 'meta' && !isset($params[$rule_param])) {
        $meta_id = substr($rule_param, 4);
        $meta_param_values = isset($params['meta'][$meta_id]) ? $params['meta'][$meta_id] : '';
        $params['meta' . $meta_id] = (is_array($meta_param_values) ? implode(',', $meta_param_values) : $meta_param_values);
      }
      
      // Make sure we don't take array here
      $param_value = (isset($params[$rule_param]) ? $params[$rule_param] : '');
      
      if($rule_param == 'sCategory' && is_array($param_value)) {
        $param_value = $param_value[0];
        
      } else if(is_array($param_value)) { 
        $param_value = implode(',', $param_value);       // Maybe better to use $param_value[0] ?
      }
      
      
      if(!isset($param_value) || $param_value == '' || strlen(trim((string)$param_value, ' +0')) < 1) {
        $missing_found = true;
        break;
      }
    }

    // All rule params exists in URL
    if($missing_found === false) {
      if($strict_match === false) {
        return true;
        
      // Check if number of params in rule and URL are same - exact match
      } else {
        // if(count($params) == count($rule_params)) {
        if(osc_custom_search_rules_count_params($params) == count($rule_params)) {
          return true;
        }
        
        return false;
      }
    }
  }
  
  return false;
}


// Replace rule keywords with real values
function osc_custom_search_rule_populate($rule, $params = array()) {
  if(!is_array($params) || count($params) <= 0 || trim((string)$rule) == '') {
    return 0;
  }

  $missing_found = false;
  $rule_populated = $rule;
  $rule_params = osc_custom_search_rule_params($rule);

  if(is_array($rule_params) && count($rule_params) > 0) {
    foreach($rule_params as $rule_param) {
      if(isset($params[$rule_param]) || substr($rule_param, 0, 4) == 'meta' && isset($params['meta'][substr($rule_param, 4)])) {
        $param_value = (isset($params[$rule_param]) ? $params[$rule_param] : '');

        // Custom fields
        if(substr($rule_param, 0, 4) == 'meta' && !isset($params[$rule_param])) {
          $meta_id = substr($rule_param, 4);
          $meta_param_values = isset($params['meta'][$meta_id]) ? $params['meta'][$meta_id] : '';
          $param_value = (is_array($meta_param_values) ? implode(',', $meta_param_values) : $meta_param_values);
          
        } else if(is_array($param_value)) {
          $param_value = $param_value[0];     // maybe we should stop here, but category may be as array
        }

        if($param_value != '' && '{' . $rule_param . '}' != urldecode($param_value)) {
          $param_value = osc_search_param_value_to_slug($rule_param, $param_value);
          $rule_populated = str_replace('{' . $rule_param . '}', $param_value, $rule_populated);
          
        } else {
          return false;                       // Make it false if any of rule param is not populated 
        }
        
      } else {
        return false;                         // Make it false if any of rule param is not populated 
      }
    }
  }

  // echo 'xxx' . $rule_populated  . 'xxx';
  return $rule_populated;
}


// Replace rule keywords with regex values like (.+?), ([a-z]), ... 
function osc_custom_search_rule_to_regex($rule) {
  if(trim((string)$rule) == '') {
    return '';
  }

  $rule_params = osc_custom_search_rule_params($rule);
  $rule_regex = $rule;
  $basic = '(.+?)';         // This worked best, string can contain also: -, _, /

  if(is_array($rule_params) && count($rule_params) > 0) {
    foreach($rule_params as $rule_param) {
      $rule_regex = str_replace('{' . $rule_param . '}', $basic, $rule_regex);
    }
  }
  
  return $rule_regex;
}


// Replace rule keywords with real values
function osc_custom_search_rule_extract_params($rule, $param_string) {
  if(trim((string)$param_string) == '' || trim((string)$rule) == '') {
    return array();
  }
  
  $params = array();
  $rule_populated = $rule;
  $rule_params = osc_custom_search_rule_params($rule);

  if(is_array($rule_params) && count($rule_params) > 0) {
    foreach($rule_params as $rule_param) {
      if(isset($params[$rule_param]) && $params[$rule_param] != '') {
        $rule_populated = str_replace('{' . $rule_param . '}', $params[$rule_param], $rule_populated);
      }
    }
  }
  
  return $rule_populated;
}


// Get remaining not used custom rules params
function osc_custom_search_rule_other_params($rule, $params = array()) {
  if(!is_array($params) || count($params) <= 0 || trim((string)$rule) == '') {
    return array();
  }

  $rule_params = osc_custom_search_rule_params($rule);
  $other_params = array();
  
  foreach($params as $k => $v) {
    if(!in_array($k, $rule_params)) {
      if(!in_array($k, array('sParams','sCustomRuleId','sShowAs','lang','iPage','sOrder','iOrderType','page')) && $v != '') {
        if($k != osc_subdomain_type_to_raw_param()) {
          $other_params[$k] = $v;
        }
      }
    }
  }

  return $other_params;
}


// Get remaining not used custom rules params
function osc_custom_search_rule_params($rule) {
  if(trim((string)$rule) == '') {
    return array();
  }

  $params = array();
  
  preg_match_all('/\{([^}]*)\}/', $rule, $matches);

  if(!empty($matches[1])) {
    return $matches[1];
  }
  
  return array();
}


// Replace IDs with slugs in URLs
function osc_search_param_value_to_slug($param, $value, $slug_strict = false, $with_id_canonical = false) {
  $value_original = $value;
  
  switch($param) {
    case 'sPattern':
      // $value = str_replace(' ', '+', $value);
      $value = urlencode($value);
      
    case 'sCategory': 
      $slug = _aux_search_category_slug($value);
      $value = ($slug != '' ? $slug : $value);
      break;
      
    case 'sCountry': 
      $country = osc_get_country_row($value);
      
      if(!isset($country['pk_c_code'])) {
        $country = osc_get_country_row_by_slug($value);
      }

      $slug = '';
      if(isset($country['pk_c_code'])) {
        $slug = ($country['s_slug'] != '' ? $country['s_slug'] : (!$slug_strict ? $country['s_name'] : ''));   // Maybe country code is better than name?
      } 
      
      $value = ($slug != '' ? $slug : $value);
      break;

    case 'sRegion': 
      if(is_numeric($value)) {
        $region = osc_get_region_row($value);
      } else {
        $region = osc_get_region_row_by_slug($value);
        
        if(!isset($region['pk_i_id'])) {
          $region = Region::newInstance()->findByName($value);
        }
      }

      $slug = '';
      if(isset($region['pk_i_id'])) {
        $slug = ($region['s_slug'] != '' ? $region['s_slug'] . ($with_id_canonical ? '-r' . $region['pk_i_id'] : '') : (!$slug_strict ? $region['s_name'] : ''));
      } 
      
      $value = ($slug != '' ? $slug : $value);
      break;

    case 'sCity': 
      if(is_numeric($value)) {
        $city = osc_get_city_row($value);
      } else {
        $city = osc_get_city_row_by_slug($value);
        
        if(!isset($city['pk_i_id'])) {
          $city = City::newInstance()->findByName($value);
        }
      }

      $slug = '';
      if(isset($city['pk_i_id'])) {
        $slug = ($city['s_slug'] != '' ? $city['s_slug'] . ($with_id_canonical ? '-c' . $city['pk_i_id'] : '') : (!$slug_strict ? $city['s_name'] : ''));
      } 
      
      $value = ($slug != '' ? $slug : $value);
      break;

    case 'sUser': 
      if(is_numeric($value)) {
        $user = osc_get_user_row($value);
      } else {
        $user = osc_get_user_row_by_username($value);
      }

      $slug = '';
      if(isset($user['pk_i_id'])) {
        $slug = ($user['s_username'] != '' ? $user['s_username'] : (!$slug_strict ? $user['pk_i_id'] : ''));
      } 
      
      $value = ($slug != '' ? $slug : $value);
      break;
  }

  $value = osc_sanitizeString($value);
  
  return osc_apply_filter('search_param_value_to_slug', $value, $param);
}


// Clean search URL for compare action
function osc_clear_compare_url($url) {
  $url = urldecode($url);
  $url = strtolower(trim((string)$url));
  $url = str_replace(array('%20', '%2B', ' '), '+', $url);      // encoded empty space, encoded plus char, empty char
  $url = remove_accents($url);                                  // comment if it cause issues
  
  return $url;
}


// Subdomain type to raw param
function osc_subdomain_type_to_raw_param() {
  $param = '';
  
  switch(osc_subdomain_type()) {
    case 'category':
      $param = 'sCategory';
      break;
      
    case 'country':
      $param = 'sCountry';
      break;
      
    case 'region':
      $param = 'sRegion';
      break;
      
    case 'city':
      $param = 'sCity';
      break;
      
    case 'user':
      $param = 'sUser';
      break;
      
    case 'language':
      $param = 'lang';
      break;
  }
  
  return $param;
}


/**
 * Gets list of countries with items
 *
 * @return array
 */
function osc_list_country() {
  if(View::newInstance()->_exists('list_countries')) {
    return View::newInstance()->_current('list_countries');
  } else {
    return null;
  }
}

/**
 * Gets list of regions with items
 *
 * @return array
 */
function osc_list_region() {
  if(View::newInstance()->_exists('list_regions')) {
    return View::newInstance()->_current('list_regions');
  } else {
    return null;
  }
}

/**
 * Gets list of cities with items
 *
 * @return array
 */
function osc_list_city() {
  if(View::newInstance()->_exists('list_cities')) {
    return View::newInstance()->_current('list_cities');
  } else {
    return null;
  }
}

/**
 * Gets the next country in the list_countries list
 *
 * @return array
 */
function osc_has_list_countries() {
  if(!View::newInstance()->_exists('list_countries')) {
    View::newInstance()->_exportVariableToView('list_countries', CountryStats::newInstance()->listCountries());
  }
  
  $result = View::newInstance()->_next('list_countries');
  
  if(!$result) {
    View::newInstance()->_reset('list_countries');
  }
  
  return $result;
}

/**
 * Gets the next region in the list_regions list
 *
 * @param string $country
 * @return array
 */
function osc_has_list_regions($country = '%%%%') {
  if(!View::newInstance()->_exists('list_regions')) {
    View::newInstance()->_exportVariableToView('list_regions', RegionStats::newInstance()->listRegions($country));
  }
  
  $result = View::newInstance()->_next('list_regions');
  
  if(!$result) {
    View::newInstance()->_reset('list_regions');
  }
  
  return $result;
}

/**
 * Gets the next city in the list_cities list
 *
 * @param string $region
 * @return array
 */
function osc_has_list_cities($region = '%%%%') {
  if(!View::newInstance()->_exists('list_cities')) {
    View::newInstance()->_exportVariableToView('list_cities', CityStats::newInstance()->listCities($region));
  }
  
  $result = View::newInstance()->_next('list_cities');
  
  if(!$result) {
    View::newInstance()->_reset('list_cities');
  }
  
  return $result;
}

/**
 * Gets the total number of countries in list_countries
 *
 * @return int
 */
function osc_count_list_countries() {
  if(!View::newInstance()->_exists('list_countries')) {
    View::newInstance()->_exportVariableToView('list_countries', CountryStats::newInstance()->listCountries());
  }
  
  return View::newInstance()->_count('list_countries');
}

/**
 * Gets the total number of regions in list_regions
 *
 * @param string $country
 * @return int
 */
function osc_count_list_regions($country = '%%%%') {
  if(!View::newInstance()->_exists('list_regions')) {
    View::newInstance()->_exportVariableToView('list_regions', RegionStats::newInstance()->listRegions($country));
  }
  
  return View::newInstance()->_count('list_regions');
}

/**
 * Gets the total number of cities in list_cities
 *
 * @param string $region
 * @return int
 */
function osc_count_list_cities($region = '%%%%') {
  if(!View::newInstance()->_exists('list_cities')) {
    View::newInstance()->_exportVariableToView('list_cities', CityStats::newInstance()->listCities($region));
  }
  
  return View::newInstance()->_count('list_cities');
}

// country attributes
/**
 * Gets the name of current "list country"
 *
 * @return string
 */
function osc_list_country_name() {
  return osc_field(osc_list_country(), 'country_name', '');
}

/**
 * Gets the number of items of current "list country"
 *
 * @return int
 */
function osc_list_country_code() {
  return osc_field(osc_list_country(), 'country_code', '');
}

/**
 * Gets the number of items of current "list country"
 *
 * @return int
 */
function osc_list_country_items() {
  return osc_field(osc_list_country(), 'items', '');
}

/**
 * Gets the url of current "list country"
 *
 * @return string
 */
function osc_list_country_url() {
  return osc_search_url(array('sCountry' => osc_list_country_code()));
}

// region attributes
/**
 * Gets the name of current "list region" by name
 *
 * @return string
 */
function osc_list_region_name() {
  return osc_field(osc_list_region(), 'region_name', '');
}

/**
 * Gets the name of current "list region" by slug
 *
 * @return string
 */
function osc_list_region_slug() {
  return osc_field(osc_list_region(), 'region_name', '');
}

/**
 * Gets the ID of current "list region"
 *
 * @return string
 */
function osc_list_region_id() {
  return osc_field(osc_list_region(), 'region_id', '');
}

/**
 * Gets the number of items of current "list region"
 *
 * @return int
 */
function osc_list_region_items() {
  return osc_field(osc_list_region(), 'items', '');
}

/**
 * Gets the url of current "list region"
 *
 * @return string
 */
function osc_list_region_url() {
  return osc_search_url(array('sRegion' => osc_list_region_id()));
}

// city attributes
/**
 * Gets the name of current "list city" by name
 *
 * @return string
 */
function osc_list_city_name() {
  return osc_field(osc_list_city(), 'city_name', '');
}

/**
 * Gets the list of current "list city" by slug
 *
 * @return string
 */
function osc_list_city_slug() {
  return osc_field(osc_list_city(), 'city_slug', '');
}

/**
 * Gets the ID of current "list city"
 *
 * @return string
 */
function osc_list_city_id() {
  return osc_field(osc_list_city(), 'city_id', '');
}

/**
 * Gets the number of items of current "list city"
 *
 * @return int
 */
function osc_list_city_items() {
  return osc_field(osc_list_city(), 'items', '');
}

/**
 * Gets the url of current "list city"
 *
 * @return string
 */
function osc_list_city_url() {
  return osc_search_url(array('sCity' => osc_list_city_id()));
}

/**********************
 ** LATEST SEARCHES **
 **********************/
/**
 * Gets the latest searches done in the website
 *
 * @param int $limit
 * @return array
 */
function osc_get_latest_searches($limit = 20) {
  if(!View::newInstance()->_exists('latest_searches')) {
    View::newInstance()->_exportVariableToView('latest_searches', LatestSearches::newInstance()->getSearches($limit));
  }
  
  return View::newInstance()->_get('latest_searches');
}

/**
 * Gets the total number of latest searches done in the website
 *
 * @return int
 */
function osc_count_latest_searches() {
  if(!View::newInstance()->_exists('latest_searches')) {
    View::newInstance()->_exportVariableToView('latest_searches', LatestSearches::newInstance()->getSearches());
  }
  
  return View::newInstance()->_count('latest_searches');
}

/**
 * Gets the next latest search
 *
 * @return array
 */
function osc_has_latest_searches() {
  if(!View::newInstance()->_exists('latest_searches')) {
    View::newInstance()->_exportVariableToView('latest_searches', LatestSearches::newInstance()->getSearches());
  }
  
  return View::newInstance()->_next('latest_searches');
}

/**
 * Gets the current latest search
 *
 * @return array
 */
function osc_latest_search() {
  if(View::newInstance()->_exists('latest_searches')) {
    return View::newInstance()->_current('latest_searches');
  }
  
  return null;
}

/**
 * Gets the current latest search pattern
 *
 * @return string
 */
function osc_latest_search_text() {
  return osc_field(osc_latest_search(), 's_search', '');
}

/**
 * Gets the current latest search date
 *
 * @return string
 */
function osc_latest_search_date() {
  return osc_field(osc_latest_search(), 'd_date', '');
}

/**
 * Gets the current latest search total
 *
 * @return string
 */
function osc_latest_search_total() {
  return osc_field(osc_latest_search(), 'i_total', '');
}


// Get canonical url for search
function osc_get_canonical() {
  $url = '';
  if(View::newInstance()->_exists('canonical')) {
    $url = View::newInstance()->_get('canonical');
  }
  
  return osc_apply_filter('canonical_url_osc', $url);
}


// Get prev url (navigation) for search
function osc_get_prev_url() {
  $url = '';
  if(View::newInstance()->_exists('search_prev_url')) {
    $url = View::newInstance()->_get('search_prev_url');
  }
  
  return osc_apply_filter('search_prev_url', $url);
}


// Get next url (navigation) for search
function osc_get_next_url() {
  $url = '';
  if(View::newInstance()->_exists('search_next_url')) {
    $url = View::newInstance()->_get('search_next_url');
  }
  
  return osc_apply_filter('search_next_url', $url);
}



// Create basic search conditions including just few params - for multi-search
function osc_get_raw_search($conditions) {
  $keys = array("user_ids", "aCategories", "countries", "regions", "cities", "city_areas", "zips");
  $pattern_string = "/'(?:\\\\'|[^']*)'|\"(?:\\\\\"|[^\"]*)\"/";
  
  foreach($keys as $key) {
    if(isset($conditions[$key]) && is_array($conditions[$key]) && !empty($conditions[$key])) {
      foreach($conditions[$key] as $k => $v) {
        
        // Check and extract number out of condition... ie: oc_t_item.fk_i_user_id = 67457 --> 67457
        if(preg_match('|([0-9]+)|', $v, $match)) {
          if($key == "aCategories") {
            $conditions[$key][$k] = Category::newInstance()->findNameByPrimaryKey($match[1]);
            
          } else if ($key == 'regions') {
            $name = Region::newInstance()->findNameByPrimaryKey($match[1]);
            $conditions[$key][$k] = ($name === false ? $match[1] : $name);

          } else if ($key == 'cities') {
            $name = City::newInstance()->findNameByPrimaryKey($match[1]);
            $conditions[$key][$k] = ($name === false ? $match[1] : $name);
            
          } else if ($key == 'user_ids') {
            $name = User::newInstance()->findNameByPrimaryKey($match[1]);
            $conditions[$key][$k] = ($name === false ? $match[1] : $name);

          } else {
            $conditions[$key][$k] = $match[1];
          }
          
        // Extract string from condition... ie: oc_t_item_location.s_city LIKE 'Detroit' OR oc_t_it... ---> Detroit
        // } else if (preg_match("|'([^']*)'|", $v, $match)) {
        } else if (preg_match($pattern_string, $v, $match)) {
          $conditions[$key][$k] = stripslashes(substr($match[0], 1, -1));
          
          if ($key == 'countries' && strlen($conditions[$key][$k]) == 2) {
            $name = Country::newInstance()->findNameByPrimaryKey($conditions[$key][$k]);
            $conditions[$key][$k] = ($name === false ? $conditions[$key][$k] : $name);
          }
        }
      }
    } else {
      unset($conditions[$key]);
    }
  }

  if(!isset($conditions['price_min']) || $conditions['price_min']==0) {
    unset($conditions['price_min']);
  }

  if(!isset($conditions['price_max']) || $conditions['price_max']==0) {
    unset($conditions['price_max']);
  }

  if(!isset($conditions['sPattern']) || $conditions['sPattern']=='') {
    unset($conditions['sPattern']);
  }

  unset($conditions['withPattern']);
  unset($conditions['tables']);
  unset($conditions['tables_join']);
  unset($conditions['no_catched_tables']);
  unset($conditions['no_catched_conditions']);
  // unset($conditions['user_ids']);
  unset($conditions['order_column']);
  unset($conditions['order_direction']);
  unset($conditions['limit_init']);
  unset($conditions['results_per_page']);
  
  return $conditions;
}


// Get search category slug for single/array category input
function _aux_search_category_slug($paramCat) {
  if(is_array($paramCat)) {
    if(count($paramCat) == 1) {
      $paramCat = $paramCat[0];
    } else {
      return '';
    }
  }

  if(osc_category_id() == $paramCat) {
    $category['s_slug'] = osc_category_slug();
  } else {
    if(is_numeric($paramCat)) {
      $category = osc_get_category_row($paramCat);
    } else {
      $category = osc_get_category_row_by_slug($paramCat);
    }
  }
  
  return isset($category['s_slug']) ? $category['s_slug'] : '';
}

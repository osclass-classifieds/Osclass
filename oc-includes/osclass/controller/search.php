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
 * Class CWebSearch
 */
class CWebSearch extends BaseModel {
  public $mSearch;
  public $uri;

  public function __construct() {
    parent::__construct();

    $this->mSearch = Search::newInstance();
    $lang_slug = strtolower(osc_locale_to_base_url_enabled() ? osc_base_url_locale_slug() . '/' : '');   // os810

    $this->uri = preg_replace('|^' . REL_WEB_URL . '|', '', Params::getServerParam('REQUEST_URI', false, false));
    if(preg_match('/^index\.php/', $this->uri)>0) {
      // search url without permalinks params
    } else {
      $this->uri = preg_replace('|/$|', '', $this->uri);    // search or langCode/search

      // redirect if it ends with a slash NOT NEEDED ANYMORE, SINCE WE CHECK WITH osc_search_url
      $search_tag = strtolower($lang_slug . osc_get_preference('rewrite_search_url'));
      
      if((strtolower($this->uri) != $search_tag && stripos(strtolower($this->uri), $search_tag . '/')===false) && osc_rewrite_enabled() && !Params::existParam('sFeed')) {
        // clean GET html params
        $this->uri = preg_replace('/(\/?)\?.*$/', '', $this->uri);
        $search_uri = preg_replace('|/[0-9]+$|', '', $this->uri);

        $this->_exportVariableToView('search_uri', $search_uri);

        // Get page ID if it's set in the url
        $iPage = preg_replace('|.*/([0-9]+)$|', '$01', $this->uri);
        if(is_numeric($iPage) && $iPage > 0) {
          Params::setParam('iPage', $iPage);
          // redirect without number of pages
          if($iPage == 1) {
            $this->redirectTo(osc_base_url(false, true) . $search_uri);
          }
        }
        
        
        // URL is enhanced via function osc_enhance_canonical_url()
        if(Params::getParam('iPage') > 1) { 
          $canonical_url = osc_apply_filter('canonical_url_search', osc_base_url(false, true) . $search_uri);
          $this->_exportVariableToView('canonical', $canonical_url);
        }

        // Get only the last segment
        $search_uri = preg_replace('|.*?/|', '', $search_uri);

        // Region canonical URL
        if(preg_match('|-r([0-9]+)$|', $search_uri, $r)) {
          $region = osc_get_region_row($r[1]);
          if(!$region) {
            $this->do404();
          }
          
          Params::setParam('sRegion', $region['pk_i_id']);
          
          if(osc_subdomain_type() != 'category') {
            Params::unsetParam('sCategory');
          }
          
          // Category_Region url
          if(preg_match('|(.*?)' . SEARCH_URL_CANONICAL_DELIMITER . '.*?-r[0-9]+|', $search_uri, $match)) {
            Params::setParam('sCategory', $match[1]);
          }
          
        // City canonical URL
        } else if(preg_match('|-c([0-9]+)$|', $search_uri, $c)) {
          $city = osc_get_city_row($c[1]);
          if(!$city) {
            $this->do404();
          }
          
          Params::setParam('sCity', $city['pk_i_id']);
          
          if(osc_subdomain_type() != 'category') {
            Params::unsetParam('sCategory');
          }
          
          // Category_City url
          if(preg_match('|(.*?)' . SEARCH_URL_CANONICAL_DELIMITER . '.*?-c[0-9]+|', $search_uri, $match)) {
            Params::setParam('sCategory', $match[1]);
          }
          
        } else {
          if(!Params::existParam('sCategory')) {
            $category = osc_get_category_row_by_slug($search_uri);

            if(!is_array($category) || count($category) === 0) {
              $this->do404();
            }
            
            Params::setParam('sCategory', $search_uri);
            
          } else {
            if (strpos(Params::getParam('sCategory') , '/') !== false) {
              $tmp = explode('/' , preg_replace('|/$|', '', Params::getParam('sCategory')));
              $category = osc_get_category_row_by_slug($tmp[count($tmp) - 1]);
              
              Params::setParam('sCategory', $tmp[count($tmp)-1]);
              
            } else {
              if((int)Params::getParam('sCategory') > 0) {
                $category = osc_get_category_row(Params::getParam('sCategory'));
              } else {
                $category = osc_get_category_row_by_slug(Params::getParam('sCategory'));
              }
              
              Params::setParam('sCategory', Params::getParam('sCategory'));
            }

            if(is_array($category) && count($category) === 0) { 
              $this->do404();
            }
          }
        }
      }
    }
  }

  //Business Layer...
  public function doModel() {
    osc_run_hook('before_search');

    if(osc_rewrite_enabled()) {
      // IF rewrite is not enabled, skip this part, preg_match is always time&resources consuming task
      $p_sParams = '/' . Params::getParam('sParams', false, false);
      $p_sParams_arr = explode('/', trim($p_sParams, '/'));


      // CUSTOM SEARCH RULES
      if(osc_rewrite_search_custom_rules_enabled()) {
        $custom_rule_id = '';
        $custom_rule = false;
        
        // Check if we got custom search rule
        if(strlen($p_sParams_arr[0]) == 1 && preg_match('/^\/([a-z])\//', $p_sParams) === 1 && count($p_sParams_arr) >= 2) {
          $custom_rules = osc_custom_search_rules_list();

          if(in_array($p_sParams_arr[0], array_keys($custom_rules))) {
            $custom_rule_id = $p_sParams_arr[0];      // Letter: a-z
            $custom_rule = $custom_rules[$custom_rule_id];


            //******************* testing   ***********
            /*
            $pattern = '#^/([a-z])/(.+?)/(.+?)(?:/|$)#';
            $pattern = '#^/([a-z]{1})/' . osc_custom_search_rule_to_regex($custom_rule) . '(?:/|$)#';

              echo '-Checking- ' . $custom_rule . ' --> ' . $pattern . ' : '  . $p_sParams . '--';

            if(preg_match($pattern, $p_sParams, $m)) {
              echo '<pre>';
              print_r($m);
              echo '</pre>';
            } else {
              echo '--NOMATCH--';
            }

            */
            // *********************************************
      
      
            unset($p_sParams_arr[0]);
            $rule_params = osc_custom_search_rule_params($custom_rule);

            // Extract values from URL string
            // /z/bremen/min-50  -->  city=bremen, pricemin=50
            $pattern = '#^/([a-z]{1})/' . osc_custom_search_rule_to_regex($custom_rule) . '(?:/|$)#';

            // if(is_array($rule_params) && count($rule_params) > 0 && count($p_sParams_arr) >= count($rule_params)) {
            if(is_array($rule_params) && count($rule_params) > 0 && preg_match($pattern, $p_sParams, $matched_values)) {
              Params::setParam('sCustomRuleId', $custom_rule_id);
              
              // print_r($p_sParams);
              // print_r($matched_values);
              // exit;
              
              // Identify pagination... url.com/search/.../23
              $url_path = rtrim(parse_url($p_sParams, PHP_URL_PATH), '/');
              $page_id = is_numeric($last = basename($url_path)) ? (int)$last : null;
              
              if($page_id > 1) {
                Params::setParam('iPage', $page_id);
              }

              for($j=0; $j<=count($rule_params)-1; $j++) {
                // if(isset($p_sParams_arr[$j+1]) && $p_sParams_arr[$j+1] != '') {
                if(isset($matched_values[$j+1]) && $matched_values[$j+2] != '') {
                  // $matched_values[0] is full matched string, $matched_values[1] is rule ID
                  
                  $param_name = $rule_params[$j];
                  $param_value = urldecode($matched_values[$j+2]);
                  //$param_value = osc_search_param_value_to_slug($param_name, $param_value);

                  // Custom rule meta param used, so take "meta14", "meta15" back to "meta"
                  if(substr($param_name, 0, 4) == 'meta' && strlen($param_name) > 4) {
                    $meta_arr = Params::getParam('meta');
                    $meta_arr = (is_array($meta_arr) ? $meta_arr : array());
                    $meta_arr[substr($param_name, 4)] = $param_value;
                    
                    $param_name = 'meta';
                    $param_value = $meta_arr;      // Back to "meta" array
                  }

                  Params::setParam($param_name, $param_value); 
                  unset($p_sParams_arr[$j+1]);                              // Unset this parameter from sParams as we used it
                  
                } else {  
                  // Not all rule params exists or are non-empty, so don't go custom rule
                  $p_sParams = str_replace('/' . $custom_rule_id, '', $p_sParams);
                  $this->redirectTo(osc_base_url(false, true) . $p_sParams); 
                  // $this->redirectTo(osc_search_url(array('page' => 'search'))); 
                }
              }
            }
            
            $p_sParams_arr = array_filter($p_sParams_arr);
            $p_sParams = '/' . implode('/', $p_sParams_arr);
            
            // echo 'Custom rule: ' . $custom_rule_id . ' ... '; EXIT;
            // We have now extracted Rule ID and it's params from sParams
          }
        }
      }


      if(preg_match_all('|\/([^,]+),([^\/]*)|', $p_sParams, $m)) {
        for($k = 0; $k<count($m[0]); $k++) {
          $param_name_orig = $m[1][$k];
          
          $m[1][$k] = osc_friendly_param_to_raw($m[1][$k], $m[2][$k], $p_sParams_arr);
          $m[2][$k] = osc_friendly_param_value_to_raw($param_name_orig, $m[2][$k], $p_sParams_arr);      // for custom fields, param name $m[1][$k] is already called "meta"


          // Enable customization via filters: friendly -> code, ie price-min -> sPriceMin
          // There is reversal filter in helpers/hSearch.php
          $m[1][$k] = osc_apply_filter('search_engine_param_name', $m[1][$k], $m[2][$k], $m, $p_sParams);
          $m[2][$k] = osc_apply_filter('search_engine_param_value', $m[2][$k], $m[1][$k], $m, $p_sParams);


          // Filters might nullify param name or value intentionally
          if($m[1][$k] != 'lang' && $m[1][$k] !== null && $m[2][$k] !== null) {
            Params::setParam($m[1][$k], $m[2][$k]);
          }
        }

        osc_run_hook('search_engine_after_params_set', $m, $p_sParams);
        Params::unsetParam('sParams');
      }
    }


    $uri_params = Params::getParamsAsArray();
    unset($uri_params['lang']);
    
    $search_url = osc_search_url($uri_params);


// print_r($uri_params);

    // echo $search_url;
    // exit;

    if($this->uri !== 'feed') {
      $current_search_url = WEB_PATH . $this->uri;
      
      // update 420, added strtolower
      // if(urlencode(strip_tags(strtolower(str_replace('+', '', str_replace(' ', '',urldecode($search_url)))))) != urlencode(strip_tags(strtolower(str_replace(' ', '', urldecode($current_search_url)))))) {  // update 420, adding strtolower
      // if (strtolower(str_replace('%20', '+', $search_url)) != strtolower(str_replace('%20', '+', $current_search_url))) {

      // update 830
      // If search url from function does not match to current URL, redirect to search url
      if(osc_clear_compare_url($search_url) != osc_clear_compare_url($current_search_url)) {
        // echo strtolower(str_replace('%20' , '+', $search_url)) . ' ::: ';
        // echo strtolower(str_replace('%20' , '+', $current_search_url));
        // exit;
        
        // print_r($uri_params);
        // echo osc_clear_compare_url($search_url) . ' != ';
        // echo osc_clear_compare_url($current_search_url); 
        // exit;
        
        $this->redirectTo($search_url, 301);
      }
    }



    // CHECK IF IT'S ALERT URL
    // If yes, reset search instance
    $alert_id = (int)Params::getParam('iAlertId');
    $alert_secret = Params::getParam('sAlertSecret');
    $alert_ok = false;
    
    if($alert_id > 0 && $alert_secret != '') {
      $alert = Alerts::newInstance()->findByPrimaryKey($alert_id);
      
      if(isset($alert['s_secret']) && $alert['s_secret'] == $alert_secret) {
        $array_conditions = @json_decode($alert['s_search'], true);
        $array_params = @json_decode($alert['s_param'], true);

        // Check if alert structure is OK
        if(isset($array_conditions['price_min']) && $alert['b_active'] == 1) {
          $alert_ok = true;

          $this->mSearch = Search::newInstance();
          $this->mSearch->setJsonAlert($array_conditions);
          
          if(is_array($array_params) && count($array_params) > 0) {
            $alert_params = $array_params;
            $skip_arr = osc_alert_skip_params();
            
            foreach($alert_params as $pname => $pval) {
              if(!in_array($pname, $skip_arr) && $pval != '') {
                Params::setParam($pname, $pval);
              }
            }
          }
        }
      }
      
      // Check if alert is valid
      if($alert_ok === false) {
        osc_add_flash_error_message(__('Invalid alert ID, non-matching secret, search details or alert in inactive'));
        $this->redirectTo(osc_search_url(array('page' => 'search'))); 
      }
      
      // Params::setParam('iAlertId', null);
      // Params::setParam('sAlertSecret', null);
    }



    // GETTING AND FIXING DATA - We want to have arrays
    $p_sCategory = osc_esc_html(Params::getParam('sCategory'));
    $p_sCategory = is_array($p_sCategory) ? $p_sCategory : ($p_sCategory == '' ? array() : explode(',', $p_sCategory));

    $p_sCityArea = osc_esc_html(Params::getParam('sCityArea'));
    $p_sCityArea = is_array($p_sCityArea) ? $p_sCityArea : ($p_sCityArea == '' ? array() : explode(',', $p_sCityArea));

    $p_sCity = osc_esc_html(Params::getParam('sCity'));
    $p_sCity = is_array($p_sCity) ? $p_sCity : ($p_sCity == '' ? array() : explode(',', $p_sCity));

    $p_sRegion = osc_esc_html(Params::getParam('sRegion'));
    $p_sRegion = is_array($p_sRegion) ? $p_sRegion : ($p_sRegion == '' ? array() : explode(',', $p_sRegion));

    $p_sCountry = osc_esc_html(Params::getParam('sCountry'));
    $p_sCountry = is_array($p_sCountry) ? $p_sCountry : ($p_sCountry == '' ? array() : explode(',', $p_sCountry));

    $p_sUser = osc_esc_html(Params::getParam('sUser'));
    $p_sUser = is_array($p_sUser) ? $p_sUser : ($p_sUser == '' ? '' : explode(',', $p_sUser));

    $p_sLocale = osc_esc_html(Params::getParam('sLocale'));
    $p_sLocale = is_array($p_sLocale) ? $p_sLocale : ($p_sLocale == '' ? '' : explode(',', $p_sLocale));


    $p_sPattern = osc_apply_filter('search_pattern', trim(strip_tags(Params::getParam('sPattern'))));

    // ADD TO THE LIST OF LAST SEARCHES
    if(osc_save_latest_searches() && (!Params::existParam('iPage') || Params::getParam('iPage')==1)) {
      $savePattern = osc_apply_filter('save_latest_searches_pattern', $p_sPattern);
      
      if($savePattern != '') {
        LatestSearches::newInstance()->insert(array('s_search' => $savePattern, 'd_date' => date('Y-m-d H:i:s')));
      }
    }

    $p_bPic = (Params::getParam('bPic') == 1) ? 1 : 0;
    $p_bPremium = (Params::getParam('bPremium') == 1) ? 1 : 0;
    $p_bPhone = (Params::getParam('bPhone') == 1) ? 1 : 0;

    $p_sPriceMin = osc_esc_html(Params::getParam('sPriceMin') > 0 ? (float)Params::getParam('sPriceMin') : '');
    $p_sPriceMax = osc_esc_html(Params::getParam('sPriceMax') > 0 ? (float)Params::getParam('sPriceMax') : '');

    // WE CAN ONLY USE THE FIELDS RETURNED BY Search::getAllowedColumnsForSorting()
    $p_sOrder = osc_esc_html(Params::getParam('sOrder'));
    
    switch($p_sOrder) {
      case osc_get_preference('rewrite_search_order_by_price'):
        $p_sOrder = 'i_price';
        break;
      case osc_get_preference('rewrite_search_order_by_pub_date'):
        $p_sOrder = 'dt_pub_date';
        break;
      case osc_get_preference('rewrite_search_order_by_relevance'):
        $p_sOrder = 'relevance';
        break;
      case osc_get_preference('rewrite_search_order_by_expiration'):
        $p_sOrder = 'dt_expiration';
        break;
      case osc_get_preference('rewrite_search_order_by_rating'):
        $p_sOrder = 'i_rating';   // not implemented yet
        break;
    }
    
    if(!in_array($p_sOrder, Search::getAllowedColumnsForSorting($p_sPattern != '' ? true : false))) {
      $p_sOrder = osc_default_order_field_at_search();
    }
    
    $old_order = $p_sOrder;

    // ONLY 0 (=> 'asc'), 1 (=> 'desc') AS ALLOWED VALUES
    $enabled_sorting_types = Search::getAllowedTypesForSorting();

    $p_iOrderType = osc_default_order_type_at_search();
    $p_sOrderType = isset($enabled_sorting_types[$p_iOrderType]) ? $enabled_sorting_types[$p_iOrderType] : 'desc';
    
    foreach($enabled_sorting_types as $k => $v) {
      if(Params::getParam('iOrderType') == $v || Params::getParam('iOrderType') == $k) {
        $p_iOrderType = $k;     // 0, 1
        $p_sOrderType = $v;     // asc, desc
        break;
      }
    }
    

    $p_sFeed = Params::getParam('sFeed');
    $p_iPage = 0;
    
    if(is_numeric(Params::getParam('iPage')) && Params::getParam('iPage') > 0) {
      $p_iPage = (int) Params::getParam('iPage') - 1;
    }

    $p_sShowAs = Params::getParam('sShowAs');
    $aValidShowAsValues = array('list', 'gallery');
    
    if (!in_array($p_sShowAs, $aValidShowAsValues)) {
      $p_sShowAs = osc_default_show_as_at_search();
    }

    // search results: it's blocked with the maxResultsPerPage@search defined in t_preferences (not for admin)
    $p_iPerPage = (int)Params::getParam('iPerPage');
    
    if($p_iPerPage > 0) {
      if($p_iPerPage > osc_max_results_per_page_at_search() && !osc_is_admin_user_logged_in()) {
        $p_iPerPage = osc_max_results_per_page_at_search();
      }
    } else {
      $p_iPerPage = osc_default_results_per_page_at_search();
    }


    // FILTERING CATEGORY
    $bAllCategoriesChecked = false;
    $successCat = false;
    
    if(count($p_sCategory) > 0) {
      foreach($p_sCategory as $category) {
        $successCat = ($this->mSearch->addCategory($category) || $successCat);
      }
      
    } else {
      $bAllCategoriesChecked = true;
    }

    //FILTERING CITY_AREA
    foreach($p_sCityArea as $city_area) {
      $this->mSearch->addCityArea($city_area);
    }
    
    $p_sCityArea = implode(', ' , $p_sCityArea);

    //FILTERING CITY
    foreach($p_sCity as $city) {
      $this->mSearch->addCity($city);
    }
    
    $p_sCity = implode(', ' , $p_sCity);

    //FILTERING REGION
    foreach($p_sRegion as $region) {
      $this->mSearch->addRegion($region);
    }
    
    $p_sRegion = implode(', ' , $p_sRegion);

    //FILTERING COUNTRY
    foreach($p_sCountry as $country) {
      $this->mSearch->addCountry($country);
    }
    
    $p_sCountry = implode(', ' , $p_sCountry);

    // FILTERING PATTERN
    if($p_sPattern != '') {
      $this->mSearch->addPattern($p_sPattern);
      $osc_request['sPattern'] = $p_sPattern;
      
    } else {
      // hardcoded - if order is by relevance and there isn't a search pattern, order by dt_pub_date desc
      if($p_sOrder == 'relevance') {
        $p_sOrder = 'dt_pub_date';
        $p_iOrderType = 1;
        $p_sOrderType = 'desc';
      }
    }

    // FILTERING USER
    if($p_sUser != '') {
      $this->mSearch->fromUser($p_sUser);
    }

    // FILTERING LOCALE
    $this->mSearch->addLocale($p_sLocale);

    // FILTERING IF WE ONLY WANT ITEMS WITH PICS
    if($p_bPic) {
      $this->mSearch->withPicture(true);
    }

    // FILTERING IF WE ONLY WANT PREMIUM ITEMS
    if($p_bPremium) {
      $this->mSearch->onlyPremium(true);
    }

    // FILTERING IF WE ONLY WANT ITEMS WITH PHONE NUMBER
    if($p_bPhone) {
      $this->mSearch->withPhone(true);
    }

    // FILTERING BY RANGE PRICE
    $this->mSearch->priceRange($p_sPriceMin, $p_sPriceMax);

    // ORDERING THE SEARCH RESULTS
    $this->mSearch->order($p_sOrder, $p_sOrderType);

    // SET PAGE
    if($p_sFeed == 'rss') {
      // If param sFeed=rss, just output last 'osc_num_rss_items()'
      $this->mSearch->page(0, osc_num_rss_items());
    } else {
      $this->mSearch->page($p_iPage, $p_iPerPage);
    }

    // CUSTOM FIELDS
    $custom_fields = Params::getParam('meta');
    $fields = Field::newInstance()->findIDSearchableByCategories($p_sCategory);

    $table = DB_TABLE_PREFIX.'t_item_meta';
    
    if(is_array($custom_fields)) {
      foreach($custom_fields as $key => $aux) {
        if(in_array($key, $fields)) {
          $field = Field::newInstance()->findByPrimaryKey($key);
          
          switch ($field['e_type']) {
            case 'TEXTAREA':
            case 'TEXT':
            case 'URL':
              if($aux!='') {
                $aux = "%$aux%";
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $str_escaped = Search::newInstance()->dao->escape($aux);
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $table . '.s_value LIKE ' . $str_escaped;
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql.')');
              }
              break;
              
            case 'DROPDOWN':
            case 'RADIO':
              if($aux!='') {
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $str_escaped = Search::newInstance()->dao->escape($aux);
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $table . '.s_value = ' . $str_escaped;
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql.')');
              }
              break;
              
            case 'CHECKBOX':
              if($aux!='') {
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $table . '.s_value = 1';
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql.')');
              }
              break;
              
            case 'DATE':
              if($aux!='') {
                $y = (int)date('Y', $aux);
                $m = (int)date('n', $aux);
                $d = (int)date('j', $aux);
                $start = mktime('0', '0', '0', $m, $d, $y);
                $end = mktime('23', '59', '59', $m, $d, $y);
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $table . '.s_value >= ' . $start . ' AND ';
                $sql .= $table . '.s_value <= ' . $end;
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql.')');
              }
              break;
              
            case 'DATEINTERVAL':
              if(is_array($aux) && (!empty($aux['from']) && !empty($aux['to']))) {
                $from = $aux['from'];
                $to = $aux['to'];
                $start = $from;
                $end = $to;
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $start . ' >= ' . $table . ".s_value AND s_multi = 'from'";
                $sql1 = "SELECT fk_i_item_id FROM $table WHERE ";
                $sql1 .= $table . '.fk_i_field_id = ' . $key . ' AND ';
                $sql1 .= $end . ' <= ' . $table . ".s_value AND s_multi = 'to'";
                $sql_interval = 'select a.fk_i_item_id from (' . $sql . ') a where a.fk_i_item_id IN (' . $sql1 . ')';
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql_interval.')');
              }
              break;
              
            default:
              break;
          }

        }
      }
    }

    osc_run_hook('search_conditions', Params::getParamsAsArray());


    // Reset search conditions to avoid clash with regular params
    // if($alert_ok === true) {
      // $this->mSearch = Search::newInstance();
      // $this->mSearch->setJsonAlert($array_conditions);
    // }
    

    // RETRIEVE CACHE, ITEMS AND TOTAL
    $key = md5(osc_base_url() . $this->mSearch->toJson(false, false));

    $found = null;
    $cache = osc_cache_get($key, $found);

    $aItems = null;
    $iTotalItems = null;
    
    if($cache && $alert_ok === false) {
      $aItems = $cache['aItems'];
      $iTotalItems = $cache['iTotalItems'];
      
    } else {
      $aItems = $this->mSearch->doSearch();
      $iTotalItems = $this->mSearch->count();
      $_cache['aItems'] = $aItems;
      $_cache['iTotalItems'] = $iTotalItems;

      osc_cache_set($key , $_cache , OSC_CACHE_TTL);
    }
    
    $aItems = osc_apply_filter('pre_show_items', $aItems);

    $iStart = $p_iPage * $p_iPerPage;
    $iEnd = min(($p_iPage+1) * $p_iPerPage, $iTotalItems);
    $iNumPages = ceil($iTotalItems / $p_iPerPage);
    
    $use_native = (osc_get_current_user_locations_native() == 1 ? true : false);

    $prev_url = '';
    $next_url = '';
    
    // Generate prev URL
    if(($p_iPage+1) > 1 && $iNumPages > 1) {
      $uri_params_prev = $uri_params;
      $uri_params_prev['iPage'] = ($p_iPage+1) - 1;
      
      $prev_url = osc_search_url($uri_params_prev);
    }
    
    // Generate next URL
    if(($p_iPage+1) < $iNumPages) {
      $uri_params_next = $uri_params;
      $uri_params_next['iPage'] = ($p_iPage+1) + 1;
      
      $next_url = osc_search_url($uri_params_next);
    }


    // Works with cache enabled ?
    osc_run_hook('search', $this->mSearch);


    // Prepare variables for export
    $country_code = null;
    $country_name = $p_sCountry;
    $country_row = array();
    
    if(strlen($p_sCountry) == 2) {
      $c = osc_get_country_row($p_sCountry);
      
      if($c) {
        $country_code = $c['pk_c_code'];
        $country_name = (($use_native && $c['s_name_native'] <> '') ? $c['s_name_native'] : $c['s_name']);
        $country_row = $c;
      }
    }
    
    $region_id = null;
    $region_name = $p_sRegion;
    $region_row = array();
    
    if(is_numeric($p_sRegion)) {
      $r = osc_get_region_row($p_sRegion);
      
      if($r) {
        $region_id = $r['pk_i_id'];
        $region_name = (($use_native && $r['s_name_native'] <> '') ? $r['s_name_native'] : $r['s_name']);
        $region_row = $r;
      }
    }
    
    $city_id = null;
    $city_name = $p_sCity;
    $city_row = array();
    
    if(is_numeric($p_sCity)) {
      $c = osc_get_city_row($p_sCity);
      
      if($c) {
        $city_id = $c['pk_i_id'];
        $city_name = (($use_native && $c['s_name_native'] <> '') ? $c['s_name_native'] : $c['s_name']);
        $city_row = $c;
      }
    }

    $this->_exportVariableToView('search_start', $iStart);
    $this->_exportVariableToView('search_end', $iEnd);
    $this->_exportVariableToView('search_category', $p_sCategory);
    
    // hardcoded - non pattern and order by relevance
    $p_sOrder = $old_order;
    $this->_exportVariableToView('search_order_type', $p_iOrderType);
    $this->_exportVariableToView('search_order_type_name', $p_sOrderType);
    $this->_exportVariableToView('search_order', $p_sOrder);
    $this->_exportVariableToView('search_pattern', $p_sPattern);
    $this->_exportVariableToView('search_from_user', $p_sUser);
    $this->_exportVariableToView('search_total_pages', $iNumPages);
    $this->_exportVariableToView('search_page', $p_iPage);
    $this->_exportVariableToView('search_has_pic', $p_bPic);
    $this->_exportVariableToView('search_only_premium', $p_bPremium);
    $this->_exportVariableToView('search_with_phone', $p_bPhone);
    $this->_exportVariableToView('search_country_code', $country_code);
    $this->_exportVariableToView('search_country', $country_name);
    $this->_exportVariableToView('search_country_row', $country_row);
    $this->_exportVariableToView('search_region_id', $region_id);
    $this->_exportVariableToView('search_region', $region_name);
    $this->_exportVariableToView('search_region_row', $region_row);
    $this->_exportVariableToView('search_city_id', $city_id);
    $this->_exportVariableToView('search_city', $city_name);
    $this->_exportVariableToView('search_city_row', $city_row);
    $this->_exportVariableToView('search_price_min', $p_sPriceMin);
    $this->_exportVariableToView('search_price_max', $p_sPriceMax);
    $this->_exportVariableToView('search_total_items', $iTotalItems);
    $this->_exportVariableToView('search_prev_url', $prev_url);
    $this->_exportVariableToView('search_next_url', $next_url);
    $this->_exportVariableToView('items', $aItems);
    $this->_exportVariableToView('search_show_as', $p_sShowAs);
    $this->_exportVariableToView('search', $this->mSearch);


    // Get current search params into Json for Alert subscription
    $json = $this->mSearch->toJson();
    $json_arr = @json_decode($json, true);
    
    $json_params = Params::getParamsAsArray();
    $skip_arr = osc_alert_skip_params();

    foreach($skip_arr as $sparam) {
      unset($json_params[$sparam]);
    }
    
    ksort($json_params);
    
    $json_arr['params'] = (is_array($json_params) ? $json_params : array());
    $json_arr['sql'] = $this->mSearch->exportSQL();
    $json_with_params = json_encode($json_arr);
    
    $encoded_alert = base64_encode(osc_encrypt_alert($json_with_params));

    // Create the HMAC signature and convert the resulting hex hash into base64
    $stringToSign = osc_get_alert_public_key() . $encoded_alert;
    $signature = hex2b64(hmacsha1(osc_get_alert_private_key(), $stringToSign));
    $server_signature = Session::newInstance()->_set('alert_signature', $signature);

    $this->_exportVariableToView('search_alert', $encoded_alert);     // passed to alert form
    $alerts_sub = 0;
    
    // Check if alert exist. Only use s_search part for check, not params or sql that can be dynamic
    if(osc_is_web_user_logged_in()) {
      $alerts = Alerts::newInstance()->findBySearchAndUser($json, osc_logged_user_id());      // search without params!
      
      if(count($alerts)>0) {
        $alerts_sub = 1;
      }
    }
    
    $this->_exportVariableToView('search_alert_subscribed', $alerts_sub);

    // Calling the view...
    if(count($aItems) === 0) {
      header('HTTP/1.1 404 Not Found');
    }

    osc_run_hook('after_search');


    // Run RSS code
    if(!Params::existParam('sFeed')) {
      $this->doView('search.php');
      
    } else if(osc_rss_enabled()) {
      if($p_sFeed == '' || $p_sFeed=='rss') {
        header('Content-type: text/xml; charset=utf-8');

        $feed = new RSSFeed;
        $feed->setTitle(__('Latest listings added') . ' - ' . osc_page_title());
        $feed->setLink(osc_base_url());
        $feed->setDescription(__('Latest listings added in') . ' ' . osc_page_title());

        if(osc_count_items()>0) {
          while(osc_has_items()) {
            $itemArray = array (
              'id' => osc_item_id(),
              'title' => osc_item_title(),
              'link' => htmlentities(osc_item_url(), ENT_COMPAT, 'UTF-8'),
              'description' => osc_item_description(),
              'country' => osc_item_country(),
              'region' => osc_item_region(),
              'city' => osc_item_city(),
              'city_area' => osc_item_city_area(),
              'address' => osc_item_address(),
              'zip' => osc_item_zip(),
              'category' => osc_item_category(),
              'price' => osc_item_price()/1000000,
              'price_formatted' => osc_item_formated_price(),
              'currency' => osc_item_currency(),
              'rating' => osc_count_item_comments_rating(),
              'contact_name' => osc_item_contact_name(),
              'contact_email' => osc_item_contact_email(),
              'dt_pub_date' => osc_item_pub_date(),
              'dt_mod_date' => osc_item_mod_date()
            );


            if(osc_count_item_resources() > 0) {
              osc_has_item_resources();
              
              $itemArray['image'] = array (
                'url' => htmlentities(osc_resource_thumbnail_url(), ENT_COMPAT, 'UTF-8'),
                'title' => htmlentities(osc_item_title()),
                'link' => htmlentities(osc_item_url(), ENT_COMPAT, 'UTF-8')
              );

              osc_get_item_resources();
              osc_reset_resources();
              
              $max_images = 10;
              $itemArray['images'] = array();
              
              for($i = 0;osc_has_item_resources(); $i++) {
                $itemArray['images'][] = array(
                  'id' => osc_resource_id(),
                  'name' => osc_resource_name(),
                  'type' => osc_resource_type(),
                  'extension' => osc_resource_extension(),
                  'path' => osc_resource_path(),
                  'thumbnail_url' => htmlentities(osc_resource_thumbnail_url(), ENT_COMPAT, 'UTF-8'),
                  'preview_url' => htmlentities(osc_resource_preview_url(), ENT_COMPAT, 'UTF-8'),
                  'normal_url' => htmlentities(osc_resource_url(), ENT_COMPAT, 'UTF-8'),
                  'original_url' => htmlentities(osc_resource_original_url(), ENT_COMPAT, 'UTF-8'),
                  'title' => htmlentities(osc_item_title() . ' / ' . ($i + 1)),
                  'link' => htmlentities(osc_item_url(), ENT_COMPAT, 'UTF-8')
                );
                
                if($i >= $max_images) {
                  break;
                }
              }
            }
            
            $feed->addItem($itemArray);
          }
        }

        osc_run_hook('feed', $feed);
        $feed->dumpXML();
        
      } else {
        osc_run_hook('feed_' . $p_sFeed, $aItems);
      }
      
    } else {
      $this->do404();     // RSS not enabled
    }
  }

  //hopefully generic...

  /**
   * @param $file
   *
   * @return mixed|void
   */
  public function doView($file) {
    osc_run_hook('before_html');
    osc_current_web_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook('after_html');
  }
}

/* file end: ./search.php */
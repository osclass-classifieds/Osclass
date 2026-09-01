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
 *
 */
class Search extends DAO {
  /**
   *
   * @var
   */
  private $conditions;
  private $itemConditions;
  private $tables;
  private $tables_join; // ?
  private $sql;
  private $order_column;
  private $order_direction;
  private $limit_init;
  private $results_per_page;
  private $cities;
  private $zips;
  private $city_areas;
  private $regions;
  private $countries;
  private $categories;
  private $search_fields;
  private $total_results;
  private $total_results_table;
  private $sPattern;
  private $sEmail;
  private $groupBy;
  private $having;
  private $locale_code;
  private $withPattern;
  private $withPicture;
  private $withLocations;
  private $withCategoryId;
  private $withUserId;
  private $withItemId;
  private $withNoUserEmail;
  private $onlyPremium;
  private $withPhone;
  private $price_min;
  private $price_max;
  private $user_ids;
  private $itemId;
  private $userTableLoaded;
  private $stats;
  private $withSubdomainFilter;

  private static $instance;

  /**
   * @return \Search
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    
    return self::$instance;
  }


  /**
   * @param bool $expired
   */
  public function __construct($expired = false, $stats = false, $subdomain_filter = true) {
    parent::__construct();
    $this->setTableName('t_item');
    $this->setFields(array('pk_i_id'));

    $this->withPattern = false;
    $this->withLocations = false;
    $this->withCategoryId = false;
    $this->withUserId = false;
    $this->withPicture = false;
    $this->withNoUserEmail = false;
    $this->onlyPremium = false;
    $this->withPhone = false;
    $this->price_min = null;
    $this->price_max = null;
    $this->user_ids = null;
    $this->itemId = null;
    $this->userTableLoaded = false;
    $this->zips = array();
    $this->city_areas = array();
    $this->cities = array();
    $this->regions = array();
    $this->countries = array();
    $this->categories = array();
    $this->conditions = array();
    $this->tables = array();
    $this->tables_join = array();
    $this->search_fields = array();
    $this->itemConditions = array();
    $this->locale_code = array();
    $this->groupBy = '';
    $this->having = '';
    $this->stats = ($stats === true ? true : false);

    $this->order();
    $this->limit();
    $this->results_per_page = 12;
    $this->withSubdomainFilter = false;

    // Include only active items
    if($expired === false) {
      $this->addItemConditions(sprintf('%st_item.b_enabled = 1 ', DB_TABLE_PREFIX));
      $this->addItemConditions(sprintf('%st_item.b_active = 1 ', DB_TABLE_PREFIX));
      $this->addItemConditions(sprintf('%st_item.b_spam = 0', DB_TABLE_PREFIX));
      $this->addItemConditions(sprintf("(%st_item.b_premium = 1 || %st_item.dt_expiration >= '%s')", DB_TABLE_PREFIX, DB_TABLE_PREFIX, date('Y-m-d H:i:s')));
    }
    
    // If not explicitely disabled, apply subdomain filter
    if($subdomain_filter === true && $expired === false && osc_subdomain_enabled() && osc_is_subdomain() && osc_subdomain_param() != '' && osc_subdomain_id() != '' && osc_subdomain_type() != 'language') {
      if(osc_is_frontoffice() && !defined('__FROM_CRON__')) {
        if(osc_apply_filter('search_subdomain_filter', true, $this) === true) {
          $this->withSubdomainFilter = true;
          
          switch(osc_subdomain_param()) {
            case 'sCategory': $this->addCategory(osc_subdomain_id()); break;
            case 'sCountry': $this->addCountry(osc_subdomain_id()); break;
            case 'sRegion': $this->addRegion(osc_subdomain_id()); break;
            case 'sCity': $this->addCity(osc_subdomain_id()); break;
            case 'sUser': $this->fromUser(osc_subdomain_id()); break;
          }
        }
      }
    }


    $this->total_results = null;
    $this->total_results_table = null;

    // get all item_location data
    if(OC_ADMIN) {
      $this->addField(sprintf('%st_item_location.*', DB_TABLE_PREFIX));
    }
  }


  /**
   * Return an array with columns allowed for sorting
   *
   * @return array
   */
  public static function getAllowedColumnsForSorting($extended = false) {
    $data = array('i_price', 'dt_pub_date');
    
    if($extended === true) {
      $data[] = 'dt_expiration';
      $data[] = 'i_rating';
      $data[] = 'relevance';
    }
    
    return osc_apply_filter('search_list_columns', $data);
  }


  /**
   * Return an array with of sorting
   *
   * @return array
   */
  public static function getAllowedTypesForSorting($extended = false) {
    $data = array(0 => 'asc', 1 => 'desc');
    
    if($extended === true) {
      $data[2] = 'rand()';
    }

    return osc_apply_filter('search_list_types', $data);
  }

  /**
   * Return if subdomain filter enabled
   *
   * @return boolean
   */
  public static function getWithSubdomainFilter() {
    return $this->withSubdomainFilter;
  }



  // juanramon: little hack to get alerts work in search layout
  public function reconnect() {
    // $this->conn = getConnection();
  }


  /**
   * Add conditions to the search
   *
   * @access public
   * @since unknown
   * @param mixed $conditions
   */
  public function addConditions($conditions) {
    if(is_array($conditions)) {
      foreach($conditions as $condition) {
        $condition = trim((string)$condition);
        
        if($condition!='') {
          if(!in_array($condition, $this->conditions)) {
            $this->conditions[] = $condition;
          }
        }
      }
      
    } else {
      $conditions = trim((string)$conditions);
      
      if($conditions!='') {
        if(!in_array($conditions, $this->conditions)) {
          $this->conditions[] = $conditions;
        }
      }
    }
  }
  

  /**
   * Add item conditions to the search
   *
   * @access public
   * @since unknown
   * @param mixed $conditions
   */
  public function addItemConditions($conditions) {
    if(is_array($conditions)) {
      foreach($conditions as $condition) {
        $condition = trim((string)$condition);
        
        if($condition!='') {
          if(!in_array($condition, $this->itemConditions)) {
            $this->itemConditions[] = $condition;
          }
        }
      }
      
    } else {
      $conditions = trim((string)$conditions);
      
      if($conditions!='') {
        if(!in_array($conditions, $this->itemConditions)) {
          $this->itemConditions[] = $conditions;
        }
      }
    }
  }
  

  /**
   * Add locale conditions to the search
   *
   * @access public
   * @since 3.2
   * @param string $locale
   */
  public function addLocale($locale) {
    if(is_array($locale)) {
      foreach($locale as $l) {
        if($l!='') {
          $this->locale_code[$l] = $l;
        }
      }
      
    } else {
      if($locale!='') {
        $this->locale_code[$locale] = $locale;
      }
    }
  }
  

  /**
   * Add new fields to the search
   *
   * @access public
   * @since unknown
   * @param mixed $fields
   */
  public function addField($fields) {
    if(is_array($fields)) {
      foreach($fields as $field) {
        $field = trim((string)$field);
        
        if($field!='') {
          if(!in_array($field, $this->fields)) {
            $this->search_fields[] = $field;
          }
        }
      }
      
    } else {
      $fields = trim((string)$fields);
      if($fields!='') {
        if(!in_array($fields, $this->fields)) {
          $this->search_fields[] = $fields;
        }
      }
    }
  }


  /**
   * Add extra table to the search
   *
   * @access public
   * @since unknown
   * @param mixed $tables
   */
  public function addTable($tables) {
    if(is_array($tables)) {
      foreach($tables as $table) {
        $table = trim((string)$table);
        
        if($table!='') {
          if(!in_array($table, $this->tables)) {
            $this->tables[] = $table;
          }
        }
      }
      
    } else {
      $tables = trim((string)$tables);
      if($tables!='') {
        if(!in_array($tables, $this->tables)) {
          $this->tables[] = $tables;
        }
      }
    }
  }


  /**
   * Add group by to the search
   *
   * @access public
   * @since  unknown
   *
   * @param $groupBy
   */
  public function addGroupBy($groupBy) {
    $this->groupBy = $groupBy;
  }


  /**
   * Establish the order of the search
   *
   * @access public
   * @since unknown
   * @param string $o_c column
   * @param string $o_d direction
   * @param string $table
   */
  public function order($o_c = 'dt_pub_date', $o_d = 'DESC', $table = NULL) {
    if($table == '') {
      $this->order_column = $o_c;
      
    } else if($table != ''){
      if($table === '%st_user') {
        $this->order_column = sprintf("ISNULL($table.$o_c), $table.$o_c", DB_TABLE_PREFIX, DB_TABLE_PREFIX);
      } else {
        $this->order_column = sprintf("$table.$o_c", DB_TABLE_PREFIX);
      }
    }
    
    $this->order_direction = $o_d;
  }


  /**
   * Limit the results of the search
   *
   * @access public
   * @since  unknown
   *
   * @param int  $l_i
   * @param null $r_p_p
   */
  public function limit($l_i = 0, $r_p_p = null) {
    $this->limit_init = $l_i;
    
    if($r_p_p!=null) { 
      $this->results_per_page = $r_p_p; 
    }
  }


  /**
   * Limit the results of the search
   *
   * @access public
   * @since  unknown
   * @param $r_p_p
   */
  public function set_rpp($r_p_p) {
    $this->results_per_page = $r_p_p;
  }
  

  /**
   * Select the page of the search
   *
   * @access public
   * @since  unknown
   * @param int  $p page
   * @param null $r_p_p
   */
  public function page($p = 0, $r_p_p = null) {
    if($r_p_p!=null) { 
      $this->results_per_page = $r_p_p; 
    }
    
    $this->limit_init = $this->results_per_page*$p;
  }


  /**
   * Add ZIP to the search
   *
   * @access public
   * @since unknown
   * @param mixed $zip
   */
  public function addZip($zip = array()) {
    if(is_array($zip)) {
      foreach($zip as $z) {
        $z = trim((string)$z);
        
        if($z != '') {
          $this->zips[] = sprintf("%st_item_location.s_zip LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($z));
        }
      }
      
    } else {
      $zip = trim((string)$zip);
      
      if($zip != '') {
        $this->zips[] = sprintf("%st_item_location.s_zip LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($zip));
      }
    }
  }


  /**
   * Remove ZIP from search
   *
   * @access public
   * @since unknown
   * @param mixed $zip
   */
  public function removeZip($zip = array(), $direct = false) {
    if(is_array($zip)) {
      foreach($zip as $z) {
        $z = trim((string)$z);
        
        if($z != '') {
          if($direct) {
            $drop_elem = $z;
          } else {
            $drop_elem = sprintf("%st_item_location.s_zip LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($z));
          }
          
          if(isset($this->zips[$drop_elem])) {
            unset($this->zips[$drop_elem]);
          }
        }
      }
      
    } else {
      $zip = trim((string)$zip);
      
      if($zip != '') {
        if($direct) {
          $drop_elem = $zip;
        } else {
          $drop_elem = sprintf("%st_item_location.s_zip LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($zip));
        }
        
        if(isset($this->zips[$drop_elem])) {
          unset($this->zips[$drop_elem]);
        }
      }
    }
  }


  /**
   * Remove all ZIPs from search
   *
   * @access public
   * @since unknown
   * @param None
   */
  public function removeZipAll() {
    $this->zips = array();
  }

  
  
  /**
   * Add city areas to the search
   *
   * @access public
   * @since unknown
   * @param mixed $city_area
   */
  public function addCityArea($city_area = array()) {
    if(is_array($city_area)) {
      foreach($city_area as $c) {
        $c = trim((string)$c);
        
        if($c!='') {
          if(is_numeric($c)) {
            $this->city_areas[] = sprintf('%st_item_location.fk_i_city_area_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($c));
          } else {
            $this->city_areas[] = sprintf("%st_item_location.s_city_area LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($c));
          }
        }
      }
      
    } else {
      $city_area = trim((string)$city_area);
      
      if($city_area != '') {
        if(is_numeric($city_area)) {
          $this->city_areas[] = sprintf('%st_item_location.fk_i_city_area_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($city_area));
        } else {
          $this->city_areas[] = sprintf("%st_item_location.s_city_area LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($city_area));
        }
      }
    }
  }


  /**
   * Remove City Area from search
   *
   * @access public
   * @since unknown
   * @param mixed $city_area
   */
  public function removeCityArea($city_area = array(), $direct = false) {
    if(is_array($city_area)) {
      foreach($city_area as $c) {
        $c = trim((string)$c);
        
        if($c != '') {
          if($direct) {
            $drop_elem = $c;
          } else {
            if(is_numeric($c)) {
              $drop_elem = sprintf('%st_item_location.fk_i_city_area_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($c));
            } else {
              $drop_elem = sprintf("%st_item_location.s_city_area LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($c));
            }
          }
          
          if(isset($this->city_areas[$drop_elem])) {
            unset($this->city_areas[$drop_elem]);
          }
        }
      }
      
    } else {
      $city_area = trim((string)$city_area);
      if($city_area != '') {
        if($direct) {
          $drop_elem = $city_area;
        } else {
          if(is_numeric($city_area)) {
            $drop_elem = sprintf('%st_item_location.fk_i_city_area_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($city_area));
          } else {
            $drop_elem = sprintf("%st_item_location.s_city_area LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($city_area));
          }
        }
        
        if(isset($this->city_areas[$drop_elem])) {
          unset($this->city_areas[$drop_elem]);
        }
      }
    }
  }


  /**
   * Remove all City Areas from search
   *
   * @access public
   * @since unknown
   * @param None
   */
  public function removeCityAreaAll() {
    $this->city_areas = array();
  }
  

  /**
   * Add cities to the search
   *
   * @access public
   * @since unknown
   * @param mixed $city
   */
  public function addCity($city = array()) {
    if(is_array($city)) {
      foreach($city as $c) {
        $c = trim((string)$c);
        
        if($c!='') {
          if(is_numeric($c)) {
            $this->cities[] = sprintf('%st_item_location.fk_i_city_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($c));
          } else {
            $this->cities[] = sprintf("(%st_item_location.s_city LIKE '%s' OR %st_item_location.s_city_native LIKE '%s') ", DB_TABLE_PREFIX, $this->dao->escapeStr($c), DB_TABLE_PREFIX, $this->dao->escapeStr($c));
          }
        }
      }
    } else {
      $city = trim((string)$city);
      
      if($city != '') {
        if(is_numeric($city)) {
          $this->cities[] = sprintf('%st_item_location.fk_i_city_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($city));
        } else {
          $this->cities[] = sprintf("(%st_item_location.s_city LIKE '%s' OR %st_item_location.s_city_native LIKE '%s') ", DB_TABLE_PREFIX, $this->dao->escapeStr($city), DB_TABLE_PREFIX, $this->dao->escapeStr($city));
        }
      }
    }
  }
  

  /**
   * Remove City from search
   *
   * @access public
   * @since unknown
   * @param mixed $city
   */
  public function removeCity($city = array(), $direct = false) {
    if(is_array($city)) {
      foreach($city as $c) {
        $c = trim((string)$c);
        
        if($c != '') {
          if($direct) {
            $drop_elem = $c;
          } else {
            if(is_numeric($c)) {
              $drop_elem = sprintf('%st_item_location.fk_i_city_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($c));
            } else {
              $drop_elem = sprintf("%st_item_location.s_city LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($c));
            }
          }
          
          if(isset($this->cities[$drop_elem])) {
            unset($this->cities[$drop_elem]);
          }
        }
      }
      
    } else {
      $city = trim((string)$city);
      
      if($city != '') {
        if($direct) {
          $drop_elem = $city;
        } else {
          if(is_numeric($city)) {
            $drop_elem = sprintf('%st_item_location.fk_i_city_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($city));
          } else {
            $drop_elem = sprintf("%st_item_location.s_city LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($city));
          }
        }
        
        if(isset($this->cities[$drop_elem])) {
          unset($this->cities[$drop_elem]);
        }
      }
    }
  }


  /**
   * Remove all Cities from search
   *
   * @access public
   * @since unknown
   * @param None
   */
  public function removeCityAll() {
    $this->cities = array();
  }
  
  
  /**
   * Add regions to the search
   *
   * @access public
   * @since unknown
   * @param mixed $region
   */
  public function addRegion($region = array()) {
    if(is_array($region)) {
      foreach($region as $r) {
        $r = trim((string)$r);
        
        if($r != '') {
          if(is_numeric($r)) {
            $this->regions[] = sprintf('%st_item_location.fk_i_region_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($r));
          } else {
            $this->regions[] = sprintf("(%st_item_location.s_region LIKE '%s' OR %st_item_location.s_region_native LIKE '%s') ", DB_TABLE_PREFIX, $this->dao->escapeStr($r), DB_TABLE_PREFIX, $this->dao->escapeStr($r));
          }
        }
      }
      
    } else {
      $region = trim((string)$region);
      
      if($region != '') {
        if(is_numeric($region)) {
          $this->regions[] = sprintf('%st_item_location.fk_i_region_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($region));
        } else {
          $this->regions[] = sprintf("(%st_item_location.s_region LIKE '%s' OR %st_item_location.s_region_native LIKE '%s') ", DB_TABLE_PREFIX, $this->dao->escapeStr($region), DB_TABLE_PREFIX, $this->dao->escapeStr($region));
        }
      }
    }
  }


  /**
   * Remove Region from search
   *
   * @access public
   * @since unknown
   * @param mixed $region
   */
  public function removeRegion($region = array(), $direct = false) {
    if(is_array($region)) {
      foreach($region as $r) {
        $r = trim((string)$r);
        
        if($r != '') {
          if($direct) {
            $drop_elem = $r;
          } else {
            if(is_numeric($r)) {
              $drop_elem = sprintf('%st_item_location.fk_i_region_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($r));
            } else {
              $drop_elem = sprintf("%st_item_location.s_region LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($r));
            }
          }
          
          if(isset($this->regions[$drop_elem])) {
            unset($this->regions[$drop_elem]);
          }
        }
      }
      
    } else {
      $region = trim((string)$region);
      
      if($region != '') {
        if($direct) {
          $drop_elem = $region;
        } else {
          if(is_numeric($region)) {
            $drop_elem = sprintf('%st_item_location.fk_i_region_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($region));
          } else {
            $drop_elem = sprintf("%st_item_location.s_region LIKE '%s' ", DB_TABLE_PREFIX, $this->dao->escapeStr($region));
          }
        }
        
        if(isset($this->regions[$drop_elem])) {
          unset($this->regions[$drop_elem]);
        }
      }
    }
  }


  /**
   * Remove all Regions from search
   *
   * @access public
   * @since unknown
   * @param None
   */
  public function removeRegionAll() {
    $this->regions = array();
  }
  

  /**
   * Add countries to the search
   *
   * @access public
   * @since unknown
   * @param mixed $country
   */
  public function addCountry($country = array()) {
    if(is_array($country)) {
      foreach($country as $c) {
        $c = trim((string)$c);
        
        if($c != '') {
          if(strlen($c) == 2) {
            $this->countries[] = sprintf("%st_item_location.fk_c_country_code = '%s' ", DB_TABLE_PREFIX, strtolower($this->dao->escapeStr($c)));
          } else {
            $this->countries[] = sprintf("(%st_item_location.s_country LIKE '%s' OR %st_item_location.s_country_native LIKE '%s') ", DB_TABLE_PREFIX, $this->dao->escapeStr($c), DB_TABLE_PREFIX, $this->dao->escapeStr($c));
          }
        }
      }
      
    } else {
      $country = trim((string)$country);
      
      if($country != '') {
        if(strlen($country) == 2) {
          $this->countries[] = sprintf("%st_item_location.fk_c_country_code = '%s' ", DB_TABLE_PREFIX, strtolower($this->dao->escapeStr($country)));
        } else {
          $this->countries[] = sprintf("(%st_item_location.s_country LIKE '%s' OR %st_item_location.s_country_native LIKE '%s') ", DB_TABLE_PREFIX, $this->dao->escapeStr($country), DB_TABLE_PREFIX, $this->dao->escapeStr($country));
        }
      }
    }
  }


  /**
   * Remove Country from search
   *
   * @access public
   * @since unknown
   * @param mixed $country
   */
  public function removeCountry($country = array(), $direct = false) {
    if(is_array($country)) {
      foreach($country as $c) {
        $c = trim((string)$c);
        
        if($c != '') {
          if($direct) {
            $drop_elem = $c;
          } else {
            if(strlen($c) == 2) {
              $drop_elem = sprintf("%st_item_location.fk_c_country_code = '%s' ", DB_TABLE_PREFIX, strtolower($this->dao->escapeStr($c)));
            } else {
              $drop_elem = sprintf("(%st_item_location.s_country LIKE '%s' OR %st_item_location.s_country_native LIKE '%s') ", DB_TABLE_PREFIX, $this->dao->escapeStr($c), DB_TABLE_PREFIX, $this->dao->escapeStr($c));
            }
          }
          
          if(isset($this->countries[$drop_elem])) {
            unset($this->countries[$drop_elem]);
          }
        }
      }
      
    } else {
      $country = trim((string)$country);
      
      if($country != '') {
        if($direct) {
          $drop_elem = $country;
        } else {
          if(strlen($country) == 2) {
            $drop_elem = sprintf("%st_item_location.fk_c_country_code = '%s' ", DB_TABLE_PREFIX, strtolower($this->dao->escapeStr($country)));
          } else {
            $drop_elem = sprintf("(%st_item_location.s_country LIKE '%s' OR %st_item_location.s_country_native LIKE '%s') ", DB_TABLE_PREFIX, $this->dao->escapeStr($country), DB_TABLE_PREFIX, $this->dao->escapeStr($country));
          }
        }
        
        if(isset($this->countries[$drop_elem])) {
          unset($this->countries[$drop_elem]);
        }
      }
    }
  }


  /**
   * Remove all Countries from search
   *
   * @access public
   * @since unknown
   * @param None
   */
  public function removeCountryAll() {
    $this->countries = array();
  }
  
  
  /**
   * Establish price range
   *
   * @access public
   * @since unknown
   * @param int $price_min
   * @param int $price_max
   */
  public function priceRange($price_min = 0, $price_max = 0) {
    $this->price_min = 1000000*((int)$price_min);
    $this->price_max = 1000000*((int)$price_max);
  }
  

  private function _priceRange() {
    if(is_numeric($this->price_min) && $this->price_min!=0) {
      $this->dao->where(sprintf('i_price >= %0.0f', $this->price_min));
    }
    if(is_numeric($this->price_max) && $this->price_max>0) {
      $this->dao->where(sprintf('i_price <= %0.0f', $this->price_max));
    }
  }

  /**
   * Establish max price
   *
   * @access public
   * @since unknown
   * @param int $price
   */
  public function priceMax($price) {
    $this->priceRange(null, $price);
  }


  /**
   * Establish min price
   *
   * @access public
   * @since unknown
   * @param int $price
   */
  public function priceMin($price) {
    $this->priceRange($price, null);
  }


  /**
   * Set having sentence to sql
   *
   * @param $having
   */
  public function addHaving($having) {
    $this->having = $having;
  }


  /**
   * Filter by ad with picture or not
   *
   * @access public
   * @since unknown
   * @param bool $pic
   */
  public function withPicture($pic = false) {
    $this->withPicture = $pic;
  }


  /**
   * Add location table to search
   *
   * @access public
   * @since unknown
   * @param bool $with_loc
   */
  public function withLocations($with_loc = false) {
    $this->withLocations = $with_loc;
  }
  

  /**
   * Filter by premium ad status
   *
   * @access public
   * @since 3.2
   * @param bool $premium
   */
  public function onlyPremium($premium = false) {
    $this->onlyPremium = $premium;
  }

  /**
   * Filter by contact phone
   *
   * @access public
   * @since 8.3
   * @param bool $phone
   */
  public function withPhone($phone = false) {
    $this->withPhone = $phone;
  }
  
  /**
   * Set with pattern to true to include item description tables
   *
   * @access public
   * @since 8.3
   * @param bool $phone
   */
  public function withPattern($with_pattern = false) {
    $this->withPattern = $with_pattern;
  }

  /**
   * Filter by search pattern
   *
   * @access public
   * @since 2.4
   * @param string $pattern
   */
  public function addPattern($pattern) {
    $this->withPattern = true;
    $pattern = trim((string)$this->dao->escapeStr($pattern));

    if($pattern != '') {
      $this->sPattern = $pattern;
    }
  }


  /**
   * Filter by email
   *
   * @access public
   * @since  2.4
   * @param $email
   */
  public function addContactEmail($email) {
    $this->withNoUserEmail = true;
    $this->sEmail = $email;
  }


  /**
   * Return ads from specified users
   *
   * @access public
   * @since unknown
   * @param mixed $id
   */
  public function fromUser($id = NULL) {
    if(is_array($id)) {
      $this->withUserId = true;
      $ids = array();
      
      foreach($id as $_id) {
        if(!is_numeric($_id)) {
          $user = User::newInstance()->findByUsername($_id);
          
          if(isset($user['pk_i_id'])) {
            $ids[] = sprintf('%st_item.fk_i_user_id = %d ', DB_TABLE_PREFIX, $this->dao->escapeStr($user['pk_i_id']));
          }
          
        } else {
          $ids[] = sprintf('%st_item.fk_i_user_id = %d ', DB_TABLE_PREFIX, $_id);
        }
      }
      
      $this->user_ids = $ids;
      
    } else {
      $this->withUserId = true;
      
      if(!is_numeric($id)) {
        $user = User::newInstance()->findByUsername($id);
        
        if(isset($user['pk_i_id'])) {
          $this->user_ids = $this->dao->escapeStr($user['pk_i_id']);
        }
        
      } else {
        $this->user_ids = $this->dao->escapeStr($id);
      }
    }
  }

  private function _fromUser() {
    $this->_loadUserTable();
    $this->dao->where(sprintf('%st_user.pk_i_id = %st_item.fk_i_user_id',DB_TABLE_PREFIX,DB_TABLE_PREFIX));

    if(is_array($this->user_ids)) {
      $this->dao->where(' (' . implode(' || ', $this->user_ids) . ') ');
    } else {
      $this->dao->where(sprintf('%st_item.fk_i_user_id = %d ', DB_TABLE_PREFIX, $this->user_ids));
    }
  }


  /**
   * @param $id
   */
  public function notFromUser($id) {
    // Update 8.0.2 - old condition connect to user table for no reason
    //$this->_loadUserTable();
    //$this->dao->where(sprintf('((%st_user.pk_i_id = %st_item.fk_i_user_id AND %st_item.fk_i_user_id != %d) || %st_item.fk_i_user_id IS NULL) ', DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX, $id, DB_TABLE_PREFIX));

    $this->dao->where(sprintf('(%st_item.fk_i_user_id != %d || %st_item.fk_i_user_id IS NULL) ', DB_TABLE_PREFIX, $id, DB_TABLE_PREFIX));
  }


  private function _loadUserTable() {
    if(!$this->userTableLoaded){
      $this->dao->from(sprintf('%st_user',DB_TABLE_PREFIX));
      $this->userTableLoaded = true;
    }
  }


  /**
   * @param $id
   */
  public function addItemId($id) {
    $this->withItemId = true;
    $this->itemId = $id;
  }

  /**
   * Clear the categories
   *
   * @access private
   * @since unknown
   * @param array $branches
   */
  private function pruneBranches($branches = null) {
    if($branches!=null) {
      foreach($branches as $branch) {
        if(!in_array($branch['pk_i_id'], $this->categories)) {
          $this->categories[] = $branch['pk_i_id'];
          if(isset($branch['categories'])) {
            $this->pruneBranches($branch['categories']);
          }
        }
      }
    }
  }


  /**
   * Add categories to the search
   *
   * @access public
   * @since  unknown
   *
   * @param mixed $category
   *
   * @return bool
   * @throws \Exception
   */
  public function addCategory($category = null) {
    if($category == null) {
      return false;
    }

    if(!is_numeric($category)) {
      $category = preg_replace('|/$|','',$category);
      $aCategory = explode('/', $category);
      $category = Category::newInstance()->findBySlug($aCategory[count($aCategory)-1]);

      if(count($category) == 0) {
        return false;
      }

      $category = $category['pk_i_id'];
    }
    
    $tree = Category::newInstance()->toSubTree($category);
    
    if(!in_array($category, $this->categories)) {
      $this->categories[] = $category;
    }
    
    $this->pruneBranches($tree);
    return true;
  }


  /**
   * Add categories to the search
   *
   * @access public
   * @since  unknown
   *
   * @param mixed $category
   *
   * @return bool
   * @throws \Exception
   */
  public function addCategoryOnly($category = null) {
    if($category == null) {
      return false;
    }

    if(!is_numeric($category)) {
      $category = preg_replace('|/$|','',$category);
      $aCategory = explode('/', $category);
      $category = Category::newInstance()->findBySlug($aCategory[count($aCategory)-1]);

      if(count($category) == 0) {
        return false;
      }

      if(!in_array($category['pk_i_id'], $this->categories)) {
        $this->categories[] = $category['pk_i_id'];
      }
    }

    return true;
  }
  

  /**
   *  Add joins for future use
   *
   * @since 2.4
   * @param string $key
   * @param string $table
   * @param string $condition
   * @param string $type
   */
  public function addJoinTable($key, $table, $condition, $type) {
    $this->tables_join[$key] = array($table, $condition, $type);
  }


  /**
   * Add join to current query
   *
   * @since 2.4
   */
  private function _joinTable() {
    foreach($this->tables_join as $tJoin) {
      $this->dao->join($tJoin[0], $tJoin[1], $tJoin[2]);
    }
  }


  /**
   * Create extraFields & conditionsSQL and return as an array
   *
   * @return array with extraFields & conditions strings
   */
  private function _conditions($sql_type = '') {
    osc_run_hook('sql_search_conditions_before', $this, $sql_type);
    
    if(count($this->city_areas) > 0) {
      $this->withLocations = true;
    }

    if(count($this->cities) > 0) {
      $this->withLocations = true;
    }

    if(count($this->regions) > 0) {
      $this->withLocations = true;
    }

    if(count($this->countries) > 0) {
      $this->withLocations = true;
    }

    if(count($this->categories) > 0) {
      $this->withCategoryId = true;
    }

    // Custom conditions
    $conditionsSQL = implode(' AND ', array_filter(osc_apply_filter('sql_search_conditions', $this->conditions, $sql_type)));
    $conditionsSQL = ($conditionsSQL != '' ? ' ' . $conditionsSQL : '');

    // Custom fields - columns
    $extraFields = implode(', ', array_filter(osc_apply_filter('sql_search_fields', $this->search_fields, $sql_type)));
    $extraFields = ($extraFields != '' ? ', ' . $extraFields : '');

    return osc_apply_filter('sql_search_conditions_fields', array(
      'extraFields' => $extraFields,
      'conditionsSQL' => $conditionsSQL
    ), $sql_type);
  }

  /**
   * Only search by pattern + location + category
   *
   * @param int $num
   *
   * @return string
   */
  private function _makeSQLPremium($num = 2, $rand = false) {
    $arrayConditions = $this->_conditions('premium');
    
    if($this->withPattern) {
      // sub select for JOIN
      $this->dao->select('DISTINCT d.fk_i_item_id');
      $this->dao->from(DB_TABLE_PREFIX . 't_item_description as d');
      $this->dao->from(DB_TABLE_PREFIX . 't_item as ti');
      $this->dao->where('ti.pk_i_id = d.fk_i_item_id');

      $search_pattern_cond = '';
      
      if($this->sPattern != '') {
        if(osc_search_pattern_method() == '') {
          $search_pattern_cond = sprintf("MATCH(d.s_title, d.s_description) AGAINST('%s' IN BOOLEAN MODE)", $this->sPattern);
          
        } else if(osc_search_pattern_method() == 'nlp') {
          $search_pattern_cond = sprintf("MATCH(d.s_title, d.s_description) AGAINST('%s' IN NATURAL LANGUAGE MODE)", $this->sPattern);
          
        } else if(osc_search_pattern_method() == 'like') {
          $search_pattern_cond = sprintf("lower(concat(d.s_title, d.s_description)) like '%%%s%%'", strtolower(trim((string)$this->sPattern)));
        }
      }

      $search_pattern_cond = osc_apply_filter('search_cond_pattern', $search_pattern_cond, $this->sPattern, 'premium');

      if($search_pattern_cond != '') {
        $this->dao->where($search_pattern_cond);
      }

      $this->dao->where('ti.b_premium = 1');

      if(osc_search_pattern_current_locale_only()) {
        if(empty($this->locale_code)) {
          if(OC_ADMIN) {
            $this->locale_code[osc_current_admin_locale()] = osc_current_admin_locale();
          } else {
            $this->locale_code[osc_current_user_locale()] = osc_current_user_locale();
          }
        }

        $this->dao->where(sprintf("(d.fk_c_locale_code LIKE '%s')", implode("' d.fk_c_locale_code LIKE '", $this->locale_code)));
      }
      
      $subSelect = $this->dao->_getSelect();
      $this->dao->_resetSelect();
      // END sub select
      
      
      $this->dao->select(DB_TABLE_PREFIX.'t_item.*, '.DB_TABLE_PREFIX.'t_item.s_contact_name as s_user_name');
      $this->dao->from(DB_TABLE_PREFIX.'t_item');
      $this->dao->from(sprintf('%st_item_stats', DB_TABLE_PREFIX));
      
      $this->dao->where(sprintf('%st_item_stats.fk_i_item_id = %st_item.pk_i_id', DB_TABLE_PREFIX, DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item.b_premium = 1', DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item.b_enabled = 1 ', DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item.b_active = 1 ', DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item.b_spam = 0', DB_TABLE_PREFIX));

      if($this->withLocations || OC_ADMIN) {
        $this->dao->join(sprintf('%st_item_location', DB_TABLE_PREFIX), sprintf('%st_item_location.fk_i_item_id = %st_item.pk_i_id', DB_TABLE_PREFIX, DB_TABLE_PREFIX), 'LEFT');
        $this->_addLocations();
      }
      
      if($this->withCategoryId && (count($this->categories) > 0)) {
        $this->dao->where(sprintf('%st_item.fk_i_category_id', DB_TABLE_PREFIX) . ' IN (' . implode(', ', $this->categories) . ')');
      }
      
      $this->dao->where(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$subSelect.')');

      $this->dao->groupBy(DB_TABLE_PREFIX.'t_item.pk_i_id');

      if($rand) {
        $this->dao->orderBy('RAND()', '');
        
      } else {
        // $this->dao->orderBy(sprintf('SUM(%st_item_stats.i_num_premium_views)', DB_TABLE_PREFIX), 'ASC');
        // $this->dao->orderBy(null, 'random');
        $rand_param = (SEARCH_PREMIUM_RAND_PARAM > 0 ? SEARCH_PREMIUM_RAND_PARAM * 1000 : 1);
        $this->dao->orderBy(sprintf('CEIL(SUM(%st_item_stats.i_num_premium_views)/%d) ASC, RAND()', DB_TABLE_PREFIX, $rand_param), 'ASC');
      }
      
      $this->dao->limit(0, $num);
      
    } else {
      $this->dao->select(DB_TABLE_PREFIX.'t_item.*, '.DB_TABLE_PREFIX.'t_item.s_contact_name as s_user_name');
      $this->dao->from(DB_TABLE_PREFIX.'t_item');
      $this->dao->from(sprintf('%st_item_stats', DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item_stats.fk_i_item_id = %st_item.pk_i_id', DB_TABLE_PREFIX, DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item.b_premium = 1', DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item.b_enabled = 1 ', DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item.b_active = 1 ', DB_TABLE_PREFIX));
      $this->dao->where(sprintf('%st_item.b_spam = 0', DB_TABLE_PREFIX));

      if($this->withLocations || OC_ADMIN) {
        $this->dao->join(sprintf('%st_item_location', DB_TABLE_PREFIX), sprintf('%st_item_location.fk_i_item_id = %st_item.pk_i_id', DB_TABLE_PREFIX, DB_TABLE_PREFIX), 'LEFT');
        $this->_addLocations();
      }
      
      if($this->withCategoryId && (count($this->categories) > 0)) {
        $this->dao->where(sprintf('%st_item.fk_i_category_id', DB_TABLE_PREFIX) . ' IN (' . implode(', ', $this->categories) . ')');
      }

      $this->dao->groupBy(DB_TABLE_PREFIX.'t_item.pk_i_id');

      if($rand) {
        $this->dao->orderBy('RAND()', '');
        
      } else {
        // $this->dao->orderBy(sprintf('SUM(%st_item_stats.i_num_premium_views)', DB_TABLE_PREFIX), 'ASC');
        // $this->dao->orderBy(null, 'random');
        $rand_param = (SEARCH_PREMIUM_RAND_PARAM > 0 ? SEARCH_PREMIUM_RAND_PARAM * 1000 : 1);
        $this->dao->orderBy(sprintf('CEIL(SUM(%st_item_stats.i_num_premium_views)/%d) ASC, RAND()', DB_TABLE_PREFIX, $rand_param), 'ASC');
      }
      
      $this->dao->limit(0, $num);
    }
    
    osc_run_hook('search_make_sql_premium', $this);

    $sql = $this->dao->_getSelect();
    // reset dao attributes
    $this->dao->_resetSelect();

    return $sql;
  }
  

  private function _addLocations() {
    if(count($this->city_areas) > 0) {
      $this->dao->where('(' . implode(' || ', $this->city_areas) . ')');
    }
    
    if(count($this->zips) > 0) {
      $this->dao->where('(' . implode(' || ', $this->zips) . ')');
    }
    
    if(count($this->cities) > 0) {
      $this->dao->where('(' . implode(' || ', $this->cities) . ')');
    }
    
    if(count($this->regions) > 0) {
      $this->dao->where('(' . implode(' || ', $this->regions) . ')');
    }
    
    if(count($this->countries) > 0) {
      $this->dao->where('(' . implode(' || ', $this->countries) . ')');
    }
  }

  /**
   * Make the SQL for the search with all the conditions and filters specified
   *
   * @access private
   * @since  unknown
   *
   * @param bool $count
   *
   * @param bool $premium
   * @return string
   */
  private function _makeSQL($count = false) {
    $this->userTableLoaded = false;
    $arrayConditions = $this->_conditions();
    $extraFields = (string)$arrayConditions['extraFields'];
    $conditionsSQL = (string)$arrayConditions['conditionsSQL'];

    $sql = '';

    if($count) {
      $this->dao->select('count(DISTINCT ' . DB_TABLE_PREFIX . 't_item.pk_i_id) as count');

    } else {
      $this->dao->select(sprintf('%st_item.*, %st_item.s_contact_name as s_user_name', DB_TABLE_PREFIX, DB_TABLE_PREFIX));

      if($extraFields != '') {
        $this->dao->select($extraFields);       // plugins and extra columns in select
      }
    }
    
    $this->dao->from(sprintf('%st_item', DB_TABLE_PREFIX));

    // Join tables
    if($this->withLocations || OC_ADMIN) {
      $this->dao->join(sprintf('%st_item_location', DB_TABLE_PREFIX), sprintf('%st_item_location.fk_i_item_id = %st_item.pk_i_id', DB_TABLE_PREFIX, DB_TABLE_PREFIX), 'LEFT');
      $this->_addLocations();
    }

    if($this->withPattern) {
      $this->dao->join(DB_TABLE_PREFIX.'t_item_description as d','d.fk_i_item_id = '.DB_TABLE_PREFIX.'t_item.pk_i_id','LEFT');
    }
    
    
    // Search by item ID
    if($this->withItemId) {
      $this->dao->where('pk_i_id', (int)$this->itemId);

    } else {
      if($this->withNoUserEmail) {
        $this->dao->where(DB_TABLE_PREFIX.'t_item.s_contact_email', $this->sEmail);
      }

      if($this->withPattern) {
        // $this->dao->join(DB_TABLE_PREFIX.'t_item_description as d','d.fk_i_item_id = '.DB_TABLE_PREFIX.'t_item.pk_i_id','LEFT');

        $search_pattern_cond = '';
        $search_pattern_select = '';
        
        if($this->sPattern != '') {
          if(osc_search_pattern_method() == '') {
            $search_pattern_cond = sprintf("MATCH(d.s_title, d.s_description) AGAINST('%s' IN BOOLEAN MODE)", $this->sPattern);
            $search_pattern_select = $search_pattern_cond;
            
          } else if(osc_search_pattern_method() == 'nlp') {
            $search_pattern_cond = sprintf("MATCH(d.s_title, d.s_description) AGAINST('%s' IN NATURAL LANGUAGE MODE)", $this->sPattern);
            $search_pattern_select = $search_pattern_cond;
            
          } else if(osc_search_pattern_method() == 'like') {
            $search_pattern_cond = sprintf("lower(concat(d.s_title, d.s_description)) like '%%%s%%'", strtolower(trim((string)$this->sPattern)));
            $search_pattern_select = sprintf("(CASE WHEN lower(d.s_title) LIKE '%%%1\$s%%' THEN 2 ELSE 0 END + CASE WHEN lower(d.s_description) LIKE '%%%1\$s%%' THEN 1 ELSE 0 END)", strtolower(trim((string)$this->sPattern)));
          }
        }

        $search_pattern_cond = osc_apply_filter('search_cond_pattern', $search_pattern_cond, $this->sPattern);
        
        if($search_pattern_select != '') {
          $search_pattern_select = $search_pattern_select . ' as relevance';
        }

        $search_pattern_select = osc_apply_filter('search_select_pattern', $search_pattern_select, $this->sPattern);

        if($search_pattern_cond != '') {
          $this->dao->where($search_pattern_cond);
          
          // Add relevance column for sorting
          if($search_pattern_select != '' && $this->order_column == 'relevance')  {
            $this->dao->select($search_pattern_select);  // match() like .. as relevance
          }
        }

        if(osc_search_pattern_current_locale_only()) {
          if(empty($this->locale_code)) {
            if(OC_ADMIN) {
              $this->locale_code[osc_current_admin_locale()] = osc_current_admin_locale();
            } else {
              $this->locale_code[osc_current_user_locale()] = osc_current_user_locale();
            }
          }
          
          $this->dao->where(sprintf("(d.fk_c_locale_code LIKE '%s')", implode("' d.fk_c_locale_code LIKE '", $this->locale_code)));
        }
      }

      // Item conditions
      if(count($this->itemConditions)>0) {
        $itemConditions = implode(' AND ', osc_apply_filter('sql_search_item_conditions', $this->itemConditions));
        $this->dao->where($itemConditions);
      }
      
      if($this->withCategoryId && (count($this->categories) > 0)) {
        $this->dao->where(sprintf('%st_item.fk_i_category_id', DB_TABLE_PREFIX) . ' IN (' . implode(', ', $this->categories) . ')');
      }
      
      if($this->withUserId) {
        $this->_fromUser();
      }
      
      // if($this->withLocations || OC_ADMIN) {
        // $this->dao->join(sprintf('%st_item_location', DB_TABLE_PREFIX), sprintf('%st_item_location.fk_i_item_id = %st_item.pk_i_id', DB_TABLE_PREFIX, DB_TABLE_PREFIX), 'LEFT');
        // $this->_addLocations();
      // }
      
      if($this->withPicture) {
        $this->dao->join(sprintf('%st_item_resource', DB_TABLE_PREFIX), sprintf('%st_item_resource.fk_i_item_id = %st_item.pk_i_id', DB_TABLE_PREFIX, DB_TABLE_PREFIX), 'INNER');
        $this->dao->where(sprintf("%st_item_resource.s_content_type LIKE '%%image%%' ", DB_TABLE_PREFIX));
        
        if($count !== true) {
          $this->dao->groupBy(DB_TABLE_PREFIX.'t_item.pk_i_id');
        }
      }
      
      if($this->onlyPremium) {
        $this->dao->where(sprintf('%st_item.b_premium = 1', DB_TABLE_PREFIX));
      }

      if($this->withPhone) {
        $this->dao->where(sprintf('TRIM(COALESCE(%st_item.s_contact_phone,"")) <> ""', DB_TABLE_PREFIX));
      }
      
      $this->_priceRange();

      // add joinTables
      $this->_joinTable();

      // PLUGINS TABLES !!
      if(!empty($this->tables)) {
        $tables = implode(', ', $this->tables);
        $this->dao->from($tables);
      }
      
      // WHERE PLUGINS extra conditions
      if(count($this->conditions) > 0) {
        $this->dao->where($conditionsSQL);
      }

      // groupBy
      if($this->groupBy != '' && ($count !== true || $this->stats === true)) {
        $this->dao->groupBy($this->groupBy);
      }
      // having
      if($this->having != '' && ($count !== true || $this->stats === true)) {
        $this->dao->having($this->having);
      }

      // order & limit
      if($count !== true) {
        $this->dao->orderBy($this->order_column, $this->order_direction);
      }
      
      if($count === true) {
        // $this->dao->limit(100*$this->results_per_page);  // update 4.2.0
        //$this->dao->limit(0, 99999);
        $this->dao->aLimit = false;       // update 8.0.2, remove limit if counting
      } else {
        $this->dao->limit($this->limit_init, $this->results_per_page);
      }
    }

    osc_run_hook('search_make_sql', $this);

    $this->sql = $this->dao->_getSelect();
    // reset dao attributes
    $this->dao->_resetSelect();

    if ($count === true && $this->stats === true) {
      // $this->sql = 'SELECT count(*) as count FROM (' . $this->sql . ') a';
      $this->sql = 'SELECT sum(count) as count FROM (' . $this->sql . ') a';
    }
    
    return $this->sql;
  }

  /**
   * Export SQL
   *
   * @access public
   * @since unknown
   */
  public function exportSQL($count = false) {
    return $this->_makeSQL($count);
  }
  

  /**
   * Return number of ads selected
   *
   * @access public
   * @since unknown
   */
  public function count() {
    if($this->total_results === NULL) {
      $this->doSearch();
    }
    
    return $this->total_results;
  }
  

  /**
   * Return total items on t_item without any filter
   *
   * @return null
   */
  public function countAll() {
    if($this->total_results_table === NULL) {
      $result = $this->dao->query(sprintf('select count(*) as total from %st_item', DB_TABLE_PREFIX));
      $row = $result->row();
      $this->total_results_table = $row['total'];
    }
    
    return $this->total_results_table;
  }


  /**
   * Perform the search
   *
   * @access public
   * @since  unknown
   *
   * @param bool $extended if you want to extend ad's data
   *
   * @param bool $count
   * @return array
   */
  public function doSearch($extended = true, $count = true) {
    $sql = $this->_makeSQL();
    
    $key = md5(osc_base_url().'Search::doSearch'.(string)$sql.(string)$extended.(string)$count.(string)osc_current_user_locale());
    $found = null;
    $cache = osc_cache_get($key, $found);

    if($cache===false) {
      $sql = osc_apply_filter('search_do_search_sql', $sql, $this);

      $result = $this->dao->query($sql);
      
      if($count) {
        $sql = $this->_makeSQL(true);
        $datatmp = $this->dao->query($sql);

        if($datatmp === false) {
          $this->total_results = 0;
        } else {
          //$this->total_results = $datatmp->numRows();  // update 420
          $count_items = $datatmp->row();
          $this->total_results = (isset($count_items['count']) ? $count_items['count'] : 0);
        }
      } else {
        $this->total_results = 0;
      }

      if($result == false) {
        return array();
      }

      $items = array ();
      if ($result) {
        $items = $result->result();
      }
      
      $items = osc_apply_filter('search_do_search_items', $items, $result);

      if ($extended) {
        $items_extend = Item::newInstance()->extendData($items);
      } else {
        $items_extend = $items;
      }
      
      osc_cache_set($key, $items_extend, OSC_CACHE_TTL);
      return $items_extend;
    } else {
      return $cache;
    }
  }


  /**
   * Return premium ads related to the search
   *
   * @access public
   * @since unknown
   * @param int $max
   */
  /**
   * solo acepta pattern + location + stats, category
   * @param int $max
   * @return array
   */
  public function getPremiums($max = 2, $rand = false, $cache_results = true) {
    $premium_sql = $this->_makeSQLPremium($max, $rand);   // make premium sql
    $user_id = (osc_is_web_user_logged_in() ? osc_logged_user_id() : @session_id());

    $key = md5(osc_base_url().'Search::getPremiums'.(string)$premium_sql.(string)$user_id.(string)osc_current_user_locale());
    $found = null;
    $cache = osc_cache_get($key, $found);

    if($cache_results === false){
      $cache = false;
    }
    
    if($cache === false) {
      $result = $this->dao->query($premium_sql);
      
      if($result) {
        $items = $result->result();

        // Update premium stats just in case it's not admin, it's real user and it's not owner of listing
        if(!osc_is_admin_user_logged_in() && osc_visitor_is_real_user()) {
          $mStat = ItemStats::newInstance();
          
          foreach($items as $item) {
            if(!(osc_is_web_user_logged_in() && $item['fk_i_user_id'] == osc_logged_user_id())) {
              $mStat->increase('i_num_premium_views', $item['pk_i_id']);
            }
          }
        }
        
        $items_extend = Item::newInstance()->extendData($items);
        osc_cache_set($key, $items_extend, OSC_CACHE_TTL);
        return $items_extend;
        
      } else {
        return array();
      }
    } else {
      return $cache;
    }
  }


  /**
   * Return latest posted items, you can filter by category and specify the
   * number of items returned.
   *
   * @param int   $numItems
   * @param bool  $withPicture
   *
   * @return array
   * @throws \Exception
   */
  public function getLatestItems($numItems = 10, $withPicture = false) {
    $key = md5(osc_base_url().'Search::getLatestItems'.(string)$numItems.(string)$withPicture.(string)osc_current_user_locale());
    $found = null;
    
    $latestItems = osc_cache_get($key, $found);
    
    if($latestItems === false) {
      $this->set_rpp($numItems);
      
      if($withPicture) {
        $this->withPicture(true);
      }
      
      /*
      if(isset($options['sCategory'])) {
        $this->addCategory($options['sCategory']);
      }
      if(isset($options['sCountry'])) {
        $this->addCountry($options['sCountry']);
      }
      if(isset($options['sRegion'])) {
        $this->addRegion($options['sRegion']);
      }
      if(isset($options['sCity'])) {
        $this->addCity($options['sCity']);
      }
      if(isset($options['sUser'])) {
        $this->fromUser($options['sUser']);
      }
      */
      
      $return = $this->doSearch();
      osc_cache_set($key, $return, OSC_CACHE_TTL);
      return $return;
      
    } else {
      return $latestItems;
    }
  }


  /**
   * Returns number of ads from each country
   *
   * @deprecated
   * @access public
   * @since  unknown
   *
   * @param string $zero if you want to include locations with zero results
   * @param string $order
   *
   * @return array
   */
  public function listCountries($zero = '>', $order = 'items DESC') {
     return CountryStats::newInstance()->listCountries($zero, $order);
  }


  /**
   * Returns number of ads from each region
   * <code>
   *  Search::newInstance()->listRegions($country, ">=", "country_name ASC")
   * </code>
   *
   * @deprecated
   * @access public
   * @since  unknown
   *
   * @param string $country
   * @param string $zero if you want to include locations with zero results
   * @param string $order
   *
   * @return array
   * @throws \Exception
   */
  public function listRegions($country = '%%%%', $zero = '>', $order = 'items DESC') {
    return RegionStats::newInstance()->listRegions($country, $zero, $order);
  }


  /**
   * Returns number of ads from each city
   *
   * <code>
   *  Search::newInstance()->listCities($region, ">=", "city_name ASC")
   * </code>
   *
   * @deprecated
   * @access public
   * @since  unknown
   *
   * @param string $region
   * @param string $zero if you want to include locations with zero results
   * @param string $order
   *
   * @return array
   * @throws \Exception
   */
  public function listCities($region = null, $zero = '>', $order = 'city_name ASC') {
    return CityStats::newInstance()->listCities($region, $zero, $order);
  }


  /**
   * Returns number of ads from each city area
   *
   * @access public
   * @since  unknown
   *
   * @param string $city
   * @param string $zero if you want to include locations with zero results
   * @param string $order
   *
   * @return array
   */
  public function listCityAreas($city = null, $zero = '>', $order = 'items DESC') {
    $aOrder = explode(' ', $order);
    $nOrder = count($aOrder);

    if ($nOrder == 2) {
      $this->dao->orderBy($aOrder[ 0 ], $aOrder[ 1 ]);
    } else if ($nOrder == 1) {
      $this->dao->orderBy($aOrder[ 0 ], 'DESC');
    } else {
      $this->dao->orderBy('item', 'DESC');
    }

    $this->dao->select('fk_i_city_area_id as city_area_id, s_city_area as city_area_name, fk_i_city_id, s_city as city_name, fk_i_region_id as region_id, s_region as region_name, fk_c_country_code as pk_c_code, s_country as country_name, count(*) as items');
    $this->dao->from(DB_TABLE_PREFIX.'t_item, '.DB_TABLE_PREFIX.'t_item_location, '.DB_TABLE_PREFIX.'t_category, '.DB_TABLE_PREFIX.'t_country');
    $this->dao->where(DB_TABLE_PREFIX.'t_item.pk_i_id = '.DB_TABLE_PREFIX.'t_item_location.fk_i_item_id');
    $this->dao->where(DB_TABLE_PREFIX.'t_item.b_enabled = 1');
    $this->dao->where(DB_TABLE_PREFIX.'t_item.b_active = 1');
    $this->dao->where(DB_TABLE_PREFIX.'t_item.b_spam = 0');
    $this->dao->where(DB_TABLE_PREFIX.'t_category.b_enabled = 1');
    $this->dao->where(DB_TABLE_PREFIX.'t_category.pk_i_id = '.DB_TABLE_PREFIX.'t_item.fk_i_category_id');
    $this->dao->where('('.DB_TABLE_PREFIX.'t_item.b_premium = 1 || '.DB_TABLE_PREFIX.'t_category.i_expiration_days = 0 || DATEDIFF(\''.date('Y-m-d H:i:s').'\','.DB_TABLE_PREFIX.'t_item.dt_pub_date) < '.DB_TABLE_PREFIX.'t_category.i_expiration_days)');
    $this->dao->where('fk_i_city_area_id IS NOT NULL');
    $this->dao->where(DB_TABLE_PREFIX.'t_country.pk_c_code = fk_c_country_code');
    $this->dao->groupBy('fk_i_city_area_id');
    $this->dao->having("items $zero 0");

    $city_int = (int)$city;

    if(is_numeric($city_int) && $city_int!=0) {
      $this->dao->where("fk_i_city_id = $city_int");
    }

    $result = $this->dao->get();
    if($result) {
      return $result->result();
    } else {
      return array();
    }
  }


  /**
   * Given the current search object, extract search parameters & conditions
   * as array.
   *
   * @return array
   */
  private function _getConditions() {
    $aData = array();

    $item_id = DB_TABLE_PREFIX.'t_item.pk_i_id';
    $item_category_id = DB_TABLE_PREFIX.'t_item.fk_i_category_id';
    $item_description_id = 'd.fk_i_item_id';
    $category_id = DB_TABLE_PREFIX.'t_category.pk_i_id';
    $item_location_id = DB_TABLE_PREFIX.'t_item_location.fk_i_item_id';
    $item_resource_id = DB_TABLE_PREFIX.'t_item_resource.fk_i_item_id';

    // get item conditions
    foreach($this->conditions as $condition) {
      // item table
      if(preg_match('/'.DB_TABLE_PREFIX.'t_item\.b_active/', $condition, $matches)) {
        $aData['itemConditions'][] = $condition;
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_item\.b_spam/', $condition, $matches)) {
        $aData['itemConditions'][] = $condition;
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_item\.b_enabled/', $condition, $matches)) {
        $aData['itemConditions'][] = $condition;
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_item\.b_premium/', $condition, $matches)) {
        $aData['itemConditions'][] = $condition;
      } else if(preg_match('/('.DB_TABLE_PREFIX.'t_item\.)?f_price >= (.*)/', $condition, $matches)) {
        $aData['price_min'] = (int) $matches[2];
      } else if(preg_match('/('.DB_TABLE_PREFIX.'t_item\.)?f_price <= (.*)/', $condition, $matches)) {
        $aData['price_max'] = (int) $matches[2];
      } else if(preg_match('/('.DB_TABLE_PREFIX.'t_item\.)?i_price >= (.*)/', $condition, $matches)) {
        $aData['price_min'] = ((float) $matches[2] / 1000000);
      } else if(preg_match('/('.DB_TABLE_PREFIX.'t_item\.)?i_price <= (.*)/', $condition, $matches)) {
        $aData['price_max'] = ((float) $matches[2] / 1000000);
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_category.b_enabled/', $condition, $matches)) {
        // t_category.b_enabled is not longer needed
      } else if(preg_match_all('/('.DB_TABLE_PREFIX.'t_item_location.s_city_area\s*LIKE\s*\'%([\s\p{L}\p{N}]*)%\'\s*)/u', $condition, $matches)) { // OJO
        // Comprobar: si (s_name existe) then get location id,
        $aData['s_city_area'][] = DB_TABLE_PREFIX.'t_item_location.s_city_area LIKE \'%'.$matches[2][0].'%\'';
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_item_location.fk_i_city_area_id = (.*)/', $condition, $matches)) {
        $aData['fk_i_city_area_id'][] = DB_TABLE_PREFIX.'t_item_location.fk_i_city_area_id = '.$matches[1];
      } else if(preg_match_all('/('.DB_TABLE_PREFIX.'t_item_location.s_city\s*LIKE\s*\'%([\s\p{L}\p{N}]*)%\'\s*)/u', $condition, $matches)) { // OJO
        // Comprobar: si (s_name existe) then get location id,
        $aData['cities'][] = DB_TABLE_PREFIX.'t_item_location.s_city LIKE \'%'.$matches[2][0].'%\'';
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_item_location.fk_i_city_id = (.*)/', $condition, $matches)) {
        $aData['cities'][] = DB_TABLE_PREFIX.'t_item_location.fk_i_city_id = '.$matches[1];
      } else if(preg_match_all('/('.DB_TABLE_PREFIX.'t_item_location.s_region\s*LIKE\s*\'%([\s\p{L}\p{N}]*)%\'\s*)/u', $condition, $matches)) { // OJO
        // Comprobar: si (s_name existe) then get location id,
        $aData['s_region'][] = DB_TABLE_PREFIX.'t_item_location.s_region LIKE \'%'.$matches[2][0].'%\'';
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_item_location.fk_i_region_id = (.*)/', $condition, $matches)) {
        $aData['fk_i_region_id'] = DB_TABLE_PREFIX.'t_item_location.fk_i_region_id = '.$matches[1];
      } else if(preg_match_all('/('.DB_TABLE_PREFIX.'t_item_location.s_country\s*LIKE\s*\'%([\s\p{L}\p{N}]*)%\'\s*)/u', $condition, $matches)) { // OJO
        // Comprobar: si (s_name existe) then get location id,
        $aData['s_country'][] = DB_TABLE_PREFIX.'t_item_location.s_country LIKE \'%'.$matches[2][0].'%\'';
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_item_location.fk_c_country_code = \'?(.*)\'?/', $condition, $matches)) {
        $aData['fk_c_country_code'][] = DB_TABLE_PREFIX.'t_item_location.fk_c_country_code = '.$matches[1];
      } else if(preg_match('/d\.s_title\s*LIKE\s*\'%([\s\p{L}\p{N}]*)%\'/u', $condition, $matches)) {  // OJO
        $aData['sPattern'] = $matches[1];
        $aData['withPattern'] = true;
      } else if(preg_match('/MATCH\(d\.s_title, d\.s_description\) AGAINST\(\'([\s\p{L}\p{N}]*)\' IN BOOLEAN MODE\)/u', $condition, $matches)) { // OJO
        $aData['sPattern'] = $matches[1];
        $aData['withPattern'] = true;
      } else if(preg_match("/$item_id\s*=\s*$item_description_id/", $condition, $matches_1)   || preg_match("/$item_description_id\s*=\s*$item_id/", $condition, $matches_2)) {
      } else if(preg_match("/$category_id\s*=\s*$item_category_id/", $condition, $matches_1)  || preg_match("/$item_id\s*=\s*$item_category_id/", $condition, $matches_2)) {
      } else if(preg_match("/$item_location_id\s*=\s*$item_id/", $condition, $matches_1)    || preg_match("/$item_id\s*=\s*$item_location_id/", $condition, $matches_2)) {
      } else if(preg_match("/$item_id\s*=\s*$item_resource_id/", $condition, $matches_1)    || preg_match("/$item_resource_id\s*=\s*$item_id/", $condition, $matches_2)) {
        // nothing to do, catch table
      } else if(preg_match_all('/('.DB_TABLE_PREFIX.'t_item\.fk_i_category_id = (\d*))/', $condition, $matches)) {
        $aData['aCategories'] = $matches[2];
      } else {
        $aData['no_catched_conditions'][] = $condition;
      }
    }

    // get tables
    foreach($this->tables as $table) {
      if(preg_match('/('.DB_TABLE_PREFIX.'t_item$)/', $table, $matches)) {
        // t_item is allways included
      } else if(preg_match('/('.DB_TABLE_PREFIX.'t_item_description(as d)?)/', $table, $matches)) {
        // t_item_description is allways included
      } else if(preg_match('/'.DB_TABLE_PREFIX.'t_category/', $table, $matches)) {
        // t_category is allways included
      } else if(preg_match('/('.DB_TABLE_PREFIX.'t_category_description(as cd)?)/', $table, $matches)) {
        // t_item_description
        $aData['tables'][] = $matches[1];
      } else if(preg_match('/('.DB_TABLE_PREFIX.'t_item_resource)/', $table, $matches)) {
        $aData['withPicture'] = true;
      } else {
        $aData['no_catched_tables'][] = $table;
      }
    }

    // get order & limit
    $aData['order_column'] = $this->order_column;
    $aData['order_direction'] = $this->order_direction;
    $aData['limit_init'] = $this->limit_init;
    $aData['results_per_page'] = $this->results_per_page;

    return $aData;
  }


  /**
   * Return json with all search attributes
   *
   * @param bool $convert
   * @return string
   */
  public function toJson($convert = false, $for_alert = true) {
    if($convert) {
      $aData = $this->_getConditions();
      
    } else {
      $aData['price_min'] = $this->price_min/1000000;
      $aData['price_max'] = $this->price_max/1000000;
      $aData['aCategories'] = $this->categories;
      
      // locations
      $aData['zips'] = $this->zips;
      $aData['city_areas'] = $this->city_areas;
      $aData['cities'] = $this->cities;
      $aData['regions'] = $this->regions;
      $aData['countries'] = $this->countries;
      
      // pattern
      $aData['withPattern'] = $this->withPattern;
      $aData['sPattern'] = $this->sPattern;
      
      if($this->withPicture) {
        $aData['withPicture'] = $this->withPicture;
      }

      if($this->onlyPremium) {
        $aData['onlyPremium'] = $this->onlyPremium;
      }
      
      if($this->withPhone) {
        $aData['withPhone'] = $this->withPhone;
      }

      $aData['user_ids'] = $this->user_ids;

      // get order & limit
      $aData['order_column'] = $this->order_column;
      $aData['order_direction'] = $this->order_direction;

      $aData['tables'] = $this->tables;
      $aData['tables_join'] = $this->tables_join;
      $aData['no_catched_tables'] = $this->tables;
      $aData['no_catched_conditions'] = $this->conditions;


      // If not for alert (but to get cache key), add also dynamic values
      // Dynamic values cannot be added into alert as it will never match it
      if($for_alert === false) {
        $aData['limit_init'] = $this->limit_init;
        $aData['results_per_page'] = $this->results_per_page;
        
        // Additional info
        // $aData['sql'] = $this->_makeSQL();

        $arrayConditions = $this->_conditions();
        $aData['extra_fields'] = (string)$arrayConditions['extraFields'];
        $aData['conditions_sql'] = (string)$arrayConditions['conditionsSQL'];
        
        $aData['item_conditions'] = (string)implode(' AND ', osc_apply_filter('sql_search_item_conditions', $this->itemConditions));
        $aData['locale'] = (OC_ADMIN ? osc_current_admin_locale() : osc_current_user_locale());

        if($this->withItemId) {
          $aData['itemId'] = $this->itemId;
        }


      // If for alert, it's used to generate json to reproduce search on alerts page
      } else {
        if(Session::newInstance()->_get('userId') > 0) { 
          $aData['no_catched_conditions'][] = sprintf('(%st_item.fk_i_user_id != %d || %st_item.fk_i_user_id IS NULL)', DB_TABLE_PREFIX, Session::newInstance()->_get('userId'), DB_TABLE_PREFIX);
        }
        
        if(Session::newInstance()->_get('userEmail') != '') {
          $aData['no_catched_conditions'][] = sprintf('%st_item.s_contact_email != "%s"', DB_TABLE_PREFIX, Session::newInstance()->_get('userEmail'));
        }

        $aData['no_catched_conditions'] = array_values(array_unique($aData['no_catched_conditions']));
      }


      // WE MUST SORT TABLES AND CONDITIONS TO AVOID DUPLICATED ALERTS WITH SAME CONDITIONS/TABLES IN DIFFERENT ORDER
      is_array($aData['no_catched_conditions']) ? sort($aData['no_catched_conditions']) : '';
      is_array($aData['no_catched_tables']) ? sort($aData['no_catched_tables']) : '';
      is_array($aData['tables']) ? sort($aData['tables']) : '';
      is_array($aData['tables_join']) ? sort($aData['tables_join']) : '';
    }
    
    $json = json_encode($aData);
    
    // Remove timestamps and dynamic values blocking cache
    $pattern_timestamp = '/\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})?/';
    $pattern_time = '/\b\d{2}:\d{2}:\d{2}\b/';

    $json_cleaned = preg_replace($pattern_timestamp, "***CACHE: dynamic timestamp removed***", $json);
    $json_cleaned = preg_replace($pattern_time, "***CACHE: dynamic time removed***", $json_cleaned);
    
    return $json_cleaned;
  }


  /**
   * Reconstruct Search model from stored json details
   *
   * @param $aData
   */
  public function setJsonAlert($aData, $email = '', $user_id = NULL) {
    if(!isset($aData['price_min'])) {
      return false;       // structure of alert seems to be wrong
    }
    
    $this->priceRange($aData['price_min'], $aData['price_max']);

    $this->categories = $aData['aCategories'];
    
    // locations
    $this->zips = isset($aData['zips']) ? $aData['zips'] : array();
    $this->city_areas = isset($aData['city_areas']) ? $aData['city_areas'] : array();
    $this->cities = $aData['cities'];
    $this->regions = $aData['regions'];
    $this->countries = $aData['countries'];

    $this->user_ids = $aData['user_ids'];

    $this->tables_join = $aData['tables_join'];
    $this->tables = $aData['no_catched_tables'];
    $this->conditions = $aData['no_catched_conditions'];

    // get order & limit
    $this->order_column = $aData['order_column'];
    $this->order_direction = $aData['order_direction'];
    
    if(isset($aData['limit_init'])) {
      $this->limit_init = $aData['limit_init'];
    }

    if(isset($aData['results_per_page'])) {
      $this->results_per_page = $aData['results_per_page'];
    }
    
    $this->dao->groupBy(DB_TABLE_PREFIX.'t_item.pk_i_id');

    if($user_id > 0) {
      $this->addConditions(sprintf('(%st_item.fk_i_user_id != %d || %st_item.fk_i_user_id IS NULL)', DB_TABLE_PREFIX, $user_id, DB_TABLE_PREFIX));
    } 

    if ($email != '') {
      $this->addConditions(sprintf('%st_item.s_contact_email != "%s"', DB_TABLE_PREFIX, $email));
    }
    
    // pattern
    if(isset($aData['sPattern'])) {
      $this->addPattern($aData['sPattern']);
    }
    
    if(isset($aData['withPicture'])) {
      $this->withPicture(true);
    }
    
    if(isset($aData['onlyPremium'])) {
      $this->onlyPremium(true);
    }
  }
}

/* file end: ./oc-includes/osclass/model/Search.php */
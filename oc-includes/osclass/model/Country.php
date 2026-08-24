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
 * Model database for Country table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class Country extends DAO
{
  /**
   *
   * @var Country
   */
  private static $instance;

  /**
   * @return \Country
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   *
   */
  public function __construct() {
    parent::__construct();
    $this->setTableName('t_country');
    $this->setPrimaryKey('pk_c_code');
    $this->setFields( array('pk_c_code', 's_name', 's_name_native', 's_phone_code', 's_currency', 's_slug') );
  }

  /**
   * Find a country by its ISO code
   *
   * @access public
   * @since unknown
   * @param $code
   * @return array
   */
  public function findByCode($code) {
    if(trim((string)$code) == '') { 
      return array();
    }
    
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('pk_c_code', $code);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }
    return $result->row();
  }

  /**
   * Find a country by its name
   *
   * @access public
   * @since unknown
   * @param $name
   * @return array
   */
  public function findByName($name) {
    if(trim((string)$name) == '') { 
      return array();
    }
    
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where(sprintf('(s_name="%s" OR s_name_native="%s")', $name, $name));

    $result = $this->dao->get();
    if($result == false) {
      return array();
    }
    return $result->row();
  }


  /**
   * Get all the rows from the table t_country
   *
   * @access public
   * @since unknown
   * @return array
   */
  public function listAll($cache_enabled = true) {
    $key = md5(osc_base_url().'Country::listAll');
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if(OC_ADMIN || $cache_enabled === false || $cache === false) {
      $this->dao->select($this->getFields());
      $this->dao->from($this->getTableName());
      $this->dao->orderBy('s_name', 'ASC');
      $result = $this->dao->get();

      if($result == false) {
        $data = array();
      } else {
        $data = $result->result();
      }
      
      osc_cache_set($key, $data, OSC_CACHE_TTL);
      return $data;
    }
    
    return $cache;
  }

  /**
   * Get all the rows from the table t_country where user has listings
   *
   * @access public
   * @since unknown
   * @return array
   */
  public function listUser($user_id, $cache_enabled = true) {
    if($user_id <= 0) {
      return array();
    }
    
    $key = md5(osc_base_url().'Country::listUser' . (string)$user_id);
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if(OC_ADMIN || $cache_enabled === false || $cache === false) {
      $this->dao->select('t.*');
      $this->dao->from($this->getTableName() . ' as t');
      $this->dao->where(sprintf('EXISTS (SELECT 1 FROM %st_item_location as l, %st_item as i WHERE i.pk_i_id = l.fk_i_item_id AND t.pk_c_code = l.fk_c_country_code AND i.fk_i_user_id = %d)', DB_TABLE_PREFIX, DB_TABLE_PREFIX, (int)$user_id));
      $this->dao->orderBy('t.s_name', 'ASC');
      $result = $this->dao->get();

      if($result == false) {
        $data = array();
      } else {
        $data = $result->result();
      }
      
      osc_cache_set($key, $data, OSC_CACHE_TTL);
      return $data;
    }
    
    return $cache;
  }

  /**
   * Count all the rows from the table t_country
   *
   * @access public
   * @since unknown
   * @return array
   */
  public function count() {
    $key = md5(osc_base_url().'Country::count');
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if(OC_ADMIN || $cache === false) {
      $count = 0;
      
      $this->dao->select('count(*) as i_count');
      $this->dao->from($this->getTableName());
      $result = $this->dao->get();

      if($result !== false) {
        $data = $result->row();
        
        if(is_array($data) && isset($data['i_count'])) {
          $count = (int)$data['i_count'];
        }
      }

      osc_cache_set($key, $count, OSC_CACHE_TTL);
      return $count;
    }
    
    return $cache;
  }
  
  /**
   * List names of all the countries. Used for location import.
   *
   * @access public
   * @since  unknown
   * @return array
   */
  public function listNames() {
    $result = $this->dao->query(sprintf('SELECT s_name FROM %s ORDER BY s_name ASC', $this->getTableName()));
    if($result == false) {
      return array();
    }
    
    return array_column($result->result(), 's_name');
  }

  /**
   * Function that work with the ajax file
   *
   * @access public
   * @since unknown
   * @param $query
   * @return array
   */
  public function ajax($query) {
    $this->dao->select('pk_c_code as id, s_name as label, s_name_native as label_native, s_name as value');
    $this->dao->from($this->getTableName());
    $this->dao->like('s_name', $query, 'after');
    $this->dao->orLike('s_name_native', $query, 'after');
    $this->dao->limit(5);
    $result = $this->dao->get();
    if($result == false) {
      return array();
    }

    $return = $result->result();
    $output = array();
    if(count($return) > 0 && osc_get_current_user_locations_native() == 1) {
      foreach($return as $r) {
        $row = $r;
        $row['label_original'] = '';

        if(@$row['label_native'] <> '') {
          $row['label_original'] = $row['label'];
          $row['label'] = $row['label_native'];
          $row['value'] = $row['label_native'];
        }

        $output[] = $row;
      }
    } else {
      $output = $return;
    }

    return $output;
  }


  /**
   *  Delete a country with its regions, cities,..
   *
   * @access public
   * @since  2.4
   *
   * @param $pk
   *
   * @return int number of failed deletions or 0 in case of none
   * @throws \Exception
   */
  public function deleteByPrimaryKey($pk) {
    $mRegions = Region::newInstance();
    $aRegions = $mRegions->findByCountry($pk);
    $result = 0;
    foreach($aRegions as $region) {
      $result += $mRegions->deleteByPrimaryKey($region['pk_i_id']);
    }
    Item::newInstance()->deleteByCountry($pk);
    CountryStats::newInstance()->delete(array('fk_c_country_code' => $pk));
    User::newInstance()->update(array('fk_c_country_code' => null, 's_country' => ''), array('fk_c_country_code' => $pk));
    if(!$this->delete(array('pk_c_code' => $pk))) {
      $result++;
    }
    return $result;
  }

  /**
   * Find a location by its slug
   *
   * @access public
   * @since 3.2.1
   * @param $slug
   * @return array
   */
  public function findBySlug($slug) {
    if(trim((string)$slug) == '') { 
      return array();
    }
    
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_slug', $slug);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }
    return $result->row();
  }

  /**
   * Find a locations with no slug
   *
   * @access public
   * @since 3.2.1
   * @return array
   */
  public function listByEmptySlug() {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_slug', '');
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }
    return $result->result();
  }


  /**
   * Return a country name given an id
   *
   * @access public
   * @since 3.1
   * @param string $id primary key
   * @return string
   */
  public function findNameByPrimaryKey($id) {
    if($id == null) {
      return false;
    }

    $this->dao->select('s_name');
    $this->dao->from($this->getTableName());
    $this->dao->where('pk_c_code', $id);
    $result = $this->dao->get();

    if($result == false) {
      return false;
    }

    $data = $result->row();

    if(isset($data['s_name']) && $data['s_name'] != '') {
      return $data['s_name'];
    }
    
    return false;
  }
}

/* file end: ./oc-includes/osclass/model/Country.php */
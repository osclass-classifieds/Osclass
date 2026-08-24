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
 * Model database for Region table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class Region extends DAO
{
  /**
   *
   * @var
   */
  private static $instance;

  /**
   * @return \Region|\type
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
    $this->setTableName('t_region');
    $this->setPrimaryKey('pk_i_id');
    $this->setFields(array('pk_i_id', 'fk_c_country_code', 's_name', 's_name_native', 'b_active', 's_slug'));
  }

  /**
   * Gets all regions from a country
   *
   * @access public
   * @since unknown
   * @deprecated since 2.3
   * @see Region::findByCountry
   * @param $countryId
   * @return array
   */
  public function getByCountry($countryId) {
    return $this->findByCountry($countryId);
  }

  /**
   * Gets all regions from a country
   *
   * @access public
   * @since unknown
   * @param $countryId
   * @return array
   */
  public function findByCountry($countryId) {
    if(trim((string)$countryId) == '') { 
      return array();
    }
    
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_c_country_code', $countryId);
    $this->dao->orderBy('s_name', 'ASC');
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Find a region by its name and country
   *
   * @access public
   * @since unknown
   * @param string $name
   * @param string $country
   * @return array
   */
  public function findByName($name, $country = null) {
    if(trim((string)$name) == '') { 
      return array();
    }
    
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where(sprintf('(s_name="%s" OR s_name_native="%s")', $name, $name));
    if($country!=null) {
      $this->dao->where('fk_c_country_code', $country);
    }

    $this->dao->limit(1);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->row();
  }

  /**
   * Function to deal with ajax queries
   *
   * @access public
   * @since  unknown
   *
   * @param $query
   * @param null $country
   *
   * @return array
   */
  public function ajax($query, $country = null) {
    $country = trim((string)$country);
    $this->dao->select('a.pk_i_id as id, a.s_name as label,  a.s_name_native as label_native, a.s_name as value');
    $this->dao->from($this->getTableName() . ' as a');
    $this->dao->like('a.s_name', $query, 'after');
    $this->dao->orLike('a.s_name_native', $query, 'after');
    if($country != null ) {
      if(strlen($country)==2) {
        $this->dao->where('a.fk_c_country_code', strtolower($country));
      } else {
        $this->dao->join(Country::newInstance()->getTableName().' as aux', 'aux.pk_c_code = a.fk_c_country_code', 'LEFT');
        $this->dao->where('aux.s_name', $country);
      }
    }
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
   * Get all the rows from the table t_region
   *
   * @access public
   * @since unknown
   * @return array
   */
  public function listAll($cache_enabled = true) {
    $key = md5(osc_base_url().'Region::listAll');
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
   * Get all the rows from the table t_region where user has listings
   *
   * @access public
   * @since unknown
   * @return array
   */
  public function listUser($user_id, $cache_enabled = true) {
    if($user_id <= 0) {
      return array();
    }
    
    $key = md5(osc_base_url().'Region::listUser' . (string)$user_id);
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if(OC_ADMIN || $cache_enabled === false || $cache === false) {
      $this->dao->select('t.*');
      $this->dao->from($this->getTableName() . ' as t');
      $this->dao->where(sprintf('EXISTS (SELECT 1 FROM %st_item_location as l, %st_item as i WHERE i.pk_i_id = l.fk_i_item_id AND t.pk_i_id = l.fk_i_region_id AND i.fk_i_user_id = %d)', DB_TABLE_PREFIX, DB_TABLE_PREFIX, (int)$user_id));
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
   * Count all the rows from the table t_region
   *
   * @access public
   * @since unknown
   * @return array
   */
  public function count() {
    $key = md5(osc_base_url().'Region::count');
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
   *  Delete a region with its cities and city areas
   *
   * @access public
   * @since  3.1
   *
   * @param $pk
   *
   * @return int number of failed deletions or 0 in case of none
   * @throws \Exception
   */
  public function deleteByPrimaryKey($pk) {
    $mCities = City::newInstance();
    $aCities = $mCities->findByRegion($pk);
    $result = 0;
    foreach($aCities as $city) {
      $result += $mCities->deleteByPrimaryKey($city['pk_i_id']);
    }
    Item::newInstance()->deleteByRegion($pk);
    RegionStats::newInstance()->delete(array('fk_i_region_id' => $pk));
    User::newInstance()->update(array('fk_i_region_id' => null, 's_region' => ''), array('fk_i_region_id' => $pk));
    if(!$this->delete(array('pk_i_id' => $pk))) {
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
   * Return a region name given an id
   *
   * @access public
   * @since 3.1
   * @param int $id primary key
   * @return string
   */
  public function findNameByPrimaryKey($id) {
    if($id == null) {
      return false;
    }

    $this->dao->select('s_name');
    $this->dao->from($this->getTableName());
    $this->dao->where('pk_i_id', $id);
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

/* file end: ./oc-includes/osclass/model/Region.php */
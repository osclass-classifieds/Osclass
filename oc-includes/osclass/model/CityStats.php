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
 * Model database for CityStats table
 *
 * @package Osclass
 * @subpackage Model
 * @since 2.4
 */
class CityStats extends DAO
{
  /**
   * It references to self object: CityStats.
   * It is used as a singleton
   *
   * @access private
   * @since 2.4
   * @var CityStats
   */
  private static $instance;

  /**
   * It creates a new CityStats object class if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since  2.4
   * @return \CityStats
   */
  public static function newInstance()
  {
    if( !self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_city_stats table
   *
   * @access public
   * @since 2.4
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_city_stats');
    $this->setPrimaryKey('fk_i_city_id');
    $this->setFields( array('fk_i_city_id', 'i_num_items') );
  }

  /**
   * Increase number of city items, given a city id
   *
   * @access public
   * @since 2.4
   * @param int $cityId City id
   * @return int number of affected rows, id error occurred return false
   */
  public function increaseNumItems($cityId)
  {
    if($cityId <= 0) {
      return false;
    }
    return $this->dao->query(sprintf('INSERT INTO %s (fk_i_city_id, i_num_items) VALUES (%d, 1) ON DUPLICATE KEY UPDATE i_num_items = i_num_items + 1', $this->getTableName(), $cityId));
  }

  /**
   * Increase number of city items, given a city id
   *
   * @access public
   * @since 2.4
   * @param int $cityId City id
   * @return int number of affected rows, id error occurred return false
   */
  public function decreaseNumItems($cityId)
  {
    if($cityId <= 0) {
      return false;
    }

    $this->dao->select( 'i_num_items' );
    $this->dao->from( $this->getTableName() );
    $this->dao->where( $this->getPrimaryKey(), $cityId );
    $result = $this->dao->get();
    $cityStat = $result->row();

    if( isset( $cityStat['i_num_items'] ) ) {
      $this->dao->from( $this->getTableName() );
      $this->dao->set( 'i_num_items', 'i_num_items - 1', false );
      $this->dao->where( 'i_num_items > 0' );
      $this->dao->where( 'fk_i_city_id', $cityId );

      return $this->dao->update();
    }

    return false;
  }

  /**
   * Set i_num_items, given a city id
   *
   * @access public
   * @since  2.4
   *
   * @param int $cityID
   * @param int $numItems
   *
   * @return mixed
   */
  public function setNumItems($cityID, $numItems)
  {
    return $this->dao->query( 'INSERT INTO ' . $this->getTableName() . " (fk_i_city_id, i_num_items) VALUES ($cityID, $numItems) ON DUPLICATE KEY UPDATE i_num_items = " . $numItems);
  }

  /**
   * Find stats by city id
   *
   * @access public
   * @since 2.4
   * @param int $cityId city id
   * @return array
   */
  public function findByCityId($cityId)
  {
    return $this->findByPrimaryKey($cityId);
  }

  /**
   *
   * @param int $regionId
   *
   * @return mixed
   */
  public function deleteByRegion($regionId)
  {
    return $this->dao->query('DELETE FROM '.DB_TABLE_PREFIX.'t_city_stats WHERE fk_i_city_id IN (SELECT pk_i_id FROM '.DB_TABLE_PREFIX.'t_city WHERE fk_i_region_id = '.$regionId.');');
  }

  /**
   * Return a list of cities and counter items.
   * Can be filtered by region and num_items,
   * and ordered by city_name or items counter
   * $order = 'city_name ASC' OR $oder = 'items DESC'
   *
   * @param int  $region
   * @param string $zero
   * @param string $order
   *
   * @return array
   * @throws \Exception
   */
  public function listCities($region = null, $zero = '>' , $order = 'city_name ASC' )
  {
    $key = md5(osc_base_url().'CityStats::listCities'.(string)$region.(string)$zero.(string)$order);
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if($cache===false) {
      $this->dao->select($this->getTableName().'.fk_i_city_id as city_id, '.$this->getTableName().'.i_num_items as items, '.DB_TABLE_PREFIX.'t_city.s_name as city_name, '.DB_TABLE_PREFIX.'t_city.s_name_native as city_name_native, '.DB_TABLE_PREFIX.'t_city.s_slug as city_slug');
      $this->dao->from( $this->getTableName() );
      $this->dao->join(DB_TABLE_PREFIX.'t_city', $this->getTableName().'.fk_i_city_id = '.DB_TABLE_PREFIX.'t_city.pk_i_id', 'LEFT');
      $this->dao->where('i_num_items '.$zero.' 0' );
      
      if($region > 0) {
        $this->dao->where(DB_TABLE_PREFIX.'t_city.fk_i_region_id = '.$region);
      }
      $this->dao->orderBy($order);

      $rs = $this->dao->get();

      if($rs === false) {
        return array();
      }

      $return = $rs->result();
      $output = array();
      if(count($return) > 0 && osc_get_current_user_locations_native() == 1) {
        foreach($return as $r) {
          $row = $r;
          $nm = (isset($row['city_name']) ? 'city_name' : 's_name');
          $row[$nm . '_original'] = '';

          if(@$row[$nm . '_native'] <> '') {
            $row[$nm . '_original'] = $row[$nm];
            $row[$nm] = $row[$nm . '_native'];
          }

          $output[] = $row;
        }
      } else {
        $output = $return;
      }
      
      osc_cache_set($key, $output, OSC_CACHE_TTL);
      return $output;
    } else {
      return $cache;
    }
  }
  
  

  /**
   * Return a list of cities and count items.
   *
   * @return array
   */
  public function listCitiesLimit($country_code = null, $region_id = null, $order = 's_name ASC', $limit = 100, $min_items = 0, $custom_condition = '') {
    $key = md5(osc_base_url().'CityStats::listCitiesLimit'.(string)$country_code.(string)$region_id.(string)$order.(string)$limit.(string)$min_items.(string)$custom_condition);
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if($cache===false) {
      $this->dao->select('c.*, coalesce(s.i_num_items, 0) as i_num_items');
      $this->dao->from(DB_TABLE_PREFIX.'t_city as c');
      $this->dao->join($this->getTableName() . ' as s', 'c.pk_i_id = s.fk_i_city_id', 'LEFT');
      
      if($min_items > 0) {
        $this->dao->where('i_num_items >= ' . $min_items);
      }
      
      if($country_code <> '' && strlen((string)$country_code) == 2) {
        $this->dao->where('c.fk_c_country_code', $country_code);
      }
      
      if($region_id > 0) {
        $this->dao->where('c.fk_i_region_id', $region_id);
      }

      if(trim((string)$custom_condition) != '') {
        $this->dao->where($custom_condition);
      }
      
      if($limit > 0) {
        $this->dao->limit($limit);
      }
      
      $this->dao->orderBy($order);

      $rs = $this->dao->get();

      if($rs === false) {
        return array();
      }

      $return = $rs->result();
      $output = array();
      
      if(count($return) > 0 && osc_get_current_user_locations_native() == 1) {
        foreach($return as $r) {
          $row = $r;
          $nm = 's_name';
          $row[$nm . '_original'] = '';

          if(@$row[$nm . '_native'] <> '') {
            $row[$nm . '_original'] = $row[$nm];
            $row[$nm] = $row[$nm . '_native'];
          }

          $output[] = $row;
        }
      } else {
        $output = $return;
      }
      
      osc_cache_set($key, $output, OSC_CACHE_TTL);
      return $output;
    } else {
      return $cache;
    }
  }


  /**
   * Calculate the total items that belong to city id
   *
   * @param int $cityId
   *
   * @return int total items
   */
  public function calculateNumItems($cityId)
  {
    $sql  = 'SELECT count(*) as total FROM '.DB_TABLE_PREFIX.'t_item_location, '.DB_TABLE_PREFIX.'t_item, '.DB_TABLE_PREFIX.'t_category ';
    $sql .= 'WHERE '.DB_TABLE_PREFIX.'t_item_location.fk_i_city_id = '.$cityId.' AND ';
    $sql .= DB_TABLE_PREFIX.'t_item.pk_i_id = '.DB_TABLE_PREFIX.'t_item_location.fk_i_item_id AND ';
    $sql .= DB_TABLE_PREFIX.'t_category.pk_i_id = '.DB_TABLE_PREFIX.'t_item.fk_i_category_id AND ';
    $sql .= DB_TABLE_PREFIX.'t_item.b_active = 1 AND '.DB_TABLE_PREFIX.'t_item.b_enabled = 1 AND '.DB_TABLE_PREFIX.'t_item.b_spam = 0 AND ';
    $sql .= '('.DB_TABLE_PREFIX.'t_item.b_premium = 1 || '.DB_TABLE_PREFIX.'t_item.dt_expiration >= \''.date('Y-m-d H:i:s').'\' ) AND ';
    $sql .= DB_TABLE_PREFIX.'t_category.b_enabled = 1 ';

    $return = $this->dao->query($sql);
    if($return === false) {
      return 0;
    }

    if($return->numRows() > 0) {
      $aux = $return->result();
      return $aux[0]['total'];
    }

    return 0;
  }
}

/* file end: ./oc-includes/osclass/model/CityStats.php */
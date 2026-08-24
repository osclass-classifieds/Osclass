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
 * Model database for CountryStats table
 *
 * @package Osclass
 * @subpackage Model
 * @since 2.4
 */
class CountryStats extends DAO
{
  /**
   * It references to self object: CountryStats.
   * It is used as a singleton
   *
   * @access private
   * @since 2.4
   * @var CountryStats
   */
  private static $instance;

  /**
  * It creates a new CountryStats object class if it has been created
  * before, it return the previous object
  *
  * @access public
  * @since 2.4
  * @return CountryStats
  */
  public static function newInstance()
  {
    if( !self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_country_stats table
   *
   * @access public
   * @since 2.4
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_country_stats');
    $this->setPrimaryKey('fk_c_country_code');
    $this->setFields( array('fk_c_country_code', 'i_num_items') );
  }

  /**
   * Increase number of country items, given a country id
   *
   * @access public
   * @since 2.4
   * @param int $countryCode Country code
   * @return int number of affected rows, id error occurred return false
   */
  public function increaseNumItems($countryCode)
  {
    $lenght = strlen((string)$countryCode);
    if(trim((string)$countryCode) == '' || $lenght > 2 || $lenght == 0) {
      return false;
    }
    
    $sql = sprintf('INSERT INTO %s (fk_c_country_code, i_num_items) VALUES (\'%s\', 1) ON DUPLICATE KEY UPDATE i_num_items = i_num_items + 1', $this->getTableName(), $countryCode);
    return $this->dao->query($sql);
  }

  /**
   * Increase number of country items, given a Country code
   *
   * @access public
   * @since  2.4
   *
   * @param $countryCode
   *
   * @return int number of affected rows, id error occurred return false
   */
  public function decreaseNumItems($countryCode)
  {
    $lenght = strlen((string)$countryCode);
    if(trim((string)$countryCode) == '' || $lenght > 2 || $lenght == 0) {
      return false;
    }
    
    $this->dao->select( 'i_num_items' );
    $this->dao->from( $this->getTableName() );
    $this->dao->where( $this->getPrimaryKey(), $countryCode );
    $result = $this->dao->get();
    
    if($result) {
      $countryStat = $result->row();
      $return = 0;

      if( isset( $countryStat['i_num_items'] ) ) {
        $this->dao->from( $this->getTableName() );
        $this->dao->set( 'i_num_items', 'i_num_items - 1', false );
        $this->dao->where( 'i_num_items > 0' );
        $this->dao->where( 'fk_c_country_code', $countryCode );

        return $this->dao->update();
      }
    }

    return false;
  }

  /**
   * Set i_num_items, given a country code
   *
   * @access public
   * @since  2.4
   *
   * @param string $countryCode
   * @param int  $numItems
   *
   * @return mixed
   */
  public function setNumItems($countryCode, $numItems)
  {
    return $this->dao->query( 'INSERT INTO ' . $this->getTableName() . " (fk_c_country_code, i_num_items) VALUES ('$countryCode', $numItems) ON DUPLICATE KEY UPDATE i_num_items = " . $numItems);
  }

  /**
   * Find stats by country code
   *
   * @access public
   * @since 2.4
   * @param int $countryCode country id
   * @return array
   */
  public function findByCountryCode($countryCode)
  {
    return $this->findByPrimaryKey($countryCode);
  }


  /**
   * Return a list of countries and counter items.
   * Can be filtered by num_items,
   * and ordered by country_name or items counter.
   * $order = 'country_name ASC' OR $oder = 'items DESC'
   *
   * @access public
   * @since 2.4
   * @param string $zero
   * @param string $order
   * @return array
   */
  public function listCountries($zero = '>' , $order = 'country_name ASC' )
  {
    $key = md5(osc_base_url().'CountryStats::listCountries'.(string)$order.(string)$zero);
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if($cache===false) {
      $this->dao->select($this->getTableName().'.fk_c_country_code as country_code, '.$this->getTableName().'.i_num_items as items, '.DB_TABLE_PREFIX.'t_country.s_name as country_name, '.DB_TABLE_PREFIX.'t_country.s_name_native as country_name_native, '.DB_TABLE_PREFIX.'t_country.s_slug as country_slug');
      $this->dao->from($this->getTableName() );
      $this->dao->join(DB_TABLE_PREFIX.'t_country', $this->getTableName().'.fk_c_country_code = '.DB_TABLE_PREFIX.'t_country.pk_c_code');
      $this->dao->where('i_num_items '.$zero.' 0' );
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
          $nm = (isset($row['country_name']) ? 'country_name' : 's_name');
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
   * Return a list of countries and count items.
   *
   * @return array
   */
  public function listCountriesLimit($order = 's_name ASC', $limit = 100, $min_items = 0, $custom_condition = '') {
    $key = md5(osc_base_url().'CountryStats::listCountriesLimit'.(string)$order.(string)$limit.(string)$min_items.(string)$custom_condition);
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if($cache===false) {
      $this->dao->select('c.*, coalesce(s.i_num_items, 0) as i_num_items');
      $this->dao->from(DB_TABLE_PREFIX.'t_country as c');
      $this->dao->join($this->getTableName() . ' as s', 'c.pk_c_code = s.fk_c_country_code', 'LEFT');
      
      if($min_items > 0) {
        $this->dao->where('i_num_items >= ' . $min_items);
      }

      if($limit > 0) {
        $this->dao->limit($limit);
      }
      
      if(trim((string)$custom_condition) != '') {
        $this->dao->where($custom_condition);
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
   * Calculate the total items that belong to countryCode
   * @access public
   * @since 2.4
   *
   * @param string $countryCode
   *
   * @return int total items
   */
  public function calculateNumItems($countryCode)
  {
    $sql  = 'SELECT count(*) as total FROM '.DB_TABLE_PREFIX.'t_item_location, '.DB_TABLE_PREFIX.'t_item, '.DB_TABLE_PREFIX.'t_category ';
    $sql .= 'WHERE '.DB_TABLE_PREFIX.'t_item_location.fk_c_country_code = \''.$countryCode.'\' AND ';
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

/* file end: ./oc-includes/osclass/model/CountryStats.php */
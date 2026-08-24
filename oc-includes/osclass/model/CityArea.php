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
 * Model database for CityArea table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class CityArea extends DAO
{
  /**
   * It references to self object: CityArea.
   * It is used as a singleton
   *
   * @access private
   * @since unknown
   * @var CityArea
   */
  private static $instance;

  /**
   * It creates a new CityArea object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since unknown
   * @return CityArea
   */
  public static function newInstance()
  {
    if( !self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_city_area table
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_city_area');
    $this->setPrimaryKey('pk_i_id');
    $this->setFields( array('pk_i_id', 'fk_c_country_code', 'fk_i_region_id', 'fk_i_city_id', 's_name', 's_name_native', 'b_active', 's_slug', 'd_coord_lat', 'd_coord_long') );
  }

  /**
   * Get the cityArea by its name and city
   *
   * @access public
   * @since  unknown
   *
   * @param   $cityAreaName
   * @param int $cityId
   *
   * @return array
   */
  public function findByName($cityAreaName, $cityId = null)
  {
    $this->dao->select($this->getFields());
    $this->dao->from($this->getTableName());
    $this->dao->where('s_name', $cityAreaName);
    $this->dao->limit(1);
    if( $cityId != null ) {
      $this->dao->where('fk_i_city_id', $cityId);
    }

    $result = $this->dao->get();

    if( $result == false ) {
      return array();
    }

    return $result->row();
  }

  /**
   * Return city areas of a given city ID
   *
   * @access public
   * @since 2.4
   * @param $cityId
   * @return array
   */
  public function findByCity($cityId) {
    $this->dao->select($this->getFields());
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_city_id', $cityId);

    $result = $this->dao->get();

    if( $result == false ) {
      return array();
    }

    return $result->result();
  }

  /**
   *  Delete a city area
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
    Item::newInstance()->deleteByCityArea($pk);
    User::newInstance()->update(array('fk_i_city_area_id' => null, 's_city_area' => ''), array('fk_i_city_area_id' => $pk));
    if(!$this->delete(array('pk_i_id' => $pk))) {
      return 1;
    }
    return 0;
  }


}

/* file end: ./oc-includes/osclass/model/CityArea.php */
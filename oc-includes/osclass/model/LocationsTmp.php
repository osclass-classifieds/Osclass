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
 * Model database for LocationsTmp table
 *
 * @package Osclass
 * @subpackage Model
 * @since 2.4
 */
class LocationsTmp extends DAO
{
  /**
   * It references to self object: LocationsTmp.
   * It is used as a singleton
   *
   * @access private
   * @since 2.4
   * @var CountryStats
   */
  private static $instance;

  /**
  * It creates a new LocationsTmp object class if it has been created
  * before, it return the previous object
  *
  * @access public
  * @since 2.4
  * @return LocationsTmp
  */
  public static function newInstance()
  {
    if (!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_locations_tmp table
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_locations_tmp');
    $this->setFields(array('id_location', 'e_type'));
  }

  /**
   * @param $max
   *
   * @return array
   */
  public function getLocations($max)
  {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->limit($max);
    $rs = $this->dao->get();

    if ($rs === false) {
      return array();
    }
    return $rs->result();
  }

  /**
   * @param array $where
   *
   * @return mixed
   */
  public function delete($where)
  {
    return $this->dao->delete($this->getTableName(), $where);
  }

  /**
   * @param $ids
   * @param $type
   *
   * @return bool|mixed
   */
  public function batchInsert($ids, $type)
  {
    if (!empty($ids)) {
      return $this->dao->query(sprintf("INSERT INTO %s (id_location, e_type) VALUES (%s, '%s')", $this->getTableName(), implode(",'".$type."'),(", $ids), $type));
    }
    return false;
  }
}

/* file end: ./oc-includes/osclass/model/LocationsTmp.php */
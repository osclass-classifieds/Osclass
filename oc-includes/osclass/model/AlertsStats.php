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
 * Model database for AlertsStats table
 *
 * @package Osclass
 * @subpackage Model
 * @since 3.1
 */
class AlertsStats extends DAO {
  /**
   * It references to self object: AlertsStats.
   * It is used as a singleton
   *
   * @access private
   * @since 3.1
   * @var AlertsStats
   */
  private static $instance;

  /**
   * It creates a new AlertsStats object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since 3.1
   * @return AlertsStats
   */
  public static function newInstance() {
    if( !self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_alerts_sent table
   */
  public function __construct() {
    parent::__construct();
    $this->setTableName('t_alerts_sent');
    $this->setPrimaryKey('d_date');
    $this->setFields( array('d_date', 'i_num_alerts_sent') );
  }

  /**
   * Increase the stat column given column name and item id
   *
   * @access public
   * @since 3.1
   * @param string $date
   * @return bool
   */
  public function increase($date) {
    // check the date it's ok
    if(!preg_match('|^[0-9]{4}-[0-9]{2}-[0-9]{2}$|', $date) ) {
      return false;
    }

    $sql = 'INSERT INTO ' . $this->getTableName() . ' (d_date, i_num_alerts_sent) VALUES (\''.date('Y-m-d').'\', 1) ON DUPLICATE KEY UPDATE i_num_alerts_sent = i_num_alerts_sent + 1';
    return $this->dao->query($sql);

/*
    // first we try to insert
    if( $this->insert(array('d_date' => $date, 'i_num_alerts_sent' => '1')) ) {
      return true;
    }

    // duplicate key?
    if( $this->getErrorLevel() != 1062 ) {
      return false;
    }

    $sql = sprintf("UPDATE %s SET i_num_alerts_sent = i_num_alerts_sent + 1 WHERE d_date = '%s'", $this->getTableName(), $date);
    $this->dao->query($sql);

    return true;
*/
  }
}

/* file end: ./oc-includes/osclass/model/AlertsStats.php */
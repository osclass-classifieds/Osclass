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
 * Model database for ItemStat table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class ItemStats extends DAO {
  /**
   * It references to self object: ItemStats.
   * It is used as a singleton
   *
   * @access private
   * @since unknown
   * @var ItemStats
   */
  private static $instance;

  /**
   * It creates a new ItemStats object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since unknown
   * @return ItemStats
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    
    return self::$instance;
  }

  /**
   * Set data related to t_item_stats table
   */
  public function __construct() {
    parent::__construct();
    $this->setTableName('t_item_stats');
    $this->setPrimaryKey('fk_i_item_id');
    $this->setFields(array('fk_i_item_id', 'i_num_views', 'i_num_spam', 'i_num_repeated', 'i_num_bad_classified', 'i_num_offensive', 'i_num_expired', 'i_num_premium_views', 'dt_date'));
  }

  /**
   * Increase the stat column given column name and item id
   *
   * @access public
   * @since unknown
   * @param string $column
   * @param int $item_id
   * @return bool
   * @todo OJO query('update ....') cambiar a ->update()
   */
  public function increase($column, $item_id) {
    //('INSERT INTO %s (fk_i_item_id, dt_date, %3$s) VALUES (%d, \'%4$s\',1) ON DUPLICATE KEY UPDATE %3$s = %3$s + 1', $this->getTableName(), $id, $column, date('Y-m-d H:i:s'));
    $increaseColumns = array('i_num_views', 'i_num_spam', 'i_num_repeated', 'i_num_bad_classified', 'i_num_offensive', 'i_num_expired', 'i_num_expired', 'i_num_premium_views');

    if(!in_array($column, $increaseColumns)) {
      return false;
    }

    if($item_id <= 0) {
      return false;
    }
    
    $num = 0;
    
    if(osc_item_stats_method() == 'PAGELOAD') {
      $num = 1;
    } else if(osc_item_stats_method() == 'SESSION' && !osc_is_item_viewed_in_session($item_id, $column)) {
      $num = 1;
    }

    $num = (int)osc_apply_filter('item_stats_increase', $num, $item_id, $column);

    if($num != 0) {
      $sql = 'INSERT INTO '.$this->getTableName().' (fk_i_item_id, dt_date, '.$column.') VALUES ('.$item_id.', \''.date('Y-m-d').'\','.$num.') ON DUPLICATE KEY UPDATE  '.$column.' = '.$column.' + '.$num.' ';
      return $this->dao->query($sql);
    } 
    
    return false;
  }

  /**
   * Insert an empty row into table item stats
   *
   * @access public
   * @since unknown
   * @param int $item_id Item id
   * @return bool
   */
  public function emptyRow($item_id) {
    return $this->insert(
      array(
        'fk_i_item_id' => $item_id,
        'dt_date' => date('Y-m-d H:i:s')
      )
    );
  }

  /**
   * Return number of views of an item
   *
   * @access public
   * @since 2.3.3
   * @param int $item_id Item id
   * @return int
   */
  public function getViews($item_id) {
    $this->dao->select('SUM(i_num_views) AS i_num_views');
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_item_id', $item_id);
    
    $result = $this->dao->get();
    
    if(!$result) {
      return 0;
    } else {
      $res = $result->result();
      return $res[0]['i_num_views'];
    }
  }

  /**
   * Return number of views of an item
   *
   * @access public
   * @since  2.3.3
   * @return int
   */
  public function getAllViews() {
    $this->dao->select('SUM(i_num_views) AS i_num_views');
    $this->dao->from($this->getTableName());
    
    $result = $this->dao->get();
    
    if(!$result) {
      return 0;
    } else {
      $res = $result->result();
      return $res[0]['i_num_views'];
    }
  }
}

/* file end: ./oc-includes/osclass/model/ItemStats.php */
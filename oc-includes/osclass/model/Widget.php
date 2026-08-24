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
class Widget extends DAO {
  /**
   *
   * @var type
   */
  private static $instance;

  /**
   * @return \type|\Widget
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
    $this->setTableName('t_widget');
    $this->setPrimaryKey('pk_i_id');
    $this->setFields(array('pk_i_id','s_description','s_location','e_kind','s_content'));
  }

  /**
   *
   * @access public
   * @since 8.3.0+
   * @return array
   */  
  public function listAll($cache_enabled = true) {
    $key = md5(osc_base_url().'Widget::listAll');
    $found = null;
    $cache = osc_cache_get($key, $found);
    
    if($cache_enabled === false || $cache === false) {
      $this->dao->from($this->getTableName());
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
   *
   * @access public
   * @since unknown
   * @param string $location
   * @return array
   */
  public function findByLocation($location) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_location', $location);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   *
   * @access public
   * @since 3.3.3+
   * @param string $description
   * @return array
   */
  public function findByDescription($description) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_description', $description);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  } 
}

/* file end: ./oc-includes/osclass/model/Widget.php */
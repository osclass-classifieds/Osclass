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
 * LastestSearches DAO
 */
class LatestSearches extends DAO {
  /**
   *
   * @var type
   */
  private static $instance;

  /**
   * @return \LatestSearches|\type
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
    $this->setTableName('t_latest_searches');
    $array_fields = array(
      'd_date',
      's_search'
    );
    
    $this->setFields($array_fields);
  }

  /**
   * Get last searches, given a limit.
   *
   * @access public
   * @since unknown
   *
   * @param int $limit
   *
   * @return array|bool
   */
  public function getSearches($limit = 20, $sort = 'd_date') {
    $this->dao->select('s_search, MAX(d_date) as d_date, COUNT(s_search) as i_total');
    $this->dao->from($this->getTableName());
    $this->dao->groupBy('s_search');
    $this->dao->orderBy($sort, 'DESC');
    $this->dao->limit($limit);
    $result = $this->dao->get();

    if($result == false) {
      return false;
    }

    $data = $result->result();
    $output = array();
    
    if(is_array($data) && count($data) > 0 && osc_latest_searches_restriction() <> 0 && osc_latest_searches_words() <> '') {
      foreach($data as $row) {
        $word = osc_latest_search_filter($row['s_search']);
        
        if($word <> '') {
          $output[] = $row;
        }
      }
      
      return $output;
    }    
    
    return $data;
  }

  /**
   * Get last searches, given since datetime.
   *
   * @access public
   * @since unknown
   *
   * @param int $datetime
   *
   * @return array|bool
   */
  public function getSearchesByDate($datetime = null) {
    $this->dao->select('d_date, s_search, COUNT(s_search) as i_total');
    $this->dao->from($this->getTableName());
    
    if($datetime !== NULL) {
      $this->dao->where(sprintf('d_date >= "%s"', date('Y-m-d H:i:s', $datetime)));
    }
    
    $this->dao->groupBy('d_date, s_search');
    $this->dao->orderBy('d_date', 'DESC');
    $this->dao->limit($limit);
    $result = $this->dao->get();

    if( $result == false ) {
      return false;
    }

    return $result->result();
  }


  /**
   * Count searches
   *
   * @access public
   * @since unknown
   *
   * @param int $time
   *
   * @return int
   */
  public function countAllSearches() {
    $this->dao->select('count(*) as i_total');
    $this->dao->from($this->getTableName());
    
    $result = $this->dao->get();

    if($result) {
      $data = $result->row();
      
      if(isset($data['i_total']) && $data['i_total'] > 0) {
        return (int)$data['i_total'];
      }
    }

    return 0;
  }
  
  /**
   * Purge search by pattern.
   *
   * @access public
   * @since unknown
   * @param string $pattern
   * @return bool
   */
  public function purgeByPattern($pattern) {
    $this->dao->from($this->getTableName());
    $this->dao->where('s_search like "' . $pattern . '"');
    return $this->dao->delete();
  }
  
  /**
   * Purge all searches by date.
   *
   * @access public
   * @since unknown
   * @param string $date
   * @return bool
   */
  public function purgeDate($date = null) {
    if($date == null) {
      return false;
    }

    $this->dao->from($this->getTableName());
    $this->dao->where('d_date <= ' . $this->dao->escape($date));
    return $this->dao->delete();
  }

  
  /**
   * Purge all searches.
   *
   * @access public
   * @since unknown
   * @return bool
   */
  public function purgeAll() {
    return $this->dao->delete($this->getTableName(), "1=1");
  }

  /**
   * Purge n last searches.
   *
   * @access public
   * @since unknown
   * @param int $number
   * @return bool
   */
  public function purgeNumber($number = null) {
    if($number == null) {
      return false;
    }

    $this->dao->select('d_date');
    $this->dao->from($this->getTableName());
    // $this->dao->groupBy('s_search');
    $this->dao->orderBy('d_date', 'DESC');
    $this->dao->limit($number, 1);
    $result = $this->dao->get();
    $last = $result->row();

    if($result == false) {
      return false;
    }

    if($result->numRows() == 0) {
      return false;
    }

    return $this->purgeDate($last['d_date']);
  }
}

/* file end: ./oc-includes/osclass/model/LatestSearches.php */
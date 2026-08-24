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
 * BanRule DAO
 */
class BanRule extends DAO {
  /**
   *
   * @var type
   */
  private static $instance;

  /**
   * @return \BanRule|\type
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
    $this->setTableName('t_ban_rule');
    $this->setPrimaryKey('pk_i_id');

    $array_fields = array(
      'pk_i_id',
      's_name',
      's_ip',
      's_email',
      'i_hit',
      'dt_date',
      'dt_expire_date'
    );

    $this->setFields($array_fields);
  }

  /**
   * Return list of ban rules
   *
   * @access public
   * @since  3.1
   *
   * @param int  $start
   * @param int  $end
   * @param string $order_column
   * @param string $order_direction
   * @param string $name
   *
   * @return array
   * @parma  string $name
   */
  public function search($start = 0, $end = 10, $order_column = 'pk_i_id', $order_direction = 'DESC', $name = '', $keyword = '', $expired = NULL, $email_like = '', $ip_like = '') {
    $allowed_sort = array('pk_i_id', 's_name', 's_ip', 's_email', 'i_hit', 'dt_expire_date', 'dt_date');
    if(!in_array($order_column, $allowed_sort)) {
      $order_column = 'pk_i_id';
    }
    $order_direction = strtoupper($order_direction);
    if(!in_array($order_direction, array('ASC', 'DESC'))) {
      $order_direction = 'DESC';
    }

    // SET data, so we always return a valid object
    $rules = array();

    $rules['rows'] = 0;
    $rules['total_results'] = 0;
    $rules['rules'] = array();

    $this->dao->select('SQL_CALC_FOUND_ROWS *');
    $this->dao->from($this->getTableName());
    $this->dao->orderBy($order_column, $order_direction);
    $this->dao->limit($start, $end);

    if($expired === true) {
      $this->dao->where(sprintf('dt_expire_date < "%s"', date('Y-m-d')));
    } else if($expired === false) {
      $this->dao->where(sprintf('(dt_expire_date >= "%s" OR dt_expire_date is null)', date('Y-m-d')));
    }

    if($name != '') {
      $this->dao->like('s_name', $name);
    }

    $email_like = trim((string)$email_like);
    if($email_like != '') {
      $email_like = str_replace('*', '%', $email_like);
      if(strpos($email_like, '%') === false) {
        $email_like = '%' . $email_like . '%';
      }
      $this->dao->where(sprintf('s_email LIKE "%s"', $this->dao->escapeStr($email_like)));
    }

    $ip_like = trim((string)$ip_like);
    if($ip_like != '') {
      $ip_like = str_replace('*', '%', $ip_like);
      if(strpos($ip_like, '%') === false) {
        $ip_like .= '%';
      }
      $this->dao->where(sprintf('s_ip LIKE "%s"', $this->dao->escapeStr($ip_like)));
    }

    $keyword = trim((string)$keyword);
    if($keyword != '') {
      $kw = $this->dao->escapeStr(str_replace('*', '%', $keyword));
      if(strpos($kw, '%') === false) {
        $kw = '%' . $kw . '%';
      }
      $this->dao->where("(s_name LIKE '" . $kw . "' OR s_ip LIKE '" . $kw . "' OR s_email LIKE '" . $kw . "' OR CAST(i_hit AS CHAR) LIKE '" . $kw . "' OR dt_expire_date LIKE '" . $kw . "' OR dt_date LIKE '" . $kw . "')");
    }

    $rs = $this->dao->get();

    if($rs == false) {
      return $rules;
    }

    $rules['rules'] = $rs->result();

    $rsRows = $this->dao->query('SELECT FOUND_ROWS() as total');
    $data = $rsRows->row();

    if($data['total']) {
      $rules['total_results'] = $data['total'];
    }

    $rsTotal = $this->dao->query('SELECT COUNT(*) as total FROM '.$this->getTableName());
    $data = $rsTotal->row();

    if($data['total']) {
      $rules['rows'] = $data['total'];
    }

    return $rules;
  }

  /**
   * Return number of ban rules
   *
   * @since 3.1
   * @return int
   */
  public function countRules($expired = NULL) {
    $this->dao->select('COUNT(*) as i_total');
    $this->dao->from($this->getTableName());

    if($expired === true) {
      $this->dao->where(sprintf('dt_expire_date < "%s"', date('Y-m-d')));
    } else if($expired === false) {
      $this->dao->where(sprintf('(dt_expire_date >= "%s" OR dt_expire_date is null)', date('Y-m-d')));
    }

    $result = $this->dao->get();

    if($result == false || $result->numRows() == 0) {
      return 0;
    }

    $row = $result->row();

    return $row['i_total'];
  }

  /**
   * Get list of email ban rules
   *
   * @return array
   */
  public function getEmailRules($expired = false) {
    $this->dao->select('pk_i_id, s_email');
    $this->dao->from($this->getTableName());

    if($expired === true) {
      $this->dao->where(sprintf('dt_expire_date < "%s"', date('Y-m-d')));
    } else if($expired === false) {
      $this->dao->where(sprintf('(dt_expire_date >= "%s" OR dt_expire_date is null)', date('Y-m-d')));
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Get list of ip ban rules
   *
   * @return array
   */
  public function getIpRules($expired = false) {
    $this->dao->select('pk_i_id, s_ip');
    $this->dao->from($this->getTableName());

    if($expired === true) {
      $this->dao->where(sprintf('dt_expire_date < "%s"', date('Y-m-d')));
    } else if($expired === false) {
      $this->dao->where(sprintf('(dt_expire_date >= "%s" OR dt_expire_date is null)', date('Y-m-d')));
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }


  /**
   * Get list of all rules
   *
   * @return array
   */
  public function listAll($expired = NULL) {
    $this->dao->select();
    $this->dao->from($this->getTableName());

    if($expired === true) {
      $this->dao->where(sprintf('dt_expire_date < "%s"', date('Y-m-d')));
    } else if($expired === false) {
      $this->dao->where(sprintf('(dt_expire_date >= "%s" OR dt_expire_date is null)', date('Y-m-d')));
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Increase counter for hits by 1
   *
   * @return array
   */
  public function increaseHit($id) {
    if($id <= 0) {
      return false;
    }

    $sql = sprintf('UPDATE %s SET i_hit = i_hit + 1 WHERE pk_i_id = %d', $this->getTableName(), (int)$id);
    return $this->dao->query($sql);
  }
}

/* file end: ./oc-includes/osclass/model/BanRule.php */

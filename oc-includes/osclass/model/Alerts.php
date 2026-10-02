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
 * Alerts DAO
 */
class Alerts extends DAO {
  /**
   *
   * @var type
   */
  private static $instance;

  /**
   * @return \Alerts|\type
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
    $this->setTableName('t_alerts');
    $this->setPrimaryKey('pk_i_id');

    $array_fields = array(
      'pk_i_id',
      's_name',
      's_email',
      'fk_i_user_id',
      's_search',
      's_param',
      's_sql',
      's_secret',
      'b_active',
      'e_type',
      'i_num_trigger',
      'dt_date',
      'dt_expire_date',
      'dt_unsub_date'
    );

    $this->setFields($array_fields);
  }

  /**
   * Searches for user alerts, given an user id.
   * If user id not exist return empty array.
   *
   * @access public
   * @since  unknown
   *
   * @param string $userId
   * @param bool   $unsub
   *
   * @return array
   */
  public function findByUser($userId, $unsub = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_user_id', $userId);

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for user alerts, given an user id.
   * If user id not exist return empty array.
   *
   * @access public
   * @since  unknown
   *
   * @param string $email
   * @param bool   $unsub
   *
   * @return array
   */
  public function findByEmail($email, $unsub = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_email', $email);

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for alerts, given a type.
   * If type don't match return empty array.
   *
   * @access public
   * @since  unknown
   * @param string $type
   * @param bool   $active
   * @param bool   $unsub
   * @return array
   */
  public function findByType($type, $active = false, $unsub = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('e_type', $type);

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    if($active) {
      $this->dao->where('b_active', 1);
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for alerts, given a type group by s_search.
   * If type don't match return empty array.
   *
   * @access public
   * @since  unknown
   * @param string $type
   * @param bool   $active
   * @param bool   $unsub
   * @return array
   */
  public function findByTypeGroup($type, $active = false, $unsub = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('e_type', $type);

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    if($active){
      $this->dao->where('b_active', 1);
    }

    $this->dao->groupBy('s_search');
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for alerts, given an user and a s_search.
   * If type don't match return empty array.
   *
   * @access public
   * @since  unknown
   * @param string $search
   * @param string $user
   * @param bool   $unsub
   * @return array
   */
  public function findBySearchAndUser($search, $user, $unsub = false) {
    //$search = substr($search, 0, strpos($search, '"user_ids"'));   // Only search up to last relevant key... sql, params can be dynamic

    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_user_id', $user);
    $this->dao->where('s_search', $search);
    // $this->dao->like('s_search', $search, 'after');

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for alerts, given a type group and a s_search.
   * If type don't match return empty array.
   *
   * @access public
   * @since  unknown
   * @param string $search
   * @param string $type
   * @param bool   $unsub
   * @return array
   */
  public function findBySearchAndType($search, $type, $unsub = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('e_type', $type);
    $this->dao->where('s_search', $search);

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for users, given a type group and a s_search.
   * If type don't match return empty array.
   *
   * @access public
   * @since  unknown
   * @param string $search
   * @param string $type
   * @param bool   $active
   * @param bool   $unsub
   * @return array
   */
  public function findUsersBySearchAndType($search, $type, $active = false, $unsub = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('e_type', $type);
    $this->dao->where('s_search', $search);

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    if($active){
      $this->dao->where('b_active', 1);
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for alerts, given a type group and an user id
   * If type don't match return empty array.
   *
   * @access public
   * @since  unknown
   * @param int  $userId
   * @param string $type
   * @param bool   $unsub
   * @return array
   */
  public function findByUserByType($userId, $type, $unsub = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());

    $conditions = array('e_type' => $type, 'fk_i_user_id' => $userId);

    $this->dao->where($conditions);

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for alerts, given a type group and an email
   * If type don't match return empty array.
   *
   * @access public
   * @since  unknown
   * @param string $email
   * @param string $type
   * @param bool   $unsub
   * @return array
   */
  public function findByEmailByType($email, $type, $unsub = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());

    $conditions = array('e_type' => $type, 's_email' => $email);

    if(!$unsub) {
      $this->dao->where('dt_unsub_date IS NULL');
    }

    $this->dao->where($conditions);

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Create a new alert
   *
   * @access public
   * @since unknown
   * @param int $userid
   * @param string $email
   * @param string $alert
   * @param string $secret
   * @param string $type
   * @return bool on success
   */
  public function createAlert($userid, $email, $name, $alert, $secret, $type = 'DAILY', $params = null, $sql = null) {
    if(!osc_alerts_enabled()) {
      return false;
    }

    $results = 0;
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_search', $alert);

    $this->dao->where('dt_unsub_date IS NULL');

    if($userid == 0 || $userid == null){
      $this->dao->where('fk_i_user_id', 0);
      $this->dao->where('s_email', $email);

    } else {
      $this->dao->where('fk_i_user_id', $userid);
    }

    $results = $this->dao->get();


    if($results->numRows() == 0) {
      $expire_date = null;
      if(function_exists('osc_alerts_default_non_expiring') && osc_alerts_default_non_expiring() === true) {
        $expire_date = null;
      } else {
        $months = 3;
        if(function_exists('osc_alerts_expiration_months_user') && function_exists('osc_alerts_expiration_months_guest')) {
          $months = ((int)$userid > 0 ? osc_alerts_expiration_months_user() : osc_alerts_expiration_months_guest());
        }
        if((int)$months > 0) {
          $expire_date = date('Y-m-d H:i:s', strtotime('+' . (int)$months . ' month'));
        }
      }

      $this->dao->insert($this->getTableName(), array(
        'fk_i_user_id' => $userid,
        's_name' => $name,
        's_email' => $email,
        's_search' => $alert,
        's_param' => $params,
        's_sql' => $sql,
        'e_type' => $type,
        's_secret' => $secret,
        'i_num_trigger' => 0,
        'dt_date' => date('Y-m-d H:i:s'),
        'dt_expire_date' => $expire_date
      ));

      return $this->dao->insertedId();
    }

    return false;
  }

  /**
   * Activate an alert
   *
   * @access public
   * @since unknown
   * @param string $id
   * @return mixed false on fail, int of num. of affected rows
   */
  public function activate($id) {
    return $this->dao->update($this->getTableName(), array('b_active' => 1), array('pk_i_id' => $id));
  }

  /**
   * Dectivate an alert
   *
   * @access public
   * @since 3.1
   * @param string $id
   * @return mixed false on fail, int of num. of affected rows
   */
  public function deactivate($id) {
    return $this->dao->update($this->getTableName(), array('b_active' => 0), array('pk_i_id' => $id));
  }

  /**
   * Unsub from an alert
   *
   * @access public
   * @since 3.1
   * @param string $id
   * @return mixed false on fail, int of num. of affected rows
   */
  public function unsub($id) {
    return $this->dao->update($this->getTableName(), array('dt_unsub_date' => date('Y-m-d H:i:s')), array('pk_i_id' => $id));
  }

  /**
   * Search alerts
   *
   * @access public
   * @since  3.1
   * @param int  $start
   * @param int  $end
   * @param string $order_column
   * @param string $order_direction
   * @param string $email
   * @return array
   */
  public function search($start = 0, $end = 10, $order_column = 'dt_date', $order_direction = 'DESC', $email = '', $user_id = null, $user_email = '') {
    $allowed_sort = array('s_email', 's_name', 'i_num_trigger', 'dt_date', 'dt_expire_date', 'dt_unsub_date');
    if(!in_array($order_column, $allowed_sort)) {
      $order_column = 'dt_date';
    }
    $order_direction = strtoupper($order_direction);
    if(!in_array($order_direction, array('ASC', 'DESC'))) {
      $order_direction = 'DESC';
    }

    $alerts = array();
    $alerts['rows'] = 0;
    $alerts['total_results'] = 0;
    $alerts['alerts'] = array();

    $this->dao->select('SQL_CALC_FOUND_ROWS *');
    $this->dao->from($this->getTableName());
    $this->dao->orderBy($order_column, $order_direction);
    $this->dao->limit($start, $end);

    if($user_id !== null) {
      $this->dao->where('fk_i_user_id', (int)$user_id);
    }

    if(trim((string)$user_email) != '') {
      $this->dao->where('s_email', trim((string)$user_email));
    }

    $email = trim((string)$email);
    if($email != '') {
      $kw = $this->dao->escapeStr(str_replace('*', '%', $email));
      if(strpos($kw, '%') === false) {
        $kw = '%' . $kw . '%';
      }
      $this->dao->where("(s_email LIKE '" . $kw . "' OR s_name LIKE '" . $kw . "' OR s_search LIKE '" . $kw . "' OR s_param LIKE '" . $kw . "' OR s_sql LIKE '" . $kw . "' OR CAST(pk_i_id AS CHAR) LIKE '" . $kw . "' OR CAST(fk_i_user_id AS CHAR) LIKE '" . $kw . "')");
    }

    $rs = $this->dao->get();

    if(!$rs) {
      return $alerts;
    }

    $alerts['alerts'] = $rs->result();

    $rsRows = $this->dao->query('SELECT FOUND_ROWS() as total');
    $data = $rsRows->row();

    if($data['total']) {
      $alerts['total_results'] = $data['total'];
    }

    $rsTotal = $this->dao->query('SELECT COUNT(*) as total FROM ' . $this->getTableName());
    $data = $rsTotal->row();

    if($data['total']) {
      $alerts['rows'] = $data['total'];
    }

    return $alerts;
  }

  /**
   * Update alert name
   */
  public function updateAlertName($id, $name) {
    return $this->dao->update($this->getTableName(), array('s_name' => $name), array('pk_i_id' => $id));
  }

  /**
   * Update alert type
   */
  public function updateAlertType($id, $type) {
    return $this->dao->update($this->getTableName(), array('e_type' => $type), array('pk_i_id' => $id));
  }

  /**
   * Update alert expiration date by operation code
   */
  public function updateAlertExpiration($id, $op, $alert = null) {
    $id = (int)$id;
    if($id <= 0) {
      return false;
    }

    if($alert === null || !is_array($alert) || !isset($alert['pk_i_id'])) {
      $alert = $this->findByPrimaryKey($id);
    }

    if(!isset($alert['pk_i_id'])) {
      return false;
    }

    $op = strtoupper(trim((string)$op));
    $allowed_ops = array('IN_DEFAULT', 'PLUS_DEFAULT', 'MINUS_DEFAULT', 'EXPIRE', 'IN_3M', 'PLUS_3M', 'IN_12M', 'PLUS_12M', 'RENEW', 'NEVER');
    if(!in_array($op, $allowed_ops, true)) {
      return false;
    }

    $defaultMonths = 3;
    if(function_exists('osc_alerts_expiration_months_user')) {
      $defaultMonths = (int)osc_alerts_expiration_months_user();
      if($defaultMonths <= 0) {
        $defaultMonths = 3;
      }
    }

    $expire = (isset($alert['dt_expire_date']) ? trim((string)$alert['dt_expire_date']) : '');
    $now = time();
    $baseTs = ($expire != '' ? strtotime($expire) : $now);
    if($baseTs === false) {
      $baseTs = $now;
    }

    $newExpire = null;

    switch($op) {
      case 'NEVER':
        $newExpire = null;
        break;
      case 'EXPIRE':
        $newExpire = date('Y-m-d H:i:s', $now - 60);
        break;
      case 'RENEW':
        $newExpire = date('Y-m-d H:i:s', strtotime('+' . $defaultMonths . ' month', $now));
        break;
      case 'IN_DEFAULT':
        $newExpire = date('Y-m-d H:i:s', strtotime('+' . $defaultMonths . ' month', $now));
        break;
      case 'IN_3M':
        $newExpire = date('Y-m-d H:i:s', strtotime('+3 month', $now));
        break;
      case 'IN_12M':
        $newExpire = date('Y-m-d H:i:s', strtotime('+12 month', $now));
        break;
      case 'PLUS_DEFAULT':
        $newExpire = date('Y-m-d H:i:s', strtotime('+' . $defaultMonths . ' month', $baseTs));
        break;
      case 'PLUS_3M':
        $newExpire = date('Y-m-d H:i:s', strtotime('+3 month', $baseTs));
        break;
      case 'PLUS_12M':
        $newExpire = date('Y-m-d H:i:s', strtotime('+12 month', $baseTs));
        break;
      case 'MINUS_DEFAULT':
        $newExpire = date('Y-m-d H:i:s', strtotime('-' . $defaultMonths . ' month', $baseTs));
        break;
      default:
        return false;
    }

    return $this->dao->update($this->getTableName(), array('dt_expire_date' => $newExpire), array('pk_i_id' => $id));
  }

  /**
   * Increase the stat column given column name and item id
   *
   * @access public
   * @since unknown
   * @param string $column
   * @param int $id
   * @return bool
   */
  public function increase($column, $id, $num = 1) {
    $supported_columns = array('i_num_trigger');

    if(!in_array($column, $supported_columns)) {
      return false;
    }

    if(!is_numeric($id)) {
      return false;
    }

    $num = (int)osc_apply_filter('alert_stats_increase', $num, $id, $column);

    if($num != 0) {
      $sql = 'UPDATE ' . $this->getTableName() . ' SET ' . $column . ' = ' . $column . ' + ' . $num . ' WHERE pk_i_id = ' . $id;
      return $this->dao->query($sql);
    }

    return false;
  }

  /**
   * Increase alert trigger count
   */
  public function increaseTrigger($id, $num = 1) {
    return $this->increase('i_num_trigger', $id, $num);
  }
}

/* file end: ./oc-includes/osclass/model/Alerts.php */

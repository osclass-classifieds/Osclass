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


class Log extends DAO {
  private static $instance;

  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  public function __construct() {
    parent::__construct();
    $this->setTableName('t_log');

    $array_fields = array(
      'dt_date',
      's_section',
      's_action',
      'fk_i_id',
      's_data',
      's_detail',
      's_comment',
      's_ip',
      's_who',
      'fk_i_who_id'
    );

    $this->setFields($array_fields);
  }


  // Insert new log
  public function insertLog($section, $action, $id, $data, $who, $whoId, $comment = null, $detail = null) {
    if(!osc_logging_enabled()) {
      return false;
    }

    $detail_json = null;

    if($detail != '') {
      if(is_array($detail) && !empty($detail)) {
        $detail_json = json_encode($detail);

      } elseif(is_string($detail) || is_numeric($detail)) {
        $detail_json = trim((string)$detail);

      } else {
        $detail_json = json_encode(array($detail));
      }
    }

    $array_set = array(
      'dt_date' => date('Y-m-d H:i:s'),
      's_section' => $section,
      's_action' => $action,
      'fk_i_id' => $id,
      's_data' => $data,
      's_detail' => $detail_json,
      's_comment' => $comment,
      's_ip' => (osc_get_ip() <> '' ? osc_get_ip() : '127.0.0.1'),
      's_who' => $who,
      'fk_i_who_id' => $whoId
    );

    return $this->dao->insert($this->getTableName(), $array_set);
  }


  // Search for alerts (datatables)
  public function search($options = array()) {
    $start = isset($options['start']) ? (int)$options['start'] : 0;
    $limit = isset($options['limit']) ? (int)$options['limit'] : 25;
    $order_column = isset($options['order_column']) ? (string)$options['order_column'] : 'dt_date';
    $order_direction = isset($options['order_direction']) ? (string)$options['order_direction'] : 'DESC';
    $allowed_sort = array('dt_date', 's_section', 's_action', 'fk_i_id', 's_data', 's_comment', 's_ip', 's_who');
    if(!in_array($order_column, $allowed_sort)) {
      $order_column = 'dt_date';
    }
    $order_direction = strtoupper($order_direction);
    if(!in_array($order_direction, array('ASC', 'DESC'))) {
      $order_direction = 'DESC';
    }

    $date = isset($options['date']) ? (string)$options['date'] : '';
    $section = isset($options['section']) ? (string)$options['section'] : '';
    $action = isset($options['action']) ? (string)$options['action'] : '';
    $id = isset($options['id']) ? (int)$options['id'] : null;
    $data = isset($options['data']) ? (string)$options['data'] : '';
    $detail = isset($options['detail']) ? (string)$options['detail'] : '';
    $comment = isset($options['comment']) ? (string)$options['comment'] : '';
    $ip = isset($options['ip']) ? (string)$options['ip'] : '';
    $who = isset($options['who']) ? (string)$options['who'] : '';
    $who_id = isset($options['who_id']) ? (int)$options['who_id'] : null;
    $keyword = isset($options['keyword']) ? (string)$options['keyword'] : '';

    $logs = array();
    $logs['rows'] = 0;
    $logs['total_results'] = 0;
    $logs['logs'] = array();

    $this->dao->select('SQL_CALC_FOUND_ROWS *');
    $this->dao->from($this->getTableName());
    $this->dao->orderBy($order_column, $order_direction);
    $this->dao->limit($start, $limit);

    if($date != '') {
      $esc_date = $this->dao->escapeStr($date);
      $this->dao->where("dt_date >= '" . $esc_date . " 00:00:00' AND dt_date <= '" . $esc_date . " 23:59:59'", null);
    }

    if($section != '') {
      $this->dao->where('s_section', $section);
    }

    if($action != '') {
      $this->dao->where('s_action', $action);
    }

    if($id !== null && $id >= 0) {
      $this->dao->where('fk_i_id', $id);
    }

    if($data != '') {
      $this->dao->like('s_data', $data);
    }

    if($detail != '') {
      $this->dao->like('s_detail', $detail);
    }

    if($comment != '') {
      $this->dao->like('s_comment', $comment);
    }

    if($ip != '') {
      $this->dao->where('s_ip', $ip);
    }

    if($who != '') {
      $this->dao->where('s_who', $who);
    }

    if($who_id !== null && $who_id >= 0) {
      $this->dao->where('fk_i_who_id', $who_id);
    }

    if($keyword != '') {
      $kw = $this->dao->escapeStr(str_replace('*', '%', $keyword));
      if(strpos($kw, '%') === false) {
        $kw = '%' . $kw . '%';
      }
      $this->dao->where("(dt_date LIKE '" . $kw . "' OR s_section LIKE '" . $kw . "' OR s_action LIKE '" . $kw . "' OR CAST(fk_i_id AS CHAR) LIKE '" . $kw . "' OR COALESCE(s_data, '') LIKE '" . $kw . "' OR COALESCE(s_detail, '') LIKE '" . $kw . "' OR COALESCE(s_comment, '') LIKE '" . $kw . "' OR COALESCE(s_ip, '') LIKE '" . $kw . "' OR s_who LIKE '" . $kw . "' OR CAST(fk_i_who_id AS CHAR) LIKE '" . $kw . "')");
    }


    $rs = $this->dao->get();

    if(!$rs) {
      return $logs;
    }

    $logs['logs'] = $rs->result();

    $rsRows = $this->dao->query('SELECT FOUND_ROWS() as total');
    $data = $rsRows->row();

    if($data['total']) {
      $logs['total_results'] = $data['total'];
    }

    $rsTotal = $this->dao->query('SELECT COUNT(*) as total FROM ' . $this->getTableName());
    $data = $rsTotal->row();

    if($data['total']) {
      $logs['rows'] = $data['total'];
    }

    return $logs;
  }


  // Delete log by legacy composite key (single row only)
  public function deleteLog($date, $section, $action, $id) {
    $dt_date = $this->dao->escapeStr((string)$date);
    $s_section = $this->dao->escapeStr((string)$section);
    $s_action = $this->dao->escapeStr((string)$action);
    $fk_i_id = (int)$id;

    return $this->dao->query('DELETE FROM ' . $this->getTableName() . " WHERE dt_date = '" . $dt_date . "' AND s_section = '" . $s_section . "' AND s_action = '" . $s_action . "' AND fk_i_id = " . $fk_i_id . ' LIMIT 1');
  }


  // Delete log by full row data (single row only)
  public function deleteExactLog($log) {
    if(!is_array($log)) {
      return false;
    }

    $dt_date = $this->dao->escapeStr(isset($log['dt_date']) ? (string)$log['dt_date'] : '');
    $s_section = $this->dao->escapeStr(isset($log['s_section']) ? (string)$log['s_section'] : '');
    $s_action = $this->dao->escapeStr(isset($log['s_action']) ? (string)$log['s_action'] : '');
    $fk_i_id = (int)(isset($log['fk_i_id']) ? $log['fk_i_id'] : 0);
    $s_data = $this->dao->escapeStr(isset($log['s_data']) ? (string)$log['s_data'] : '');
    $s_detail = isset($log['s_detail']) && $log['s_detail'] !== null ? $this->dao->escapeStr((string)$log['s_detail']) : null;
    $s_comment = isset($log['s_comment']) && $log['s_comment'] !== null ? $this->dao->escapeStr((string)$log['s_comment']) : null;
    $s_ip = $this->dao->escapeStr(isset($log['s_ip']) ? (string)$log['s_ip'] : '');
    $s_who = $this->dao->escapeStr(isset($log['s_who']) ? (string)$log['s_who'] : '');
    $fk_i_who_id = (int)(isset($log['fk_i_who_id']) ? $log['fk_i_who_id'] : 0);

    $where = "dt_date = '" . $dt_date . "' AND s_section = '" . $s_section . "' AND s_action = '" . $s_action . "' AND fk_i_id = " . $fk_i_id . " AND s_data = '" . $s_data . "' AND s_ip = '" . $s_ip . "' AND s_who = '" . $s_who . "' AND fk_i_who_id = " . $fk_i_who_id;
    $where .= ($s_detail !== null ? " AND coalesce(s_detail, '') = '" . $s_detail . "'" : " AND s_detail IS NULL");
    $where .= ($s_comment !== null ? " AND coalesce(s_comment, '') = '" . $s_comment . "'" : " AND s_comment IS NULL");

    return $this->dao->query('DELETE FROM ' . $this->getTableName() . ' WHERE ' . $where . ' LIMIT 1');
  }
}

/* file end: ./oc-includes/osclass/model/Log.php */

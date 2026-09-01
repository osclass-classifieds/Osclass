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
    if($detail != '' && is_array($detail) && !empty($detail)) {
      $detail_json = json_encode($detail);
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
      $this->dao->like('dt_date', $date);
    }
    
    if($section != '') {
      $this->dao->like('s_section', $section);
    }
    
    if($action != '') {
      $this->dao->like('s_action', $action);
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
      $this->dao->like('s_ip', $ip);
    }
    
    if($who != '') {
      $this->dao->like('s_who', $who);
    }
    
    if($who_id !== null && $who_id >= 0) {
      $this->dao->where('fk_i_who_id', $who_id);
    }
    
    if($keyword != '') {
      $this->dao->like('dt_date', $keyword);
      $this->dao->orLike('s_section', $keyword);
      $this->dao->orLike('s_action', $keyword);
      $this->dao->orLike('fk_i_id', $keyword);
      $this->dao->orLike('s_data', $keyword);
      $this->dao->orLike('s_detail', $keyword);
      $this->dao->orLike('s_comment', $keyword);
      $this->dao->orLike('s_ip', $keyword);
      $this->dao->orLike('s_who', $keyword);
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


  // Delete log - may not be 100% precise!
  public function deleteLog($date, $section, $action, $id) {
    $where = array(
      'dt_date' => $date,
      's_section' => $section,
      's_action' => $action,
      'fk_i_id' => $id
    );

    return $this->dao->delete($this->getTableName(), $where);
  }
}

/* file end: ./oc-includes/osclass/model/Log.php */
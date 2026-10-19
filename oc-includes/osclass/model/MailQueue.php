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
 * Mail queue (t_mail_queue). Priority 1 instant, 2 normal, 3 bulk.
 */
class MailQueue extends DAO {
  private static $instance;

  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  public function __construct() {
    parent::__construct();
    $this->setTableName('t_mail_queue');
    $this->setPrimaryKey('pk_i_id');

    $array_fields = array(
      'pk_i_id',
      'i_priority',
      'e_status',
      's_type',
      's_group',
      's_ref',
      's_to_email',
      's_subject',
      's_params',
      's_lock',
      'i_attempts',
      'b_attachment',
      'b_private',
      's_error',
      'dt_created',
      'dt_send_after',
      'dt_expire',
      'dt_lock',
      'dt_done'
    );

    $this->setFields($array_fields);
  }


  // Ids of due pending rows of one priority, oldest first. With $skip_groups only rows of other named groups.
  public function findDue($priority, $limit, $skip_groups = null) {
    $sql = sprintf("SELECT pk_i_id, s_group FROM %s WHERE e_status = 'PENDING' AND i_priority = %d AND dt_send_after <= '%s'", $this->getTableName(), (int)$priority, date('Y-m-d H:i:s'));

    if(is_array($skip_groups)) {
      $sql .= ' AND s_group IS NOT NULL';

      if(count($skip_groups) > 0) {
        $sql .= " AND s_group NOT IN ('" . implode("','", array_map(array($this->dao, 'escapeStr'), $skip_groups)) . "')";
      }
    }

    $result = $this->dao->query($sql . ' ORDER BY dt_send_after ASC LIMIT ' . max(1, (int)$limit));
    if($result) {
      return $result->result();
    }

    return array();
  }


  // Claim pending rows for one run (safe with parallel runs) and return them in the given order
  public function claim($ids, $token) {
    $ids = array_values(array_filter(array_map('intval', (array)$ids)));
    if(empty($ids)) {
      return array();
    }

    $list = implode(',', $ids);
    $token = $this->dao->escapeStr($token);
    $this->dao->query(sprintf("UPDATE %s SET e_status = 'SENDING', s_lock = '%s', dt_lock = '%s', i_attempts = i_attempts + 1 WHERE pk_i_id IN (%s) AND e_status = 'PENDING'", $this->getTableName(), $token, date('Y-m-d H:i:s'), $list));

    $result = $this->dao->query(sprintf("SELECT * FROM %s WHERE pk_i_id IN (%s) AND s_lock = '%s' AND e_status = 'SENDING' ORDER BY FIELD(pk_i_id, %s)", $this->getTableName(), $list, $token, $list));
    if($result) {
      return $result->result();
    }

    return array();
  }


  // Give claimed but not sent rows back to the queue
  public function release($ids, $token) {
    $ids = array_values(array_filter(array_map('intval', (array)$ids)));
    if(empty($ids)) {
      return 0;
    }

    $this->dao->query(sprintf("UPDATE %s SET e_status = 'PENDING', s_lock = NULL, dt_lock = NULL, i_attempts = IF(i_attempts > 0, i_attempts - 1, 0) WHERE pk_i_id IN (%s) AND s_lock = '%s' AND e_status = 'SENDING'", $this->getTableName(), implode(',', $ids), $this->dao->escapeStr($token)));
    return $this->dao->affectedRows();
  }


  // Rows left in SENDING since before $date (crashed run)
  public function findStuck($date, $limit = 100) {
    $result = $this->dao->query(sprintf("SELECT * FROM %s WHERE e_status = 'SENDING' AND dt_lock < '%s' LIMIT %d", $this->getTableName(), $this->dao->escapeStr($date), (int)$limit));
    if($result) {
      return $result->result();
    }

    return array();
  }


  // Ids of finished rows (SENT, FAILED, CANCELLED) done before $date, oldest first
  public function findDone($status, $date, $limit = 1000) {
    $result = $this->dao->query(sprintf("SELECT pk_i_id FROM %s WHERE e_status = '%s' AND dt_done < '%s' ORDER BY dt_done ASC LIMIT %d", $this->getTableName(), $this->dao->escapeStr($status), $this->dao->escapeStr($date), (int)$limit));
    if($result) {
      return $result->result();
    }

    return array();
  }


  // Ids among $ids that have stored attachments and are not being sent
  public function findAttachmentIds($ids) {
    $ids = array_values(array_filter(array_map('intval', (array)$ids)));
    if(empty($ids)) {
      return array();
    }

    $result = $this->dao->query(sprintf("SELECT pk_i_id FROM %s WHERE pk_i_id IN (%s) AND b_attachment = 1 AND e_status <> 'SENDING'", $this->getTableName(), implode(',', $ids)));
    if($result) {
      return array_column($result->result(), 'pk_i_id');
    }

    return array();
  }


  // Delete rows that are not being sent
  public function deleteIds($ids) {
    $ids = array_values(array_filter(array_map('intval', (array)$ids)));
    if(empty($ids)) {
      return 0;
    }

    $this->dao->query(sprintf("DELETE FROM %s WHERE pk_i_id IN (%s) AND e_status <> 'SENDING'", $this->getTableName(), implode(',', $ids)));
    return $this->dao->affectedRows();
  }


  // Change rows in one of $from statuses, by ids or by group
  public function changeStatus($ids, $group, $from, $values) {
    $where = array();
    $ids = array_values(array_filter(array_map('intval', (array)$ids)));

    if(!empty($ids)) {
      $where[] = 'pk_i_id IN (' . implode(',', $ids) . ')';
    } else if(trim((string)$group) != '') {
      $where[] = "s_group = '" . $this->dao->escapeStr(trim((string)$group)) . "'";
    } else {
      return 0;
    }

    $where[] = "e_status IN ('" . implode("','", array_map(array($this->dao, 'escapeStr'), (array)$from)) . "')";

    $set = array();
    foreach($values as $k => $v) {
      if(in_array($k, $this->getFields())) {
        $set[] = $k . ' = ' . ($v === null ? 'NULL' : "'" . $this->dao->escapeStr((string)$v) . "'");
      }
    }

    if(empty($set)) {
      return 0;
    }

    $this->dao->query('UPDATE ' . $this->getTableName() . ' SET ' . implode(', ', $set) . ' WHERE ' . implode(' AND ', $where));
    return $this->dao->affectedRows();
  }


  // Admin list (no body), totals for pagination
  public function search($options = array()) {
    $start = (isset($options['start']) ? max(0, (int)$options['start']) : 0);
    $limit = (isset($options['limit']) ? max(1, (int)$options['limit']) : 25);
    $order_column = (isset($options['order_column']) ? (string)$options['order_column'] : 'pk_i_id');
    $order_direction = (isset($options['order_direction']) && strtoupper((string)$options['order_direction']) == 'ASC' ? 'ASC' : 'DESC');

    if(!in_array($order_column, array('pk_i_id', 'dt_send_after', 'dt_done', 's_to_email', 's_subject', 's_type', 's_group', 'i_priority', 'i_attempts'))) {
      $order_column = 'pk_i_id';
    }

    $where = array();

    if(!empty($options['status'])) {
      $where[] = "e_status = '" . $this->dao->escapeStr((string)$options['status']) . "'";
    }

    if(!empty($options['priority'])) {
      $where[] = 'i_priority = ' . (int)$options['priority'];
    }

    if(isset($options['type']) && $options['type'] != '') {
      $where[] = "s_type = '" . $this->dao->escapeStr((string)$options['type']) . "'";
    }

    if(isset($options['group']) && $options['group'] != '') {
      $where[] = "s_group = '" . $this->dao->escapeStr((string)$options['group']) . "'";
    }

    if(isset($options['ref']) && $options['ref'] != '') {
      $where[] = "s_ref = '" . $this->dao->escapeStr((string)$options['ref']) . "'";
    }

    if(isset($options['date_from']) && $options['date_from'] != '') {
      $where[] = "dt_created >= '" . $this->dao->escapeStr((string)$options['date_from']) . " 00:00:00'";
    }

    if(isset($options['date_to']) && $options['date_to'] != '') {
      $where[] = "dt_created <= '" . $this->dao->escapeStr((string)$options['date_to']) . " 23:59:59'";
    }

    // Recipient starts with the keyword (index) or subject contains it
    if(isset($options['keyword']) && trim((string)$options['keyword']) != '') {
      $kw = $this->dao->escapeStr(trim((string)$options['keyword']), true);
      $where[] = "(s_to_email LIKE '" . $kw . "%' OR s_subject LIKE '%" . $kw . "%')";
    }

    $sql_where = (count($where) > 0 ? ' WHERE ' . implode(' AND ', $where) : '');
    $data = array('rows' => array(), 'total_filtered' => 0);

    $result = $this->dao->query('SELECT pk_i_id, i_priority, e_status, s_type, s_group, s_ref, s_to_email, s_subject, i_attempts, b_attachment, b_private, s_error, dt_created, dt_send_after, dt_expire, dt_lock, dt_done FROM ' . $this->getTableName() . $sql_where . ' ORDER BY ' . $order_column . ' ' . $order_direction . ($order_column != 'pk_i_id' ? ', pk_i_id ' . $order_direction : '') . ' LIMIT ' . $start . ', ' . $limit);
    if($result) {
      $data['rows'] = $result->result();
    }

    $result = $this->dao->query('SELECT COUNT(*) AS total FROM ' . $this->getTableName() . $sql_where);
    if($result) {
      $row = $result->row();
      $data['total_filtered'] = (int)(isset($row['total']) ? $row['total'] : 0);
    }

    return $data;
  }


  // True when some row waits or is being sent
  public function hasOpen() {
    $result = $this->dao->query(sprintf("SELECT pk_i_id FROM %s WHERE e_status IN ('PENDING','SENDING') LIMIT 1", $this->getTableName()));
    return ($result && $result->numRows() > 0);
  }


  // Counters: rows per status, oldest due pending send date, sent and failed since $since
  public function stats($since) {
    $stats = array('PENDING' => 0, 'SENDING' => 0, 'SENT' => 0, 'FAILED' => 0, 'CANCELLED' => 0, 'oldest_due' => '', 'sent_since' => 0, 'failed_since' => 0);

    $result = $this->dao->query('SELECT e_status, COUNT(*) AS total FROM ' . $this->getTableName() . ' GROUP BY e_status');
    if($result) {
      foreach($result->result() as $row) {
        $stats[$row['e_status']] = (int)$row['total'];
      }
    }

    $result = $this->dao->query(sprintf("SELECT i_priority, MIN(dt_send_after) AS dt_min FROM %s WHERE e_status = 'PENDING' GROUP BY i_priority", $this->getTableName()));
    if($result) {
      $now = date('Y-m-d H:i:s');
      foreach($result->result() as $row) {
        if($row['dt_min'] != '' && $row['dt_min'] <= $now && ($stats['oldest_due'] == '' || $row['dt_min'] < $stats['oldest_due'])) {
          $stats['oldest_due'] = $row['dt_min'];
        }
      }
    }

    foreach(array('SENT' => 'sent_since', 'FAILED' => 'failed_since') as $status => $key) {
      $result = $this->dao->query(sprintf("SELECT COUNT(*) AS total FROM %s WHERE e_status = '%s' AND dt_done >= '%s'", $this->getTableName(), $status, $this->dao->escapeStr($since)));
      if($result) {
        $row = $result->row();
        $stats[$key] = (int)(isset($row['total']) ? $row['total'] : 0);
      }
    }

    return $stats;
  }
}

/* file end: ./oc-includes/osclass/model/MailQueue.php */

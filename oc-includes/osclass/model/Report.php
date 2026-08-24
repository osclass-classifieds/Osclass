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
 * Model database for Report table
 *
 * @package Osclass
 * @subpackage Model
 * @since 8.4.0
 */
class Report extends DAO {
  /**
   * It references to self object: Report.
   * It is used as a singleton
   *
   * @access private
   * @since 8.4.0
   * @var Report
   */
  private static $instance;

  /**
   * It creates a new Report object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since 8.4.0
   * @return Report
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_report table
   */
  public function __construct() {
    parent::__construct();
    $this->setTableName('t_report');
    $this->setPrimaryKey('pk_i_id');
    $array_fields = array(
      'pk_i_id',
      'fk_i_reporter_user_id',
      'fk_i_user_id',
      'fk_i_item_id',
      'i_reported_id',
      'fk_c_locale_code',
      's_type',
      's_reason',
      's_status',
      's_source',
      's_comment',
      's_admin_comment',
      's_file',
      'b_open',
      'dt_status_date',
      'dt_update_date',
      'dt_create_date'
    );

    $this->setFields($array_fields);
  }

  /**
   * Get table name of report comments
   *
   * @access public
   * @since 8.4.0
   * @return string
   */
  public function getCommentTableName() {
    return DB_TABLE_PREFIX . 't_report_comment';
  }

  /**
   * Create new report, returns report id or false
   *
   * @access public
   * @since 8.4.0
   * @param array $data
   * @return int|bool
   */
  public function createReport($data) {
    $data['s_status'] = isset($data['s_status']) ? $data['s_status'] : 'submitted';
    $data['b_open'] = 1;
    $data['dt_status_date'] = date('Y-m-d H:i:s');
    $data['dt_create_date'] = date('Y-m-d H:i:s');
    $data['dt_update_date'] = $data['dt_create_date'];

    if(!$this->insert($data)) {
      return false;
    }

    $id = $this->dao->insertedId();

    Log::newInstance()->insertLog(
      'report',
      'create',
      $id,
      (string)@$data['s_type'] . '/' . (string)@$data['s_reason'],
      'user',
      (int)$data['fk_i_reporter_user_id'],
      osc_substr((string)@$data['s_comment'], 0, 250)
    );

    return $id;
  }

  /**
   * Update report status, keeps b_open and status date in sync
   *
   * @access public
   * @since 8.4.0
   * @param int $id
   * @param string $status
   * @param string $who
   * @param int $whoId
   * @return bool
   */
  public function updateStatus($id, $status, $who = 'admin', $whoId = 0) {
    $report = $this->findByPrimaryKey($id);
    if(!$report || !in_array($status, array_keys(osc_report_statuses()))) {
      return false;
    }

    if($report['s_status'] == $status) {
      return true;
    }

    $status = osc_apply_filter('pre_report_status_change', $status, $report);
    if($status === false || $status === '' || !in_array($status, array_keys(osc_report_statuses()))) {
      return false;
    }

    if($report['s_status'] == $status) {
      return true;
    }

    $this->update(array(
      's_status' => $status,
      'b_open' => osc_report_status_is_closed($status) ? 0 : 1,
      'dt_status_date' => date('Y-m-d H:i:s'),
      'dt_update_date' => date('Y-m-d H:i:s')
    ), array('pk_i_id' => $id));

    Log::newInstance()->insertLog('report', 'status', $id, $report['s_status'] . ' > ' . $status, $who, $whoId);

    return true;
  }

  /**
   * Add comment to report, marks admin replies as seen
   *
   * @access public
   * @since 8.4.0
   * @param int $reportId
   * @param string $comment
   * @param int $userId
   * @param int $adminId
   * @return int|bool
   */
  public function addComment($reportId, $comment, $userId = null, $adminId = null) {
    $report = $this->findByPrimaryKey($reportId);
    if(!$report) {
      return false;
    }

    $status = $this->dao->insert($this->getCommentTableName(), array(
      'fk_i_report_id' => $reportId,
      'fk_i_user_id' => ($userId > 0 ? $userId : null),
      'fk_i_admin_id' => ($adminId > 0 ? $adminId : null),
      's_comment' => $comment,
      'b_admin_seen' => ($adminId > 0 ? 1 : 0),
      'dt_date' => date('Y-m-d H:i:s')
    ));

    if(!$status) {
      return false;
    }

    $commentId = $this->dao->insertedId();

    $this->update(array('dt_update_date' => date('Y-m-d H:i:s')), array('pk_i_id' => $reportId));

    Log::newInstance()->insertLog(
      'report',
      'comment',
      $reportId,
      'comment #' . $commentId,
      ($adminId > 0 ? 'admin' : 'user'),
      ($adminId > 0 ? $adminId : (int)$userId),
      osc_substr((string)$comment, 0, 250)
    );

    return $commentId;
  }

  /**
   * Get comments of report sorted by publish date
   *
   * @access public
   * @since 8.4.0
   * @param int $reportId
   * @return array
   */
  public function getComments($reportId) {
    $this->dao->select('c.*, u.s_name as s_user_name, a.s_name as s_admin_name');
    $this->dao->from($this->getCommentTableName() . ' c');
    $this->dao->join(DB_TABLE_PREFIX . 't_user u', 'u.pk_i_id = c.fk_i_user_id', 'LEFT');
    $this->dao->join(DB_TABLE_PREFIX . 't_admin a', 'a.pk_i_id = c.fk_i_admin_id', 'LEFT');
    $this->dao->where('c.fk_i_report_id', (int)$reportId);
    $this->dao->orderBy('c.dt_date', 'ASC');

    $result = $this->dao->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Get single comment of report by its id
   *
   * @access public
   * @since 8.4.0
   * @param int $commentId
   * @return array|bool
   */
  public function findCommentByPrimaryKey($commentId) {
    $this->dao->select();
    $this->dao->from($this->getCommentTableName());
    $this->dao->where('pk_i_id', (int)$commentId);

    $result = $this->dao->get();
    if($result) {
      return $result->row();
    }
    return false;
  }

  /**
   * Delete single comment of report by its id
   *
   * @access public
   * @since 8.4.0
   * @param int $commentId
   * @return bool
   */
  public function deleteComment($commentId) {
    $comment = $this->findCommentByPrimaryKey($commentId);
    if(!$comment) {
      return false;
    }

    osc_run_hook('before_delete_report_comment', $comment);

    $deleted = $this->dao->delete($this->getCommentTableName(), array('pk_i_id' => (int)$commentId));
    if($deleted) {
      $who = 'user';
      $whoId = 0;
      if(defined('OC_ADMIN') && OC_ADMIN) {
        $who = 'admin';
        $whoId = (int)osc_logged_admin_id();
      } else if(function_exists('osc_is_web_user_logged_in') && osc_is_web_user_logged_in()) {
        $who = 'user';
        $whoId = (int)osc_logged_user_id();
      } else {
        $who = 'cron';
      }

      Log::newInstance()->insertLog(
        'report',
        'delete_comment',
        (int)$comment['fk_i_report_id'],
        'comment #' . (int)$commentId,
        $who,
        $whoId,
        osc_substr((string)$comment['s_comment'], 0, 250)
      );

      osc_run_hook('delete_report_comment', $comment);
      osc_run_hook('after_delete_report_comment', $comment);
    }

    return $deleted;
  }

  /**
   * Mark all comments of report as seen by admin
   *
   * @access public
   * @since 8.4.0
   * @param int $reportId
   * @return bool
   */
  public function markCommentsSeen($reportId) {
    return $this->dao->update($this->getCommentTableName(), array('b_admin_seen' => 1), array('fk_i_report_id' => (int)$reportId));
  }

  /**
   * Count comments not seen by admin yet (toolbar)
   *
   * @access public
   * @since 8.4.0
   * @return int
   */
  public function countUnseenComments() {
    $this->dao->select('COUNT(*) as i_total');
    $this->dao->from($this->getCommentTableName());
    $this->dao->where('b_admin_seen', 0);

    $result = $this->dao->get();
    if($result) {
      $row = $result->row();
      return (int)$row['i_total'];
    }
    return 0;
  }

  /**
   * Count reports by status (toolbar & widget)
   *
   * @access public
   * @since 8.4.0
   * @param string $status
   * @return int
   */
  public function countByStatus($status) {
    $this->dao->select('COUNT(*) as i_total');
    $this->dao->from($this->getTableName());
    $this->dao->where('s_status', $status);

    $result = $this->dao->get();
    if($result) {
      $row = $result->row();
      return (int)$row['i_total'];
    }
    return 0;
  }

  /**
   * Count reports created by user today (daily limit)
   *
   * @access public
   * @since 8.4.0
   * @param int $userId
   * @return int
   */
  public function countByUserToday($userId) {
    $this->dao->select('COUNT(*) as i_total');
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_reporter_user_id', (int)$userId);
    $this->dao->where('dt_create_date >= \'' . date('Y-m-d') . ' 00:00:00\'');

    $result = $this->dao->get();
    if($result) {
      $row = $result->row();
      return (int)$row['i_total'];
    }
    return 0;
  }

  /**
   * Find existing report of same reporter & target (duplicate check, any status)
   *
   * @access public
   * @since 8.4.0
   * @param int $reporterUserId
   * @param string $type
   * @param int $itemId
   * @param int $userId
   * @param int $reportedId
   * @return array|bool
   */
  public function findExistingReport($reporterUserId, $type, $itemId = null, $userId = null, $reportedId = null) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_reporter_user_id', (int)$reporterUserId);
    $this->dao->where('s_type', $type);

    if($type == 'item' && $itemId > 0) {
      $this->dao->where('fk_i_item_id', (int)$itemId);
    } else if($type == 'user' && $userId > 0) {
      $this->dao->where('fk_i_user_id', (int)$userId);
    } else if($reportedId > 0) {
      $this->dao->where('i_reported_id', (int)$reportedId);
    } else {
      return false;
    }

    $this->dao->limit(1);
    $result = $this->dao->get();
    if($result) {
      return $result->row();
    }
    return false;
  }

  /**
   * Find open report of same reporter & target
   *
   * @access public
   * @since 8.4.0
   * @param int $reporterUserId
   * @param string $type
   * @param int $itemId
   * @param int $userId
   * @param int $reportedId
   * @return array|bool
   */
  public function findOpenReport($reporterUserId, $type, $itemId = null, $userId = null, $reportedId = null) {
    $report = $this->findExistingReport($reporterUserId, $type, $itemId, $userId, $reportedId);
    if($report && $report['b_open'] == 1) {
      return $report;
    }
    return false;
  }

  /**
   * Get reports awaiting feedback with no activity for given days (auto close)
   *
   * @access public
   * @since 8.4.0
   * @param int $days
   * @return array
   */
  public function findAwaitingFeedbackOlderThan($days) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_status', 'awaiting_feedback');
    $this->dao->where('dt_status_date <= \'' . date('Y-m-d H:i:s', time() - ((int)$days * 86400)) . '\'');

    $result = $this->dao->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Get reports older than given days (retention cleanup)
   *
   * @access public
   * @since 8.4.0
   * @param int $days
   * @param int $limit
   * @return array
   */
  public function findOlderThan($days, $limit = 500) {
    $this->dao->select('pk_i_id');
    $this->dao->from($this->getTableName());
    $this->dao->where('dt_create_date <= \'' . date('Y-m-d H:i:s', time() - ((int)$days * 86400)) . '\'');
    $this->dao->limit((int)$limit);

    $result = $this->dao->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Search reports for admin data table
   *
   * @access public
   * @since 8.4.0
   * @param int $start
   * @param int $limit
   * @param string $columnOrder
   * @param string $typeOrder
   * @param string $status
   * @param string $keyword
   * @param string $type
   * @param int $userId
   * @param bool $unseen
   * @param string $reason
   * @param int $itemId
   * @param int $reporterId
   * @param int $reportedUserId
   * @param int $reportedId
   * @return array
   */
  public function search($start = 0, $limit = 25, $columnOrder = 'r.pk_i_id', $typeOrder = 'desc', $status = '', $keyword = '', $type = '', $userId = null, $unseen = false, $reason = '', $itemId = null, $reporterId = null, $reportedUserId = null, $reportedId = null) {
    $this->dao->select('r.*, (SELECT COUNT(*) FROM ' . $this->getCommentTableName() . ' rc WHERE rc.fk_i_report_id = r.pk_i_id) as i_comments, (SELECT COUNT(*) FROM ' . $this->getCommentTableName() . ' ru WHERE ru.fk_i_report_id = r.pk_i_id AND ru.b_admin_seen = 0) as i_unseen, u.s_name as s_reporter_name');
    $this->dao->from($this->getTableName() . ' r');
    $this->dao->join(DB_TABLE_PREFIX . 't_user u', 'u.pk_i_id = r.fk_i_reporter_user_id', 'LEFT');

    $this->searchConditions($status, $keyword, $type, $userId, $unseen, $reason, $itemId, $reporterId, $reportedUserId, $reportedId);

    $this->dao->orderBy($columnOrder, $typeOrder);
    $this->dao->limit((int)$limit, (int)$start);

    $result = $this->dao->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Count reports for admin data table
   *
   * @access public
   * @since 8.4.0
   * @param string $status
   * @param string $keyword
   * @param string $type
   * @param int $userId
   * @param bool $unseen
   * @param string $reason
   * @param int $itemId
   * @param int $reporterId
   * @param int $reportedUserId
   * @param int $reportedId
   * @return int
   */
  public function countSearch($status = '', $keyword = '', $type = '', $userId = null, $unseen = false, $reason = '', $itemId = null, $reporterId = null, $reportedUserId = null, $reportedId = null) {
    $this->dao->select('COUNT(*) as i_total');
    $this->dao->from($this->getTableName() . ' r');
    $this->dao->join(DB_TABLE_PREFIX . 't_user u', 'u.pk_i_id = r.fk_i_reporter_user_id', 'LEFT');

    $this->searchConditions($status, $keyword, $type, $userId, $unseen, $reason, $itemId, $reporterId, $reportedUserId, $reportedId);

    $result = $this->dao->get();
    if($result) {
      $row = $result->row();
      return (int)$row['i_total'];
    }
    return 0;
  }

  /**
   * Apply shared search conditions for admin data table
   *
   * @access private
   * @since 8.4.0
   * @param string $status
   * @param string $keyword
   * @param string $type
   * @param int $userId
   * @param bool $unseen
   * @param string $reason
   * @param int $itemId
   * @param int $reporterId
   * @param int $reportedUserId
   * @param int $reportedId
   */
  private function searchConditions($status, $keyword, $type, $userId, $unseen, $reason = '', $itemId = null, $reporterId = null, $reportedUserId = null, $reportedId = null) {
    if($status != '') {
      if($status == 'open') {
        $this->dao->where('r.b_open', 1);
      } else if($status == 'closed') {
        $this->dao->where('r.b_open', 0);
      } else {
        $this->dao->where('r.s_status', $status);
      }
    }

    if($type != '') {
      $this->dao->where('r.s_type', $type);
    }

    if($reason != '') {
      $this->dao->where('r.s_reason', $reason);
    }

    if($itemId > 0) {
      $this->dao->where('r.fk_i_item_id', (int)$itemId);
    }

    if($reporterId > 0) {
      $this->dao->where('r.fk_i_reporter_user_id', (int)$reporterId);
    }

    if($reportedUserId > 0) {
      $this->dao->where('r.fk_i_user_id', (int)$reportedUserId);
    }

    if($reportedId > 0) {
      $this->dao->where('r.i_reported_id', (int)$reportedId);
    }

    if($userId > 0) {
      $this->dao->where('(r.fk_i_reporter_user_id = ' . (int)$userId . ' OR r.fk_i_user_id = ' . (int)$userId . ')');
    }

    if($unseen) {
      $this->dao->where('(SELECT COUNT(*) FROM ' . $this->getCommentTableName() . ' rs WHERE rs.fk_i_report_id = r.pk_i_id AND rs.b_admin_seen = 0) > 0');
    }

    if($keyword != '') {
      $keyword = $this->dao->escapeStr($keyword);
      $this->dao->where('(r.s_comment LIKE \'%' . $keyword . '%\' OR r.s_admin_comment LIKE \'%' . $keyword . '%\' OR r.s_reason LIKE \'%' . $keyword . '%\' OR r.s_type LIKE \'%' . $keyword . '%\' OR r.s_source LIKE \'%' . $keyword . '%\' OR u.s_name LIKE \'%' . $keyword . '%\' OR r.pk_i_id = \'' . $keyword . '\' OR EXISTS (SELECT 1 FROM ' . $this->getCommentTableName() . ' rk WHERE rk.fk_i_report_id = r.pk_i_id AND rk.s_comment LIKE \'%' . $keyword . '%\'))');
    }

    osc_run_hook('manage_report_search_conditions', $this->dao);
  }

  /**
   * Clear item relation on reports when item is deleted (report itself is kept)
   *
   * @access public
   * @since 8.4.0
   * @param int $itemId
   * @return bool
   */
  public function clearItemRelation($itemId) {
    return $this->dao->update($this->getTableName(), array('fk_i_item_id' => null), array('fk_i_item_id' => (int)$itemId));
  }

  /**
   * Delete report by primary key, removes comments and attachment file too
   *
   * @access public
   * @since 8.4.0
   * @param int $id
   * @param string $who
   * @param int $whoId
   * @return bool
   */
  public function deleteByPrimaryKey($id, $who = null, $whoId = null) {
    $report = $this->findByPrimaryKey($id);

    if(!$report) {
      return false;
    }

    osc_run_hook('before_delete_report', $report);

    if(!empty($report['s_file'])) {
      @unlink(osc_report_attachment_path($report['s_file']));
    }

    $this->dao->delete($this->getCommentTableName(), array('fk_i_report_id' => (int)$id));

    osc_run_hook('delete_report', $id);
    osc_run_hook('report_delete', $id, $report);

    $deleted = parent::deleteByPrimaryKey($id);

    if($deleted) {
      if($who === null) {
        if(defined('OC_ADMIN') && OC_ADMIN) {
          $who = 'admin';
          $whoId = (int)osc_logged_admin_id();
        } else if(function_exists('osc_is_web_user_logged_in') && osc_is_web_user_logged_in()) {
          $who = 'user';
          $whoId = (int)osc_logged_user_id();
        } else {
          $who = 'cron';
          $whoId = 0;
        }
      }

      Log::newInstance()->insertLog(
        'report',
        'delete',
        $id,
        (string)$report['s_type'] . '/' . (string)$report['s_reason'],
        $who,
        (int)$whoId
      );

      osc_run_hook('after_delete_report', $id, $report);
    }

    return $deleted;
  }

  /**
   * Link item reports without owner to newly registered user by contact email / item ownership
   *
   * @access public
   * @since 8.4.0
   * @param int $userId
   * @param string $email
   * @return bool
   */
  public function assignOwnerByContactEmail($userId, $email) {
    $userId = (int)$userId;
    $email = trim((string)$email);
    if($userId <= 0 || $email == '' || !osc_reports_tables_ready()) {
      return false;
    }

    $sql = sprintf(
      "UPDATE %st_report r
       INNER JOIN %st_item i ON i.pk_i_id = r.fk_i_item_id
       SET r.fk_i_user_id = %d, r.dt_update_date = '%s'
       WHERE r.s_type = 'item'
         AND (r.fk_i_user_id IS NULL OR r.fk_i_user_id = 0)
         AND (i.fk_i_user_id = %d OR i.s_contact_email = '%s')",
      DB_TABLE_PREFIX,
      DB_TABLE_PREFIX,
      $userId,
      date('Y-m-d H:i:s'),
      $userId,
      $this->dao->escapeStr($email)
    );

    return ($this->dao->query($sql) !== false);
  }
}

/* file end: ./oc-includes/osclass/model/Report.php */

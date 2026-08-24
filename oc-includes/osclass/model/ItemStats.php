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
    $this->setFields($this->allowedColumns());
  }

  /**
   * Return allowed t_item_stats columns including key and date
   *
   * @return array
   */
  public function allowedColumns() {
    $cols = array(
      'fk_i_item_id',
      'i_num_views',
      'i_num_premium_views',
      'i_num_views_engaged',
      'i_num_views_search',
      'i_num_views_logged',
      'i_num_views_home',
      'i_num_view_minutes',
      'i_num_phone_clicks',
      'i_num_contactother_clicks',
      'i_num_favorites',
      'i_num_contactforms',
      'i_num_contacts',
      'i_num_orders',
      'i_num_offers',
      'i_num_promotions',
      'i_num_reports',
      'i_num_tops',
      'i_num_renews',
      'i_num_repubs',
      'i_num_alerts_sent',
      'i_num_shares',
      'i_num_comments',
      'i_num_rated_comments',
      'i_num_custom1',
      'i_num_custom2',
      'i_num_custom3',
      'dt_date'
    );

    if(function_exists('osc_item_stats_measures')) {
      foreach(osc_item_stats_measures(false) as $row) {
        if(!is_array($row) || !isset($row['column'])) {
          continue;
        }
        $c = $this->safeColumnName($row['column']);
        if($c != '') {
          $cols[] = $c;
        }
      }
    }

    $cols = osc_apply_filter('osc_item_stats_columns', $cols);
    $out = array();
    foreach((array)$cols as $c) {
      $c = $this->safeColumnName($c);
      if($c != '' && !in_array($c, $out, true)) {
        $out[] = $c;
      }
    }
    if(!in_array('fk_i_item_id', $out, true)) {
      array_unshift($out, 'fk_i_item_id');
    }
    if(!in_array('dt_date', $out, true)) {
      $out[] = 'dt_date';
    }
    return $out;
  }

  /**
   * Accept only safe t_item_stats column names
   *
   * @param string $column
   * @return string
   */
  protected function safeColumnName($column) {
    $column = trim((string)$column);
    if($column === 'fk_i_item_id' || $column === 'dt_date') {
      return $column;
    }
    if(!preg_match('/^i_[a-z0-9_]{1,60}$/', $column)) {
      return '';
    }
    return $column;
  }

  /**
   * Return measurable columns only
   *
   * @return array
   */
  public function measureColumns() {
    $cols = $this->allowedColumns();
    unset($cols[array_search('fk_i_item_id', $cols, true)]);
    unset($cols[array_search('dt_date', $cols, true)]);
    return array_values($cols);
  }

  /**
   * Increase the stat column given column name and item id
   *
   * @access public
   * @since unknown
   * @param string $column
   * @param int $item_id
   * @param int $num
   * @return bool
   */
  public function increase($column, $item_id, $num = 1) {
    $item_id = (int)$item_id;
    $column = $this->sanitizeColumn($column);
    $num = (int)$num;

    if($column == '' || $item_id <= 0 || $num == 0) {
      return false;
    }

    if(!$this->canIncrease($column, $item_id, $num)) {
      return false;
    }

    $num = (int)osc_apply_filter('item_stats_increase', $num, $item_id, $column);

    if($num == 0) {
      return false;
    }

    $sql = 'INSERT INTO '.$this->getTableName().' (fk_i_item_id, dt_date, '.$column.') VALUES ('.$item_id.', \''.date('Y-m-d').'\','.$num.') ON DUPLICATE KEY UPDATE '.$column.' = '.$column.' + '.$num;
    $ok = $this->dao->query($sql);

    if($ok) {
      $measure = $column;
      if(function_exists('osc_item_stats_measure_by_column')) {
        $def = osc_item_stats_measure_by_column($column);
        if(is_array($def) && isset($def['key'])) {
          $measure = $def['key'];
        }
      }
      osc_run_hook('item_stat_increased', $item_id, $measure, $num);
    }

    return $ok;
  }

  /**
   * Increase several columns for one item in one query
   *
   * @param int $item_id
   * @param array $assoc column => num
   * @return bool
   */
  public function increaseMany($item_id, $assoc) {
    $item_id = (int)$item_id;
    if($item_id <= 0 || !is_array($assoc) || empty($assoc)) {
      return false;
    }

    $cols = array();
    $vals = array();
    $upd = array();

    foreach($assoc as $column => $num) {
      $column = $this->sanitizeColumn($column);
      $num = (int)$num;
      if($column == '' || $num == 0) {
        continue;
      }
      if(!$this->canIncrease($column, $item_id, $num)) {
        continue;
      }
      $num = (int)osc_apply_filter('item_stats_increase', $num, $item_id, $column);
      if($num == 0) {
        continue;
      }
      $cols[] = $column;
      $vals[] = $num;
      $upd[] = $column.' = '.$column.' + '.$num;
    }

    if(empty($cols)) {
      return false;
    }

    $sql = 'INSERT INTO '.$this->getTableName().' (fk_i_item_id, dt_date, '.implode(', ', $cols).') VALUES ('.$item_id.', \''.date('Y-m-d').'\','.implode(', ', $vals).') ON DUPLICATE KEY UPDATE '.implode(', ', $upd);
    $ok = $this->dao->query($sql);

    if($ok) {
      foreach($cols as $i => $column) {
        $measure = $column;
        if(function_exists('osc_item_stats_measure_by_column')) {
          $def = osc_item_stats_measure_by_column($column);
          if(is_array($def) && isset($def['key'])) {
            $measure = $def['key'];
          }
        }
        osc_run_hook('item_stat_increased', $item_id, $measure, $vals[$i]);
      }
    }

    return $ok;
  }

  /**
   * Increase one column for many items in chunked INSERT queries
   *
   * @param string $column
   * @param array $item_ids
   * @param int $num
   * @return bool
   */
  public function increaseForItems($column, $item_ids, $num = 1) {
    $column = $this->sanitizeColumn($column);
    $num = (int)$num;
    if($column == '' || $num == 0 || !is_array($item_ids) || empty($item_ids)) {
      return false;
    }

    $groups = array();
    foreach($item_ids as $item_id) {
      $item_id = (int)$item_id;
      if($item_id <= 0) {
        continue;
      }
      if(!$this->canIncrease($column, $item_id, $num)) {
        continue;
      }
      $n = (int)osc_apply_filter('item_stats_increase', $num, $item_id, $column);
      if($n == 0) {
        continue;
      }
      if(!isset($groups[$n])) {
        $groups[$n] = array();
      }
      if(!in_array($item_id, $groups[$n], true)) {
        $groups[$n][] = $item_id;
      }
    }

    if(empty($groups)) {
      return false;
    }

    $measure = $column;
    if(function_exists('osc_item_stats_measure_by_column')) {
      $def = osc_item_stats_measure_by_column($column);
      if(is_array($def) && isset($def['key'])) {
        $measure = $def['key'];
      }
    }

    $ok = false;
    $today = date('Y-m-d');
    foreach($groups as $n => $ids) {
      foreach(array_chunk($ids, 100) as $chunk) {
        $values = array();
        foreach($chunk as $id) {
          $values[] = '('.$id.', \''.$today.'\','.$n.')';
        }
        $sql = 'INSERT INTO '.$this->getTableName().' (fk_i_item_id, dt_date, '.$column.') VALUES '.implode(',', $values).' ON DUPLICATE KEY UPDATE '.$column.' = '.$column.' + '.$n;
        if($this->dao->query($sql)) {
          $ok = true;
          foreach($chunk as $id) {
            osc_run_hook('item_stat_increased', $id, $measure, $n);
          }
        }
      }
    }

    return $ok;
  }

  /**
   * Apply a signed delta to today's row (UNSIGNED columns never go below 0)
   *
   * @param string $column
   * @param int $item_id
   * @param int $delta
   * @return bool
   */
  public function applyDelta($column, $item_id, $delta) {
    $item_id = (int)$item_id;
    $delta = (int)$delta;
    $column = $this->sanitizeColumn($column);
    if($column == '' || $item_id <= 0 || $delta == 0) {
      return false;
    }

    if(function_exists('osc_item_stats_enabled') && function_exists('osc_item_stats_measure_by_column')) {
      $def = osc_item_stats_measure_by_column($column);
      $measure = (is_array($def) ? $def['key'] : '');
      if($measure != '' && !osc_item_stats_enabled($measure)) {
        return false;
      }
    }

    $today = date('Y-m-d');
    if($delta > 0) {
      $sql = 'INSERT INTO '.$this->getTableName().' (fk_i_item_id, dt_date, '.$column.') VALUES ('.$item_id.', \''.$today.'\','.$delta.') ON DUPLICATE KEY UPDATE '.$column.' = '.$column.' + '.$delta;
      return $this->dao->query($sql);
    }

    $sql = 'UPDATE '.$this->getTableName().' SET '.$column.' = GREATEST(0, CAST('.$column.' AS SIGNED) + ('.$delta.')) WHERE fk_i_item_id = '.$item_id.' AND dt_date = \''.$today.'\'';
    $ok = $this->dao->query($sql);
    if($ok && $this->dao->affectedRows() > 0) {
      return true;
    }

    $sql = 'UPDATE '.$this->getTableName().' SET '.$column.' = GREATEST(0, CAST('.$column.' AS SIGNED) + ('.$delta.')) WHERE fk_i_item_id = '.$item_id.' AND '.$column.' > 0 ORDER BY dt_date DESC LIMIT 1';
    return $this->dao->query($sql);
  }

  /**
   * Check enabled, logged-only and session rules before writing
   *
   * @param string $column
   * @param int $item_id
   * @param int $num
   * @return bool
   */
  protected function canIncrease($column, $item_id, $num) {
    if($num == 0) {
      return false;
    }

    $measure = '';
    $def = array();
    if(function_exists('osc_item_stats_measure_by_column')) {
      $def = osc_item_stats_measure_by_column($column);
      $measure = (is_array($def) ? $def['key'] : '');
      if($measure != '' && function_exists('osc_item_stats_enabled') && !osc_item_stats_enabled($measure)) {
        return false;
      }
    }

    $session_guard = 'method';
    $logged_only_applies = true;
    if(is_array($def) && isset($def['session_guard'])) {
      $session_guard = $def['session_guard'];
    }
    if(is_array($def) && isset($def['logged_only'])) {
      $logged_only_applies = (bool)$def['logged_only'];
    }

    if($logged_only_applies && function_exists('osc_item_stats_logged_only') && osc_item_stats_logged_only() && !osc_is_web_user_logged_in()) {
      return false;
    }

    if(is_array($def) && isset($def['collect_guest']) && !$def['collect_guest'] && !osc_is_web_user_logged_in()) {
      return false;
    }

    if($session_guard == 'never') {
      return true;
    }

    if($session_guard == 'always') {
      return !osc_is_item_viewed_in_session($item_id, $column);
    }

    if(osc_item_stats_method() == 'PAGELOAD') {
      return true;
    }

    return !osc_is_item_viewed_in_session($item_id, $column);
  }

  /**
   * Allow only known measure columns
   *
   * @param string $column
   * @return string
   */
  public function sanitizeColumn($column) {
    $column = trim((string)$column);
    if($column == '' || !in_array($column, $this->measureColumns(), true)) {
      return '';
    }
    if(!$this->tableHasColumn($column)) {
      return '';
    }
    return $column;
  }

  /**
   * Request-cached SHOW COLUMNS so missing upgrade columns read as 0
   *
   * @param string $column
   * @return bool
   */
  protected function tableHasColumn($column) {
    static $cols = null;
    if($cols === null) {
      $cols = array();
      $result = $this->dao->query('SHOW COLUMNS FROM '.$this->getTableName());
      if($result) {
        foreach($result->result() as $row) {
          if(isset($row['Field'])) {
            $cols[$row['Field']] = true;
          }
        }
      }
    }
    return isset($cols[$column]);
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
        'dt_date' => date('Y-m-d')
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
    return $this->getStat($item_id, 'i_num_views');
  }

  /**
   * Return SUM of one column for an item
   *
   * @param int $item_id
   * @param string $column
   * @return int
   */
  public function getStat($item_id, $column) {
    $item_id = (int)$item_id;
    $column = $this->sanitizeColumn($column);
    if($item_id <= 0 || $column == '') {
      return 0;
    }

    $this->dao->select('SUM('.$column.') AS total');
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_item_id', $item_id);
    $result = $this->dao->get();

    if(!$result) {
      return 0;
    }

    $res = $result->row();
    return (int)(isset($res['total']) ? $res['total'] : 0);
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
      return (int)$res[0]['i_num_views'];
    }
  }

  /**
   * Daily series for given columns, optionally filtered
   *
   * @param array $columns
   * @param string $from_date Y-m-d
   * @param int|null $item_id
   * @param int|null $category_id
   * @param int|null $user_id
   * @return array
   */
  public function getSeries($columns, $from_date, $item_id = null, $category_id = null, $user_id = null) {
    $safe = array();
    foreach((array)$columns as $column) {
      $column = $this->sanitizeColumn($column);
      if($column != '') {
        $safe[] = $column;
      }
    }

    if(empty($safe)) {
      return array();
    }

    $select = array('s.dt_date AS d_date');
    foreach($safe as $column) {
      $select[] = 'SUM(s.'.$column.') AS '.$column;
    }

    $this->dao->select(implode(', ', $select));
    $this->dao->from($this->getTableName().' AS s');

    $need_item = ((int)$item_id > 0 || (int)$category_id > 0 || (int)$user_id > 0);
    if($need_item) {
      $this->dao->join(DB_TABLE_PREFIX.'t_item AS i', 'i.pk_i_id = s.fk_i_item_id', 'INNER');
    }

    if($from_date != '') {
      $this->dao->where('s.dt_date >=', $from_date);
    }
    if((int)$item_id > 0) {
      $this->dao->where('s.fk_i_item_id', (int)$item_id);
    }
    if((int)$category_id > 0) {
      $this->dao->where('i.fk_i_category_id', (int)$category_id);
    }
    if((int)$user_id > 0) {
      $this->dao->where('i.fk_i_user_id', (int)$user_id);
    }

    $this->dao->groupBy('s.dt_date');
    $this->dao->orderBy('s.dt_date', 'ASC');
    $result = $this->dao->get();

    if(!$result) {
      return array();
    }

    return $result->result();
  }

  /**
   * Batch SUM of columns for a list of items
   *
   * @param array $item_ids
   * @param array $columns
   * @return array
   */
  public function getTotalsByItems($item_ids, $columns) {
    $ids = array();
    foreach((array)$item_ids as $id) {
      $id = (int)$id;
      if($id > 0) {
        $ids[] = $id;
      }
    }

    $safe = array();
    foreach((array)$columns as $column) {
      $column = $this->sanitizeColumn($column);
      if($column != '') {
        $safe[] = $column;
      }
    }

    if(empty($ids) || empty($safe)) {
      return array();
    }

    $select = array('fk_i_item_id');
    foreach($safe as $column) {
      $select[] = 'SUM('.$column.') AS '.$column;
    }

    $this->dao->select(implode(', ', $select));
    $this->dao->from($this->getTableName());
    $this->dao->where(sprintf('fk_i_item_id IN (%s)', implode(',', $ids)));
    $this->dao->groupBy('fk_i_item_id');
    $result = $this->dao->get();

    if(!$result) {
      return array();
    }

    $out = array();
    foreach($result->result() as $row) {
      $out[(int)$row['fk_i_item_id']] = $row;
    }

    return $out;
  }

  /**
   * Top listings for one measure since from_date
   *
   * @param string $column
   * @param string $from_date
   * @param int $limit
   * @param array $filters
   * @return array
   */
  public function getTopByMeasure($column, $from_date, $limit = 10, $filters = array()) {
    $column = $this->sanitizeColumn($column);
    $limit = (int)$limit;
    if($column == '' || $limit <= 0) {
      return array();
    }

    $item_id = (isset($filters['item_id']) ? (int)$filters['item_id'] : 0);
    $category_id = (isset($filters['category_id']) ? (int)$filters['category_id'] : 0);
    $user_id = (isset($filters['user_id']) ? (int)$filters['user_id'] : 0);

    $this->dao->select('s.fk_i_item_id, SUM(s.'.$column.') AS total, i.fk_i_category_id, i.fk_i_user_id');
    $this->dao->from($this->getTableName().' AS s');
    $this->dao->join(DB_TABLE_PREFIX.'t_item AS i', 'i.pk_i_id = s.fk_i_item_id', 'INNER');
    if($from_date != '') {
      $this->dao->where('s.dt_date >=', $from_date);
    }
    if($item_id > 0) {
      $this->dao->where('s.fk_i_item_id', $item_id);
    }
    if($category_id > 0) {
      $this->dao->where('i.fk_i_category_id', $category_id);
    }
    if($user_id > 0) {
      $this->dao->where('i.fk_i_user_id', $user_id);
    }
    $this->dao->groupBy('s.fk_i_item_id');
    $this->dao->orderBy('total', 'DESC');
    $this->dao->limit($limit);
    $result = $this->dao->get();

    if(!$result) {
      return array();
    }

    return $result->result();
  }
}

/* file end: ./oc-includes/osclass/model/ItemStats.php */

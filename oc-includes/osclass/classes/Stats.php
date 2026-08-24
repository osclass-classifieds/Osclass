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
 * Stats
 */
class Stats
{
  /**
   *
   * @var type
   */
  private static $instance;
  private $conn;

  /**
   * @return \Stats
   */
  public static function newInstance()
  {
    if(!self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   *
   */
  public function __construct()
  {
    $conn = DBConnectionClass::newInstance();
    $data = $conn->getOsclassDb();
    $this->conn = new DBCommandClass($data);
  }

  /**
   * @param    $from_date
   * @param string $date
   *
   * @return mixed
   */
  public function new_users_count( $from_date , $date = 'day' )
  {
    if($date === 'week') {
      $this->conn->select('WEEK(dt_reg_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('WEEK(dt_reg_date)');
    } else if($date === 'month') {
      $this->conn->select('MONTHNAME(dt_reg_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('MONTH(dt_reg_date)');
    } else {
      $this->conn->select('DATE(dt_reg_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('DATE(dt_reg_date)');
    }
    $this->conn->from(DB_TABLE_PREFIX.'t_user');
    $this->conn->where("dt_reg_date >= '$from_date'");
    $this->conn->orderBy('dt_reg_date', 'DESC');

    $result = $this->conn->get();
    return $result->result();
  }

  /**
   * @return mixed
   */
  public function users_by_country()
  {
    $this->conn->select('s_country, COUNT(pk_i_id) as num');
    $this->conn->from(DB_TABLE_PREFIX.'t_user');
    $this->conn->groupBy('s_country');
    $this->conn->orderBy('num', 'DESC');

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * @return mixed
   */
  public function users_by_region()
  {
    $this->conn->select('s_region, COUNT(pk_i_id) as num');
    $this->conn->from(DB_TABLE_PREFIX.'t_user');
    $this->conn->groupBy('s_region');
    $this->conn->orderBy('num', 'DESC');

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Registered user counts by t_user.b_company (0 personal, 1 company)
   *
   * @return array
   */
  public function users_by_company()
  {
    $out = array('personal' => 0, 'company' => 0, 'other' => 0);
    $this->conn->select('b_company, COUNT(pk_i_id) as num');
    $this->conn->from(DB_TABLE_PREFIX.'t_user');
    $this->conn->groupBy('b_company');

    $result = $this->conn->get();
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $num = (int)(isset($row['num']) ? $row['num'] : 0);
      if(!isset($row['b_company']) || $row['b_company'] === null || $row['b_company'] === '') {
        $out['other'] += $num;
      } else if((int)$row['b_company'] === 1) {
        $out['company'] += $num;
      } else if((int)$row['b_company'] === 0) {
        $out['personal'] += $num;
      } else {
        $out['other'] += $num;
      }
    }
    return $out;
  }

  /**
   * Registered user counts by status: blocked, pending validation, active
   *
   * @return array
   */
  public function users_by_status()
  {
    $out = array('pending' => 0, 'active' => 0, 'blocked' => 0);
    $sql = 'SELECT CASE WHEN b_enabled = 0 THEN \'blocked\' WHEN b_active = 0 THEN \'pending\' ELSE \'active\' END AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_user GROUP BY s_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }

  /**
   * Listing counts by country from t_item_location
   *
   * @return array
   */
  public function items_by_country()
  {
    $this->conn->select('l.s_country as s_country, COUNT(i.pk_i_id) as num');
    $this->conn->from(DB_TABLE_PREFIX.'t_item i, '.DB_TABLE_PREFIX.'t_item_location l');
    $this->conn->where('l.fk_i_item_id = i.pk_i_id');
    $this->conn->groupBy('l.s_country');
    $this->conn->orderBy('num', 'DESC');

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Listing counts by region from t_item_location
   *
   * @return array
   */
  public function items_by_region()
  {
    $this->conn->select('l.s_region as s_region, COUNT(i.pk_i_id) as num');
    $this->conn->from(DB_TABLE_PREFIX.'t_item i, '.DB_TABLE_PREFIX.'t_item_location l');
    $this->conn->where('l.fk_i_item_id = i.pk_i_id');
    $this->conn->groupBy('l.s_region');
    $this->conn->orderBy('num', 'DESC');

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Listing counts rolled up to root categories
   *
   * @return array
   */
  public function items_by_root_category()
  {
    $locale = osc_current_admin_locale();
    $locale = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$locale);
    if($locale == '') {
      $locale = 'en_US';
    }
    $sql = sprintf(
      "SELECT COALESCE(c.fk_i_parent_id, c.pk_i_id) AS root_id, COALESCE(d.s_name, CONCAT('#', COALESCE(c.fk_i_parent_id, c.pk_i_id))) AS s_name, COUNT(i.pk_i_id) AS num FROM %st_item i INNER JOIN %st_category c ON c.pk_i_id = i.fk_i_category_id LEFT JOIN %st_category_description d ON d.fk_i_category_id = COALESCE(c.fk_i_parent_id, c.pk_i_id) AND d.fk_c_locale_code = '%s' GROUP BY root_id, s_name ORDER BY num DESC",
      DB_TABLE_PREFIX,
      DB_TABLE_PREFIX,
      DB_TABLE_PREFIX,
      $locale
    );
    $result = $this->conn->query($sql);
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Listing counts by root category and its child category
   *
   * @return array
   */
  public function items_by_category_levels()
  {
    $locale = osc_current_admin_locale();
    $locale = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$locale);
    if($locale == '') {
      $locale = 'en_US';
    }
    $sql = sprintf(
      "SELECT COALESCE(c.fk_i_parent_id, c.pk_i_id) AS root_id, c.pk_i_id AS cat_id, COALESCE(rd.s_name, CONCAT('#', COALESCE(c.fk_i_parent_id, c.pk_i_id))) AS root_name, COALESCE(d.s_name, CONCAT('#', c.pk_i_id)) AS cat_name, COUNT(i.pk_i_id) AS num FROM %st_item i INNER JOIN %st_category c ON c.pk_i_id = i.fk_i_category_id LEFT JOIN %st_category_description rd ON rd.fk_i_category_id = COALESCE(c.fk_i_parent_id, c.pk_i_id) AND rd.fk_c_locale_code = '%s' LEFT JOIN %st_category_description d ON d.fk_i_category_id = c.pk_i_id AND d.fk_c_locale_code = '%s' GROUP BY root_id, cat_id, root_name, cat_name ORDER BY num DESC",
      DB_TABLE_PREFIX,
      DB_TABLE_PREFIX,
      DB_TABLE_PREFIX,
      $locale,
      DB_TABLE_PREFIX,
      $locale
    );
    $result = $this->conn->query($sql);
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Listing counts by i_price: 0 free, NULL check with seller, >0 standard price
   *
   * @return array
   */
  public function items_by_price_type()
  {
    $out = array('free' => 0, 'ask' => 0, 'priced' => 0);
    $sql = 'SELECT CASE WHEN i_price IS NULL THEN \'ask\' WHEN i_price = 0 THEN \'free\' ELSE \'priced\' END AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_item GROUP BY s_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }

  /**
   * Listing counts by whether t_item.s_contact_phone is filled
   *
   * @return array
   */
  public function items_by_phone()
  {
    $out = array('filled' => 0, 'empty' => 0);
    $sql = 'SELECT CASE WHEN TRIM(COALESCE(s_contact_phone, \'\')) <> \'\' THEN \'filled\' ELSE \'empty\' END AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_item GROUP BY s_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }

  /**
   * Listing counts by poster type from item.fk_i_user_id and t_user.b_company
   *
   * @return array
   */
  public function items_by_user_type()
  {
    $out = array('guest' => 0, 'personal' => 0, 'business' => 0);
    $sql = 'SELECT CASE WHEN i.fk_i_user_id IS NULL OR i.fk_i_user_id = 0 THEN \'guest\' WHEN u.b_company = 1 THEN \'business\' ELSE \'personal\' END AS s_type, COUNT(i.pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_item i LEFT JOIN ' . DB_TABLE_PREFIX . 't_user u ON u.pk_i_id = i.fk_i_user_id GROUP BY s_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }

  /**
   * @return mixed
   */
  public function items_by_user()
  {
    $result = $this->conn->query( 'SELECT AVG( num ) as avg FROM (SELECT COUNT( pk_i_id ) AS num FROM ' . DB_TABLE_PREFIX . 't_item GROUP BY s_contact_email ) AS dummy_table' );
    return $result->result();
  }

  /**
   * @return mixed
   */
  public function latest_users($limit = 8)
  {
    $limit = (int)$limit;
    if($limit < 1) {
      $limit = 8;
    }
    if($limit > 20) {
      $limit = 20;
    }
    $this->conn->select();
    $this->conn->from(DB_TABLE_PREFIX.'t_user');
    $this->conn->orderBy('dt_reg_date', 'DESC');
    $this->conn->limit($limit);

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * @param    $from_date
   * @param string $date
   *
   * @return array
   */
  public function new_items_count( $from_date , $date = 'day' )
  {
    if($date === 'week') {
      $this->conn->select('WEEK(dt_pub_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('WEEK(dt_pub_date)');
    } else if($date === 'month') {
      $this->conn->select('MONTHNAME(dt_pub_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('MONTH(dt_pub_date)');
    } else {
      $this->conn->select('DATE(dt_pub_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('DATE(dt_pub_date)');
    }

    $this->conn->from( DB_TABLE_PREFIX . 't_item' );
    $this->conn->where("dt_pub_date >= '$from_date'");
    $this->conn->orderBy('dt_pub_date', 'DESC');

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * @return mixed
   */
  public function latest_items($limit = 8)
  {
    $limit = (int)$limit;
    if($limit < 1) {
      $limit = 8;
    }
    if($limit > 20) {
      $limit = 20;
    }
    $this->conn->select('l.*, i.*, d.*');
    $this->conn->from(DB_TABLE_PREFIX.'t_item i, '.DB_TABLE_PREFIX.'t_item_location l, '.DB_TABLE_PREFIX.'t_item_description d');
    $this->conn->where('l.fk_i_item_id = i.pk_i_id AND d.fk_i_item_id = i.pk_i_id');
    $this->conn->groupBy('i.pk_i_id');
    $this->conn->orderBy('dt_pub_date', 'DESC');
    $this->conn->limit($limit);

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * @param    $from_date
   * @param string $date
   *
   * @return mixed
   */
  public function new_comments_count( $from_date , $date = 'day' )
  {
    if($date === 'week') {
      $this->conn->select('WEEK(dt_pub_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('WEEK(dt_pub_date)');
    } else if($date === 'month') {
      $this->conn->select('MONTH(dt_pub_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('MONTH(dt_pub_date)');
    } else {
      $this->conn->select('DATE(dt_pub_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('DATE(dt_pub_date)');
    }

    $this->conn->from( DB_TABLE_PREFIX . 't_item_comment' );
    $this->conn->where("dt_pub_date >= '$from_date'");
    $this->conn->orderBy('dt_pub_date', 'DESC');

    $result = $this->conn->get();
    return $result->result();
  }

  /**
   * @param $from_date
   * @param string $date
   *
   * @return array
   */
  public function new_reports_count( $from_date , $date = 'day' )
  {
    if(!osc_reports_tables_ready()) {
      return array();
    }

    if($date === 'week') {
      $this->conn->select('WEEK(dt_create_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('WEEK(dt_create_date)');
      $this->conn->orderBy('WEEK(dt_create_date)', 'DESC');
    } else if($date === 'month') {
      $this->conn->select('MONTH(dt_create_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('MONTH(dt_create_date)');
      $this->conn->orderBy('MONTH(dt_create_date)', 'DESC');
    } else {
      $this->conn->select('DATE(dt_create_date) as d_date, COUNT(pk_i_id) as num');
      $this->conn->groupBy('DATE(dt_create_date)');
      $this->conn->orderBy('DATE(dt_create_date)', 'DESC');
    }

    $this->conn->from( DB_TABLE_PREFIX . 't_report' );
    $this->conn->where("dt_create_date >= '$from_date'");

    $result = $this->conn->get();
    if(!$result) {
      return array();
    }

    return $result->result();
  }

  /**
   * @return mixed
   */
  public function latest_comments($limit = 8)
  {
    $limit = (int)$limit;
    if($limit < 1) {
      $limit = 8;
    }
    if($limit > 20) {
      $limit = 20;
    }
    $this->conn->select('i.*, c.*');
    $this->conn->from(DB_TABLE_PREFIX.'t_item i, '.DB_TABLE_PREFIX.'t_item_comment c');
    $this->conn->where('c.fk_i_item_id = i.pk_i_id');
    $this->conn->orderBy('c.dt_pub_date', 'DESC');
    $this->conn->limit($limit);

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Comment counts with a rating versus without, optionally from a start date
   *
   * @param string $from_date
   * @return array
   */
  public function comments_by_rating($from_date = '')
  {
    $out = array('rated' => 0, 'unrated' => 0);
    $sql = 'SELECT CASE WHEN i_rating IS NOT NULL AND i_rating > 0 THEN \'rated\' ELSE \'unrated\' END AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_item_comment';
    if($from_date != '') {
      $sql .= " WHERE dt_pub_date >= '" . $from_date . "'";
    }
    $sql .= ' GROUP BY s_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }

  /**
   * Top-level comment counts versus replies, optionally from a start date
   *
   * @param string $from_date
   * @return array
   */
  public function comments_by_reply($from_date = '')
  {
    $out = array('comment' => 0, 'reply' => 0);
    $sql = 'SELECT CASE WHEN fk_i_reply_id IS NOT NULL AND fk_i_reply_id > 0 THEN \'reply\' ELSE \'comment\' END AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_item_comment';
    if($from_date != '') {
      $sql .= " WHERE dt_pub_date >= '" . $from_date . "'";
    }
    $sql .= ' GROUP BY s_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }

  /**
   * Comment counts by status: blocked first, then pending validation, else active
   *
   * @param string $from_date
   * @return array
   */
  public function comments_by_status($from_date = '')
  {
    $out = array('pending' => 0, 'active' => 0, 'blocked' => 0);
    $sql = 'SELECT CASE WHEN b_enabled = 0 OR b_spam = 1 THEN \'blocked\' WHEN b_active = 0 THEN \'pending\' ELSE \'active\' END AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_item_comment';
    if($from_date != '') {
      $sql .= " WHERE dt_pub_date >= '" . $from_date . "'";
    }
    $sql .= ' GROUP BY s_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }

  /**
   * Daily comment counts split into registered users versus guests
   *
   * @param string $from_date
   * @return array
   */
  public function new_comments_by_user_kind($from_date)
  {
    $sql = 'SELECT DATE(dt_pub_date) AS d_date, CASE WHEN fk_i_user_id IS NULL OR fk_i_user_id = 0 THEN \'guest\' ELSE \'user\' END AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_item_comment WHERE dt_pub_date >= \'' . $from_date . '\' GROUP BY d_date, s_type ORDER BY d_date ASC';
    $result = $this->conn->query($sql);
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Daily alert counts split into guests, personal accounts and company accounts
   *
   * @param string $from_date
   * @return array
   */
  public function new_alerts_by_user_kind($from_date)
  {
    $sql = 'SELECT DATE(a.dt_date) AS d_date, CASE WHEN a.fk_i_user_id IS NULL OR a.fk_i_user_id = 0 THEN \'guest\' WHEN u.b_company = 1 THEN \'company\' ELSE \'personal\' END AS s_type, COUNT(a.pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts a LEFT JOIN ' . DB_TABLE_PREFIX . 't_user u ON u.pk_i_id = a.fk_i_user_id WHERE a.dt_date >= \'' . $from_date . '\' AND a.dt_unsub_date IS NULL GROUP BY d_date, s_type ORDER BY d_date ASC';
    $result = $this->conn->query($sql);
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Alert counts by send frequency, optionally from a start date
   *
   * @param string $from_date
   * @return array
   */
  public function alerts_by_frequency($from_date = '')
  {
    $out = array('INSTANT' => 0, 'HOURLY' => 0, 'DAILY' => 0, 'WEEKLY' => 0, 'CUSTOM' => 0);
    $sql = 'SELECT e_type AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts WHERE dt_unsub_date IS NULL';
    if($from_date != '') {
      $sql .= " AND dt_date >= '" . $from_date . "'";
    }
    $sql .= ' GROUP BY e_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }


  /**
   * Latest saved-search alerts
   *
   * @param int $limit
   * @return array
   */
  public function latest_alerts($limit = 8)
  {
    $limit = (int)$limit;
    if($limit < 1) {
      $limit = 8;
    }
    if($limit > 20) {
      $limit = 20;
    }
    $this->conn->select('a.pk_i_id, a.s_email, a.dt_date, a.b_active, a.e_type, a.fk_i_user_id, a.dt_expire_date, u.b_company');
    $this->conn->from(DB_TABLE_PREFIX.'t_alerts a');
    $this->conn->join(DB_TABLE_PREFIX.'t_user u', 'u.pk_i_id = a.fk_i_user_id', 'LEFT');
    $this->conn->where('a.dt_unsub_date IS NULL');
    $this->conn->orderBy('a.dt_date', 'DESC');
    $this->conn->limit($limit);

    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * @param    $from_date
   * @param string $date
   *
   * @return mixed
   */
  public function new_alerts_count( $from_date , $date = 'day' )
  {
    if($date === 'week') {
      $this->conn->select('WEEK(dt_date) as d_date, COUNT(s_email) as num');
      $this->conn->groupBy('WEEK(dt_date)');
    } else if($date === 'month') {
      $this->conn->select('MONTHNAME(dt_date) as d_date, COUNT(s_email) as num');
      $this->conn->groupBy('MONTH(dt_date)');
    } else {
      $this->conn->select('DATE(dt_date) as d_date, COUNT(s_email) as num');
      $this->conn->groupBy('DATE(dt_date)');
    }

    $this->conn->from( DB_TABLE_PREFIX . 't_alerts' );
    $this->conn->where("dt_date >= '$from_date'");
    $this->conn->where( 'dt_unsub_date IS NULL' );
    $this->conn->orderBy('dt_date', 'ASC');

    $result = $this->conn->get();
    return $result->result();
  }

  /**
   * @param    $from_date
   * @param string $date
   *
   * @return mixed
   */
  public function new_subscribers_count( $from_date , $date = 'day' )
  {
    if($date === 'week') {
      $this->conn->select('WEEK(dt_date) as d_date, COUNT(DISTINCT s_email) as num');
      $this->conn->groupBy('WEEK(dt_date)');
    } else if($date === 'month') {
      $this->conn->select('MONTHNAME(dt_date) as d_date, COUNT(DISTINCT s_email) as num');
      $this->conn->groupBy('MONTH(dt_date)');
    } else {
      $this->conn->select('DATE(dt_date) as d_date, COUNT(DISTINCT s_email) as num');
      $this->conn->groupBy('DATE(dt_date)');
    }

    $this->conn->from( DB_TABLE_PREFIX . 't_alerts' );
    $this->conn->where("dt_date >= '$from_date'");
    $this->conn->where( 'dt_unsub_date IS NULL' );
    $this->conn->orderBy('dt_date', 'ASC');

    $result = $this->conn->get();
    return $result->result();
  }

  /**
   * Daily alert emails sent from t_alerts_sent
   *
   * @param string $from_date
   * @return array
   */
  public function alerts_sent_count($from_date)
  {
    $this->conn->select('d_date, i_num_alerts_sent as num');
    $this->conn->from(DB_TABLE_PREFIX . 't_alerts_sent');
    $this->conn->where("d_date >= '$from_date'");
    $this->conn->orderBy('d_date', 'ASC');
    $result = $this->conn->get();
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Daily listings included in sent alert emails
   *
   * @param string $from_date
   * @return array
   */
  public function alerts_matched_count($from_date)
  {
    $sql = 'SELECT dt_date AS d_date, SUM(i_num_alerts_sent) AS num FROM ' . DB_TABLE_PREFIX . 't_item_stats WHERE dt_date >= \'' . $from_date . '\' GROUP BY dt_date ORDER BY dt_date ASC';
    $result = $this->conn->query($sql);
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Running count of active alerts at the end of each day
   *
   * @param string $from_date
   * @return array
   */
  public function alerts_active_by_day($from_date)
  {
    $from_date = date('Y-m-d', strtotime($from_date));
    $base = 0;
    $sql = 'SELECT COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts WHERE DATE(dt_date) < \'' . $from_date . '\' AND b_active = 1 AND (dt_unsub_date IS NULL OR DATE(dt_unsub_date) >= \'' . $from_date . '\') AND (dt_expire_date IS NULL OR DATE(dt_expire_date) >= \'' . $from_date . '\')';
    $result = $this->conn->query($sql);
    if($result) {
      $row = $result->row();
      $base = (int)(isset($row['num']) ? $row['num'] : 0);
    }
    $created = array();
    $sql = 'SELECT DATE(dt_date) AS d_date, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts WHERE DATE(dt_date) >= \'' . $from_date . '\' AND b_active = 1 GROUP BY DATE(dt_date)';
    $result = $this->conn->query($sql);
    if($result) {
      foreach($result->result() as $row) {
        $created[$row['d_date']] = (int)$row['num'];
      }
    }
    $unsub = array();
    $sql = 'SELECT DATE(dt_unsub_date) AS d_date, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts WHERE dt_unsub_date IS NOT NULL AND DATE(dt_unsub_date) >= \'' . $from_date . '\' AND b_active = 1 GROUP BY DATE(dt_unsub_date)';
    $result = $this->conn->query($sql);
    if($result) {
      foreach($result->result() as $row) {
        $unsub[$row['d_date']] = (int)$row['num'];
      }
    }
    $expired = array();
    $sql = 'SELECT DATE(dt_expire_date) AS d_date, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts WHERE dt_expire_date IS NOT NULL AND DATE(dt_expire_date) >= \'' . $from_date . '\' AND DATE(dt_expire_date) <= \'' . date('Y-m-d') . '\' AND b_active = 1 GROUP BY DATE(dt_expire_date)';
    $result = $this->conn->query($sql);
    if($result) {
      foreach($result->result() as $row) {
        $expired[$row['d_date']] = (int)$row['num'];
      }
    }
    $out = array();
    $ts = strtotime($from_date);
    $end = strtotime(date('Y-m-d'));
    $cur = $base;
    if($ts === false || $end === false) {
      return $out;
    }
    while($ts <= $end) {
      $d = date('Y-m-d', $ts);
      if(isset($created[$d])) {
        $cur += (int)$created[$d];
      }
      $out[$d] = $cur;
      if(isset($unsub[$d])) {
        $cur -= (int)$unsub[$d];
      }
      if(isset($expired[$d])) {
        $cur -= (int)$expired[$d];
      }
      if($cur < 0) {
        $cur = 0;
      }
      $ts = strtotime('+1 day', $ts);
    }
    return $out;
  }

  /**
   * Daily alerts that reached their expire date
   *
   * @param string $from_date
   * @return array
   */
  public function alerts_expired_count($from_date)
  {
    $from_date = date('Y-m-d', strtotime($from_date));
    $today = date('Y-m-d');
    $sql = 'SELECT DATE(dt_expire_date) AS d_date, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts WHERE dt_expire_date IS NOT NULL AND DATE(dt_expire_date) >= \'' . $from_date . '\' AND DATE(dt_expire_date) <= \'' . $today . '\' GROUP BY DATE(dt_expire_date) ORDER BY d_date ASC';
    $result = $this->conn->query($sql);
    if($result) {
      return $result->result();
    }
    return array();
  }

  /**
   * Subscribed alerts by active flag, optionally created from a start date
   *
   * @param string $from_date
   * @return array
   */
  public function alerts_by_status($from_date = '')
  {
    $out = array('active' => 0, 'inactive' => 0, 'expired' => 0);
    $sql = 'SELECT CASE WHEN dt_expire_date IS NOT NULL AND dt_expire_date <= NOW() THEN \'expired\' WHEN b_active = 1 THEN \'active\' ELSE \'inactive\' END AS s_type, COUNT(pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts WHERE dt_unsub_date IS NULL';
    if($from_date != '') {
      $sql .= " AND dt_date >= '" . $from_date . "'";
    }
    $sql .= ' GROUP BY s_type';
    $result = $this->conn->query($sql);
    if(!$result) {
      return $out;
    }
    foreach($result->result() as $row) {
      $key = (isset($row['s_type']) ? $row['s_type'] : '');
      if(isset($out[$key])) {
        $out[$key] = (int)$row['num'];
      }
    }
    return $out;
  }

  /**
   * Subscribed alerts by poster country (guests have no country)
   *
   * @return array
   */
  public function alerts_by_country()
  {
    $sql = 'SELECT COALESCE(NULLIF(u.s_country, \'\'), \'\') AS s_country, COUNT(a.pk_i_id) AS num FROM ' . DB_TABLE_PREFIX . 't_alerts a LEFT JOIN ' . DB_TABLE_PREFIX . 't_user u ON u.pk_i_id = a.fk_i_user_id WHERE a.dt_unsub_date IS NULL GROUP BY s_country ORDER BY num DESC';
    $result = $this->conn->query($sql);
    if($result) {
      return $result->result();
    }
    return array();
  }
}

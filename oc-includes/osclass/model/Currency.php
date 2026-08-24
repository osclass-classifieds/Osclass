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
 * Model database for Currency table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class Currency extends DAO
{
  /**
   * It references to self object: Currency.
   * It is used as a singleton
   *
   * @access private
   * @since unknown
   * @var Currency
   */
  private static $instance;
  private static $_currencies;

  /**
   * It creates a new Currency object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since unknown
   * @return Currency
   */
  public static function newInstance()
  {
    if(!self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_currency table
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_currency');
    $this->setPrimaryKey('pk_c_code');
    $this->setFields(array('pk_c_code', 's_name', 's_description', 'd_exchange_rate', 'b_enabled'));
  }

  /**
   * @param string $value
   *
   * @return bool|mixed
   */
  public function findByPrimaryKey( $value )
  {
    if(isset( self::$_currencies[ $value ] ) ) {
      return self::$_currencies[ $value ];
    }

    if(trim((string)$value) == '') {
      return false;
    }

    $this->dao->select($this->fields);
    $this->dao->from($this->getTableName());
    $this->dao->where($this->getPrimaryKey(), $value);
    $result = $this->dao->get();

    if($result === false ) {
      return false;
    }

    if($result->numRows() !== 1 ) {
      return false;
    }

    self::$_currencies[ $value ] = $result->row();

    return self::$_currencies[ $value ];
  }


  /**
   * Return all locales.
   *
   * @access public
   * @since  unknown
   *
   * @param bool $isBo
   * @param bool $indexedByPk
   *
   * @return array
   */
  public function listAll($indexedByPk = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    $aResults = $result->result();


    // Array key is locale code
    if($indexedByPk) {
      $aTmp = array();

      for($i = 0, $iMax = count($aResults); $i < $iMax; $i++) {
        $aTmp[(string)$aResults[$i][$this->getPrimaryKey()]] = $aResults[$i];
      }

      $aResults = $aTmp;
    }

    return $aResults;
  }


  /**
   * Delete file cache for listAllRaw (all + enabled-only + legacy key).
   */
  public static function clearListAllRawCache() {
    osc_cache_delete(md5(osc_base_url() . 'Currency::listAllRaw'));
    osc_cache_delete(md5(osc_base_url() . 'Currency::listAllRaw_all'));
    osc_cache_delete(md5(osc_base_url() . 'Currency::listAllRaw_en'));
  }

  /**
   *
   * @access public
   * @since 8.3.0+
   * @param bool        $cache_enabled
   * @param bool|null   $only_enabled null = front-office only enabled, false = all rows
   *
   * @return array
   */
  public function listAllRaw($cache_enabled = true, $only_enabled = null) {
    if($only_enabled === null) {
      $only_enabled = (!defined('OC_ADMIN') || !OC_ADMIN);
    }
    $key = md5(osc_base_url() . 'Currency::listAllRaw' . ($only_enabled ? '_en' : '_all'));
    $found = null;
    $cache = osc_cache_get($key, $found);

    if(OC_ADMIN || $cache_enabled === false || $cache === false) {
      $this->dao->select();
      $this->dao->from($this->getTableName());
      if($only_enabled) {
        $this->dao->where('b_enabled', 1);
      }
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
   * Clear in-memory cache for findByPrimaryKey (after admin updates).
   */
  public static function clearStaticCache() {
    self::$_currencies = array();
  }

  /**
   * Admin currencies list: pagination, optional keyword, sort.
   *
   * @param int    $start
   * @param int    $limit
   * @param string $sortKey     logical key: name, code, symbol, exchange_rate, status
   * @param string $direction   ASC|DESC
   * @param string $search      quick search
   *
   * @return array
   */
  public function adminSearch($start, $limit, $sortKey, $direction, $search) {
    $out = array('total' => 0, 'total_results' => 0, 'currencies' => array());
    $start = (int)$start;
    $limit = (int)$limit;
    if($limit <= 0) {
      $limit = 25;
    }
    if($start < 0) {
      $start = 0;
    }

    $direction = strtoupper(trim((string)$direction));
    if($direction != 'ASC' && $direction != 'DESC') {
      $direction = 'ASC';
    }

    $sortMap = array(
      'name' => 's_name',
      'code' => 'pk_c_code',
      'symbol' => 's_description',
      'exchange_rate' => 'COALESCE(d_exchange_rate, -1)',
      'status' => 'b_enabled',
    );

    if(!isset($sortMap[$sortKey])) {
      $sortKey = 'name';
    }
    $orderExpr = $sortMap[$sortKey];

    $kw = trim((string)$search);
    $match = str_replace('*', '%', $kw);
    if(strpos($match, '%') !== false) {
      $match = str_replace('%', '', $match);
    }

    $this->dao->select('COUNT(*) AS num');
    $this->dao->from($this->getTableName());
    $rTot = $this->dao->get();
    if($rTot && $rTot->numRows() >= 1) {
      $rw = $rTot->row();
      $out['total'] = (int)$rw['num'];
    }

    $this->dao->select('COUNT(*) AS num');
    $this->dao->from($this->getTableName());
    if($match != '') {
      $this->dao->like('pk_c_code', $match, 'both');
      $this->dao->orLike('s_name', $match, 'both');
      $this->dao->orLike('s_description', $match, 'both');
    }
    $rF = $this->dao->get();
    if($rF && $rF->numRows() >= 1) {
      $rw = $rF->row();
      $out['total_results'] = (int)$rw['num'];
    }

    $this->dao->select('*');
    $this->dao->from($this->getTableName());
    if($match != '') {
      $this->dao->like('pk_c_code', $match, 'both');
      $this->dao->orLike('s_name', $match, 'both');
      $this->dao->orLike('s_description', $match, 'both');
    }
    $this->dao->orderBy($orderExpr, $direction);
    $this->dao->limit($start, $limit);
    $rList = $this->dao->get();
    if($rList && $rList->numRows() >= 1) {
      $out['currencies'] = $rList->result();
    }

    return $out;
  }
}

/* file end: ./oc-includes/osclass/model/Currency.php */

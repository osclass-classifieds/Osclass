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
 * OSCLocale DAO
 */
class OSCLocale extends DAO {
  /**
   *
   * @var type
   */
  private static $instance;

  /**
   * @return \OSCLocale|\type
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
    $this->setTableName('t_locale');
    $this->setPrimaryKey('pk_c_code');
    $array_fields = array(
      'pk_c_code',
      's_name',
      's_short_name',
      's_description',
      's_version',
      's_author_name',
      's_author_url',
      's_currency_format',
      's_dec_point',
      's_thousands_sep',
      'i_num_dec',
      's_date_format',
      's_stop_words',
      'b_enabled',
      'b_enabled_bo',
      'b_locations_native',
      'b_rtl',
      'fk_c_currency_code'
    );
    $this->setFields($array_fields);
  }

  /**
   * Return all enabled locales.
   *
   * @access public
   * @since  unknown
   *
   * @param bool $isBo
   * @param bool $indexedByPk
   *
   * @return array
   */
  public function listAllEnabled($isBo = false, $indexedByPk = false) {
    $this->dao->select();
    $this->dao->from($this->getTableName());

    if($isBo) {
      $this->dao->where('b_enabled_bo', 1);
    } else {
      $this->dao->where('b_enabled', 1);
    }

    $this->dao->orderBy('s_name', 'ASC');
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

    $this->dao->orderBy('s_name', 'ASC');
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
   *
   * @access public
   * @since 8.3.0+
   * @return array
   */
  public function listAllRaw($cache_enabled = true) {
    $key = md5(osc_base_url().'OSCLocale::listAllRaw');
    $found = null;
    $cache = osc_cache_get($key, $found);

    if(OC_ADMIN || $cache_enabled === false || $cache === false) {
      $this->dao->from($this->getTableName());
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
   * Return all locales by code
   *
   * @access public
   * @since 2.3
   * @param string $code
   * @return array
   */
  public function findByCode($code) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('pk_c_code', $code);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Return first locale found by first 2 letters
   *
   * @access public
   * @since 8.0.2
   * @param string $code
   * @return array
   */
  public function findByShortCode($code) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->orderBy('b_enabled', 'DESC');
    $this->dao->where(sprintf('left(pk_c_code, 2) = "%s"', strtolower($code)));
    $this->dao->limit(1);

    $result = $this->dao->get();

    if($result == false) {
      return false;
    }

    return $result->row();
  }

  /**
   * Admin languages list: pagination, optional keyword, sort.
   *
   * @param int    $start
   * @param int    $limit
   * @param string $sortKey   logical key: name, short_name, description, code, enabled_fo, enabled_bo, locations_native, rtl, status
   * @param string $direction ASC|DESC
   * @param string $search    quick search
   *
   * @return array
   */
  public function adminSearch($start, $limit, $sortKey, $direction, $search) {
    $out = array('total' => 0, 'total_results' => 0, 'locales' => array());
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
      'short_name' => 's_short_name',
      'description' => 's_description',
      'code' => 'pk_c_code',
      'enabled_fo' => 'b_enabled',
      'enabled_bo' => 'b_enabled_bo',
      'locations_native' => 'b_locations_native',
      'rtl' => 'b_rtl',
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
      $this->dao->orLike('s_short_name', $match, 'both');
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
      $this->dao->orLike('s_short_name', $match, 'both');
      $this->dao->orLike('s_description', $match, 'both');
    }
    $this->dao->orderBy($orderExpr, $direction);
    $this->dao->limit($start, $limit);
    $rList = $this->dao->get();
    if($rList && $rList->numRows() >= 1) {
      $out['locales'] = $rList->result();
    }

    return $out;
  }

  /**
   * Delete all related to locale code.
   *
   * @access public
   * @since unknown
   * @param string $locale
   * @return bool
   */
  public function deleteLocale($locale) {
    osc_run_hook('delete_locale', $locale);

    $array_where = array('fk_c_locale_code' => $locale );
    $this->dao->delete(DB_TABLE_PREFIX.'t_category_description',  $array_where);
    $this->dao->delete(DB_TABLE_PREFIX.'t_item_description', $array_where);
    $this->dao->delete(DB_TABLE_PREFIX.'t_keywords', $array_where);
    $this->dao->delete(DB_TABLE_PREFIX.'t_user_description', $array_where);
    $this->dao->delete(DB_TABLE_PREFIX.'t_pages_description', $array_where);
    return $this->dao->delete($this->getTableName(), array('pk_c_code' => $locale));
  }
}

/* file end: ./oc-includes/osclass/model/OSCLocale.php */

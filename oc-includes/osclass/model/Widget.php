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
 * Widget DAO
 */
class Widget extends DAO {
  /**
   * @var \Widget
   */
  private static $instance;

  /**
   * @return \Widget
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
    $this->setTableName('t_widget');
    $this->setPrimaryKey('pk_i_id');
    $this->setFields(array(
      'pk_i_id',
      's_description',
      's_internal_name',
      's_location',
      'e_kind',
      's_code',
      's_device_visibility',
      's_css',
      'i_order',
      'b_single_locale'
    ));
  }

  /**
   * Description table name
   *
   * @return string
   */
  public function getDescriptionTableName() {
    return $this->getTablePrefix() . 't_widget_description';
  }

  /**
   * Cache key for widget lists
   *
   * @return string
   */
  private function cacheKey() {
    return md5(osc_base_url() . 'Widget::listAll:locale');
  }

  /**
   * Drop widget list cache
   *
   * @return void
   */
  public function clearCache() {
    osc_cache_delete($this->cacheKey());
  }

  /**
   * @param array $values
   * @return mixed
   */
  public function insert($values) {
    $res = parent::insert($values);
    $this->clearCache();
    if($res) {
      return $this->dao->insertedId();
    }
    return false;
  }

  /**
   * @param array $values
   * @param array $where
   * @return mixed
   */
  public function update($values, $where) {
    $res = parent::update($values, $where);
    $this->clearCache();
    return $res;
  }

  /**
   * @param array $where
   * @return mixed
   */
  public function delete($where) {
    if(isset($where['pk_i_id'])) {
      return $this->deleteByPrimaryKey($where['pk_i_id']);
    }

    $res = parent::delete($where);
    $this->clearCache();
    return $res;
  }

  /**
   * Delete widget and its locale rows
   *
   * @param int|array $id
   * @return int
   */
  public function deleteByPrimaryKey($id) {
    if(!is_array($id)) {
      $id = array($id);
    }

    $deleted = 0;
    foreach($id as $widgetId) {
      $widgetId = (int)$widgetId;
      if($widgetId <= 0) {
        continue;
      }

      $this->dao->delete($this->getDescriptionTableName(), array('fk_i_widget_id' => $widgetId));
      $res = $this->dao->delete($this->getTableName(), array('pk_i_id' => $widgetId));
      if($res) {
        $deleted++;
      }
    }

    $this->clearCache();
    return $deleted;
  }

  /**
   * Load all locale rows for given widget ids
   *
   * @param array $ids
   * @return array
   */
  private function descriptionsByWidgetIds($ids) {
    $map = array();
    if(!is_array($ids) || count($ids) == 0) {
      return $map;
    }

    $this->dao->select();
    $this->dao->from($this->getDescriptionTableName());
    $this->dao->whereIn('fk_i_widget_id', $ids);
    $result = $this->dao->get();

    if($result == false) {
      return $map;
    }

    foreach($result->result() as $row) {
      $wid = (int)$row['fk_i_widget_id'];
      if(!isset($map[$wid])) {
        $map[$wid] = array();
      }
      $map[$wid][$row['fk_c_locale_code']] = $row;
    }

    return $map;
  }

  /**
   * Attach locale arrays without flattening s_content
   *
   * @param array $rows
   * @return array
   */
  private function attachLocales($rows) {
    if(!is_array($rows) || count($rows) == 0) {
      return array();
    }

    $ids = array();
    foreach($rows as $row) {
      $ids[] = (int)$row['pk_i_id'];
    }

    $descriptions = $this->descriptionsByWidgetIds($ids);
    $out = array();

    foreach($rows as $row) {
      $wid = (int)$row['pk_i_id'];
      $row['locale'] = (isset($descriptions[$wid]) ? $descriptions[$wid] : array());
      $out[] = $row;
    }

    return $out;
  }

  /**
   * Flatten resolved s_content on a list of widgets
   *
   * @param array $rows
   * @return array
   */
  private function flattenContent($rows) {
    if(!is_array($rows) || count($rows) == 0) {
      return array();
    }

    $out = array();
    foreach($rows as $row) {
      $row['s_content'] = $this->resolveContent($row);
      $out[] = $row;
    }

    return $out;
  }

  /**
   * Attach locale arrays and flatten s_content
   *
   * @param array $rows
   * @return array
   */
  private function extendRows($rows) {
    return $this->flattenContent($this->attachLocales($rows));
  }

  /**
   * Resolve localized content for one widget
   *
   * @param array $widget
   * @return string
   */
  public function resolveContent($widget) {
    $locales = (isset($widget['locale']) && is_array($widget['locale']) ? $widget['locale'] : array());
    $single = (isset($widget['b_single_locale']) && (int)$widget['b_single_locale'] === 1);

    if($single) {
      foreach($locales as $row) {
        $text = (isset($row['s_content']) ? trim((string)$row['s_content']) : '');
        if($text != '') {
          return $row['s_content'];
        }
      }
      return '';
    }

    if(function_exists('osc_is_backoffice') && osc_is_backoffice()) {
      $current = (function_exists('osc_current_admin_locale') ? osc_current_admin_locale() : '');
    } else {
      $current = (function_exists('osc_current_user_locale') ? osc_current_user_locale() : '');
    }

    $strict = (function_exists('osc_widget_locale_strict') && osc_widget_locale_strict());
    $text = $this->contentFromLocale($locales, $current);
    if($strict) {
      return $text;
    }

    if(trim($text) != '') {
      return $text;
    }

    $default = (function_exists('osc_language') ? osc_language() : '');
    if($default != '' && $default != $current) {
      $text = $this->contentFromLocale($locales, $default);
      if(trim($text) != '') {
        return $text;
      }
    }

    $enabled = (function_exists('osc_get_locales') ? osc_get_locales() : array());
    if(is_array($enabled)) {
      foreach($enabled as $locale) {
        $code = (isset($locale['pk_c_code']) ? $locale['pk_c_code'] : '');
        if($code == '') {
          continue;
        }
        $text = $this->contentFromLocale($locales, $code);
        if(trim($text) != '') {
          return $text;
        }
      }
    }

    foreach($locales as $row) {
      $text = (isset($row['s_content']) ? trim((string)$row['s_content']) : '');
      if($text != '') {
        return $row['s_content'];
      }
    }

    return '';
  }

  /**
   * Content for one locale code
   *
   * @param array $locales
   * @param string $code
   * @return string
   */
  private function contentFromLocale($locales, $code) {
    if($code == '' || !isset($locales[$code]['s_content'])) {
      return '';
    }
    return (string)$locales[$code]['s_content'];
  }

  /**
   * List widgets with locale rows
   *
   * @param bool $cache_enabled
   * @return array
   */
  public function listAll($cache_enabled = true) {
    $found = null;
    $cache = osc_cache_get($this->cacheKey(), $found);

    if($cache_enabled === false || $cache === false) {
      $this->dao->select();
      $this->dao->from($this->getTableName());
      $this->dao->orderBy('s_location', 'ASC');
      $this->dao->orderBy('i_order', 'ASC');
      $this->dao->orderBy('pk_i_id', 'ASC');
      $result = $this->dao->get();

      if($result == false) {
        $data = array();
      } else {
        $data = $this->attachLocales($result->result());
      }

      osc_cache_set($this->cacheKey(), $data, OSC_CACHE_TTL);
      return $this->flattenContent($data);
    }

    return $this->flattenContent($cache);
  }

  /**
   * Find widgets by location
   *
   * @param string $location
   * @return array
   */
  public function findByLocation($location) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_location', $location);
    $this->dao->orderBy('i_order', 'ASC');
    $this->dao->orderBy('pk_i_id', 'ASC');
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $this->extendRows($result->result());
  }

  /**
   * Alias of findByLocation
   *
   * @param string $hook
   * @return array
   */
  public function findByHook($hook) {
    return $this->findByLocation($hook);
  }

  /**
   * Find widgets by admin label
   *
   * @param string $description
   * @return array
   */
  public function findByDescription($description) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_description', $description);
    $this->dao->orderBy('i_order', 'ASC');
    $this->dao->orderBy('pk_i_id', 'ASC');
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $this->extendRows($result->result());
  }

  /**
   * Find one widget by internal name
   *
   * @param string $name
   * @return array
   */
  public function findByInternalName($name) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_internal_name', $name);
    $result = $this->dao->get();

    if($result == false || $result->numRows() == 0) {
      return array();
    }

    $rows = $this->extendRows(array($result->row()));
    return (isset($rows[0]) ? $rows[0] : array());
  }

  /**
   * Find one widget by id
   *
   * @param int $id
   * @return array
   */
  public function findByPrimaryKey($id) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('pk_i_id', (int)$id);
    $result = $this->dao->get();

    if($result == false || $result->numRows() == 0) {
      return array();
    }

    $rows = $this->extendRows(array($result->row()));
    return (isset($rows[0]) ? $rows[0] : array());
  }

  /**
   * Distinct locations from DB
   *
   * @return array
   */
  public function listLocations() {
    $this->dao->select('DISTINCT s_location');
    $this->dao->from($this->getTableName());
    $this->dao->orderBy('s_location', 'ASC');
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    $out = array();
    foreach($result->result() as $row) {
      if(isset($row['s_location']) && $row['s_location'] != '') {
        $out[] = $row['s_location'];
      }
    }

    return $out;
  }

  /**
   * Next i_order for a location
   *
   * @param string $location
   * @return int
   */
  public function nextOrder($location) {
    $this->dao->select('MAX(i_order) as max_order');
    $this->dao->from($this->getTableName());
    $this->dao->where('s_location', $location);
    $result = $this->dao->get();

    if($result == false) {
      return 1;
    }

    $row = $result->row();
    return (int)$row['max_order'] + 1;
  }

  /**
   * Check if internal name is used by another widget
   *
   * @param int $id
   * @param string $internalName
   * @return bool
   */
  public function internalNameExists($id, $internalName) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_internal_name', $internalName);
    $this->dao->where('pk_i_id <> ' . (int)$id);
    $result = $this->dao->get();

    if($result == false) {
      return false;
    }

    return $result->numRows() > 0;
  }

  /**
   * Insert or update locale content
   *
   * @param int $id
   * @param string $locale
   * @param string $content
   * @return mixed
   */
  public function updateDescription($id, $locale, $content) {
    $conditions = array('fk_c_locale_code' => $locale, 'fk_i_widget_id' => $id);
    if(!$this->existDescription($conditions)) {
      $this->dao->insert($this->getDescriptionTableName(), array(
        'fk_i_widget_id' => $id,
        'fk_c_locale_code' => $locale,
        's_content' => $content
      ));
      $this->clearCache();
      return $this->dao->affectedRows();
    }

    $res = $this->dao->update(
      $this->getDescriptionTableName(),
      array('s_content' => $content),
      $conditions
    );
    $this->clearCache();
    return $res;
  }

  /**
   * Delete all locale rows except one
   *
   * @param int $id
   * @param string $keepLocale
   * @return mixed
   */
  public function deleteOtherDescriptions($id, $keepLocale) {
    $this->dao->from($this->getDescriptionTableName());
    $this->dao->where('fk_i_widget_id', (int)$id);
    $this->dao->where("fk_c_locale_code <> '" . addslashes($keepLocale) . "'");
    $res = $this->dao->delete();
    $this->clearCache();
    return $res;
  }

  /**
   * @param array $conditions
   * @return bool
   */
  public function existDescription($conditions) {
    $this->dao->select('COUNT(*) as total');
    $this->dao->from($this->getDescriptionTableName());

    foreach($conditions as $key => $value) {
      $this->dao->where($key, $value);
    }

    $result = $this->dao->get();
    if($result == false) {
      return false;
    }

    $count = $result->row();
    return $count['total'] > 0;
  }

  /**
   * Swap order with neighbor in the same section
   *
   * @param int $id
   * @param string $direction
   * @return bool
   */
  public function moveOrder($id, $direction) {
    $id = (int)$id;
    if($id <= 0 || ($direction !== 'up' && $direction !== 'down')) {
      return false;
    }

    $widget = $this->findByPrimaryKey($id);
    if(empty($widget) || !isset($widget['s_location'])) {
      return false;
    }

    $list = $this->findByLocation($widget['s_location']);
    $index = -1;
    foreach($list as $i => $row) {
      if((int)$row['pk_i_id'] === $id) {
        $index = $i;
        break;
      }
    }

    if($index < 0) {
      return false;
    }

    $swap = ($direction === 'up' ? $index - 1 : $index + 1);
    if(!isset($list[$swap])) {
      return false;
    }

    $orderA = (int)$list[$index]['i_order'];
    $orderB = (int)$list[$swap]['i_order'];
    if($orderA === $orderB) {
      $orderB = ($direction === 'up' ? $orderA - 1 : $orderA + 1);
    }

    parent::update(array('i_order' => $orderB), array('pk_i_id' => $list[$index]['pk_i_id']));
    parent::update(array('i_order' => $orderA), array('pk_i_id' => $list[$swap]['pk_i_id']));
    $this->clearCache();
    return true;
  }
}

/* file end: ./oc-includes/osclass/model/Widget.php */

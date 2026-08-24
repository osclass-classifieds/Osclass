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
 * Model database for Field table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class Field extends DAO
{
  /**
   * It references to self object: Field.
   * It is used as a singleton
   *
   * @access private
   * @since unknown
   * @var Field
   */
  private static $instance;

  /**
   * It creates a new Field object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since unknown
   * @return Field
   */
  public static function newInstance()
  {
    if(!self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_meta_fields table
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_meta_fields');
    $this->setPrimaryKey('pk_i_id');
    $this->setFields( array('pk_i_id', 's_name', 'e_type', 'b_required', 'b_searchable', 's_slug', 's_options', 'i_order') );
  }

  /**
   * List all categories
   *
   * @access public
   * @since unknown
   * @return array
   */
  public function listAll($description = true)
  {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->orderBy('i_order', 'ASC');
    $result = $this->dao->get();

    if($result == false ) {
      return array();
    }

    return $result->result();
  }

  /**
   * Find a field by its id.
   *
   * @access public
   * @since unknown
   * @param int $id
   * @return array Field information. If there's no information, return an empty array.
   */
  public function findByPrimaryKey($id)
  {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('pk_i_id', $id);

    $result = $this->dao->get();

    if($result == false ) {
      return array();
    }

    return $result->row();
  }

  /**
   * Find a field by its name
   *
   * @access public
   * @since unknown
   * @param string $id
   * @return array Field information. If there's no information, return an empty array.
   */
  public function findByCategory($id)
  {
    $this->dao->select('mf.*');
    $this->dao->from(sprintf('%st_meta_fields mf, %st_meta_categories mc', DB_TABLE_PREFIX, DB_TABLE_PREFIX));
    $this->dao->where('mc.fk_i_category_id', $id);
    $this->dao->where('mf.pk_i_id = mc.fk_i_field_id');
    $this->dao->orderBy('mf.i_order', 'ASC');

    $result = $this->dao->get();

    if($result == false ) {
      return array();
    }

    return $result->result();
  }

  /**
   * Find a field by its name
   *
   * @access public
   * @since  unknown
   *
   * @param mixed $ids
   *
   * @return array Fields' id
   * @throws \Exception
   */
  public function findIDSearchableByCategories($ids)
  {
    if(!is_array($ids)) { $ids = array($ids); }
    $this->dao->select('f.pk_i_id');
    $this->dao->from( $this->getTableName() . ' f, ' . DB_TABLE_PREFIX . 't_meta_categories c' );
    $where = array();
    $mCat = Category::newInstance();
    foreach($ids as $id) {
      if(is_numeric($id)) {
        $where[] = 'c.fk_i_category_id = '.$id;
      } else {
        $cat = $mCat->findBySlug($id);
        if(isset($cat['pk_i_id'])) {
          $where[] = 'c.fk_i_category_id = ' . $cat['pk_i_id'];
        }
      }
    }
    if(empty($where)) {
      return array();
    } else {
      $this->dao->where('( '.implode(' OR ', $where).' )');
    }
    $this->dao->where('f.pk_i_id = c.fk_i_field_id');
    $this->dao->where('f.b_searchable', 1);
    $this->dao->orderBy('f.i_order', 'ASC');

    $result = $this->dao->get();

    if($result == false ) {
      return array();
    }

    $tmp = array();
    foreach($result->result() as $t) { $tmp[] = $t['pk_i_id']; }

    return $tmp;
  }

  /**
   * Find fields from a category and an item
   *
   * @access public
   * @since  unknown
   *
   * @param $catId
   * @param $itemId
   *
   * @return array Field information. If there's no information, return an empty array.
   */
  public function findByCategoryItem($catId, $itemId)
  {
    if(!is_numeric($catId) || (!is_numeric($itemId) && $itemId != null) ) {
      return array();
    }

    $result = $this->dao->query(sprintf( 'SELECT query.*, im.s_value as s_value, im.fk_i_item_id FROM (SELECT mf.* FROM %st_meta_fields mf, %st_meta_categories mc WHERE mc.fk_i_category_id = %d AND mf.pk_i_id = mc.fk_i_field_id) as query LEFT JOIN %st_item_meta im ON im.fk_i_field_id = query.pk_i_id AND im.fk_i_item_id = %d group by pk_i_id ORDER BY query.i_order ASC' , DB_TABLE_PREFIX, DB_TABLE_PREFIX, $catId, DB_TABLE_PREFIX, $itemId));

    if($result == false ) {
      return array();
    }

    return $result->result();
  }

  /**
   * Find a field by its name
   *
   * @access public
   * @since unknown
   * @param string $name
   * @return array Field information. If there's no information, return an empty array.
   */
  public function findByName($name)
  {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_name', $name);

    $result = $this->dao->get();

    if($result == false ) {
      return array();
    }

    return $result->row();
  }

  /**
   * Find a field by its name
   *
   * @access public
   * @since unknown
   * @param string $slug
   * @return array Field information. If there's no information, return an empty array.
   */
  public function findBySlug($slug)
  {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_slug', $slug);

    $result = $this->dao->get();

    if($result == false ) {
      return array();
    }

    return $result->row();
  }

  /**
   * Return an array with from and to date values
   * given a meta field id
   *
   * @param $item_id
   * @param $field_id
   *
   * @return array
   */
  public function getDateIntervalByPrimaryKey($item_id, $field_id)
  {
    $this->dao->select();
    $this->dao->from(DB_TABLE_PREFIX.'t_item_meta');
    $this->dao->where('fk_i_field_id', $field_id);
    $this->dao->where('fk_i_item_id', $item_id);

    $result = $this->dao->get();

    if($result == false ) {
      return array();
    }

    $aAux = $result->result();
    $aInterval = array();
    foreach($aAux as $k => $v) {
      $aInterval[$v['s_multi']] = $v['s_value'];
    }
    return $aInterval;
  }

  /**
   * Gets which categories are associated with that field
   *
   * @access public
   * @since unknown
   * @param string $id
   * @return array
   */
  public function categories($id)
  {
    $this->dao->select('fk_i_category_id');
    $this->dao->from(sprintf('%st_meta_categories', DB_TABLE_PREFIX));
    $this->dao->where('fk_i_field_id', $id);

    $result = $this->dao->get();

    if($result == false ) {
      return array();
    }

    $categories = $result->result();
    $cats = array();
    foreach($categories as $k => $v) {
      $cats[] = $v['fk_i_category_id'];
    }
    return $cats;
  }

  /**
   * Insert a new field
   *
   * @access public
   * @since  unknown
   *
   * @param string $name
   * @param string $type
   * @param string $slug
   * @param bool   $required
   * @param array  $options
   * @param array  $categories
   *
   * @return int|false Field id on success, false on failure
   */
  public function insertField($name, $type, $slug, $required, $options, $categories = null, $order = 999, $searchable = 0) {
    if($slug == '') {
      $slug = $name;
    }
    $slug_base = $this->normalizeSlug($slug, 'new-custom-field');
    $res = $this->dao->insert($this->getTableName(), array('s_name' => $name, 'e_type' => $type, 'b_required' => $required, 'b_searchable' => (int)$searchable, 's_slug' => $slug_base, 's_options' => $options, 'i_order' => $order));
    $id = (int)$this->dao->insertedId();
    if(!$res || $id <= 0) {
      return false;
    }
    $slug = $this->resolveUniqueSlug($slug_base, $id);
    if($slug !== $slug_base) {
      $this->update(array('s_slug' => $slug), array('pk_i_id' => $id));
    }
    if($categories != null && is_array($categories)) {
      foreach($categories as $c) {
        $result = $this->dao->insert(sprintf('%st_meta_categories', DB_TABLE_PREFIX), array('fk_i_category_id' => $c, 'fk_i_field_id' => $id));
        if(!$result) {
          return false;
        }
      }
    }
    return $id;
  }


  // Count all custom fields
  public function countAll() {
    $this->dao->select('COUNT(*) AS num');
    $this->dao->from($this->getTableName());
    $result = $this->dao->get();
    if($result) {
      $row = $result->row();
      return (int)$row['num'];
    }
    return 0;
  }

  // Allowed field types for admin type filter
  public static function allowedTypes() {
    return array('TEXT', 'NUMBER', 'TEL', 'EMAIL', 'COLOR', 'TEXTAREA', 'DROPDOWN', 'RADIO', 'CHECKBOX', 'URL', 'DATE', 'DATEINTERVAL');
  }

  // Build admin custom fields list URL preserving filters and pagination
  public static function adminListUrl($extra = array()) {
    $url = osc_admin_base_url(true) . '?page=custom_fields';
    $keep = array('iDisplayLength', 'sort', 'direction', 'iPage', 'sSearch', 'e_type');

    foreach($keep as $key) {
      if(is_array($extra) && array_key_exists($key, $extra)) {
        continue;
      }
      $value = Params::getParam($key);
      if($value !== '' && $value !== null) {
        $url .= '&' . rawurlencode($key) . '=' . rawurlencode((string)$value);
      }
    }

    if(is_array($extra)) {
      foreach($extra as $key => $value) {
        if($value === '' || $value === null) {
          continue;
        }
        $url .= '&' . rawurlencode($key) . '=' . rawurlencode((string)$value);
      }
    }

    return $url;
  }

  // Normalize identifier slug for storage
  public function normalizeSlug($slug, $fallback = '') {
    $slug = preg_replace('|([-]+)|', '-', preg_replace('|[^a-z0-9_-]|', '-', strtolower(trim((string)$slug))));
    if($slug === '' && $fallback !== '') {
      return $fallback;
    }
    return $slug;
  }

  // Load field row for quick-edit iframe
  public function findForAdminIframe($fieldId) {
    $fieldId = (int)$fieldId;
    if($fieldId <= 0) {
      return array();
    }

    $field = $this->findByPrimaryKey($fieldId);
    if(is_array($field) && isset($field['pk_i_id']) && (int)$field['pk_i_id'] > 0) {
      return $field;
    }

    $s_name = __('NEW custom field');
    $slug = $this->normalizeSlug($s_name, 'new-custom-field');

    return array(
      'pk_i_id' => $fieldId,
      's_name' => $s_name,
      's_slug' => $this->resolveUniqueSlug($slug, $fieldId),
      'e_type' => 'TEXT',
      'b_required' => 0,
      'b_searchable' => 0,
      's_options' => ''
    );
  }

  // Admin list with search, sort and pagination
  public function adminList($start, $limit, $sortKey, $direction, $search, $typeFilter = '') {
    $out = array('total' => 0, 'total_results' => 0, 'fields' => array());
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
      'type' => 'e_type',
      'required' => 'b_required',
      'searchable' => 'b_searchable',
      'options' => 's_options',
      'order' => 'i_order',
      'position' => 'i_order',
    );

    if(!isset($sortMap[$sortKey])) {
      $sortKey = 'order';
    }
    $orderExpr = $sortMap[$sortKey];

    $this->dao->select('COUNT(*) AS num');
    $this->dao->from($this->getTableName());
    $rTot = $this->dao->get();
    if($rTot && $rTot->numRows() >= 1) {
      $rw = $rTot->row();
      $out['total'] = (int)$rw['num'];
    }

    $this->dao->select('COUNT(*) AS num');
    $this->dao->from($this->getTableName());
    $this->adminApplyListFilters($search, $typeFilter);
    $rF = $this->dao->get();
    if($rF && $rF->numRows() >= 1) {
      $rw = $rF->row();
      $out['total_results'] = (int)$rw['num'];
    }

    $this->dao->select('*');
    $this->dao->from($this->getTableName());
    $this->adminApplyListFilters($search, $typeFilter);
    $this->dao->orderBy($orderExpr, $direction);
    if($sortKey == 'order' || $sortKey == 'position') {
      $this->dao->orderBy('pk_i_id', 'ASC');
    }
    $this->dao->limit($start, $limit);
    $rList = $this->dao->get();
    if($rList && $rList->numRows() >= 1) {
      $out['fields'] = $rList->result();
    }

    return $out;
  }

  // Apply admin list search and type filters
  private function adminApplyListFilters($search, $typeFilter) {
    $typeFilter = trim((string)$typeFilter);
    if($typeFilter !== '' && in_array($typeFilter, self::allowedTypes(), true)) {
      $this->dao->where('e_type', $typeFilter);
    }

    $kw = trim((string)$search);
    if($kw === '') {
      return;
    }

    $like = '%' . str_replace(array('%', '_'), array('\\%', '\\_'), $kw) . '%';
    $conds = array();
    $conds[] = 's_name LIKE "' . $this->dao->connId->real_escape_string($like) . '"';
    $conds[] = 's_slug LIKE "' . $this->dao->connId->real_escape_string($like) . '"';
    $conds[] = 's_options LIKE "' . $this->dao->connId->real_escape_string($like) . '"';
    $conds[] = 'e_type LIKE "' . $this->dao->connId->real_escape_string($like) . '"';

    if(ctype_digit($kw)) {
      $conds[] = 'pk_i_id = ' . (int)$kw;
    }

    $this->dao->where('(' . implode(' OR ', $conds) . ')');
  }

  // List id/position pairs for order controls
  public function listOrderRows() {
    $this->dao->select('pk_i_id, i_order');
    $this->dao->from($this->getTableName());
    $this->dao->orderBy('i_order', 'ASC');
    $this->dao->orderBy('pk_i_id', 'ASC');
    $result = $this->dao->get();

    if($result) {
      return $result->result();
    }

    return array();
  }

  // Persist position values for all fields
  private function saveFieldOrders($items) {
    if(!is_array($items) || count($items) == 0) {
      return 0;
    }

    require_once osc_lib_path() . 'osclass/classes/PositionOrder.php';

    $items = PositionOrder::normalize($items, 'pk_i_id', 'i_order', 1);
    $rows = 0;

    foreach($items as $item) {
      $pos = (int)$item['i_order'];
      if($pos < 1) {
        $pos = 1;
      }

      $rows += (int)$this->dao->update(
        $this->tableName,
        array('i_order' => $pos),
        array('pk_i_id' => (int)$item['pk_i_id'])
      );
    }

    return $rows;
  }

  // Re-sequence field positions to 1..n
  public function normalizeOrders() {
    $items = $this->listOrderRows();
    if(count($items) == 0) {
      return 0;
    }

    return $this->saveFieldOrders($items);
  }

  // Move field position up/down
  public function moveOrder($id, $direction) {
    $id = (int)$id;
    if($id <= 0 || ($direction !== 'up' && $direction !== 'down')) {
      return false;
    }

    $field = $this->findByPrimaryKey($id);
    if(empty($field)) {
      return false;
    }

    require_once osc_lib_path() . 'osclass/classes/PositionOrder.php';

    $items = $this->listOrderRows();
    if(count($items) == 0) {
      return false;
    }

    $before = json_encode($items);
    $items = PositionOrder::move($items, $id, $direction, 'pk_i_id', 'i_order', 1);
    if($before === json_encode($items)) {
      return false;
    }

    $this->saveFieldOrders($items);

    return true;
  }

  // Next position when adding a field
  public function getNextOrder() {
    $this->dao->select('MAX(i_order) AS max_order');
    $this->dao->from($this->getTableName());
    $result = $this->dao->get();
    if($result) {
      $row = $result->row();
      return max(1, (int)$row['max_order'] + 1);
    }
    return 1;
  }

  // Resolve unique slug for admin save
  public function resolveUniqueSlug($slug, $fieldId) {
    $slug = $this->normalizeSlug($slug, '');
    if($slug === '') {
      return '';
    }

    $slug_base = $slug;
    $field = $this->findBySlug($slug);
    if(!$field || (int)$field['pk_i_id'] === (int)$fieldId) {
      return $slug;
    }

    if((int)$fieldId > 0) {
      $slug = $slug_base . '-' . (int)$fieldId;
      $field = $this->findBySlug($slug);
      if(!$field || (int)$field['pk_i_id'] === (int)$fieldId) {
        return $slug;
      }
    }

    return $slug_base;
  }

  // Trim comma-separated field options
  public function trimOptionsString($options) {
    $s_options = '';
    $aux = (string)$options;
    $aAux = explode(',', $aux);

    foreach($aAux as &$option) {
      $option = trim($option);
    }

    return implode(',', $aAux);
  }

  // Save field configuration and category links from admin POST params
  public function saveAdminConfiguration($fieldId) {
    $fieldId = (int)$fieldId;
    if($fieldId <= 0) {
      return array('ok' => false, 'error' => 1, 'message' => __('An error occurred while updating.'));
    }

    $name = Params::getParam('s_name');
    $existing = $this->findByName($name);
    if(isset($existing['pk_i_id']) && (int)$existing['pk_i_id'] !== $fieldId) {
      return array('ok' => false, 'error' => 1, 'message' => __('Sorry, you already have a field with that name'));
    }

    $slugInput = Params::getParam('field_slug');
    if($slugInput === '' || $slugInput === null) {
      $slugInput = $name;
    }
    $slug = $this->resolveUniqueSlug($slugInput, $fieldId);
    $s_options = $this->trimOptionsString(Params::getParam('s_options'));
    $type = Params::getParam('field_type');
    if(!in_array($type, self::allowedTypes(), true)) {
      $type = 'TEXT';
    }

    $res = $this->update(
      array(
        's_name' => $name,
        'e_type' => $type,
        's_slug' => $slug,
        'b_required' => (Params::getParam('field_required') == '1' ? 1 : 0),
        'b_searchable' => (Params::getParam('field_searchable') == '1' ? 1 : 0),
        's_options' => $s_options
      ),
      array('pk_i_id' => $fieldId)
    );

    if(is_bool($res) && !$res) {
      return array('ok' => false, 'error' => 1, 'message' => __('An error occurred while updating.'));
    }

    $this->cleanCategoriesFromField($fieldId);
    $aCategories = Params::getParam('categories');
    if(is_array($aCategories) && count($aCategories) > 0) {
      $res = $this->insertCategories($fieldId, $aCategories);
      if(!$res) {
        return array('ok' => false, 'error' => 1, 'message' => __('An error occurred while updating.'));
      }
    }

    return array('ok' => true, 'error' => 0, 'message' => __('The custom field has been updated'), 'field_id' => $fieldId, 'text' => $name);
  }

  // Create placeholder custom field for quick management (last list position)
  public function createQuickManagementPlaceholder() {
    $s_name = __('NEW custom field');
    $slug = $this->normalizeSlug($s_name, 'new-custom-field');
    $fieldId = $this->insertField($s_name, 'TEXT', $slug, 0, '', null, $this->getNextOrder(), 0);
    if($fieldId === false || (int)$fieldId <= 0) {
      return array('ok' => false, 'message' => __('Custom field could not be added'));
    }

    $fieldId = (int)$fieldId;
    $field = $this->findByPrimaryKey($fieldId);
    $fieldName = (is_array($field) && isset($field['s_name']) ? $field['s_name'] : $s_name);

    return array('ok' => true, 'field_id' => $fieldId, 'field_name' => $fieldName);
  }

  // Create custom field from admin add form
  public function createAdminField() {
    $name = trim((string)Params::getParam('s_name'));
    if($name === '') {
      return array('ok' => false, 'message' => __('Name is required'));
    }

    $existing = $this->findByName($name);
    if(!empty($existing['pk_i_id'])) {
      return array('ok' => false, 'message' => __('Sorry, you already have a field with that name'));
    }

    $slugInput = trim((string)Params::getParam('field_slug'));
    if($slugInput === '') {
      return array('ok' => false, 'message' => __('Identifier is required'));
    }

    $slug = $this->normalizeSlug($slugInput, '');
    if($slug === '') {
      return array('ok' => false, 'message' => __('Identifier is required'));
    }

    $type = Params::getParam('field_type');
    if(!in_array($type, self::allowedTypes(), true)) {
      $type = 'DROPDOWN';
    }

    $s_options = $this->trimOptionsString(Params::getParam('s_options'));

    $required = (Params::getParam('field_required') == '1' ? 1 : 0);
    $searchable = (Params::getParam('field_searchable') == '1' ? 1 : 0);
    $categories = Params::getParam('categories');
    if(!is_array($categories)) {
      $categories = array();
    }

    $order = $this->getNextOrder();
    $fieldId = $this->insertField($name, $type, $slug, $required, $s_options, $categories, $order, $searchable);
    if($fieldId === false || (int)$fieldId <= 0) {
      return array('ok' => false, 'message' => __('Custom field could not be added'));
    }

    return array('ok' => true, 'message' => __('The custom field has been added'), 'field_id' => (int)$fieldId);
  }

  // Human-readable label for field type enum
  public static function typeLabel($type) {
    $map = array(
      'TEXT' => __('Text'),
      'NUMBER' => __('Number'),
      'EMAIL' => __('Email'),
      'TEL' => __('Phone'),
      'URL' => __('URL'),
      'COLOR' => __('Color'),
      'TEXTAREA' => __('Textarea'),
      'DROPDOWN' => __('Select box'),
      'CHECKBOX' => __('Check box'),
      'RADIO' => __('Radio button'),
      'DATE' => __('Date'),
      'DATEINTERVAL' => __('Date interval'),
    );

    if(isset($map[$type])) {
      return $map[$type];
    }

    return $type;
  }

  /**
   * Update fields' order
   *
   * @access public
   * @since unknown
   * @param integer $pk_i_id
   * @param integer $order
   * @return mixed false on fail, int of num. of affected rows
   */
  public function updateOrder($pk_i_id, $order)
  {
    return $this->dao->update($this->tableName, array('i_order' => $order), array('pk_i_id' => $pk_i_id));

  }

  /**
   * Save the categories linked to a field
   *
   * @access public
   * @since unknown
   * @param int $id
   * @param array $categories
   * @return bool
   */
  public function insertCategories($id, $categories = null) {
    if($categories!=null) {
      $return = true;
      foreach($categories as $c) {
        $result = $this->dao->insert(sprintf('%st_meta_categories', DB_TABLE_PREFIX), array('fk_i_category_id' => $c, 'fk_i_field_id' =>$id));
        if(!$result) {
          $return = false;
        }
      }
      return $return;
    }
    return false;
  }

  /**
   * Removes categories from a field
   *
   * @access public
   * @since unknown
   * @param int $id
   * @return bool on success
   */
  public function cleanCategoriesFromField($id) {
    return $this->dao->delete(sprintf('%st_meta_categories', DB_TABLE_PREFIX), array('fk_i_field_id' =>$id));
  }

  /**
   * Update a field value
   *
   * @access public
   * @since unknown
   * @param int $itemId
   * @param int $field
   * @param string $value
   * @return mixed false on fail, int of num. of affected rows
   */
  public function replace($itemId, $field, $value) {
    if(is_array($value) ) {
      foreach($value as $key => $v) {
        $this->dao->replace(sprintf('%st_item_meta', DB_TABLE_PREFIX), array('fk_i_item_id' => $itemId, 'fk_i_field_id' => $field, 's_multi' => $key, 's_value' => $v));
      }
    } else {
      return $this->dao->replace(sprintf('%st_item_meta', DB_TABLE_PREFIX), array('fk_i_item_id' => $itemId, 'fk_i_field_id' => $field, 's_value' => $value));
    }
  }

  /**
   * Delete a field and all information associated with it
   *
   * @access public
   * @since unknown
   * @param int $id
   * @return bool on success
   */
  public function deleteByPrimaryKey($id) {
    $this->dao->delete(sprintf('%st_item_meta', DB_TABLE_PREFIX), array('fk_i_field_id' =>$id));
    $this->dao->delete(sprintf('%st_meta_categories', DB_TABLE_PREFIX), array('fk_i_field_id' =>$id));
    return $this->dao->delete($this->getTableName(), array('pk_i_id' =>$id));
  }
}

/* file end: ./oc-includes/osclass/model/Field.php */

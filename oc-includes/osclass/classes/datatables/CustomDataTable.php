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
 * Configurable admin datatable for plugins and custom backoffice lists.
 *
 * Full usage (controller, view, pagination, search, bulk actions) is documented
 * in hCustomDataTable.php — see the USAGE block and helper functions there.
 *
 * @package Osclass
 * @subpackage classes
 * @see hCustomDataTable.php
 */
class CustomDataTable extends DataTable {
  private $config = array();
  private $search = '';
  private $withFilters = false;
  private $listParams = array();
  private $orderBy = array('column_name' => '', 'type' => 'DESC');
  private $rowClassRegistered = false;

  /**
   * @param array $options
   */
  public function __construct($options = array()) {
    parent::__construct();
    $this->configure($options);
  }

  /**
   * Merge or replace configuration.
   *
   * @param array $options
   *
   * @return CustomDataTable
   */
  public function configure($options = array()) {
    $options = (is_array($options) ? $options : array());
    $this->config = osc_apply_filter('custom_datatable_config', array_merge($this->defaultConfig(), $options), $this);
    $this->registerRowClassFilter();
    return $this;
  }

  /**
   * @return array
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * @return array
   */
  public function getListParams() {
    return $this->listParams;
  }

  /**
   * @return string
   */
  public function getSearch() {
    return $this->search;
  }

  /**
   * @return bool
   */
  public function withFilters() {
    return $this->withFilters;
  }

  /**
   * @return array
   */
  public function getOrderBy() {
    return $this->orderBy;
  }

  /**
   * Main entry: build headers, fetch rows, return datatable payload.
   *
   * @param array $params
   *
   * @return array
   */
  public function table($params) {
    $params = (is_array($params) ? $params : array());
    $id = $this->getConfigId();
    $params = osc_apply_filter('custom_datatable_params_' . $id, $params, $this);

    $this->addTableHeader();
    $this->applyListParams($params);

    osc_run_hook('custom_datatable_before_fetch_' . $id, $this, $this->listParams);

    $fetch = $this->invokeCallback('fetch', array($this, $this->listParams));
    $fetch = (is_array($fetch) ? $fetch : array());
    $rows = (isset($fetch['rows']) && is_array($fetch['rows']) ? $fetch['rows'] : array());

    $this->total = (isset($fetch['total']) ? (int)$fetch['total'] : count($rows));
    $this->totalFiltered = (isset($fetch['total_filtered']) ? (int)$fetch['total_filtered'] : $this->total);

    $this->processData($rows);

    osc_run_hook('custom_datatable_after_fetch_' . $id, $this, $fetch, $this->listParams);

    return $this->getData();
  }

  /**
   * Default row_class filter handler when callbacks.row_class is set.
   *
   * @param array $class
   * @param array $rawRow
   * @param array $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
    $class = (is_array($class) ? $class : array());
    $callback = $this->getCallback('row_class');

    if(is_callable($callback)) {
      return call_user_func($callback, $class, $rawRow, $row, $this);
    }

    return $class;
  }

  /**
   * @return string
   */
  protected function getDatatableFilterKey() {
    $id = $this->getConfigId();
    if($id != '') {
      return preg_replace('/[^a-z0-9_]+/', '_', $id);
    }

    return parent::getDatatableFilterKey();
  }

  /**
   * @return array
   */
  private function defaultConfig() {
    return array(
      'id' => 'custom',
      'columns' => array(),
      'default_sort' => array('key' => '', 'direction' => 'desc'),
      'per_page' => 25,
      'status_columns' => false,
      'bulk_actions' => false,
      'header_hook' => '',
      'callbacks' => array(
        'fetch' => null,
        'process_row' => null,
        'row_class' => null,
        'row_actions' => null,
        'actions_column' => ''
      )
    );
  }

  /**
   * @return string
   */
  private function getConfigId() {
    $id = (isset($this->config['id']) ? trim((string)$this->config['id']) : '');
    if($id == '') {
      return 'custom';
    }

    return preg_replace('/[^a-z0-9_\-]+/', '_', strtolower($id));
  }

  /**
   * @param array $params
   */
  private function applyListParams($params) {
    $defaultSort = (isset($this->config['default_sort']) && is_array($this->config['default_sort']) ? $this->config['default_sort'] : array());
    $defaultKey = (isset($defaultSort['key']) ? (string)$defaultSort['key'] : '');
    $defaultDir = (isset($defaultSort['direction']) ? (string)$defaultSort['direction'] : 'desc');

    if($defaultKey != '') {
      $this->setDefaultSort($defaultKey, $defaultDir);
    }

    $list = $this->resolveListParams($params, array(
      'default_per_page' => (int)(isset($this->config['per_page']) ? $this->config['per_page'] : 25),
      'default_sort_key' => $defaultKey,
      'default_sort_dir' => $defaultDir,
      'with_sort' => true
    ));

    $this->iPage = $list['iPage'];
    $this->start = $list['start'];
    $this->limit = $list['limit'];
    $this->search = $list['search'];
    $this->withFilters = $list['with_filters'];

    $sortColumn = (isset($list['sort']['column']) ? $list['sort']['column'] : '');
    $this->orderBy = array(
      'column_name' => $sortColumn,
      'type' => strtoupper((isset($list['sort']['direction']) ? $list['sort']['direction'] : 'desc'))
    );

    $this->listParams = array_merge($list, array(
      'order_by' => $this->orderBy,
      'sort_key' => (isset($list['sort']['key']) ? $list['sort']['key'] : ''),
      'config' => $this->config
    ));
  }

  /**
   * Register columns, sort metadata and table header.
   */
  private function addTableHeader() {
    $page = (int)Params::getParam('iPage');
    if($page == 0) {
      $page = 1;
    }
    Params::setParam('iPage', $page);

    $url_base = $this->getSortUrlBase();
    $sort = Params::getParam('sort');
    $direction = Params::getParam('direction');

    $this->clearSortColumns();
    $this->clearSourceColumns();

    $defaultSort = (isset($this->config['default_sort']) && is_array($this->config['default_sort']) ? $this->config['default_sort'] : array());
    if(isset($defaultSort['key']) && $defaultSort['key'] != '') {
      $this->setDefaultSort($defaultSort['key'], (isset($defaultSort['direction']) ? $defaultSort['direction'] : 'desc'));
    }

    $priority = 1;

    if(!empty($this->config['status_columns'])) {
      $this->addColumn('status-border', '', $priority++);
      $this->addColumn('status', __('Status'), $priority++);
      $this->addSourceColumn('status-border', '');
      $this->addSourceColumn('status', 'status');
    }

    $bulk = $this->config['bulk_actions'];
    if($bulk !== false && $bulk !== null) {
      $checkAll = '<input id="check_all" type="checkbox" />';
      if(is_array($bulk) && isset($bulk['check_all']) && $bulk['check_all'] === false) {
        $checkAll = '';
      }
      $this->addColumn('bulkactions', $checkAll, $priority++);
      $this->addSourceColumn('bulkactions', '');
    }

    $columns = (isset($this->config['columns']) && is_array($this->config['columns']) ? $this->config['columns'] : array());
    $columns = osc_apply_filter('custom_datatable_columns_' . $this->getConfigId(), $columns, $this);

    foreach($columns as $column) {
      if(!is_array($column) || !isset($column['id'])) {
        continue;
      }

      $colId = (string)$column['id'];
      $colPriority = (isset($column['priority']) ? (int)$column['priority'] : $priority++);
      if($colPriority < 1) {
        $colPriority = $priority++;
      }

      if(!empty($column['sortable']) && isset($column['sort_column']) && $column['sort_column'] != '') {
        $coalesce = (isset($column['sort_coalesce']) ? $column['sort_coalesce'] : false);
        $this->addSortColumn($colId, $column['sort_column'], $coalesce);
      }

      if(isset($column['source'])) {
        $this->addSourceColumn($colId, $column['source']);
      }

      $header = '';
      if(isset($column['header']) && $column['header'] !== '') {
        $header = $column['header'];
      } else if(!empty($column['sortable'])) {
        $label = (isset($column['label']) ? $column['label'] : $colId);
        $header = '<a href="' . osc_esc_html($url_base . $this->buildSortArgs($colId, $sort, $direction)) . '">' . $label . '</a>';
      } else {
        $header = (isset($column['label']) ? $column['label'] : $colId);
      }

      $this->addColumn($colId, $header, $colPriority);
    }

    $hook = (isset($this->config['header_hook']) && $this->config['header_hook'] != '' ? $this->config['header_hook'] : 'admin_' . $this->getConfigId() . '_table');
    $dummy = &$this;
    osc_run_hook($hook, $dummy);
  }

  /**
   * @param array $rows
   */
  private function processData($rows) {
    if(empty($rows)) {
      return;
    }

    $id = $this->getConfigId();
    $processRow = $this->getCallback('process_row');

    if(!is_callable($processRow)) {
      return;
    }

    foreach($rows as $rawRow) {
      $row = call_user_func($processRow, $rawRow, $this);
      $row = (is_array($row) ? $row : array());

      $row = $this->applyBulkCheckbox($row, $rawRow);
      $row = $this->applyRowActions($row, $rawRow);

      $row = osc_apply_filter('custom_datatable_row_' . $id, $row, $rawRow, $this);

      $this->addRow($row);
      $this->rawRows[] = $rawRow;
    }
  }

  /**
   * @param array $row
   * @param array $rawRow
   *
   * @return array
   */
  private function applyBulkCheckbox($row, $rawRow) {
    $bulk = $this->config['bulk_actions'];
    if($bulk === false || $bulk === null) {
      return $row;
    }

    if(isset($row['bulkactions']) && $row['bulkactions'] != '') {
      return $row;
    }

    if(!function_exists('osc_custom_datatable_bulk_checkbox')) {
      require_once osc_lib_path() . 'osclass/helpers/hCustomDataTable.php';
    }

    $options = (is_array($bulk) ? $bulk : array());
    $value = '';

    if(isset($options['value_key']) && isset($rawRow[$options['value_key']])) {
      $value = $rawRow[$options['value_key']];
    } else if(isset($rawRow['pk_i_id'])) {
      $value = $rawRow['pk_i_id'];
    }

    $row['bulkactions'] = osc_custom_datatable_bulk_checkbox($value, $options);
    return $row;
  }

  /**
   * @param array $row
   * @param array $rawRow
   *
   * @return array
   */
  private function applyRowActions($row, $rawRow) {
    $actionsCallback = $this->getCallback('row_actions');
    if(!is_callable($actionsCallback)) {
      return $row;
    }

    $options = call_user_func($actionsCallback, $rawRow, $this);
    $options = (is_array($options) ? $options : array());
    $options = osc_apply_filter('custom_datatable_row_actions_' . $this->getConfigId(), $options, $rawRow, $this);

    if(count($options) == 0) {
      return $row;
    }

    $actions = $this->buildRowActions($options, array(), 8);
    $columnId = (isset($this->config['callbacks']['actions_column']) ? (string)$this->config['callbacks']['actions_column'] : '');

    if($columnId == '' && count($this->config['columns']) > 0) {
      $first = $this->config['columns'][0];
      $columnId = (is_array($first) && isset($first['id']) ? (string)$first['id'] : '');
    }

    if($columnId != '' && isset($row[$columnId])) {
      $row[$columnId] .= $actions;
    }

    return $row;
  }

  /**
   * @param string $name
   *
   * @return callable|null
   */
  private function getCallback($name) {
    if(!isset($this->config['callbacks']) || !is_array($this->config['callbacks'])) {
      return null;
    }

    if(!isset($this->config['callbacks'][$name])) {
      return null;
    }

    $callback = $this->config['callbacks'][$name];
    return (is_callable($callback) ? $callback : null);
  }

  /**
   * @param string $name
   * @param array  $args
   *
   * @return mixed
   */
  private function invokeCallback($name, $args = array()) {
    $callback = $this->getCallback($name);
    if(!is_callable($callback)) {
      return null;
    }

    return call_user_func_array($callback, $args);
  }

  /**
   * Register datatable_{id}_class filter when row_class callback exists.
   */
  private function registerRowClassFilter() {
    if($this->rowClassRegistered) {
      return;
    }

    if(is_callable($this->getCallback('row_class'))) {
      osc_add_filter('datatable_' . $this->getConfigId() . '_class', array(&$this, 'row_class'));
      $this->rowClassRegistered = true;
    }
  }
}

/* file end: ./oc-includes/osclass/classes/datatables/CustomDataTable.php */

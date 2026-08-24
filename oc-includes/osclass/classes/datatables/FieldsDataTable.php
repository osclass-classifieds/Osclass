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
 * FieldsDataTable class
 *
 * @package Osclass
 * @subpackage classes
 */
class FieldsDataTable extends DataTable {
  private $search;
  private $typeFilter;
  private $orderKey;
  private $orderDir;
  private $withFilters = false;
  private $orderBoundsById = array();

  public function __construct() {
    parent::__construct();
  }

  // Whether list has active search filter
  public function withFilters() {
    return $this->withFilters;
  }

  /**
   * @param array $params
   *
   * @return array
   */
  public function table($params) {
    $this->search = '';
    $this->typeFilter = '';
    $this->orderKey = 'order';
    $this->orderDir = 'ASC';
    $this->addTableHeader();
    $this->getDBParams($params);

    $fieldManager = Field::newInstance();
    $fieldManager->normalizeOrders();
    $this->buildOrderBounds();

    $list = $fieldManager->adminList($this->start, $this->limit, $this->orderKey, $this->orderDir, $this->search, $this->typeFilter);

    $this->processData($list['fields']);
    $this->total = (int)$list['total'];
    $this->totalFiltered = (int)$list['total_results'];

    return $this->getData();
  }

  private function addTableHeader() {
    Rewrite::newInstance()->init();
    $page = (int)Params::getParam('iPage');
    if($page == 0) {
      $page = 1;
    }
    Params::setParam('iPage', $page);

    $url_base = preg_replace('|&direction=([^&]*)|', '', preg_replace('|&sort=([^&]*)|', '', osc_base_url() . Rewrite::newInstance()->get_raw_request_uri()));
    $sort = Params::getParam('sort');
    $direction = Params::getParam('direction');

    $this->clearSortColumns();
    $this->clearSourceColumns();
    $this->setDefaultSort('order', 'asc');

    $this->addSortColumn('name', 's_name', true);
    $this->addSortColumn('type', 'e_type', true);
    $this->addSortColumn('required', 'b_required', true);
    $this->addSortColumn('searchable', 'b_searchable', true);
    $this->addSortColumn('options', 's_options', true);
    $this->addSortColumn('order', 'i_order', true);

    $this->addSourceColumn('name', 's_name');
    $this->addSourceColumn('type', 'e_type');
    $this->addSourceColumn('required', 'b_required');
    $this->addSourceColumn('searchable', 'b_searchable');
    $this->addSourceColumn('options', 's_options');
    $this->addSourceColumn('order', 'i_order');

    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Name') . '</a>');
    $this->addColumn('type', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('type', $sort, $direction)) . '">' . __('Type') . '</a>');
    $this->addColumn('required', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('required', $sort, $direction)) . '">' . __('Required') . '</a>');
    $this->addColumn('searchable', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('searchable', $sort, $direction)) . '">' . __('Searchable') . '</a>');
    $this->addColumn('options', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('options', $sort, $direction)) . '">' . __('Options') . '</a>');
    $this->addColumn('order', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('order', $sort, $direction)) . '">' . __('Position') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_fields_table', $dummy);
  }

  private function getDBParams($params) {
    if(!isset($params['iDisplayLength']) || (int)$params['iDisplayLength'] <= 0) {
      $params['iDisplayLength'] = 25;
    }

    if(!isset($params['iPage']) || (int)$params['iPage'] < 1) {
      $params['iPage'] = 1;
    }

    $this->iPage = (int)$params['iPage'];
    $this->limit = (int)$params['iDisplayLength'];
    $this->start = (int)(($this->iPage - 1) * $this->limit);

    $this->search = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');
    $this->typeFilter = (isset($params['e_type']) ? trim((string)$params['e_type']) : '');
    if($this->typeFilter !== '' && !in_array($this->typeFilter, Field::allowedTypes(), true)) {
      $this->typeFilter = '';
    }
    $this->withFilters = ($this->search != '' || $this->typeFilter != '');

    $sortData = $this->resolveSort($params, 'order', 'asc');
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);

    $this->orderKey = ($sortData['key'] != '' ? $sortData['key'] : 'order');
    if($this->orderKey == 'position') {
      $this->orderKey = 'order';
    }
    $this->orderDir = strtoupper($sortData['direction']);
    if($this->orderDir != 'ASC' && $this->orderDir != 'DESC') {
      $this->orderDir = 'ASC';
    }
  }

  // Map field ids to position and move bounds
  private function buildOrderBounds() {
    $orderRows = Field::newInstance()->listOrderRows();
    $total = count($orderRows);
    $this->orderBoundsById = array();

    foreach($orderRows as $i => $row) {
      $id = (int)$row['pk_i_id'];
      $this->orderBoundsById[$id] = array(
        'position' => max(1, (int)$row['i_order']),
        'can_up' => ($i > 0),
        'can_down' => ($i < $total - 1)
      );
    }
  }

  // Render position controls for one row
  private function renderOrderBox($id) {
    $bounds = (isset($this->orderBoundsById[$id]) ? $this->orderBoundsById[$id] : array('position' => 1, 'can_up' => false, 'can_down' => false));
    $position = (int)$bounds['position'];
    $canUp = !empty($bounds['can_up']);
    $canDown = !empty($bounds['can_down']);
    $upClass = 'order-up' . ($canUp ? '' : ' is-disabled');
    $downClass = 'order-down' . ($canDown ? '' : ' is-disabled');
    $upClick = ($canUp ? 'order_up(' . (int)$id . ');' : 'return false;');
    $downClick = ($canDown ? 'order_down(' . (int)$id . ');' : 'return false;');

    $html = '<div class="order-box">';
    $html .= '<div class="order-box-value">' . (int)$position . '</div>';
    $html .= '<div class="order-box-arrows">';
    $html .= '<span class="' . $upClass . '" onclick="' . $upClick . '" title="' . osc_esc_html(__('Up')) . '">&#9650;</span>';
    $html .= '<span class="' . $downClass . '" onclick="' . $downClick . '" title="' . osc_esc_html(__('Down')) . '">&#9660;</span>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
  }

  // Build edit URL without list state params
  private function buildEditUrl($id) {
    return osc_admin_base_url(true) . '?page=custom_fields&action=edit&id=' . (int)$id;
  }

  private function processData($rows) {
    if(empty($rows)) {
      return;
    }

    foreach($rows as $aRow) {
      $id = (int)$aRow['pk_i_id'];
      $requiredOn = ((int)$aRow['b_required'] === 1);
      $searchableOn = ((int)$aRow['b_searchable'] === 1);
      $requiredBadge = ($requiredOn ? '<span class="enabled-value">✓ ' . __('Yes') . '</span>' : '<span class="disabled-value">✗ ' . __('No') . '</span>');
      $searchableBadge = ($searchableOn ? '<span class="enabled-value">✓ ' . __('Yes') . '</span>' : '<span class="disabled-value">✗ ' . __('No') . '</span>');

      $options = array();
      $options[] = '<a href="' . osc_esc_html($this->buildEditUrl($id)) . '">' . __('Edit') . '</a>';
      $options[] = '<a onclick="return delete_dialog(\'' . $id . '\');" href="javascript:void(0);">' . __('Delete') . '</a>';

      $auxOptions = '<ul>' . PHP_EOL;
      foreach($options as $actual) {
        $auxOptions .= '<li>' . $actual . '</li>' . PHP_EOL;
      }
      $actions = '<div class="actions">' . $auxOptions . '</div>' . PHP_EOL;

      $nameLink = '<a href="' . osc_esc_html($this->buildEditUrl($id)) . '">' . osc_esc_html($aRow['s_name']) . '</a>';

      $optionsText = trim((string)$aRow['s_options']);
      if($optionsText === '') {
        $optionsCell = '-';
      } else {
        $optionsCell = osc_esc_html(osc_substr($optionsText, 0, 100));
        if(osc_strlen($optionsText) > 100) {
          $optionsCell .= '&hellip;';
        }
      }

      $typeLabel = Field::typeLabel($aRow['e_type']);
      $typeHtml = '<a href="' . osc_esc_html(Field::adminListUrl(array('e_type' => $aRow['e_type'], 'iPage' => 1))) . '">' . osc_esc_html($typeLabel) . '</a>';

      $row = array();
      $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $id . '" />';
      $row['name'] = $nameLink . $actions;
      $row['type'] = $typeHtml;
      $row['required'] = $requiredBadge;
      $row['searchable'] = $searchableBadge;
      $row['options'] = $optionsCell;
      $row['order'] = $this->renderOrderBox($id);

      $row = osc_apply_filter('fields_processing_row', $row, $aRow);

      $this->addRow($row);
      $this->rawRows[] = $aRow;
    }
  }
}

/* file end: ./oc-includes/osclass/classes/datatables/FieldsDataTable.php */

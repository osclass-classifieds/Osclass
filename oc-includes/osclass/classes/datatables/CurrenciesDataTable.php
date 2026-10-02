<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

/*
 * Copyright 2014 Osclass
 * Copyright 2026 Osclass by OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * You may not use this file except in compliance with the License.
 *
 * Do not edit or add to this file if you wish to upgrade Osclass to newer
 * versions in the future. Software is distributed on an "AS IS" basis, without
 * warranties or conditions of any kind, either express or implied. Do not remove
 * this NOTICE section as it contains license information and copyrights.
 */


/**
 * CurrenciesDataTable class
 *
 * @package Osclass
 * @subpackage classes
 */
class CurrenciesDataTable extends DataTable {
  private $search;
  private $orderKey;
  private $orderDir;
  private $withFilters = false;

  public function __construct() {
    parent::__construct();
    osc_add_filter('datatable_currencies_class', array(&$this, 'row_class'));
  }

  /**
   * @param array $class
   * @param array $rawRow
   * @param array $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
    $enabled = (isset($rawRow['b_enabled']) && (int)$rawRow['b_enabled'] === 1);
    $class[] = ($enabled ? 'status-active' : 'status-inactive');
    return $class;
  }

  /**
   * @param array $params
   *
   * @return array
   */
  public function table($params) {
    $this->search = '';
    $this->orderKey = 'name';
    $this->orderDir = 'ASC';
    $this->addTableHeader();
    $this->getDBParams($params);

    $list = Currency::newInstance()->adminSearch($this->start, $this->limit, $this->orderKey, $this->orderDir, $this->search);

    $this->processData($list['currencies']);
    $this->total = (int)$list['total'];
    $this->totalFiltered = (int)$list['total_results'];

    return $this->getData();
  }

  /**
   * @return bool
   */
  public function withFilters() {
    return $this->withFilters;
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
    $this->setDefaultSort('name', 'asc');

    $this->addSortColumn('name', 's_name', true);
    $this->addSortColumn('code', 'pk_c_code', true);
    $this->addSortColumn('symbol', 's_description', true);
    $this->addSortColumn('exchange_rate', 'd_exchange_rate', true);
    $this->addSortColumn('status', 'b_enabled', true);

    $this->addSourceColumn('status-border', '');
    $this->addSourceColumn('status', 'b_enabled');
    $this->addSourceColumn('name', 's_name');
    $this->addSourceColumn('code', 'pk_c_code');
    $this->addSourceColumn('symbol', 's_description');
    $this->addSourceColumn('exchange_rate', 'd_exchange_rate');

    $this->addColumn('status-border', '', 1);
    $this->addColumn('status', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('status', $sort, $direction)) . '">' . __('Status') . '</a>', 2);
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />', 3);
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Name') . '</a>', 4);
    $this->addColumn('code', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('code', $sort, $direction)) . '">' . __('Code') . '</a>', 5);
    $this->addColumn('symbol', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('symbol', $sort, $direction)) . '">' . __('Symbol') . '</a>', 6);
    $this->addColumn('exchange_rate', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('exchange_rate', $sort, $direction)) . '">' . __('Exchange rate') . '</a>', 7);

    $dummy = &$this;
    osc_run_hook('admin_currencies_table', $dummy);
  }

  /**
   * @param array $params
   */
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
    $this->withFilters = ($this->search != '');

    $sortData = $this->resolveSort($params, 'name', 'asc');
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);

    $this->orderKey = ($sortData['key'] != '' ? $sortData['key'] : 'name');
    $this->orderDir = strtoupper($sortData['direction']);
    if($this->orderDir != 'ASC' && $this->orderDir != 'DESC') {
      $this->orderDir = 'ASC';
    }
  }

  /**
   * @param array $rows
   */
  private function processData($rows) {
    if(empty($rows)) {
      return;
    }

    $def = trim((string)osc_currency());
    foreach($rows as $aRow) {
      $code = $aRow['pk_c_code'];
      $options = array();
      $options[] = '<a href="' . osc_admin_base_url(true) . '?page=currencies&amp;action=edit&amp;code=' . osc_esc_html($code) . '">' . __('Edit') . '</a>';
      $enabled = (isset($aRow['b_enabled']) && (int)$aRow['b_enabled'] === 1);
      if($enabled) {
        if($def == '' || $code != $def) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=currencies&amp;action=disable_selected&amp;code[]=' . osc_esc_html($code) . '&amp;' . osc_csrf_token_url() . '">' . __('Disable') . '</a>';
        }
      } else {
        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=currencies&amp;action=enable_selected&amp;code[]=' . osc_esc_html($code) . '&amp;' . osc_csrf_token_url() . '">' . __('Enable') . '</a>';
      }
      $options[] = '<a onclick="return delete_dialog(\'' . osc_esc_js($code) . '\');" href="' . osc_admin_base_url(true) . '?page=currencies&amp;action=delete&amp;code=' . osc_esc_html($code) . '">' . __('Delete') . '</a>';
      $actions = $this->buildRowActions($options, array(), 10, array());

      $row = array();
      $row['status-border'] = '';
      $row['status'] = $enabled ? __('Active') : __('Inactive');
      $row['bulkactions'] = '<input type="checkbox" name="code[]" value="' . osc_esc_html($code) . '" />';
      $nameHtml = osc_esc_html($aRow['s_name']);
      if($def != '' && $code == $def) {
        $nameHtml .= ' <span class="default-value">✓ ' . __('Default') . '</span>';
      }
      $row['name'] = $nameHtml . $actions;
      $row['code'] = osc_esc_html($code);
      $row['symbol'] = osc_esc_html(isset($aRow['s_description']) ? (string)$aRow['s_description'] : '');
      $er = (isset($aRow['d_exchange_rate']) && $aRow['d_exchange_rate'] !== null && $aRow['d_exchange_rate'] !== '') ? (string)$aRow['d_exchange_rate'] : '';
      if($er != '' && is_numeric($er)) {
        $row['exchange_rate'] = osc_esc_html(number_format((float)$er, 4, '.', ''));
      } else {
        $row['exchange_rate'] = '&mdash;';
      }

      $row = osc_apply_filter('currencies_processing_row', $row, $aRow);

      $this->addRow($row);
      $this->rawRows[] = $aRow;
    }
  }
}

/* file end: ./oc-includes/osclass/classes/datatables/CurrenciesDataTable.php */

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
 * LanguagesDataTable class
 *
 * @package Osclass
 * @subpackage classes
 */
class LanguagesDataTable extends DataTable {
  private $search;
  private $orderKey;
  private $orderDir;
  private $withFilters = false;

  public function __construct() {
    parent::__construct();
    osc_add_filter('datatable_languages_class', array(&$this, 'row_class'));
  }

  /**
   * @param array $class
   * @param array $rawRow
   * @param array $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
    $fo = (isset($rawRow['b_enabled']) && (int)$rawRow['b_enabled'] === 1);
    $class[] = ($fo ? 'status-active' : 'status-inactive');
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

    $list = OSCLocale::newInstance()->adminSearch($this->start, $this->limit, $this->orderKey, $this->orderDir, $this->search);

    $this->processData($list['locales'], json_decode(osc_get_preference('languages_to_update'), true));
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
    $this->addSortColumn('short_name', 's_short_name', true);
    $this->addSortColumn('description', 's_description', true);
    $this->addSortColumn('code', 'pk_c_code', true);
    $this->addSortColumn('enabled_fo', 'b_enabled', true);
    $this->addSortColumn('enabled_bo', 'b_enabled_bo', true);
    $this->addSortColumn('locations_native', 'b_locations_native', true);
    $this->addSortColumn('rtl', 'b_rtl', true);
    $this->addSortColumn('status', 'b_enabled', true);

    $this->addSourceColumn('status-border', '');
    $this->addSourceColumn('status', 'b_enabled');
    $this->addSourceColumn('bulkactions', '');
    $this->addSourceColumn('name', 's_name');
    $this->addSourceColumn('short_name', 's_short_name');
    $this->addSourceColumn('description', 's_description');
    $this->addSourceColumn('code', 'pk_c_code');
    $this->addSourceColumn('enabled_fo', 'b_enabled');
    $this->addSourceColumn('enabled_bo', 'b_enabled_bo');
    $this->addSourceColumn('locations_native', 'b_locations_native');
    $this->addSourceColumn('rtl', 'b_rtl');

    $this->addColumn('status-border', '', 1);
    $this->addColumn('status', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('status', $sort, $direction)) . '">' . __('Status') . '</a>', 1);
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />', 2);
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Name') . '</a>', 3);
    $this->addColumn('short_name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('short_name', $sort, $direction)) . '">' . __('Short name') . '</a>', 4);
    $this->addColumn('description', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('description', $sort, $direction)) . '">' . __('Description') . '</a>', 5);
    $this->addColumn('code', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('code', $sort, $direction)) . '">' . __('Code') . '</a>', 6);
    $this->addColumn('enabled_fo', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('enabled_fo', $sort, $direction)) . '">' . __('Front-office') . '</a>', 7);
    $this->addColumn('enabled_bo', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('enabled_bo', $sort, $direction)) . '">' . __('Back-office') . '</a>', 8);
    $this->addColumn('locations_native', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('locations_native', $sort, $direction)) . '">' . __('Native loc.') . '</a>', 9);
    $this->addColumn('rtl', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('rtl', $sort, $direction)) . '">' . __('Direction') . '</a>', 10);

    $dummy = &$this;
    osc_run_hook('admin_languages_table', $dummy);
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
   * @param mixed $aLanguagesToUpdate
   */
  private function processData($rows, $aLanguagesToUpdate) {
    if(empty($rows)) {
      return;
    }

    $bLanguagesToUpdate = is_array($aLanguagesToUpdate);
    $defLang = osc_language();

    foreach($rows as $l) {
      $code = $l['pk_c_code'];
      $options = array();
      $options[] = '<a href="' . osc_admin_base_url(true) . '?page=languages&amp;action=edit&amp;id=' . osc_esc_html($code) . '">' . __('Edit') . '</a>';
      $options[] = '<a href="' . osc_admin_base_url(true) . '?page=languages&amp;action=' . ($l['b_enabled'] == 1 ? 'disable_selected' : 'enable_selected') . '&amp;id[]=' . osc_esc_html($code) . '&amp;' . osc_csrf_token_url() . '">' . ($l['b_enabled'] == 1 ? __('Disable in Front-office') : __('Enable in Front-office')) . '</a> ';
      $options[] = '<a href="' . osc_admin_base_url(true) . '?page=languages&amp;action=' . ($l['b_enabled_bo'] == 1 ? 'disable_bo_selected' : 'enable_bo_selected') . '&amp;id[]=' . osc_esc_html($code) . '&amp;' . osc_csrf_token_url() . '">' . ($l['b_enabled_bo'] == 1 ? __('Disable in Back-office') : __('Enable in Back-office')) . '</a>';
      $options[] = '<a onclick="return delete_dialog(\'' . osc_esc_js($code) . '\');" href="' . osc_admin_base_url(true) . '?page=languages&amp;action=delete&amp;id[]=' . osc_esc_html($code) . '&amp;' . osc_csrf_token_url() . '">' . __('Delete') . '</a>';
      $actions = $this->buildRowActions($options, array(), 8, array());

      $sUpdate = '';
      if($bLanguagesToUpdate && is_array($aLanguagesToUpdate) && in_array($code, $aLanguagesToUpdate)) {
        $sUpdate = '<a class="btn-market-update btn-market-popup btn-lang-update btn" href="#' . htmlentities($code) . '">' . __('Update') . '</a>';
      }

      $nameHtml = osc_esc_html($l['s_name']);
      if($defLang == $code) {
        $nameHtml .= ' <span class="default-value">✓ ' . __('Default') . '</span>';
      }

      $foOn = ((int)$l['b_enabled'] === 1);
      $boOn = ((int)$l['b_enabled_bo'] === 1);
      $natOn = ((int)$l['b_locations_native'] === 1);
      $rtlOn = ((int)$l['b_rtl'] === 1);

      $row = array();
      $row['status-border'] = '';
      $row['status'] = ($foOn ? __('Active') : __('Inactive'));
      $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . osc_esc_html($code) . '" />';
      $row['name'] = $nameHtml . $sUpdate . $actions;
      $row['short_name'] = osc_esc_html($l['s_short_name']);
      $row['description'] = osc_esc_html($l['s_description']);
      $row['code'] = osc_esc_html($code);
      $row['enabled_fo'] = ($foOn ? '<span class="enabled-value">✓ ' . __('Enabled') . '</span>' : '<span class="disabled-value">✗ ' . __('Disabled') . '</span>');
      $row['enabled_bo'] = ($boOn ? '<span class="enabled-value">✓ ' . __('Enabled') . '</span>' : '<span class="disabled-value">✗ ' . __('Disabled') . '</span>');
      $row['locations_native'] = ($natOn ? '<span class="enabled-value">✓ ' . __('Enabled') . '</span>' : '<span class="disabled-value">✗ ' . __('Disabled') . '</span>');
      if($rtlOn) {
        $row['rtl'] = '<span class="standard-value" title="' . osc_esc_html(__('Right to left')) . '">🠈 RTL</span>';
      } else {
        $row['rtl'] = '<span class="standard-value" title="' . osc_esc_html(__('Left to right')) . '">LTR 🠊</span>';
      }

      $row = osc_apply_filter('languages_processing_row', $row, $l);

      $this->addRow($row);
      $this->rawRows[] = $l;
    }
  }
}

/* file end: ./oc-includes/osclass/classes/datatables/LanguagesDataTable.php */

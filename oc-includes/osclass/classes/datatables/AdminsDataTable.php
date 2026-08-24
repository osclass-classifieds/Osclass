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
 * AdminsDataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class AdminsDataTable extends DataTable {
  private $order_by;
  private $conditions;
  private $withFilters = false;

  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {
    $this->conditions = array();
    $this->addTableHeader();
    $this->getDBParams($params);

    $list_admins = Admin::newInstance()->search($this->start, $this->limit, $this->order_by['column_name'], $this->order_by['type'], $this->conditions);

    $this->processData($list_admins['admins']);
    $this->total = $list_admins['rows'];
    $this->totalFiltered = $list_admins['total_results'];

    return $this->getData();
  }

  private function addTableHeader() {
    Rewrite::newInstance()->init();
    $page = (int)Params::getParam('iPage');
    if($page == 0) { $page = 1; }
    Params::setParam('iPage', $page);

    $url_base = preg_replace('|&direction=([^&]*)|', '', preg_replace('|&sort=([^&]*)|', '', osc_base_url() . Rewrite::newInstance()->get_raw_request_uri()));
    $sort = Params::getParam('sort');
    $direction = Params::getParam('direction');

    $this->clearSortColumns();
    $this->clearSourceColumns();
    $this->setDefaultSort('username', 'asc');

    $this->addSortColumn('username', 's_username', true);
    $this->addSortColumn('name', 's_name', true);
    $this->addSortColumn('email', 's_email', true);
    $this->addSortColumn('type', 'b_moderator', true);

    $this->addSourceColumn('username', 's_username');
    $this->addSourceColumn('name', 's_name');
    $this->addSourceColumn('email', 's_email');
    $this->addSourceColumn('type', 'b_moderator');

    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('username', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('username', $sort, $direction)) . '">' . __('Username') . '</a>');
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Name') . '</a>');
    $this->addColumn('email', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('email', $sort, $direction)) . '">' . __('E-mail') . '</a>');
    $this->addColumn('type', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('type', $sort, $direction)) . '">' . __('Type') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_admins_table', $dummy);
  }

  /**
   * @param $admins
   */
  private function processData($admins) {
    if(!empty($admins)) {
      foreach($admins as $aRow) {
        $row = array();
        $options = array();
        $options_more = array();

        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=admins&action=edit&amp;id=' . $aRow['pk_i_id'] . '">' . __('Edit') . '</a>';
        $options[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=admins&action=delete&amp;id[]=' . $aRow['pk_i_id'] . '">' . __('Delete') . '</a>';
        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=tools&action=logs&logWho=admin&whoId=' . (int)$aRow['pk_i_id'] . '">' . __('View action logs') . '</a>';

        $options_more = osc_apply_filter('more_actions_manage_admins', $options_more, $aRow);
        $options = osc_apply_filter('actions_manage_admins', $options, $aRow);
        $actions = $this->buildRowActions($options, $options_more, 8);

        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" /></div>';
        $row['username'] = osc_esc_html($aRow['s_username']) . $actions;
        $row['name'] = osc_esc_html($aRow['s_name']);
        $row['email'] = osc_esc_html($aRow['s_email']);

        $is_moderator = (isset($aRow['b_moderator']) && (int)$aRow['b_moderator'] == 1);
        $type_label = ($is_moderator ? __('Moderator') : __('Administrator'));
        $type_value = ($is_moderator ? 1 : 0);
        $row['type'] = '<a href="' . osc_admin_base_url(true) . '?page=admins&b_moderator=' . $type_value . '">' . $type_label . '</a>';

        $row = osc_apply_filter('admins_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }
    }
  }

  /**
   * @param array $params
   */
  private function getDBParams($params) {
    if(!isset($params['iDisplayStart'])) {
      $params['iDisplayStart'] = 0;
    }

    $p_iPage = 1;

    if(!is_numeric(Params::getParam('iPage')) || Params::getParam('iPage') < 1) {
      Params::setParam('iPage', $p_iPage);
      $this->iPage = $p_iPage;
    } else {
      $this->iPage = Params::getParam('iPage');
    }

    $sortData = $this->resolveSort($params);
    $this->order_by['column_name'] = ($sortData['column'] != '' ? $sortData['column'] : 's_username');
    $this->order_by['type'] = strtoupper($sortData['direction']);
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);

    if(@$params['s_username'] != '') {
      if(substr($params['s_username'], 0, 1) == '"' || substr($params['s_username'], -1) == '"') {
        $esc_username = Admin::newInstance()->dao->escapeStr(str_replace('*','%', str_replace('"','', $params['s_username'])));
        $this->conditions['s_username'] = $esc_username;
      } else {
        $esc_username = Admin::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $params['s_username'] . '%')));
        $this->conditions["s_username LIKE '" . $esc_username . "'"] = null;
      }

      $this->withFilters = true;
    }

    if(@$params['s_name'] != '') {
      if(substr($params['s_name'], 0, 1) == '"' || substr($params['s_name'], -1) == '"') {
        $esc_name = Admin::newInstance()->dao->escapeStr(str_replace('*','%', str_replace('"','', $params['s_name'])));
        $this->conditions['s_name'] = $esc_name;
      } else {
        $esc_name = Admin::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $params['s_name'] . '%')));
        $this->conditions["s_name LIKE '" . $esc_name . "'"] = null;
      }

      $this->withFilters = true;
    }

    if(@$params['s_email'] != '') {
      if(substr($params['s_email'], 0, 1) == '"' || substr($params['s_email'], -1) == '"') {
        $esc_email = Admin::newInstance()->dao->escapeStr(str_replace('*','%', str_replace('"','', $params['s_email'])));
        $this->conditions['s_email'] = $esc_email;
      } else {
        $esc_email = Admin::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $params['s_email'] . '%')));
        $this->conditions["s_email LIKE '" . $esc_email . "'"] = null;
      }

      $this->withFilters = true;
    }

    if(@$params['b_moderator'] !== '' && @$params['b_moderator'] !== null) {
      $this->conditions['b_moderator'] = (int)$params['b_moderator'];
      $this->withFilters = true;
    }

    if(@$params['sSearch'] != '') {
      $search = trim((string)$params['sSearch']);
      $esc_search = Admin::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $search . '%')));
      $search_sql = "(s_username LIKE '" . $esc_search . "' OR s_name LIKE '" . $esc_search . "' OR s_email LIKE '" . $esc_search . "'";
      $search_lc = strtolower($search);

      if($search_lc == strtolower(__('Moderator'))) {
        $search_sql .= " OR b_moderator = 1";
      } else if($search_lc == strtolower(__('Administrator'))) {
        $search_sql .= " OR b_moderator = 0";
      }

      $search_sql .= ")";
      $this->conditions[$search_sql] = null;
      $this->withFilters = true;
    }

    $start = ($this->iPage - 1) * $params['iDisplayLength'];

    $this->start = (int)$start;
    $this->limit = (int)$params['iDisplayLength'];
  }

  /**
   * @return bool
   */
  public function withFilters() {
    return $this->withFilters;
  }
}

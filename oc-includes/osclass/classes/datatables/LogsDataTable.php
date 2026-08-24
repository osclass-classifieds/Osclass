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
 * LogsDataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class LogsDataTable extends DataTable {
  private $order_by;
  private $keyword;
  private $logId;
  private $logSection;
  private $logAction;
  private $logIp;
  private $logWho;
  private $whoId;
  private $logDate;
  private $withFilters = false;

  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {

    $this->addTableHeader();
    $this->getDBParams($params);

    $list_logs = Log::newInstance()->search(array(
      'start' => $this->start,
      'limit' => $this->limit,
      'order_column' => $this->order_by['column_name'],
      'order_direction' => $this->order_by['type'],
      'id' => $this->logId,
      'section' => $this->logSection,
      'action' => $this->logAction,
      'ip' => $this->logIp,
      'who' => $this->logWho,
      'who_id' => $this->whoId,
      'date' => $this->logDate,
      'keyword' => $this->keyword
    ));

    $this->processData($list_logs['logs']);
    $this->total = $list_logs['rows'];
    $this->totalFiltered = $list_logs['total_results'];

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
    $this->setDefaultSort('date', 'desc');
    // List of sortable columns in datatable
    $this->addSortColumn('section', 's_section', true);
    $this->addSortColumn('action', 's_action', true);
    $this->addSortColumn('id', 'fk_i_id', true);
    $this->addSortColumn('data', 's_data', true);
    $this->addSortColumn('comment', 's_comment', true);
    $this->addSortColumn('ip', 's_ip', true);
    $this->addSortColumn('who', 's_who', true);
    $this->addSortColumn('date', 'dt_date');

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('section', 's_section');
    $this->addSourceColumn('action', 's_action');
    $this->addSourceColumn('id', 'fk_i_id');
    $this->addSourceColumn('data', 's_data');
    $this->addSourceColumn('comment', 's_comment');
    $this->addSourceColumn('ip', 's_ip');
    $this->addSourceColumn('who', 's_who');
    $this->addSourceColumn('date', 'dt_date');

    // Table header columns rendered in admin
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('section', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('section', $sort, $direction)) . '">' . __('Section') . '</a>');
    $this->addColumn('action', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('action', $sort, $direction)) . '">' . __('Action') . '</a>');
    $this->addColumn('id', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('id', $sort, $direction)) . '">' . __('ID') . '</a>');
    $this->addColumn('data', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('data', $sort, $direction)) . '">' . __('Data') . '</a>');
    $this->addColumn('comment', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('comment', $sort, $direction)) . '">' . __('Comment') . '</a>');
    $this->addColumn('ip', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('ip', $sort, $direction)) . '">' . __('IP') . '</a>');
    $this->addColumn('who', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('who', $sort, $direction)) . '">' . __('Who') . '</a>');
    $this->addColumn('date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('date', $sort, $direction)) . '">' . __('Date') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_logs_table', $dummy);
  }

  private function buildFilterUrl($params) {
    $url = osc_admin_base_url(true) . '?page=tools&action=logs';

    foreach($params as $k => $v) {
      if($v !== '' && $v !== null) {
        $url .= '&' . $k . '=' . rawurlencode($v);
      }
    }

    return $url;
  }

  /**
   * @param $rules
   */
  private function processData($rules) {
    if(!empty($rules)) {
      $csrf_token_url = osc_csrf_token_url();
      $row_index = 0;

      foreach($rules as $aRow) {
        $row_data = array(
          'dt_date' => (string)$aRow['dt_date'],
          's_section' => (string)$aRow['s_section'],
          's_action' => (string)$aRow['s_action'],
          'fk_i_id' => (int)$aRow['fk_i_id'],
          's_data' => (string)$aRow['s_data'],
          's_detail' => ($aRow['s_detail'] !== null ? (string)$aRow['s_detail'] : null),
          's_comment' => ($aRow['s_comment'] !== null ? (string)$aRow['s_comment'] : null),
          's_ip' => (string)$aRow['s_ip'],
          's_who' => (string)$aRow['s_who'],
          'fk_i_who_id' => (int)$aRow['fk_i_who_id']
        );
        $delete_token = rawurlencode(base64_encode(json_encode($row_data)));
        $detail_id = 'log-' . md5($delete_token . '|' . $row_index);
        $row_index++;

        $row = array();
        $options = array();
        $options_more = array();

        $options[] = '<a onclick="return delete_dialog(\'' . $delete_token . '\');" href="' . osc_admin_base_url(true) . '?page=tools&action=logs_delete&amp;' . $csrf_token_url . '&amp;id[]=' . $delete_token . '">' . __('Delete') . '</a>';
        $options[] = '<a onclick="return show_hide_log_details(this, \'' . $detail_id . '\');" href="#" data-detail-id="' . $detail_id . '" data-show="' . osc_esc_html(__('Show details')) . '" data-hide="' . osc_esc_html(__('Hide details')) . '">' . __('Show details') . '</a>';
        if((int)$aRow['fk_i_id'] > 0) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=tools&action=logs&logId=' . (int)$aRow['fk_i_id'] . '">' . __('View logs with same ID') . '</a>';
        }

        $options_more = osc_apply_filter('more_actions_manage_logs', $options_more, $aRow);
        $options = osc_apply_filter('actions_manage_logs', $options, $aRow);
        $actions = $this->buildRowActions($options, $options_more, 8);
        $details = '<div id="details-' . $detail_id . '" class="log-details" style="display:none;"><code>'. ($aRow['s_detail'] <> '' ? $aRow['s_detail'] : '- ' . __('No details found') . ' -') .'</code></div>'.PHP_EOL;

        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $delete_token . '" /></div>';

        $section_value = ($aRow['s_section'] <> '' ? $aRow['s_section'] : '-');
        if($section_value != '-') {
          $row['section'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('logSection' => $section_value))) . '">' . osc_esc_html($section_value) . '</a>';
        } else {
          $row['section'] = $section_value;
        }
        $row['section'] .= $actions;

        $action_value = ($aRow['s_action'] <> '' ? $aRow['s_action'] : '-');
        if($action_value != '-') {
          $row['action'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('logAction' => $action_value))) . '">' . osc_esc_html($action_value) . '</a>';
        } else {
          $row['action'] = $action_value;
        }

        $section_type = ($aRow['s_section'] <> '' ? $aRow['s_section'] : '-');
        $section_id = (int)($aRow['fk_i_id'] <> '' ? $aRow['fk_i_id'] : 0);
        $id_value = ($section_id > 0 ? $section_id : '-');

        if($id_value != '-') {
          $row['id'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('logId' => $section_id))) . '">' . $section_id . '</a>';
        } else {
          $row['id'] = $id_value;
        }

        if($section_type === 'user' && $section_id > 0) {
          $user = osc_get_user_row($section_id);

          if($user !== false && isset($user['pk_i_id'])) {
            $name = $user['s_name'];
            $url = osc_admin_base_url(true) . '?page=users&action=edit&id=' . $section_id;

            $row['id'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('logId' => $section_id))) . '">' . $section_id . '</a> (<a href="' . osc_esc_html($url) . '" target="_blank">' . osc_esc_html($name) . '</a>)';
          }

        } else if($section_type === 'item' && $section_id > 0) {
          $item = osc_get_item_row($section_id);

          if($item !== false && isset($item['pk_i_id'])) {
            $title = osc_highlight($item['s_title'], 40);
            $url = osc_admin_base_url(true) . '?page=items&action=item_edit&id=' . $section_id;

            $row['id'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('logId' => $section_id))) . '">' . $section_id . '</a> (<a href="' . osc_esc_html($url) . '" target="_blank">' . $title . '</a>)';
          }
        } else if($section_type === 'report' && $section_id > 0) {
          $url = osc_admin_base_url(true) . '?page=reports&action=edit&id=' . $section_id;
          $row['id'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('logId' => $section_id))) . '">' . $section_id . '</a> (<a href="' . osc_esc_html($url) . '" target="_blank">#' . $section_id . '</a>)';
        }

        $row['data'] = ($aRow['s_data'] <> '' ? $aRow['s_data'] : '-');
        $row['data'] .= $details;

        $row['comment'] = ($aRow['s_comment'] <> '' ? $aRow['s_comment'] : '-');

        $ip_value = ($aRow['s_ip'] <> '' ? $aRow['s_ip'] : '-');
        if($ip_value != '-') {
          $row['ip'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('logIp' => $ip_value))) . '">' . osc_esc_html($ip_value) . '</a>';
        } else {
          $row['ip'] = $ip_value;
        }

        $who_type = ($aRow['s_who'] ?: '-');
        $who_id = (int)($aRow['fk_i_who_id'] ?? 0);

        if($who_type != '-') {
          $who_params = array('logWho' => $who_type);
          if($who_id > 0) {
            $who_params['whoId'] = $who_id;
          }
          $row['who'] = '<a href="' . osc_esc_html($this->buildFilterUrl($who_params)) . '">' . osc_esc_html($who_type) . '</a>';
        } else {
          $row['who'] = $who_type;
        }

        if($who_type === 'user' && $who_id > 0) {
          $user = osc_get_user_row($who_id);

          if($user !== false && isset($user['pk_i_id'])) {
            $name = $user['s_name'];
            $url = osc_admin_base_url(true) . '?page=users&action=edit&id=' . $who_id;
            $who_params = array('logWho' => $who_type, 'whoId' => $who_id);

            $row['who'] = '<a href="' . osc_esc_html($this->buildFilterUrl($who_params)) . '">' . osc_esc_html($who_type) . '</a> (<a href="' . osc_esc_html($url) . '" target="_blank">' . osc_esc_html($name) . '</a>)';
          }
        } else if($who_type === 'admin' && $who_id > 0) {
          $admin = Admin::newInstance()->findByPrimaryKey($who_id);

          if($admin !== false && isset($admin['pk_i_id'])) {
            $who_params = array('logWho' => $who_type, 'whoId' => $who_id);

            $row['who'] = '<a href="' . osc_esc_html($this->buildFilterUrl($who_params)) . '">' . osc_esc_html($admin['s_username']) . '</a> (' . osc_esc_html($admin['s_name']) . ')';
          }
        }

        $log_date = ($aRow['dt_date'] <> '' ? $aRow['dt_date'] : '');
        $log_date_only = ($log_date != '' ? date('Y-m-d', strtotime($log_date)) : '');
        if($log_date_only != '') {
          $row['date'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('logDate' => $log_date_only))) . '" title="' . osc_esc_html($log_date) . '">' . osc_format_date($log_date) . '</a>';
        } else {
          $row['date'] = '-';
        }

        $row = osc_apply_filter('rules_processing_row', $row, $aRow);

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
    $this->order_by['column_name'] = ($sortData['column'] != '' ? $sortData['column'] : 'dt_date');
    $this->order_by['type'] = strtoupper($sortData['direction']);
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);
    // set start and limit using iPage param
    $start = ($this->iPage - 1) * $params['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int) $params['iDisplayLength'];

    $this->keyword = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');
    $this->logId = null;
    $this->logSection = '';
    $this->logAction = '';
    $this->logIp = '';
    $this->logWho = '';
    $this->whoId = null;
    $this->logDate = '';

    if(isset($params['logId']) && $params['logId'] !== '' && is_numeric($params['logId'])) {
      $logId = (int)$params['logId'];

      if($logId >= 0) {
        $this->logId = $logId;
        $this->withFilters = true;
      }
    }

    if(isset($params['logSection']) && trim((string)$params['logSection']) != '') {
      $this->logSection = trim((string)$params['logSection']);
      $this->withFilters = true;
    }

    if(isset($params['logAction']) && trim((string)$params['logAction']) != '') {
      $this->logAction = trim((string)$params['logAction']);
      $this->withFilters = true;
    }

    if(isset($params['logIp']) && trim((string)$params['logIp']) != '') {
      $this->logIp = trim((string)$params['logIp']);
      $this->withFilters = true;
    }

    if(isset($params['logWho']) && trim((string)$params['logWho']) != '') {
      $this->logWho = trim((string)$params['logWho']);
      $this->withFilters = true;
    }

    if(isset($params['whoId']) && $params['whoId'] !== '' && is_numeric($params['whoId'])) {
      $whoId = (int)$params['whoId'];

      if($whoId >= 0) {
        $this->whoId = $whoId;
        $this->withFilters = true;
      }
    }

    if(isset($params['logDate']) && trim((string)$params['logDate']) != '') {
      $this->logDate = trim((string)$params['logDate']);
      $this->withFilters = true;
    }

    if($this->keyword != '') {
      $this->withFilters = true;
    }
  }

  /**
   * @return bool
   */
  public function withFilters() {
    return $this->withFilters;
  }
}

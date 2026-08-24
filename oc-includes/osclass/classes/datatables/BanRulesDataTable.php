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
 * BanRulesDataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class BanRulesDataTable extends DataTable {

  private $order_by;
  private $keyword;
  private $emailLike;
  private $ipLike;


  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {

    $this->addTableHeader();
    $this->getDBParams($params);

    $list_rules  = BanRule::newInstance()->search($this->start, $this->limit, $this->order_by['column_name'], $this->order_by['type'], '', $this->keyword, null, $this->emailLike, $this->ipLike);

    $this->processData($list_rules['rules']);
    $this->total = $list_rules['rows'];
    $this->totalFiltered = $list_rules['total_results'];

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
    $this->setDefaultSort('cdate', 'desc');
    // List of sortable columns in datatable
    $this->addSortColumn('name', 's_name', true);
    $this->addSortColumn('ip', 's_ip', true);
    $this->addSortColumn('email', 's_email', true);
    $this->addSortColumn('hit', 'i_hit', true);
    $this->addSortColumn('expire_date', 'dt_expire_date');
    $this->addSortColumn('cdate', 'dt_date');

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('name', 's_name');
    $this->addSourceColumn('ip', 's_ip');
    $this->addSourceColumn('email', 's_email');
    $this->addSourceColumn('hit', 'i_hit');
    $this->addSourceColumn('expire_date', 'dt_expire_date');
    $this->addSourceColumn('cdate', 'dt_date');

    // Table header columns rendered in admin
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Ban name / Reason') . '</a>');
    $this->addColumn('ip', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('ip', $sort, $direction)) . '">' . __('IP rule') . '</a>');
    $this->addColumn('email', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('email', $sort, $direction)) . '">' . __('E-mail rule') . '</a>');
    $this->addColumn('hit', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('hit', $sort, $direction)) . '">' . __('Hits') . '</a>');
    $this->addColumn('expire_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('expire_date', $sort, $direction)) . '">' . __('Expire Date') . '</a>');
    $this->addColumn('cdate', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('cdate', $sort, $direction)) . '">' . __('Create Date') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_rules_table', $dummy);
  }

  /**
   * @param $rules
   */
  private function processData($rules) {
    if(!empty($rules)) {
      $csrf_token_url = osc_csrf_token_url();
      foreach($rules as $aRow) {
        $row = array();
        $options = array();
        $options_more = array();
        // first column

        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=edit_ban_rule&amp;id=' . $aRow['pk_i_id'] . '">' . __('Edit') . '</a>';
        $options[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=users&action=delete_ban_rule&amp;id[]=' . $aRow['pk_i_id'] . '">' . __('Delete') . '</a>';

        $emailRule = trim((string)$aRow['s_email']);
        $ipRule = trim((string)$aRow['s_ip']);

        $emailFilterUrl = '';
        if($emailRule != '' && strpos($emailRule, '@') !== false) {
          $domain = strtolower(trim(substr($emailRule, strrpos($emailRule, '@') + 1)));
          $domain = ltrim($domain, '%.*');

          if($domain != '') {
            $emailFilterUrl = osc_admin_base_url(true) . '?page=users&action=ban&emailLike=' . rawurlencode('%' . $domain);
          }
        }

        $ipFilterUrl = '';
        if($ipRule != '') {
          $ipPrefix = trim(str_replace('*', '', $ipRule));

          if(preg_match('/^\d{1,3}(?:\.\d{1,3}){0,3}/', $ipPrefix, $match)) {
            $segments = explode('.', $match[0]);
            if(count($segments) > 1) {
              array_pop($segments);
            }

            $prefixBase = implode('.', $segments);
            if($prefixBase != '') {
              $prefixPattern = $prefixBase . '.';
              $ipFilterUrl = osc_admin_base_url(true) . '?page=users&action=ban&ipLike=' . rawurlencode($prefixPattern);
            }
          }
        }

        $options_more = osc_apply_filter('more_actions_manage_rules', $options_more, $aRow);

        $options = osc_apply_filter('actions_manage_rules', $options, $aRow);
        $actions = $this->buildRowActions($options, $options_more, 8);

        $row['id'] = $aRow['pk_i_id'];
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" /></div>';
        $row['name'] = $aRow['s_name'];
        $row['name'] .= $actions;

        if($aRow['s_ip'] <> '') {
          $row['ip'] = ($ipFilterUrl != '' ? '<a href="' . osc_esc_html($ipFilterUrl) . '">' . osc_esc_html($aRow['s_ip']) . '</a>' : osc_esc_html($aRow['s_ip']));
        } else {
          $row['ip'] = '-';
        }

        if($aRow['s_email'] <> '') {
          $row['email'] = ($emailFilterUrl != '' ? '<a href="' . osc_esc_html($emailFilterUrl) . '">' . osc_esc_html($aRow['s_email']) . '</a>' : osc_esc_html($aRow['s_email']));
        } else {
          $row['email'] = '-';
        }
        $row['hit'] = ($aRow['i_hit'] > 0 ? $aRow['i_hit'] : 0) . 'x';
        $row['expire_date'] = ($aRow['dt_expire_date'] <> '' ? $aRow['dt_expire_date'] : __('Never'));
        $row['cdate'] = '<span title="' . osc_esc_html($aRow['dt_date']) . '">' . ($aRow['dt_date'] <> '' ? osc_format_date($aRow['dt_date']) : '-') . '</span>';

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
    $this->limit = (int) $params[ 'iDisplayLength' ];

    $this->keyword = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');

    $this->emailLike = '';
    if(isset($params['emailLike']) && trim((string)$params['emailLike']) != '') {
      $this->emailLike = trim((string)$params['emailLike']);
    }

    $this->ipLike = '';
    if(isset($params['ipLike']) && trim((string)$params['ipLike']) != '') {
      $this->ipLike = trim((string)$params['ipLike']);
    }
  }
}

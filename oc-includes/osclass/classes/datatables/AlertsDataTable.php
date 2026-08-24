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
 * AlertsDataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class AlertsDataTable extends DataTable {

  private $search;
  private $order_by;
  private $alertUserId;
  private $alertEmail;
  public function __construct() {
    parent::__construct();
    osc_add_filter('datatable_alert_class', array(&$this, 'row_class'));
  }

  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {
    $this->addTableHeader();
    $this->getDBParams($params);

    $alerts = Alerts::newInstance()->search($this->start, $this->limit, $this->order_by['column_name'], $this->order_by['type'], $this->search, $this->alertUserId, $this->alertEmail);
    $this->processData($alerts);
    $this->total = $alerts['rows'];
    $this->totalFiltered = $alerts['total_results'];

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
    $this->setDefaultSort('create_date', 'desc');
    // List of sortable columns in datatable
    $this->addSortColumn('email', 's_email', true);
    $this->addSortColumn('name', 's_name', true);
    $this->addSortColumn('trigger', 'i_num_trigger', true);
    $this->addSortColumn('create_date', 'dt_date');
    $this->addSortColumn('expire_date', 'dt_expire_date');
    $this->addSortColumn('unsub_date', 'dt_unsub_date');

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('email', 's_email|fk_i_user_id');
    $this->addSourceColumn('name', 's_name');
    $this->addSourceColumn('trigger', 'i_num_trigger');
    $this->addSourceColumn('create_date', 'dt_date');
    $this->addSourceColumn('expire_date', 'dt_expire_date');
    $this->addSourceColumn('unsub_date', 'dt_unsub_date');

    // Table header columns rendered in admin
    $this->addColumn('status-border', '');
    $this->addColumn('status', __('Status'));
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox"/>');
    $this->addColumn('email', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('email', $sort, $direction)) . '">' . __('User') . '</a>');
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Name') . '</a>');
    $this->addColumn('alert', __('Details'));
    $this->addColumn('trigger', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('trigger', $sort, $direction)) . '">' . __('Triggered') . '</a>');
    $this->addColumn('create_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('create_date', $sort, $direction)) . '">' . __('Subscribe date') . '</a>');
    $this->addColumn('expire_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('expire_date', $sort, $direction)) . '">' . __('Expire date') . '</a>');
    $this->addColumn('unsub_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('unsub_date', $sort, $direction)) . '">' . __('Unsubscribe date') . '</a>');

    $dummy = &$this;
    osc_run_hook( 'admin_alerts_table' , $dummy);
  }

  /**
   * @param $alerts
   */
  private function processData($alerts) {
    if(!empty($alerts) && !empty($alerts['alerts'])) {

      $csrf_token_url = osc_csrf_token_url();
      foreach($alerts['alerts'] as $aRow) {
        $row = array();
        $options = array();
        $user = null;
        $userEmail = trim((string)$aRow['s_email']);

        if((int)$aRow['fk_i_user_id'] > 0) {
          $user = User::newInstance()->findByPrimaryKey((int)$aRow['fk_i_user_id']);
        }

        if((!is_array($user) || !isset($user['pk_i_id']) || (int)$user['pk_i_id'] <= 0) && $userEmail != '') {
          $user = User::newInstance()->findByEmail($userEmail);
        }

        if(is_array($user) && isset($user['s_email']) && trim((string)$user['s_email']) != '') {
          $userEmail = trim((string)$user['s_email']);
        }

        $row['id'] = $aRow['pk_i_id'];
        $expireRaw = (isset($aRow['dt_expire_date']) ? trim((string)$aRow['dt_expire_date']) : '');
        $isUnsubscribed = (isset($aRow['dt_unsub_date']) && trim((string)$aRow['dt_unsub_date']) != '');
        $isExpired = ($expireRaw != '' && strtotime($expireRaw) <= time());
        $isActive = (!$isUnsubscribed && !$isExpired && (int)$aRow['b_active'] == 1);
        $row['status-border'] = '';
        if($isUnsubscribed) {
          $row['status'] = __('Unsubscribed');
        } else if($isExpired) {
          $row['status'] = __('Expired');
        } else {
          $row['status'] = ($aRow['b_active'] == 1 ? __('Active') : __('Inactive'));
        }
        $row['bulkactions'] = '<input type="checkbox" name="alert_id[]" value="' . $aRow['pk_i_id'] . '" /></div>';

        $options[] = '<a onclick="return delete_alert(\'' . $aRow['pk_i_id'] . '\');" href="#">' . __('Delete') . '</a>';

        if($aRow['b_active'] == 1 ) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=status_alerts&amp;alert_id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;status=0" >' . __('Deactivate') . '</a>';
        } else {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=status_alerts&amp;alert_id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;status=1" >' . __('Activate') . '</a>';
        }

        if(is_array($user) && isset($user['pk_i_id']) && (int)$user['pk_i_id'] > 0) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=edit&amp;id=' . (int)$user['pk_i_id'] . '">' . __('Edit user') . '</a>';
        }

        if(!$isUnsubscribed && $isExpired) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=status_alerts&amp;alert_id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;alert_action=renew">' . __('Renew') . '</a>';
        }

        if($isActive) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=status_alerts&amp;alert_id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;alert_action=expire">' . __('Expire') . '</a>';
        }

        $options[] = '<a href="#" class="alert-popup" data-id="' . osc_esc_html($aRow['pk_i_id']) . '" data-secret="' . osc_esc_html($aRow['s_secret']) . '" data-conditions="' . osc_esc_html($aRow['s_search']) . '" data-params="' . osc_esc_html($aRow['s_param']) . '" data-sql="' . osc_esc_html($aRow['s_sql']) . '">' . __('Details') . '</a>';
        $options[] = '<a href="' . osc_search_alert_url($aRow['pk_i_id'], $aRow['s_secret']) . '" target="_blank">' . __('View in search') . '</a>';

        $options = osc_apply_filter('actions_manage_alerts', $options, $aRow);
        $actions = $this->buildRowActions($options, array(), 8);

        $row['name'] = ($aRow['s_name'] <> '' ? $aRow['s_name'] : '-');
        if(is_array($user) && isset($user['pk_i_id']) && (int)$user['pk_i_id'] > 0 && $userEmail != '') {
          $displayName = trim((string)$user['s_name']);
          if($displayName == '') {
            $displayName = $userEmail;
          }
          $row['email'] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=alerts&alertEmail=' . rawurlencode($userEmail) . '">' . osc_esc_html($displayName) . '</a>' . $actions;
        } else {
          $row['email'] = ($aRow['s_email'] != '' ? osc_esc_html($aRow['s_email']) : '-') . $actions;
        }


        $pieces = array();
        $conditions = osc_get_raw_search((array)json_decode($aRow['s_search'], true));

        if(isset($conditions['sPattern']) && $conditions['sPattern']!='') {
          $pieces[] = sprintf( __( '<b>Pattern:</b> %s' ), $conditions['sPattern']);
        }

        if(isset($conditions['aCategories']) && !empty($conditions['aCategories'])) {
          $l = min(count($conditions['aCategories']), 4);
          $cat_array = array();
          for($c=0;$c<$l;$c++) {
            $cat_array[] = $conditions['aCategories'][$c];
          }
          if(count($conditions['aCategories'])>$l) {
            $cat_array[] = '<a href="#" class="more-tooltip" categories="'.osc_esc_html(implode( ', ' , $conditions['aCategories'])) . '" >' . __( '...More' ) . '</a>';
          }

          $pieces[] = sprintf( __( '<b>Categories:</b> %s' ), implode(', ', $cat_array));
        }

        $details = osc_generate_alert_name($aRow['s_search'], 1000, true);

        if(strlen($details) > 240) {
          $row['alert'] = substr($details, 0, 200);
          $row['alert'] .= '<a href="#" class="more-tooltip" details="' . osc_esc_html($details) . '">' . __( '...More' ) . '</a>';

        } else {
          $row['alert'] = $details;
        }

        $triggerCount = (int)$aRow['i_num_trigger'];
        if($triggerCount == 1) {
          $row['trigger'] = __('1 email alert sent');
        } else {
          $row['trigger'] = sprintf(__('%d email alerts sent'), $triggerCount);
        }
        $row['create_date'] = osc_format_date($aRow['dt_date']);
        $row['expire_date'] = ($aRow['dt_expire_date'] <> '' ? osc_format_date($aRow['dt_expire_date']) : '-');
        $row['unsub_date'] = ($aRow['dt_unsub_date'] <> '' ? osc_format_date($aRow['dt_unsub_date']) : '-');

        $row = osc_apply_filter('alerts_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }

    }
  }

  /**
   * @param array $params
   */
  private function getDBParams($params) {
    $list = $this->resolveListParams($params, array(
      'default_sort_key' => 'create_date',
      'default_sort_dir' => 'desc'
    ));

    $this->iPage = $list['iPage'];
    $this->start = $list['start'];
    $this->limit = $list['limit'];
    $this->search = $list['search'];

    $this->order_by['column_name'] = ($list['sort']['column'] != '' ? $list['sort']['column'] : 'dt_date');
    $this->order_by['type'] = strtoupper($list['sort']['direction']);

    $this->alertUserId = null;
    if(isset($params['alertUserId']) && $params['alertUserId'] !== '' && is_numeric($params['alertUserId'])) {
      $uid = (int)$params['alertUserId'];
      if($uid >= 0) {
        $this->alertUserId = $uid;
      }
    }

    $this->alertEmail = '';
    if(isset($params['alertEmail']) && trim((string)$params['alertEmail']) != '') {
      $this->alertEmail = trim((string)$params['alertEmail']);
    }
  }

  /**
   * @param $class
   * @param $rawRow
   * @param $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
    if(isset($rawRow['dt_unsub_date']) && trim((string)$rawRow['dt_unsub_date']) != '') {
      $class[] = 'status-unsubscribed';
    } else if(isset($rawRow['dt_expire_date']) && trim((string)$rawRow['dt_expire_date']) != '' && strtotime($rawRow['dt_expire_date']) <= time()) {
      $class[] = 'status-expired';
    } else {
      $class[] = ($rawRow['b_active'] == 1 ? 'status-active' : 'status-inactive');
    }
    return $class;
  }
}

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
 * ReportsDataTable class
 *
 * @since 8.4.0
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class ReportsDataTable extends DataTable {
  private $order_by;
  private $status;
  private $type;
  private $keyword;
  private $userId;
  private $unseen;
  private $reason;
  private $itemId;
  private $reporterId;
  private $reportedUserId;
  private $reportedId;

  public function __construct() {
    parent::__construct();
    osc_add_filter('datatable_report_class', array(&$this, 'row_class'));
  }

  /**
   * @param $params
   *
   * @return array
   * @throws \Exception
   */
  public function table($params) {
    $this->addTableHeader();
    $this->getDBParams($params);

    $reports = Report::newInstance()->search(
      $this->start,
      $this->limit,
      ($this->order_by['column_name'] ?: 'COALESCE(r.dt_update_date, r.dt_create_date)'),
      ($this->order_by['type'] ?: 'desc'),
      $this->status,
      $this->keyword,
      $this->type,
      $this->userId,
      $this->unseen,
      $this->reason,
      $this->itemId,
      $this->reporterId,
      $this->reportedUserId,
      $this->reportedId
    );

    $this->processData($reports);

    $this->total = Report::newInstance()->countSearch($this->status, '', $this->type, $this->userId, $this->unseen, $this->reason, $this->itemId, $this->reporterId, $this->reportedUserId, $this->reportedId);
    $this->totalFiltered = Report::newInstance()->countSearch($this->status, $this->keyword, $this->type, $this->userId, $this->unseen, $this->reason, $this->itemId, $this->reporterId, $this->reportedUserId, $this->reportedId);

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
    $this->setDefaultSort('last_update', 'desc');
    // List of sortable columns in datatable
    $this->addSortColumn('type', 'r.s_type', true);
    $this->addSortColumn('reason', 'r.s_reason', true);
    $this->addSortColumn('reporter', 'u.s_name', true);
    $this->addSortColumn('comments', 'i_comments', true);
    $this->addSortColumn('last_update', 'COALESCE(r.dt_update_date, r.dt_create_date)');
    $this->addSortColumn('created', 'r.dt_create_date');

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('type', 's_type');
    $this->addSourceColumn('reason', 's_reason');
    $this->addSourceColumn('reporter', 's_reporter_name');
    $this->addSourceColumn('comments', 'i_comments');
    $this->addSourceColumn('last_update', 'dt_update_date');
    $this->addSourceColumn('created', 'dt_create_date');

    // Table header columns rendered in admin
    $this->addColumn('status-border', '');
    $this->addColumn('status', __('Status'));
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('title', __('Title'));
    $this->addColumn('type', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('type', $sort, $direction)) . '">' . __('Type') . '</a>');
    $this->addColumn('target', __('Reported'));
    $this->addColumn('reason', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('reason', $sort, $direction)) . '">' . __('Reason') . '</a>');
    $this->addColumn('reporter', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('reporter', $sort, $direction)) . '">' . __('Reporter') . '</a>');
    $this->addColumn('comments', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('comments', $sort, $direction)) . '">' . __('Conversation') . '</a>');
    $this->addColumn('last_update', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('last_update', $sort, $direction)) . '">' . __('Last update') . '</a>');
    $this->addColumn('created', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('created', $sort, $direction)) . '">' . __('Created') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_reports_table', $dummy);
  }

  /**
   * @param $reports
   *
   * @throws \Exception
   */
  private function processData($reports) {
    if(!empty($reports)) {
      $csrf_token_url = osc_csrf_token_url();
      foreach($reports as $aRow) {
        $row = array();
        $options = array();
        $options_more = array();

        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=edit&amp;id=' . $aRow['pk_i_id'] . '" id="dt_link_edit">' . __('Edit') . '</a>';
        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=edit&amp;id=' . $aRow['pk_i_id'] . '#comments">' . __('Reply') . '</a>';

        if($aRow['b_open']) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=resolved&amp;list=1">' . __('Resolve') . '</a>';
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=rejected&amp;list=1">' . __('Reject') . '</a>';
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=cancelled&amp;list=1">' . __('Cancel') . '</a>';
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=in_review&amp;list=1">' . osc_report_status_label('in_review') . '</a>';

          if($aRow['fk_i_user_id'] > 0) {
            $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=block_user&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '" onclick="return confirm(\'' . osc_esc_js(__('Are you sure you want to block the reported user?')) . '\');">' . __('Block reported user') . '</a>';
          }

          if($aRow['fk_i_reporter_user_id'] > 0) {
            $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=block_reporter&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '" onclick="return confirm(\'' . osc_esc_js(__('Are you sure you want to block the reporter?')) . '\');">' . __('Block reporter') . '</a>';
          }

          if($aRow['fk_i_item_id'] > 0) {
            $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=block_item&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '" onclick="return confirm(\'' . osc_esc_js(__('Are you sure you want to block the reported listing?')) . '\');">' . __('Block listing') . '</a>';
          }
        } else {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=reports&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=in_review&amp;list=1">' . __('Reopen') . '</a>';
        }

        if($aRow['fk_i_item_id'] > 0) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=item_edit&amp;id=' . (int)$aRow['fk_i_item_id'] . '">' . __('Edit listing') . '</a>';
        }

        if($aRow['fk_i_user_id'] > 0) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&amp;action=edit&amp;id=' . (int)$aRow['fk_i_user_id'] . '">' . __('Edit reported user') . '</a>';
        }

        if($aRow['fk_i_reporter_user_id'] > 0) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&amp;action=edit&amp;id=' . (int)$aRow['fk_i_reporter_user_id'] . '">' . __('Edit reporter') . '</a>';
        }

        $options_more[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=reports&amp;action=delete&amp;id=' . $aRow['pk_i_id'] . '" id="dt_link_delete">' . __('Delete') . '</a>';

        $options_more = osc_apply_filter('more_actions_manage_reports', $options_more, $aRow);
        $options = osc_apply_filter('actions_manage_reports', $options, $aRow);

        $actions = $this->buildRowActions($options, $options_more, 8);

        $status = $this->get_row_status($aRow);
        $status = osc_apply_filter('report_table_row_status', $status, $aRow);

        // Reported target
        $target = '-';
        $targetFilter = array('page' => 'reports', 'type' => $aRow['s_type']);
        if($aRow['s_type'] == 'item' && $aRow['fk_i_item_id'] > 0) {
          $item = osc_get_item_row($aRow['fk_i_item_id']);
          View::newInstance()->_exportVariableToView('item', $item);
          $targetFilter['itemId'] = (int)$aRow['fk_i_item_id'];
          $targetLabel = osc_esc_html((string)@$item['s_title']);
          if($targetLabel == '') {
            $targetLabel = '#' . (int)$aRow['fk_i_item_id'];
          }
          $target = '<a href="' . osc_esc_html($this->build_report_filter_url($targetFilter)) . '">' . $targetLabel . '</a>';
        } else if($aRow['fk_i_user_id'] > 0) {
          $user = osc_get_user_row($aRow['fk_i_user_id']);
          $targetFilter['reportedUserId'] = (int)$aRow['fk_i_user_id'];
          $targetLabel = osc_esc_html((string)@$user['s_name']);
          if($targetLabel == '') {
            $targetLabel = '#' . (int)$aRow['fk_i_user_id'];
          }
          $target = '<a href="' . osc_esc_html($this->build_report_filter_url($targetFilter)) . '">' . $targetLabel . '</a>';
        } else if($aRow['i_reported_id'] > 0) {
          $targetFilter['reportedId'] = (int)$aRow['i_reported_id'];
          $target = '<a href="' . osc_esc_html($this->build_report_filter_url($targetFilter)) . '">' . osc_esc_html(osc_report_type_label($aRow['s_type'])) . ' #' . (int)$aRow['i_reported_id'] . '</a>';
        } else if($aRow['s_type'] == 'webcontact') {
          $target = osc_esc_html(osc_report_type_label('webcontact'));
        }

        $comment = trim((string)$aRow['s_comment']);
        $titleText = ($comment != '' ? osc_esc_html(osc_substr($comment, 0, 32)) . (osc_strlen($comment) > 32 ? '...' : '') : '-');
        $title = '<a href="' . osc_esc_html(osc_report_view_url($aRow['pk_i_id'])) . '" target="_blank">' . $titleText . ' <i class="fa fa-external-link"></i></a>';

        $row['status-border'] = '';
        $row['status'] = $status['text'];
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" />';
        $row['title'] = $title . $actions;
        $row['type'] = '<a href="' . osc_esc_html($this->build_report_filter_url(array('page' => 'reports', 'type' => $aRow['s_type']))) . '">' . osc_esc_html(osc_report_type_label($aRow['s_type'])) . '</a>' . ($aRow['s_source'] != 'osclass' ? '<br/><i>' . osc_esc_html(osc_report_source_label($aRow['s_source'])) . '</i>' : '');
        $row['target'] = $target;
        $row['reason'] = '<a href="' . osc_esc_html($this->build_report_filter_url(array('page' => 'reports', 'reason' => $aRow['s_reason']))) . '">' . osc_esc_html(osc_report_reason_label($aRow['s_reason'])) . '</a>';
        $row['reporter'] = ($aRow['fk_i_reporter_user_id'] > 0
          ? '<a href="' . osc_esc_html($this->build_report_filter_url(array('page' => 'reports', 'reporterId' => (int)$aRow['fk_i_reporter_user_id']))) . '">' . osc_esc_html((string)$aRow['s_reporter_name']) . '</a>'
          : osc_esc_html(__('Guest')));
        $row['comments'] = ($aRow['i_comments'] > 0 ? '<a href="' . osc_admin_base_url(true) . '?page=reports&action=edit&id=' . $aRow['pk_i_id'] . '#comments">' . (int)$aRow['i_comments'] . '</a>' . ($aRow['i_unseen'] > 0 ? ' <b>(' . sprintf(__('%d new'), (int)$aRow['i_unseen']) . ')</b>' : '') : '-');
        $row['last_update'] = osc_format_date(!empty($aRow['dt_update_date']) ? $aRow['dt_update_date'] : $aRow['dt_create_date']);
        $row['created'] = osc_format_date($aRow['dt_create_date']);

        $row = osc_apply_filter('reports_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }
    }
  }

  /**
   * @param array $params
   */
  private function getDBParams($params) {
    $sortData = $this->resolveSort($params);
    $this->order_by['column_name'] = ($sortData['column'] != '' ? $sortData['column'] : 'COALESCE(r.dt_update_date, r.dt_create_date)');
    $this->order_by['type'] = $sortData['direction'];
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);
    $this->keyword = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');
    $this->status = (isset($params['status']) ? trim((string)$params['status']) : '');
    $this->type = (isset($params['type']) ? trim((string)$params['type']) : '');
    $this->reason = (isset($params['reason']) ? trim((string)$params['reason']) : '');
    $this->unseen = (isset($params['unseen']) && $params['unseen'] != '');
    $this->itemId = null;
    $this->reporterId = null;
    $this->reportedUserId = null;
    $this->reportedId = null;
    $this->userId = null;

    foreach($params as $k => $v) {
      if(($k === 'userId') && !empty($v)) {
        $this->userId = (int) $v;
      }

      if(($k === 'itemId') && !empty($v)) {
        $this->itemId = (int) $v;
      }

      if(($k === 'reporterId') && !empty($v)) {
        $this->reporterId = (int) $v;
      }

      if(($k === 'reportedUserId') && !empty($v)) {
        $this->reportedUserId = (int) $v;
      }

      if(($k === 'reportedId') && !empty($v)) {
        $this->reportedId = (int) $v;
      }

      if($k === 'iDisplayStart') {
        $this->start = (int) $v;
      }

      if($k === 'iDisplayLength') {
        $this->limit = (int) $v;
      }
    }

    // set start and limit using iPage param
    $start = ((int)Params::getParam('iPage')-1) * $params['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int) $params['iDisplayLength'];
  }

  /**
   * Build admin reports list filter URL
   *
   * @param array $params
   * @return string
   */
  private function build_report_filter_url($params = array()) {
    $url = osc_admin_base_url(true);
    if(!is_array($params) || count($params) == 0) {
      return $url;
    }

    return $url . '?' . http_build_query($params);
  }

  /**
   * @param $class
   * @param $rawRow
   * @param $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
    $status = $this->get_row_status($rawRow);
    $class[] = $status['class'];
    return $class;
  }

  /**
   * Get the status of the row. Uses existing backoffice status color classes.
   *
   * @since 8.4.0
   *
   * @param $report
   *
   * @return array Array with the class and text of the status of the report in this row.
   */
  private function get_row_status($report) {
    if($report['s_status'] == 'resolved') {
      return array(
        'class' => 'status-expired',
        'text' => osc_report_status_label($report['s_status'])
      );
    }

    if($report['s_status'] == 'rejected') {
      return array(
        'class' => 'status-blocked',
        'text' => osc_report_status_label($report['s_status'])
      );
    }

    if($report['s_status'] == 'cancelled') {
      return array(
        'class' => 'status-inactive',
        'text' => osc_report_status_label($report['s_status'])
      );
    }

    if($report['s_status'] == 'submitted') {
      return array(
        'class' => 'status-warning',
        'text' => osc_report_status_label($report['s_status'])
      );
    }

    if($report['s_status'] == 'awaiting_feedback') {
      return array(
        'class' => 'status-warning',
        'text' => osc_report_status_label($report['s_status'])
      );
    }

    return array(
      'class' => 'status-active',
      'text' => osc_report_status_label($report['s_status'])
    );
  }
}

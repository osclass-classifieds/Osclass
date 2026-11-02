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
 * MailQueueDataTable class (Settings > Mail queue)
 *
 * @since 8.4.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class MailQueueDataTable extends DataTable {
  private $order_by;
  private $search;
  private $filters = array();
  private $withFilters = false;

  public function __construct() {
    parent::__construct();
    osc_add_filter('datatable_mail_queue_class', array(&$this, 'row_class'));
  }

  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {
    $this->addTableHeader();
    $this->getDBParams($params);

    $data = MailQueue::newInstance()->search(array_merge($this->filters, array(
      'start' => $this->start,
      'limit' => $this->limit,
      'order_column' => $this->order_by['column_name'],
      'order_direction' => $this->order_by['type'],
      'keyword' => $this->search
    )));

    $stats = osc_mail_queue_stats();
    $this->processData($data['rows']);
    $this->total = $stats['total'];
    $this->totalFiltered = $data['total_filtered'];

    return $this->getData();
  }

  private function addTableHeader() {
    $url_base = $this->getSortUrlBase();
    $sort = Params::getParam('sort');
    $direction = Params::getParam('direction');

    $this->clearSortColumns();
    $this->clearSourceColumns();
    $this->setDefaultSort('created', 'desc');
    // List of sortable columns in datatable
    $this->addSortColumn('status', 'e_status');
    $this->addSortColumn('to', 's_to_email');
    $this->addSortColumn('subject', 's_subject');
    $this->addSortColumn('type', 's_type');
    $this->addSortColumn('queue_type', 's_queue_type');
    $this->addSortColumn('priority', 'i_priority');
    $this->addSortColumn('attempts', 'i_attempts');
    $this->addSortColumn('created', 'pk_i_id');
    $this->addSortColumn('send_after', 'dt_send_after');
    $this->addSortColumn('done', 'dt_done');

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('status', 'e_status');
    $this->addSourceColumn('to', 's_to_email');
    $this->addSourceColumn('subject', 's_subject|s_error');
    $this->addSourceColumn('type', 's_type|s_group');
    $this->addSourceColumn('queue_type', 's_queue_type');
    $this->addSourceColumn('priority', 'i_priority');
    $this->addSourceColumn('attempts', 'i_attempts');
    $this->addSourceColumn('created', 'dt_created');
    $this->addSourceColumn('send_after', 'dt_send_after');
    $this->addSourceColumn('done', 'dt_done');

    // Table header columns rendered in admin
    $this->addColumn('status-border', '');
    $this->addColumn('status', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('status', $sort, $direction)) . '">' . __('Status') . '</a>');
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('to', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('to', $sort, $direction)) . '">' . __('Recipient') . '</a>');
    $this->addColumn('subject', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('subject', $sort, $direction)) . '">' . __('Subject') . '</a>');
    $this->addColumn('type', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('type', $sort, $direction)) . '">' . __('Type') . '</a>');
    $this->addColumn('queue_type', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('queue_type', $sort, $direction)) . '">' . __('Queue') . '</a>');
    $this->addColumn('priority', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('priority', $sort, $direction)) . '">' . __('Priority') . '</a>');
    $this->addColumn('attempts', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('attempts', $sort, $direction)) . '">' . __('Attempts') . '</a>');
    $this->addColumn('created', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('created', $sort, $direction)) . '">' . __('Created') . '</a>');
    $this->addColumn('send_after', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('send_after', $sort, $direction)) . '">' . __('Scheduled') . '</a>');
    $this->addColumn('done', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('done', $sort, $direction)) . '">' . __('Finished') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_mail_queue_table', $dummy);
  }

  private function buildFilterUrl($params) {
    $url = osc_admin_base_url(true) . '?page=settings&action=mailqueue';

    foreach($params as $k => $v) {
      if($v !== '' && $v !== null) {
        $url .= '&' . $k . '=' . rawurlencode($v);
      }
    }

    return $url;
  }

  /**
   * @param $rows
   */
  private function processData($rows) {
    if(!empty($rows)) {
      $csrf_token_url = osc_csrf_token_url();
      $date_format = osc_date_format() . ' ' . osc_time_format();
      $status_labels = osc_mail_queue_status_labels();
      $priorities = array(1 => __('Instant'), 2 => __('Normal'), 3 => __('Bulk'));
      $max_attempts = osc_mail_queue_max_attempts();

      foreach($rows as $aRow) {
        $row = array();
        $options = array();
        $options_more = array();
        $id = (int)$aRow['pk_i_id'];
        $status = (string)$aRow['e_status'];
        $action_url = osc_admin_base_url(true) . '?page=settings&amp;action=mailqueue_action&amp;id[]=' . $id . '&amp;' . $csrf_token_url . '&amp;do=';

        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=settings&amp;action=mailqueue_view&amp;id=' . $id . '">' . __('View') . '</a>';

        if(osc_mail_queue_can_send($aRow)) {
          $options[] = '<a href="' . $action_url . 'send">' . ($status == 'SENT' ? __('Send again') : __('Send now')) . '</a>';
        }

        if(in_array($status, array('PENDING', 'FAILED'))) {
          $options[] = '<a href="' . $action_url . 'cancel">' . __('Cancel') . '</a>';
        }

        if($status != 'SENDING') {
          $options[] = '<a onclick="return delete_dialog(\'' . $id . '\');" href="' . $action_url . 'delete">' . __('Delete') . '</a>';

          if($status != 'PENDING' && osc_mail_queue_can_send($aRow)) {
            $options_more[] = '<a href="' . $action_url . 'pending">' . __('Mark as pending') . '</a>';
          }

          if($status != 'SENT') {
            $options_more[] = '<a href="' . $action_url . 'sent">' . __('Mark as sent') . '</a>';
          }

          if($status != 'FAILED') {
            $options_more[] = '<a href="' . $action_url . 'failed">' . __('Mark as failed') . '</a>';
          }
        }

        $options_more[] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('email' => $aRow['s_to_email']))) . '">' . __('Emails to this recipient') . '</a>';

        if($status == 'PENDING' && (string)$aRow['s_group'] != '') {
          $options_more[] = '<a onclick="return cancel_group_dialog(this);" data-group="' . osc_esc_html($aRow['s_group']) . '" href="#">' . __('Cancel pending emails of this group') . '</a>';
        }

        $options_more = osc_apply_filter('more_actions_manage_mail_queue', $options_more, $aRow);
        $options = osc_apply_filter('actions_manage_mail_queue', $options, $aRow);
        $actions = $this->buildRowActions($options, $options_more, 4);

        $row['status-border'] = '';
        $row['status'] = (isset($status_labels[$status]) ? $status_labels[$status] : osc_esc_html($status));
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $id . '" />';

        $row['to'] = '<a href="' . osc_admin_base_url(true) . '?page=settings&amp;action=mailqueue_view&amp;id=' . $id . '">' . osc_esc_html($aRow['s_to_email'] != '' ? $aRow['s_to_email'] : '-') . '</a>';
        if($aRow['b_attachment'] == 1) {
          $row['to'] .= ' <i class="fa fa-paperclip" title="' . osc_esc_html(__('Attachment')) . '"></i>';
        }
        $row['to'] .= $actions;

        $row['subject'] = osc_esc_html($aRow['s_subject'] != '' ? $aRow['s_subject'] : '-');
        if((string)$aRow['s_error'] != '') {
          $row['subject'] .= '<span class="mail-queue-error" title="' . osc_esc_html($aRow['s_error']) . '">' . osc_esc_html(osc_strlen($aRow['s_error']) > 120 ? osc_substr($aRow['s_error'], 0, 120) . '...' : $aRow['s_error']) . '</span>';
        }

        $row['type'] = ($aRow['s_type'] != '' ? '<a href="' . osc_esc_html($this->buildFilterUrl(array('type' => $aRow['s_type']))) . '">' . osc_esc_html($aRow['s_type']) . '</a>' : '-');
        if((string)$aRow['s_group'] != '') {
          $row['type'] .= '<span class="mail-queue-sub">' . __('Group') . ': <a href="' . osc_esc_html($this->buildFilterUrl(array('group' => $aRow['s_group']))) . '">' . osc_esc_html($aRow['s_group']) . '</a></span>';
        }

        $row['queue_type'] = '<a href="' . osc_esc_html($this->buildFilterUrl(array('queue_type' => $aRow['s_queue_type']))) . '">' . osc_esc_html($aRow['s_queue_type']) . '</a>';
        $row['priority'] = (isset($priorities[(int)$aRow['i_priority']]) ? $priorities[(int)$aRow['i_priority']] : (int)$aRow['i_priority']);
        $row['attempts'] = sprintf(__('%1$d of %2$d'), (int)$aRow['i_attempts'], max($max_attempts, (int)$aRow['i_attempts']));
        $row['created'] = osc_format_date($aRow['dt_created'], $date_format);
        $row['send_after'] = ($status == 'PENDING' ? osc_format_date($aRow['dt_send_after'], $date_format) : '-');
        $row['done'] = ($aRow['dt_done'] != '' ? osc_format_date($aRow['dt_done'], $date_format) : '-');

        $row = osc_apply_filter('mail_queue_processing_row', $row, $aRow);

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
      'default_sort_key' => 'created',
      'default_sort_dir' => 'desc'
    ));

    $this->iPage = $list['iPage'];
    $this->start = $list['start'];
    $this->limit = $list['limit'];
    $this->search = $list['search'];
    $this->withFilters = $list['with_filters'];

    $this->order_by['column_name'] = ($list['sort']['column'] != '' ? $list['sort']['column'] : 'pk_i_id');
    $this->order_by['type'] = strtoupper($list['sort']['direction']);

    $statuses = array('pending' => 'PENDING', 'sending' => 'SENDING', 'sent' => 'SENT', 'failed' => 'FAILED', 'cancelled' => 'CANCELLED');
    $status = strtolower(isset($params['status']) ? (string)$params['status'] : '');

    $this->filters = array(
      'id' => (isset($params['mailId']) ? (int)$params['mailId'] : 0),
      'status' => (isset($statuses[$status]) ? $statuses[$status] : ''),
      'priority' => (isset($params['priority']) ? (int)$params['priority'] : 0),
      'type' => (isset($params['type']) ? trim((string)$params['type']) : ''),
      'queue_type' => (isset($params['queue_type']) ? trim((string)$params['queue_type']) : ''),
      'group' => (isset($params['group']) ? trim((string)$params['group']) : ''),
      'ref' => (isset($params['ref']) ? trim((string)$params['ref']) : ''),
      'email' => (isset($params['email']) ? trim((string)$params['email']) : ''),
      'date_from' => (isset($params['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$params['date_from']) ? (string)$params['date_from'] : ''),
      'date_to' => (isset($params['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$params['date_to']) ? (string)$params['date_to'] : '')
    );

    // The status select is a view switch, the rest are filters
    foreach($this->filters as $key => $value) {
      if($key != 'status' && $value !== '' && $value !== 0) {
        $this->withFilters = true;
      }
    }
  }

  /**
   * @return bool
   */
  public function withFilters() {
    return $this->withFilters;
  }

  /**
   * @param $class
   * @param $rawRow
   * @param $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
    $classes = array('PENDING' => 'status-warning', 'SENDING' => 'status-info', 'SENT' => 'status-success', 'FAILED' => 'status-blocked', 'CANCELLED' => 'status-inactive');
    $class[] = (isset($classes[$rawRow['e_status']]) ? $classes[$rawRow['e_status']] : 'status-inactive');
    return $class;
  }
}

/* file end: ./oc-includes/osclass/classes/datatables/MailQueueDataTable.php */

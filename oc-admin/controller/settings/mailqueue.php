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


class CAdminSettingsMailqueue extends AdminSecBaseModel {
  // Mail queue: list with tabs, health, row and bulk actions, settings
  function doModel() {
    $statuses = array('pending' => 'PENDING', 'sending' => 'SENDING', 'sent' => 'SENT', 'failed' => 'FAILED', 'cancelled' => 'CANCELLED');
    $tab = strtolower((string)Params::getParam('status'));
    $tab = (isset($statuses[$tab]) ? $tab : '');
    $list_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue' . ($tab != '' ? '&status=' . $tab : '');

    switch($this->action) {
      case('mailqueue_post'):
        osc_csrf_check();

        if(defined('DEMO')) {
          osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
          $this->redirectTo($list_url);
        }

        $instant_types = array_filter(array_map('trim', explode(',', (string)Params::getParam('mail_queue_instant_types'))), function($v) { return preg_match('/^[a-zA-Z0-9_\-]+$/', $v); });

        $iUpdated = 0;
        $iUpdated += osc_set_preference('mail_queue_enabled', (Params::getParam('mail_queue_enabled') != '' ? 1 : 0), 'osclass', 'BOOLEAN');
        $iUpdated += osc_set_preference('mail_queue_paused', (Params::getParam('mail_queue_paused') != '' ? 1 : 0), 'osclass', 'BOOLEAN');
        $iUpdated += osc_set_preference('mail_queue_per_run', max(1, min(5000, (int)Params::getParam('mail_queue_per_run'))), 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('mail_queue_bulk_per_run', max(1, min(5000, (int)Params::getParam('mail_queue_bulk_per_run'))), 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('mail_queue_group_per_run', max(1, min(5000, (int)Params::getParam('mail_queue_group_per_run'))), 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('mail_queue_time_budget', max(5, min(3600, (int)Params::getParam('mail_queue_time_budget'))), 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('mail_queue_max_attempts', max(1, min(10, (int)Params::getParam('mail_queue_max_attempts'))), 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('mail_queue_keep_days', max(1, min(365, (int)Params::getParam('mail_queue_keep_days'))), 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('mail_queue_instant_types', implode(',', array_unique($instant_types)), 'osclass', 'STRING');

        if($iUpdated > 0) {
          osc_add_flash_ok_message(_m('Mail queue settings have been updated'), 'admin');
        }

        $this->redirectTo($list_url);
        break;

      case('mailqueue_action'):
        osc_csrf_check();

        if(defined('DEMO')) {
          osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
          $this->redirectTo($list_url);
        }

        $mq = MailQueue::newInstance();
        $do = (string)Params::getParam('do');
        $ids = array_values(array_filter(array_map('intval', (array)Params::getParam('id'))));
        $now = date('Y-m-d H:i:s');
        $count = 0;

        if($do == 'pause' || $do == 'resume') {
          osc_set_preference('mail_queue_paused', ($do == 'pause' ? 1 : 0), 'osclass', 'BOOLEAN');
          osc_add_flash_ok_message(($do == 'pause' ? _m('Sending is paused. New emails still wait in the queue.') : _m('Sending is resumed')), 'admin');
          $this->redirectTo($list_url);
        }

        if($do == 'cancel') {
          $count = $mq->changeStatus($ids, '', array('PENDING'), array('e_status' => 'CANCELLED', 'dt_done' => $now, 's_error' => 'Cancelled by admin'));
        } else if($do == 'cancel_group') {
          $count = $mq->changeStatus(array(), (string)Params::getParam('group'), array('PENDING'), array('e_status' => 'CANCELLED', 'dt_done' => $now, 's_error' => 'Cancelled by admin'));
        } else if($do == 'retry') {
          $count = $mq->changeStatus($ids, '', array('FAILED', 'CANCELLED'), array('e_status' => 'PENDING', 'i_attempts' => 0, 'dt_send_after' => $now, 'dt_done' => null, 's_error' => null));
        } else if($do == 'delete') {
          $count = osc_mail_queue_delete($ids);
        } else if($do == 'send') {
          // Failed or cancelled mail gets a new set of attempts, then it is sent in this request
          $mq->changeStatus($ids, '', array('FAILED', 'CANCELLED'), array('e_status' => 'PENDING', 'i_attempts' => 0, 'dt_done' => null, 's_error' => null));
          $token = bin2hex(random_bytes(16));

          foreach($mq->claim(array_slice($ids, 0, 20), $token) as $row) {
            if(osc_mail_queue_send($row)) {
              $count++;
            }
          }

          if($count == 0) {
            osc_add_flash_error_message(_m('The email was not sent. See the error in the list.'), 'admin');
            $this->redirectTo($list_url);
          }
        }

        osc_add_flash_ok_message(sprintf(_mn('One email has been updated', '%s emails have been updated', $count), $count), 'admin');
        $this->redirectTo($list_url);
        break;

      case('mailqueue_view'):
        $mail = MailQueue::newInstance()->findByPrimaryKey((int)Params::getParam('id'));

        if($mail === false) {
          osc_add_flash_error_message(_m('Email not found in the mail queue'), 'admin');
          $this->redirectTo($list_url);
        }

        $params = json_decode((string)$mail['s_params'], true);
        $this->_exportVariableToView('mail', $mail);
        $this->_exportVariableToView('mail_params', (is_array($params) ? $params : array()));
        $this->doView('settings/mailqueue_view.php');
        break;

      default:
        require_once osc_lib_path() . 'osclass/helpers/hCustomDataTable.php';
        osc_custom_datatable_hook_admin_head(array('datatable_id' => 'mail_queue'));

        $priorities = array(1 => __('Instant'), 2 => __('Normal'), 3 => __('Bulk'));
        $status_labels = array('PENDING' => __('Pending'), 'SENDING' => __('Sending'), 'SENT' => __('Sent'), 'FAILED' => __('Failed'), 'CANCELLED' => __('Cancelled'));
        $status_classes = array('PENDING' => 'status-warning', 'SENDING' => 'status-premium', 'SENT' => 'status-active', 'FAILED' => 'status-blocked', 'CANCELLED' => 'status-inactive');
        $stats = osc_mail_queue_stats();
        $base_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue';
        $csrf = osc_csrf_token_url();

        $filters = array(
          'status' => ($tab != '' ? $statuses[$tab] : ''),
          'priority' => (int)Params::getParam('priority'),
          'type' => trim((string)Params::getParam('type')),
          'group' => trim((string)Params::getParam('group')),
          'ref' => trim((string)Params::getParam('ref')),
          'date_from' => (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)Params::getParam('date_from')) ? (string)Params::getParam('date_from') : ''),
          'date_to' => (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)Params::getParam('date_to')) ? (string)Params::getParam('date_to') : '')
        );

        $tableOptions = array(
          'id' => 'mail_queue',
          'default_sort' => array('key' => 'created', 'direction' => 'desc'),
          'status_columns' => true,
          'bulk_actions' => array('input_name' => 'id[]', 'value_key' => 'pk_i_id'),
          'columns' => array(
            array('id' => 'to', 'label' => __('Recipient'), 'sortable' => true, 'sort_column' => 's_to_email', 'source' => 's_to_email'),
            array('id' => 'subject', 'label' => __('Subject'), 'sortable' => true, 'sort_column' => 's_subject', 'source' => 's_subject'),
            array('id' => 'type', 'label' => __('Type'), 'sortable' => true, 'sort_column' => 's_type', 'source' => 's_type'),
            array('id' => 'group', 'label' => __('Group'), 'sortable' => true, 'sort_column' => 's_group', 'source' => 's_group'),
            array('id' => 'priority', 'label' => __('Priority'), 'sortable' => true, 'sort_column' => 'i_priority', 'source' => 'i_priority'),
            array('id' => 'attempts', 'label' => __('Attempts'), 'sortable' => true, 'sort_column' => 'i_attempts', 'source' => 'i_attempts'),
            array('id' => 'created', 'label' => __('Created'), 'sortable' => true, 'sort_column' => 'pk_i_id', 'source' => 'dt_created'),
            array('id' => 'send_after', 'label' => __('Send after'), 'sortable' => true, 'sort_column' => 'dt_send_after', 'source' => 'dt_send_after'),
            array('id' => 'done', 'label' => __('Finished'), 'sortable' => true, 'sort_column' => 'dt_done', 'source' => 'dt_done'),
            array('id' => 'error', 'label' => __('Error'), 'source' => 's_error')
          ),
          'callbacks' => array(
            'actions_column' => 'to',
            'fetch' => function($table, $list) use ($filters, $stats, $tab) {
              $data = MailQueue::newInstance()->search(array_merge($filters, array(
                'start' => $list['start'],
                'limit' => $list['limit'],
                'order_column' => $list['order_by']['column_name'],
                'order_direction' => $list['order_by']['type'],
                'keyword' => $list['search']
              )));

              return array('rows' => $data['rows'], 'total' => ($tab != '' ? $stats[$tab] : $stats['total']), 'total_filtered' => $data['total_filtered']);
            },
            'process_row' => function($raw, $table) use ($priorities, $status_labels, $base_url) {
              $date_format = osc_date_format() . ' ' . osc_time_format();
              $type = (string)$raw['s_type'];
              $group = (string)$raw['s_group'];

              return array_merge(osc_custom_datatable_status_cells(isset($status_labels[$raw['e_status']]) ? $status_labels[$raw['e_status']] : osc_esc_html($raw['e_status'])), array(
                'to' => '<a href="' . osc_esc_html($base_url . '&sSearch=' . rawurlencode($raw['s_to_email'])) . '">' . osc_esc_html($raw['s_to_email']) . '</a>' . ($raw['b_attachment'] == 1 ? ' <i class="fa fa-paperclip" title="' . osc_esc_html(__('Attachment')) . '"></i>' : ''),
                'subject' => '<span title="' . osc_esc_html($raw['s_subject']) . '">' . osc_esc_html(osc_substr((string)$raw['s_subject'], 0, 80) . (osc_strlen((string)$raw['s_subject']) > 80 ? '...' : '')) . '</span>',
                'type' => ($type != '' ? '<a href="' . osc_esc_html($base_url . '&type=' . rawurlencode($type)) . '">' . osc_esc_html($type) . '</a>' : '-'),
                'group' => ($group != '' ? '<a href="' . osc_esc_html($base_url . '&group=' . rawurlencode($group)) . '">' . osc_esc_html($group) . '</a>' : '-'),
                'priority' => (isset($priorities[(int)$raw['i_priority']]) ? $priorities[(int)$raw['i_priority']] : (int)$raw['i_priority']),
                'attempts' => (int)$raw['i_attempts'],
                'created' => osc_format_date($raw['dt_created'], $date_format),
                'send_after' => osc_format_date($raw['dt_send_after'], $date_format),
                'done' => ($raw['dt_done'] != '' ? osc_format_date($raw['dt_done'], $date_format) : '-'),
                'error' => ($raw['s_error'] != '' ? '<span title="' . osc_esc_html($raw['s_error']) . '">' . osc_esc_html(osc_substr((string)$raw['s_error'], 0, 60) . (osc_strlen((string)$raw['s_error']) > 60 ? '...' : '')) . '</span>' : '-')
              ));
            },
            'row_class' => function($class, $raw, $row) use ($status_classes) {
              $class[] = (isset($status_classes[$raw['e_status']]) ? $status_classes[$raw['e_status']] : 'status-inactive');
              return $class;
            },
            'row_actions' => function($raw, $table) use ($tab, $csrf) {
              $id = (int)$raw['pk_i_id'];
              $url = osc_admin_base_url(true) . '?page=settings&amp;action=mailqueue_action&amp;id[]=' . $id . ($tab != '' ? '&amp;status=' . $tab : '') . '&amp;' . $csrf . '&amp;do=';
              $options = array('<a href="' . osc_admin_base_url(true) . '?page=settings&amp;action=mailqueue_view&amp;id=' . $id . '">' . __('View') . '</a>');

              if(in_array($raw['e_status'], array('PENDING', 'FAILED', 'CANCELLED'))) {
                $options[] = '<a href="' . $url . 'send">' . __('Send now') . '</a>';
              }

              if($raw['e_status'] == 'PENDING') {
                $options[] = '<a href="' . $url . 'cancel">' . __('Cancel') . '</a>';

                if((string)$raw['s_group'] != '') {
                  $options[] = '<a href="' . osc_admin_base_url(true) . '?page=settings&amp;action=mailqueue_action&amp;do=cancel_group&amp;group=' . rawurlencode($raw['s_group']) . ($tab != '' ? '&amp;status=' . $tab : '') . '&amp;' . $csrf . '" onclick="return confirm(\'' . osc_esc_js(__('Cancel all pending emails of this group?')) . '\');">' . __('Cancel group') . '</a>';
                }
              }

              if(in_array($raw['e_status'], array('FAILED', 'CANCELLED'))) {
                $options[] = '<a href="' . $url . 'retry">' . __('Retry') . '</a>';
              }

              if($raw['e_status'] != 'SENDING') {
                $options[] = '<a href="' . $url . 'delete" onclick="return confirm(\'' . osc_esc_js(__('Delete this email from the queue?')) . '\');">' . __('Delete') . '</a>';
              }

              return $options;
            }
          )
        );

        $bulkOptions = osc_custom_datatable_bulk_options(array(
          'cancel' => __('Cancel'),
          'retry' => __('Retry'),
          'delete' => __('Delete')
        ));

        $result = osc_custom_datatable_admin_prepare(array(
          'options' => $tableOptions,
          'admin' => array('cookie_key' => 'mail_queue_iDisplayLength', 'default_sort' => 'created', 'default_direction' => 'desc'),
          'bulk_options' => $bulkOptions
        ));

        osc_custom_datatable_redirect_empty_page($result['aData'], $this);
        osc_custom_datatable_export_admin_view($this, $result, array('extra' => array(
          'mail_queue_stats' => $stats,
          'mail_queue_tab' => $tab,
          'withFilters' => ($result['withFilters'] || $filters['priority'] > 0 || $filters['type'] != '' || $filters['group'] != '' || $filters['ref'] != '' || $filters['date_from'] != '' || $filters['date_to'] != '')
        )));

        $this->doView('settings/mailqueue.php');
        break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/mailqueue.php

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
  // Mail queue: list, email detail with history, row and bulk actions, settings
  function doModel() {
    $list_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue';

    // Back to the list the admin came from (keeps filters and page)
    $referer = (string)Params::getServerParam('HTTP_REFERER', false, false);
    $back_url = (strpos($referer, osc_admin_base_url(true) . '?page=settings&action=mailqueue') === 0 && strpos($referer, 'action=mailqueue_') === false ? $referer : $list_url);

    switch($this->action) {
      case('mailqueue_action'):
        osc_csrf_check();

        if(defined('DEMO')) {
          osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
          $this->redirectTo($back_url);
        }

        $do = (string)Params::getParam('do');
        $ids = array_values(array_filter(array_map('intval', (array)Params::getParam('id'))));
        $admin_id = osc_logged_admin_id();

        if($do == 'pause' || $do == 'resume') {
          osc_set_preference('mail_queue_paused', ($do == 'pause' ? 1 : 0), 'osclass', 'BOOLEAN');
          osc_add_flash_ok_message(($do == 'pause' ? _m('Sending is paused. New emails still wait in the queue.') : _m('Sending is resumed')), 'admin');
          $this->redirectTo($back_url);
        }

        // Back to the detail when the action came from there
        if(Params::getParam('from') == 'view' && count($ids) == 1) {
          $back_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue_view&id=' . $ids[0];
        }

        if($do == 'cancel_group') {
          $ids = MailQueue::newInstance()->findGroupPending(trim((string)Params::getParam('group')));
          $do = 'cancel';
        }

        if(empty($ids)) {
          osc_add_flash_warning_message(_m('Select at least one email'), 'admin');
          $this->redirectTo($back_url);
        }

        $statuses = array('pending' => 'PENDING', 'sent' => 'SENT', 'failed' => 'FAILED', 'cancel' => 'CANCELLED');

        if(isset($statuses[$do])) {
          $count = osc_mail_queue_set_status($ids, $statuses[$do], $admin_id);

          if($count > 0) {
            osc_add_flash_ok_message(sprintf(_mn('One email has been updated', '%s emails have been updated', $count), $count), 'admin');
          } else {
            osc_add_flash_warning_message(_m('No email was changed. Emails being sent right now cannot be changed.'), 'admin');
          }
        } else if($do == 'delete') {
          $count = osc_mail_queue_delete($ids);

          if($count > 0) {
            osc_add_flash_ok_message(sprintf(_mn('One email has been deleted', '%s emails have been deleted', $count), $count), 'admin');
          } else {
            osc_add_flash_warning_message(_m('No email was deleted. Emails being sent right now cannot be deleted.'), 'admin');
          }

          if(Params::getParam('from') == 'view') {
            $back_url = $list_url;
          }
        } else if($do == 'send') {
          $result = osc_mail_queue_send_now($ids, $admin_id);

          if($result['sent'] > 0) {
            osc_add_flash_ok_message(sprintf(_mn('One email has been sent', '%s emails have been sent', $result['sent']), $result['sent']), 'admin');
          }

          if($result['not_sent'] > 0) {
            osc_add_flash_error_message(sprintf(_mn('One email was not sent. See the error in its history.', '%s emails were not sent. See the error in their history.', $result['not_sent']), $result['not_sent']), 'admin');
          }

          if($result['queued'] > 0) {
            osc_add_flash_info_message(sprintf(_mn('One email will be sent with the next cron run', '%s emails will be sent with the next cron run', $result['queued']), $result['queued']), 'admin');
          }

          if($result['sent'] + $result['not_sent'] + $result['queued'] == 0) {
            osc_add_flash_warning_message(_m('No email was sent. Emails being sent right now and sent private emails (their text was removed) cannot be sent again.'), 'admin');
          }
        } else {
          osc_add_flash_warning_message(_m('Choose an action'), 'admin');
        }

        $this->redirectTo($back_url);
        break;

      case('mailqueue_view'):
        $mq = MailQueue::newInstance();
        $mail = $mq->findByPrimaryKey((int)Params::getParam('id'));

        if($mail === false || !isset($mail['pk_i_id'])) {
          osc_add_flash_error_message(_m('Email not found in the mail queue'), 'admin');
          $this->redirectTo($list_url);
        }

        $params = json_decode((string)$mail['s_params'], true);
        $this->_exportVariableToView('mail', $mail);
        $this->_exportVariableToView('mail_params', (is_array($params) ? $params : array()));
        $this->_exportVariableToView('mail_logs', $mq->findLogs($mail['pk_i_id']));
        $this->doView('settings/mailqueue_view.php');
        break;

      default:
        require_once osc_lib_path() . 'osclass/classes/datatables/MailQueueDataTable.php';

        // set default iDisplayLength
        if(Params::getParam('iDisplayLength') != '') {
          Cookie::newInstance()->push('listing_iDisplayLength', Params::getParam('iDisplayLength'));
          Cookie::newInstance()->set();
        } else {
          // set a default value if it's set in the cookie
          if(Cookie::newInstance()->get_value('listing_iDisplayLength') != '') {
            Params::setParam('iDisplayLength', Cookie::newInstance()->get_value('listing_iDisplayLength'));
          } else {
            Params::setParam('iDisplayLength', 25);
          }
        }
        $this->_exportVariableToView('iDisplayLength', Params::getParam('iDisplayLength'));

        // Table header order by related
        if(Params::getParam('sort') == '') {
          Params::setParam('sort', 'created');
        }

        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'desc');
        }

        $page = (int)Params::getParam('iPage');
        if($page == 0) { $page = 1; }
        Params::setParam('iPage', $page);

        $params = Params::getParamsAsArray();

        $mailQueueDataTable = new MailQueueDataTable();
        $mailQueueDataTable->table($params);
        $aData = $mailQueueDataTable->getData();

        if(count($aData['aRows']) == 0 && $page != 1) {
          $total = (int)$aData['iTotalDisplayRecords'];
          $maxPage = ceil($total / (int)$aData['iDisplayLength']);

          $url = osc_admin_base_url(true) . '?' . Params::getServerParam('QUERY_STRING', false, false);

          if($maxPage == 0) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage=1', $url);
            $this->redirectTo($url);
          }

          if($page > 1) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage=' . $maxPage, $url);
            $this->redirectTo($url);
          }
        }

        $this->_exportVariableToView('aData', $aData);
        $this->_exportVariableToView('aRawRows', $mailQueueDataTable->rawRows());
        $this->_exportVariableToView('withFilters', $mailQueueDataTable->withFilters());
        $this->_exportVariableToView('mail_queue_stats', osc_mail_queue_stats());
        $this->_exportVariableToView('mail_queue_types', MailQueue::newInstance()->findTypes());

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array('value' => 'send', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected emails?'), strtolower(__('Send now'))), 'label' => __('Send now')),
          array('value' => 'pending', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected emails?'), strtolower(__('Mark as pending'))), 'label' => __('Mark as pending')),
          array('value' => 'sent', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected emails?'), strtolower(__('Mark as sent'))), 'label' => __('Mark as sent')),
          array('value' => 'failed', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected emails?'), strtolower(__('Mark as failed'))), 'label' => __('Mark as failed')),
          array('value' => 'cancel', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected emails?'), strtolower(__('Cancel'))), 'label' => __('Cancel')),
          array('value' => 'delete', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected emails?'), strtolower(__('Delete'))), 'label' => __('Delete'))
        );

        $bulk_options = osc_apply_filter('mail_queue_bulk_filter', $bulk_options);
        $this->_exportVariableToView('bulk_options', $bulk_options);

        $this->doView('settings/mailqueue.php');
        break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/mailqueue.php

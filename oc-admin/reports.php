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


class CAdminReports extends AdminSecBaseModel {
  private $reportManager;

  function __construct() {
    parent::__construct();

    //specific things for this class
    osc_ensure_reports_tables();
    $this->reportManager = Report::newInstance();
  }

  //Business Layer...
  function doModel() {
    parent::doModel();

    //specific things for this class
    switch($this->action) {
      case('bulk_actions'):
        osc_csrf_check();
        $id = Params::getParam('id');
        if($id) {
          switch(Params::getParam('bulk_actions')) {
            case('delete_all'):
              foreach($id as $_id) {
                $this->reportManager->deleteByPrimaryKey($_id);
              }

              osc_add_flash_ok_message(_m('The reports have been deleted'), 'admin');
              break;

            case('resolve_all'):
              foreach($id as $_id) {
                $this->changeStatus($_id, 'resolved');
              }

              osc_add_flash_ok_message(_m('The reports have been resolved'), 'admin');
              break;

            case('reject_all'):
              foreach($id as $_id) {
                $this->changeStatus($_id, 'rejected');
              }

              osc_add_flash_ok_message(_m('The reports have been rejected'), 'admin');
              break;

            case('cancel_all'):
              foreach($id as $_id) {
                $this->changeStatus($_id, 'cancelled');
              }

              osc_add_flash_ok_message(_m('The reports have been cancelled'), 'admin');
              break;

            case('review_all'):
              foreach($id as $_id) {
                $this->changeStatus($_id, 'in_review');
              }

              osc_add_flash_ok_message(_m('The reports have been moved to review'), 'admin');
              break;

            default:
              if(Params::getParam("bulk_actions")!="") {
                osc_run_hook("report_bulk_".Params::getParam("bulk_actions"), Params::getParam('id'));
              }
              break;

          }
        }
        $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        break;

      case('status'):
        osc_csrf_check(false);
        $id = (int)Params::getParam('id');
        $value = Params::getParam('value');

        if(!$id) return false;
        if(!in_array($value, array_keys(osc_report_statuses()))) return false;

        if($this->changeStatus($id, $value)) {
          osc_add_flash_ok_message(_m('The report status has been updated'), 'admin');
        } else {
          osc_add_flash_error_message(_m('The report status could not be updated'), 'admin');
        }

        if(Params::getParam('list') != '') {
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        }

        $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=edit&id=" . $id);
        break;

      case('edit'):
        $report = $this->reportManager->findByPrimaryKey(Params::getParam('id'));

        if(!$report) {
          osc_add_flash_error_message(_m('The report does not exist'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        }

        $item = false;
        if($report['fk_i_item_id'] > 0) {
          $item = Item::newInstance()->findByPrimaryKey($report['fk_i_item_id']);
        }

        // Sync reported user from listing when report still has no owner (guest listing claimed later)
        if(!($report['fk_i_user_id'] > 0) && $item !== false && $item['fk_i_user_id'] > 0) {
          $this->reportManager->update(
            array('fk_i_user_id' => (int)$item['fk_i_user_id'], 'dt_update_date' => date('Y-m-d H:i:s')),
            array('pk_i_id' => $report['pk_i_id'])
          );
          $report['fk_i_user_id'] = (int)$item['fk_i_user_id'];
        }

        $reporter = false;
        if($report['fk_i_reporter_user_id'] > 0) {
          $reporter = User::newInstance()->findByPrimaryKey($report['fk_i_reporter_user_id']);
        }

        $reported_user = false;
        if($report['fk_i_user_id'] > 0) {
          $reported_user = User::newInstance()->findByPrimaryKey($report['fk_i_user_id']);
        }

        $comments = $this->reportManager->getComments($report['pk_i_id']);

        // Mark replies as seen by admin
        $this->reportManager->markCommentsSeen($report['pk_i_id']);

        $this->_exportVariableToView('report', $report);
        $this->_exportVariableToView('item', $item);
        $this->_exportVariableToView('reporter', $reporter);
        $this->_exportVariableToView('reported_user', $reported_user);
        $this->_exportVariableToView('comments', $comments);
        $this->doView('reports/edit.php');
        break;

      case('edit_post'):
        osc_csrf_check();

        $report = $this->reportManager->findByPrimaryKey(Params::getParam('id'));

        if(!$report) {
          osc_add_flash_error_message(_m('The report does not exist'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        }

        $update = array(
          's_reason' => (in_array(Params::getParam('reason'), array_keys(osc_report_reasons_for_type($report['s_type'], $report['s_reason']))) ? Params::getParam('reason') : $report['s_reason']),
          's_admin_comment' => osc_substr(trim(strip_tags((string)Params::getParam('admin_comment'))), 0, 2000),
          'dt_update_date' => date('Y-m-d H:i:s')
        );

        osc_run_hook('pre_report_edit', $report);
        osc_run_hook('before_edit_report', $report);
        $update = osc_apply_filter('report_edit_data', $update, $report);

        $this->reportManager->update($update, array('pk_i_id' => $report['pk_i_id']));

        Log::newInstance()->insertLog(
          'report',
          'edit',
          $report['pk_i_id'],
          $update['s_reason'],
          'admin',
          osc_logged_admin_id(),
          osc_substr((string)$update['s_admin_comment'], 0, 250)
        );

        if(Params::getParam('status') != $report['s_status']) {
          $this->changeStatus($report['pk_i_id'], Params::getParam('status'));
        }

        $report = $this->reportManager->findByPrimaryKey($report['pk_i_id']);
        osc_run_hook('edit_report', $report['pk_i_id']);
        osc_run_hook('edited_report', $report);

        osc_add_flash_ok_message(_m('The report has been updated'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=edit&id=" . $report['pk_i_id']);
        break;

      case('comment_post'):
        osc_csrf_check();

        $report = $this->reportManager->findByPrimaryKey(Params::getParam('id'));

        if(!$report) {
          osc_add_flash_error_message(_m('The report does not exist'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        }

        osc_run_hook('report_comment_post', $report);

        $comment = osc_substr(trim(strip_tags((string)Params::getParam('comment'))), 0, 2000);
        $new_status = Params::getParam('status');
        $notify_reporter = (Params::getParam('notify_reporter') != '');
        $notify_reported = (Params::getParam('notify_reported') != '');
        $status_changed = ($new_status != '' && $new_status != $report['s_status'] && in_array($new_status, array_keys(osc_report_statuses())));

        if($comment == '') {
          osc_add_flash_error_message(_m('Reply cannot be empty'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=edit&id=" . $report['pk_i_id'] . '#comments');
        }

        osc_run_hook('before_add_report_comment', $report, $comment);
        $comment = osc_apply_filter('report_comment_insert_data', $comment, $report);

        $commentId = $this->reportManager->addComment($report['pk_i_id'], $comment, null, osc_logged_admin_id());

        if($commentId) {
          $commentRow = $this->reportManager->findCommentByPrimaryKey($commentId);
          osc_run_hook('add_report_comment', $report, $commentRow);
          osc_run_hook('after_add_report_comment', $report, $commentRow);

          if($status_changed) {
            // Update status without preference-based emails; checkboxes control notifications below
            $this->changeStatus($report['pk_i_id'], $new_status, array('reporter' => false, 'reported' => false));
            $report = $this->reportManager->findByPrimaryKey($report['pk_i_id']);
          }

          if($status_changed && osc_report_status_is_closed($new_status)) {
            if($notify_reporter) {
              osc_run_hook('hook_email_report_reporter_resolved', $report);
            }

            if($notify_reported) {
              osc_run_hook('hook_email_report_owner_resolved', $report);
            }
          } else {
            if($notify_reporter && $report['fk_i_reporter_user_id'] > 0) {
              osc_run_hook('hook_email_report_new_comment_user', $report, $commentRow, $report['fk_i_reporter_user_id']);
            }

            if($notify_reported) {
              if($status_changed && $new_status == 'awaiting_feedback' && osc_reports_feedback_enabled() && $report['fk_i_user_id'] > 0) {
                osc_run_hook('hook_email_report_feedback_request', $report);
              } else {
                osc_run_hook('hook_email_report_new_comment_user', $report, $commentRow, (int)$report['fk_i_user_id']);
              }
            }
          }

          osc_add_flash_ok_message(_m('Your reply has been added'), 'admin');
        } else {
          osc_add_flash_error_message(_m('There was a problem submitting your reply, please try again later.'), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=edit&id=" . $report['pk_i_id'] . '#comments');
        break;

      case('block_user'):
        osc_csrf_check(false);
        $report = $this->reportManager->findByPrimaryKey(Params::getParam('id'));

        if(!$report || !($report['fk_i_user_id'] > 0)) {
          osc_add_flash_error_message(_m('Reported user could not be blocked'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        }

        $userActions = new UserActions(true);
        if($userActions->disable($report['fk_i_user_id'])) {
          osc_run_hook('report_block_user', $report);
          osc_add_flash_ok_message(_m('The reported user has been blocked'), 'admin');
        } else {
          osc_add_flash_error_message(_m('Reported user could not be blocked'), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=edit&id=" . $report['pk_i_id']);
        break;

      case('block_reporter'):
        osc_csrf_check(false);
        $report = $this->reportManager->findByPrimaryKey(Params::getParam('id'));

        if(!$report || !($report['fk_i_reporter_user_id'] > 0)) {
          osc_add_flash_error_message(_m('Reporter could not be blocked'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        }

        $userActions = new UserActions(true);
        if($userActions->disable($report['fk_i_reporter_user_id'])) {
          osc_run_hook('report_block_reporter', $report);
          osc_add_flash_ok_message(_m('The reporter has been blocked'), 'admin');
        } else {
          osc_add_flash_error_message(_m('Reporter could not be blocked'), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=edit&id=" . $report['pk_i_id']);
        break;

      case('block_item'):
        osc_csrf_check(false);
        $report = $this->reportManager->findByPrimaryKey(Params::getParam('id'));

        if(!$report || !($report['fk_i_item_id'] > 0)) {
          osc_add_flash_error_message(_m('Reported listing could not be blocked'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        }

        $itemActions = new ItemActions(true);
        if($itemActions->disable($report['fk_i_item_id'])) {
          osc_run_hook('report_block_item', $report);
          osc_add_flash_ok_message(_m('The reported listing has been blocked'), 'admin');
        } else {
          osc_add_flash_error_message(_m('Reported listing could not be blocked'), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=edit&id=" . $report['pk_i_id']);
        break;

      case('request_feedback'):
        osc_csrf_check(false);

        $report = $this->reportManager->findByPrimaryKey(Params::getParam('id'));

        if(!$report || !($report['fk_i_user_id'] > 0)) {
          osc_add_flash_error_message(_m('Feedback cannot be requested, report has no related user'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        }

        $this->changeStatus($report['pk_i_id'], 'awaiting_feedback');

        osc_add_flash_ok_message(_m('Feedback has been requested from reported user'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=edit&id=" . $report['pk_i_id']);
        break;

      case('delete'):
        osc_csrf_check();
        $this->reportManager->deleteByPrimaryKey(Params::getParam('id'));
        osc_add_flash_ok_message(_m('The report has been deleted'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . "?page=reports");
        break;

      case('settings'):
        $this->doView('reports/settings.php');
        break;

      case('settings_post'):
        osc_csrf_check();

        osc_set_preference('reports_enabled', (Params::getParam('reports_enabled') != '' ? 1 : 0));
        osc_set_preference('reports_per_day', (int)Params::getParam('reports_per_day'));
        osc_set_preference('reports_allow_multiple', (Params::getParam('reports_allow_multiple') != '' ? 1 : 0));
        osc_set_preference('reports_notify_admin', (Params::getParam('reports_notify_admin') != '' ? 1 : 0));
        osc_set_preference('reports_notify_reporter_created', (Params::getParam('reports_notify_reporter_created') != '' ? 1 : 0));
        osc_set_preference('reports_notify_reporter_resolved', (Params::getParam('reports_notify_reporter_resolved') != '' ? 1 : 0));
        osc_set_preference('reports_notify_owner_created', (Params::getParam('reports_notify_owner_created') != '' ? 1 : 0));
        osc_set_preference('reports_notify_owner_resolved', (Params::getParam('reports_notify_owner_resolved') != '' ? 1 : 0));
        osc_set_preference('reports_notify_comments', (Params::getParam('reports_notify_comments') != '' ? 1 : 0));
        osc_set_preference('reports_enable_feedback', (Params::getParam('reports_enable_feedback') != '' ? 1 : 0));
        osc_set_preference('reports_auto_close_enabled', (Params::getParam('reports_auto_close_enabled') != '' ? 1 : 0));
        osc_set_preference('reports_auto_close_days', (int)Params::getParam('reports_auto_close_days'));
        osc_set_preference('reports_retention_months', (int)Params::getParam('reports_retention_months'));
        osc_set_preference('reports_attachment_enabled', (Params::getParam('reports_attachment_enabled') != '' ? 1 : 0));
        osc_set_preference('reports_attachment_extensions', strtolower(trim((string)Params::getParam('reports_attachment_extensions'))));

        $aReasons = Params::getParam('reports_enabled_reasons');
        $aReasons = (is_array($aReasons) ? $aReasons : array());
        $validReasons = array();
        foreach($aReasons as $code) {
          $code = trim((string)$code);
          if($code !== '' && in_array($code, array_keys(osc_report_reasons()), true)) {
            $validReasons[] = $code;
          }
        }
        foreach(osc_report_required_reasons() as $code) {
          if(in_array($code, array_keys(osc_report_reasons()), true) && !in_array($code, $validReasons, true)) {
            $validReasons[] = $code;
          }
        }
        osc_set_preference('reports_enabled_reasons', implode(',', $validReasons));

        $aStatuses = Params::getParam('reports_enabled_statuses');
        $aStatuses = (is_array($aStatuses) ? $aStatuses : array());
        $validStatuses = array();
        foreach($aStatuses as $code) {
          $code = trim((string)$code);
          if($code !== '' && in_array($code, array_keys(osc_report_statuses()), true)) {
            $validStatuses[] = $code;
          }
        }
        foreach(osc_report_required_statuses() as $code) {
          if(in_array($code, array_keys(osc_report_statuses()), true) && !in_array($code, $validStatuses, true)) {
            $validStatuses[] = $code;
          }
        }
        osc_set_preference('reports_enabled_statuses', implode(',', $validStatuses));

        osc_run_hook('reports_settings_post');

        osc_add_flash_ok_message(_m('Reports settings have been updated'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . "?page=reports&action=settings");
        break;

      default:
        require_once osc_lib_path()."osclass/classes/datatables/ReportsDataTable.php";

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
          Params::setParam('sort', 'last_update');
        }
        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'desc');
        }

        $page = (int)Params::getParam('iPage');
        if($page==0) { $page = 1; }
        Params::setParam('iPage', $page);

        $params = Params::getParamsAsArray();

        $reportsDataTable = new ReportsDataTable();
        $reportsDataTable->table($params);
        $aData = $reportsDataTable->getData();

        if(count($aData['aRows']) == 0 && $page!=1) {
          $total = (int)$aData['iTotalDisplayRecords'];
          $maxPage = ceil($total / (int)$aData['iDisplayLength']);

          $url = osc_admin_base_url(true).'?'.Params::getServerParam('QUERY_STRING', false, false);

          if($maxPage==0) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage=1', $url);
            $this->redirectTo($url);
          }

          if($page > 1) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage='.$maxPage, $url);
            $this->redirectTo($url);
          }
        }


        $this->_exportVariableToView('aData', $aData);
        $this->_exportVariableToView('aRawRows', $reportsDataTable->rawRows());

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array('value' => 'delete_all', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected reports?'), strtolower(__('Delete'))), 'label' => __('Delete')),
          array('value' => 'resolve_all', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected reports?'), strtolower(__('Resolve'))), 'label' => __('Resolve')),
          array('value' => 'reject_all', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected reports?'), strtolower(__('Reject'))), 'label' => __('Reject')),
          array('value' => 'cancel_all', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected reports?'), strtolower(__('Cancel'))), 'label' => __('Cancel')),
          array('value' => 'review_all', 'data-dialog-content' => sprintf(__('Are you sure you want to move the selected reports to %s?'), strtolower(osc_report_status_label('in_review'))), 'label' => osc_report_status_label('in_review'))
       );

        $bulk_options = osc_apply_filter("report_bulk_filter", $bulk_options);
        $this->_exportVariableToView('bulk_options', $bulk_options);

        $this->doView('reports/index.php');
        break;

    }
  }

  //hopefully generic...
  function doView($file) {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }

  // Change report status and send related notifications
  // $notify = null uses preferences; array('reporter'=>bool,'reported'=>bool) overrides
  function changeStatus($id, $status, $notify = null) {
    $report = $this->reportManager->findByPrimaryKey($id);

    if(!$report || $report['s_status'] == $status) {
      return false;
    }

    if(!$this->reportManager->updateStatus($id, $status, 'admin', osc_logged_admin_id())) {
      return false;
    }

    $report = $this->reportManager->findByPrimaryKey($id);
    $status = $report['s_status'];

    $notify_reporter = ($notify === null ? osc_reports_notify_reporter_resolved() : !empty($notify['reporter']));
    $notify_reported = ($notify === null ? osc_reports_notify_owner_resolved() : !empty($notify['reported']));

    if(osc_report_status_is_closed($status)) {
      if($notify_reporter) {
        osc_run_hook('hook_email_report_reporter_resolved', $report);
      }

      if($notify_reported) {
        osc_run_hook('hook_email_report_owner_resolved', $report);
      }
    } else if($status == 'awaiting_feedback' && $report['fk_i_user_id'] > 0 && osc_reports_feedback_enabled()) {
      if($notify === null || $notify_reported) {
        osc_run_hook('hook_email_report_feedback_request', $report);
      }
    }

    osc_run_hook('report_status_change', $report, $status);

    return true;
  }
}

/* file end: ./oc-admin/reports.php */

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
 * Class CWebReport
 */
class CWebReport extends BaseModel {
  public function __construct() {
    parent::__construct();
    osc_run_hook('init_report');
  }

  //Business Layer...
  public function doModel() {
    // Contact form conversations stay reachable when only contact messages are stored as reports
    $conversation = in_array($this->action, array('view', 'comment_post', 'comments', 'status_post'));
    if(!osc_reports_enabled() && !($conversation && osc_web_contact_create_report())) {
      osc_add_flash_warning_message(_m('Sorry, reports are disabled.'));
      $this->redirectTo($this->itemReportRedirectUrl());
    }

    if(!osc_ensure_reports_tables()) {
      osc_add_flash_error_message(_m('Reports are temporarily unavailable.'));
      $this->redirectTo($this->itemReportRedirectUrl());
    }

    if(!osc_is_web_user_logged_in()) {
      // Conversation actions check access themselves (admin, reporter or reported user, logged in or with access key)
      if($conversation) {
        // continue
      } else {
        $id = (int)Params::getParam('id');
        $redirect_url = '';

        if($this->action == 'item' || ($this->action == 'report_post' && Params::getParam('type') == 'item')) {
          osc_add_flash_warning_message(_m('You must be logged in to report a listing.'));
          $item = Item::newInstance()->findByPrimaryKey($id);
          if($item) {
            $redirect_url = osc_item_url_from_item($item);
          }
        } else if($this->action == 'user' || ($this->action == 'report_post' && Params::getParam('type') == 'user')) {
          osc_add_flash_warning_message(_m('You must be logged in to report a user.'));
          $user = User::newInstance()->findByPrimaryKey($id);
          if($user) {
            $redirect_url = osc_user_public_profile_url($id);
          }
        } else {
          osc_add_flash_warning_message(_m('You must be logged in to report content.'));
        }

        if($redirect_url == '') {
          $referer = osc_get_http_referer();
          if($referer != '' && strpos($referer, '/report/') === false && strpos($referer, 'page=report') === false) {
            $redirect_url = $referer;
          } else {
            $redirect_url = osc_base_url();
          }
        }

        $this->redirectTo($redirect_url);
      }
    }

    switch($this->action) {
      case 'report_post':
        osc_csrf_check();

        if(osc_recaptcha_enabled() && osc_recaptcha_private_key() != '') {
          if(!osc_check_recaptcha()) {
            osc_add_flash_error_message(_m('Recaptcha validation has failed'));
            $this->redirectTo($this->itemReportRedirectUrl());
            return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
          }
        }

        osc_run_hook('report_post');

        $type = osc_esc_html(Params::getParam('type'));
        $reason = osc_esc_html(Params::getParam('reason'));
        $source = osc_esc_html(Params::getParam('source'));
        $comment = trim(strip_tags((string)Params::getParam('comment')));
        $id = (int)Params::getParam('id');
        $reportedId = (int)Params::getParam('reportedId');

        $can_submit = osc_apply_filter('report_can_submit', true, $type, $id, $reportedId, $source);
        if(!$can_submit) {
          osc_add_flash_error_message(_m('You cannot submit this report.'));
          $this->redirectTo($this->itemReportRedirectUrl());
        }

        if(!in_array($type, array_keys(osc_report_types())) || !in_array($reason, array_keys(osc_report_reasons_for_type($type)))) {
          osc_add_flash_error_message(_m('Invalid report data.'));
          $this->redirectTo($this->itemReportRedirectUrl());
        }

        if($type == 'webcontact') {
          osc_add_flash_error_message(_m('Invalid report data.'));
          $this->redirectTo($this->itemReportRedirectUrl());
        }

        if($source == '' || !in_array($source, array_keys(osc_report_sources()))) {
          $source = 'osclass';
        }

        if($comment == '' || osc_strlen($comment) < 3) {
          osc_add_flash_error_message(_m('Please describe reason of your report.'));
          $this->redirectTo(osc_report_url($type, $id, ($reportedId > 0 ? $reportedId : null), $source));
        }

        if(osc_strlen($comment) > 2000) {
          $comment = osc_substr($comment, 0, 2000);
        }

        // Resolve report target
        $data = array(
          'fk_i_reporter_user_id' => osc_logged_user_id(),
          'fk_c_locale_code' => osc_current_user_locale(),
          's_type' => $type,
          's_reason' => $reason,
          's_source' => $source,
          's_comment' => $comment
        );

        $redirect_url = osc_base_url();

        if($type == 'item') {
          $item = $this->loadItemForReport($id);

          View::newInstance()->_exportVariableToView('item', $item);
          $redirect_url = osc_item_url_from_item($item);

          if((int)$item['fk_i_user_id'] === (int)osc_logged_user_id() || (osc_logged_user_email() != '' && isset($item['s_contact_email']) && strcasecmp((string)$item['s_contact_email'], (string)osc_logged_user_email()) === 0)) {
            osc_add_flash_error_message(_m('You cannot report your own listing.'));
            $this->redirectTo($redirect_url);
          }

          $data['fk_i_item_id'] = $item['pk_i_id'];
          $data['fk_i_user_id'] = ($item['fk_i_user_id'] > 0 ? $item['fk_i_user_id'] : null);

        } else if($type == 'user') {
          $user = User::newInstance()->findByPrimaryKey($id);

          if(!$user || $user['b_enabled'] == 0 || $user['b_active'] == 0) {
            osc_add_flash_error_message(_m('User you are trying to report does not exist.'));
            $this->redirectTo(osc_base_url());
          }

          $redirect_url = osc_user_public_profile_url($user['pk_i_id']);

          if((int)$user['pk_i_id'] === (int)osc_logged_user_id()) {
            osc_add_flash_error_message(_m('You cannot report yourself.'));
            $this->redirectTo($redirect_url);
          }

          $data['fk_i_user_id'] = $user['pk_i_id'];

        } else {
          // Plugin types (auction, blog article, transaction, ...)
          $data['i_reported_id'] = ($reportedId > 0 ? $reportedId : $id);
        }

        // Daily limit per user
        if(Report::newInstance()->countByUserToday(osc_logged_user_id()) >= osc_reports_per_day()) {
          osc_add_flash_warning_message(_m('You have reached the maximum number of reports for today, no more reports are possible at this moment.'));
          $this->redirectTo($redirect_url);
        }

        // Duplicate check - same user cannot report same object twice (unless allowed)
        if(!osc_reports_allow_multiple()) {
          $duplicate = Report::newInstance()->findExistingReport(osc_logged_user_id(), $type, @$data['fk_i_item_id'], ($type == 'user' ? @$data['fk_i_user_id'] : null), @$data['i_reported_id']);
          if($duplicate) {
            $this->redirectTo(osc_report_duplicate_redirect_url($type, $duplicate, $redirect_url));
          }
        }

        $flash_error = '';
        $flash_error = osc_apply_filter('pre_report_add_error', $flash_error, $data);
        if($flash_error != '') {
          osc_add_flash_error_message($flash_error);
          $this->redirectTo($redirect_url);
        }

        osc_run_hook('pre_report_add', $data);

        // Attachment upload
        $attachment_ext = false;
        $attachment_tmp = false;

        if(osc_reports_attachment_enabled()) {
          $attachment = Params::getFiles('attachment');

          if(!empty($attachment) && isset($attachment['error']) && $attachment['error'] == UPLOAD_ERR_OK) {
            $attachment_ext = strtolower(pathinfo($attachment['name'], PATHINFO_EXTENSION));
            $attachment_tmp = $attachment['tmp_name'];

            $resourceType = $attachment['type'];

            if(function_exists('mime_content_type')) {
              $resourceType = mime_content_type($attachment_tmp);
            }

            if(function_exists('finfo_open')) {
              $finfo = finfo_open(FILEINFO_MIME);
              $output = finfo_file($finfo, $attachment_tmp);
              finfo_close($finfo);

              $output = explode('; ', $output);

              if(is_array($output)) {
                $output = $output[0];
              }

              $resourceType = $output;
            }

            $size_ok = ((int)$attachment['size'] <= osc_reports_attachment_max_size_mb() * 1024 * 1024);
            $ext_ok = in_array($attachment_ext, osc_reports_attachment_extensions());
            $mime_ok = in_array($resourceType, osc_allowed_mime_types());
            $image_ok = (in_array($attachment_ext, array('jpg', 'jpeg', 'png', 'gif', 'webp')) ? (@getimagesize($attachment_tmp) !== false) : true);

            if(!$size_ok || !$ext_ok || !$mime_ok || !$image_ok) {
              osc_add_flash_error_message(_m('Attached file is not allowed, check its type and size.'));
              $this->redirectTo(osc_report_url($type, $id, ($reportedId > 0 ? $reportedId : null), $source));
            }
          } else {
            $attachment_ext = false;
          }
        }

        $data = osc_apply_filter('report_data', $data);

        $attachment_dir = osc_uploads_path() . 'report/';
        $reportId = osc_report_create($data, true, function($reportId) use ($data, $attachment_ext, $attachment_tmp, $attachment_dir) {
          if(isset($data['fk_i_item_id']) && (int)$data['fk_i_item_id'] > 0) {
            osc_increase_item_stat('reports', (int)$data['fk_i_item_id']);
          }

          // Store attachment renamed to {report_id}_{random 8 chars}.{ext}
          if($attachment_ext !== false && $attachment_tmp !== false) {
            if(!file_exists($attachment_dir)) {
              @mkdir($attachment_dir, 0755, true);
              @file_put_contents($attachment_dir . 'index.php', '<?php // Silence is golden');
            }

            $file_name = $reportId . '_' . strtolower(osc_genRandomPassword(8)) . '.' . $attachment_ext;

            if(@move_uploaded_file($attachment_tmp, $attachment_dir . $file_name)) {
              Report::newInstance()->update(array('s_file' => $file_name), array('pk_i_id' => $reportId));
            }
          }
        });

        if(!$reportId) {
          osc_add_flash_error_message(_m('There was a problem submitting your report, please try again later.'));
          $this->redirectTo($redirect_url);
        }

        $report = Report::newInstance()->findByPrimaryKey($reportId);

        if(osc_is_admin_user_logged_in() && is_array($report) && (int)$report['pk_i_id'] > 0) {
          osc_add_flash_ok_message(sprintf(_m('Report #%d has been submitted.'), (int)$report['pk_i_id']));
          $redirect_url = osc_report_view_url($report['pk_i_id']);
        } else {
          osc_add_flash_ok_message(_m('Thank you, your report has been submitted and our team will review it.'));
        }
        $redirect_url = osc_apply_filter('report_post_redirect_url', $redirect_url, $report);
        $this->redirectTo($redirect_url);
        break;

      case 'view':
        $report = $this->loadReportForViewer();
        $viewer = osc_report_viewer($report);

        // Admin reading the conversation on front marks replies as seen; every visit stops reply emails while the person is here
        if($viewer['role'] == 'admin') {
          Report::newInstance()->markCommentsSeen($report['pk_i_id']);
        }
        osc_report_mark_seen($report, $viewer['role']);

        $this->_exportVariableToView('report', $report);
        $this->_exportVariableToView('report_viewer', $viewer);
        $this->_exportVariableToView('report_comments', osc_report_front_comments(Report::newInstance()->getComments($report['pk_i_id']), $report));
        $this->_exportVariableToView('report_mode', 'view');

        osc_run_hook('before_report_view', $report);
        $this->doView('report.php');
        break;

      // New messages and status for live refresh (ajax): comments newer than "after" id that this viewer may see
      case 'comments':
        $report = $this->loadReportForViewer();
        $viewer = osc_report_viewer($report);
        if($viewer['role'] == 'admin') {
          Report::newInstance()->markCommentsSeen($report['pk_i_id']);
        }
        osc_report_mark_seen($report, $viewer['role']);

        $this->ajaxResponse($report, osc_report_front_comments(Report::newInstance()->getComments($report['pk_i_id'], (int)Params::getParam('after')), $report));
        break;

      case 'comment_post':
        osc_csrf_check();

        $report = $this->loadReportForViewer();
        $viewer = osc_report_viewer($report);
        $is_ajax = (Params::getParam('ajax') != '');

        osc_run_hook('report_comment_post', $report);

        $error = '';
        if($viewer['role'] != 'admin' && !osc_reports_feedback_enabled()) {
          $error = _m('Sorry, replies to reports are disabled.');
        } else if(!osc_report_user_can_comment($report)) {
          $error = ((int)$report['b_open'] === 0 ? _m('This report has been closed, replies are not possible anymore.') : _m('You cannot reply to this report.'));
        }

        $comment = trim(strip_tags((string)Params::getParam('comment')));
        if($error == '' && ($comment == '' || osc_strlen($comment) < 2)) {
          $error = _m('Please enter your reply.');
        }

        if($error != '') {
          if($is_ajax) {
            $this->ajaxResponse($report, array(), $error);
          }
          osc_add_flash_warning_message($error);
          $this->redirectTo(osc_report_view_url($report['pk_i_id']));
        }

        if(osc_strlen($comment) > 2000) {
          $comment = osc_substr($comment, 0, 2000);
        }

        osc_run_hook('before_add_report_comment', $report, $comment);
        $comment = osc_apply_filter('report_comment_insert_data', $comment, $report);

        // Admin writes to the party picked in the form (or the default one), reporter and reported user to their own conversation
        if($viewer['role'] == 'admin') {
          $party = Params::getParam('party');
          $party = ($party == 'reporter' || $party == 'reported' ? $party : osc_report_default_party($report));
          $commentId = Report::newInstance()->addComment($report['pk_i_id'], $comment, null, $viewer['admin_id'], $party);
        } else {
          $party = $viewer['role'];
          $commentId = Report::newInstance()->addComment($report['pk_i_id'], $comment, $viewer['user_id'], null, $party);
        }

        if(!$commentId) {
          $error = _m('There was a problem submitting your reply, please try again later.');
          if($is_ajax) {
            $this->ajaxResponse($report, array(), $error);
          }
          osc_add_flash_error_message($error);
          $this->redirectTo(osc_report_view_url($report['pk_i_id']));
        }

        // Whose turn it is: after customer reply the admin, after admin reply the customer (paused report keeps its status)
        $after_status = ($viewer['role'] == 'admin' ? 'waiting_customer' : 'waiting_admin');
        if((int)$report['b_open'] === 1 && $report['s_status'] != 'on_hold' && $report['s_status'] != $after_status) {
          Report::newInstance()->updateStatus($report['pk_i_id'], $after_status, ($viewer['role'] == 'admin' ? 'admin' : 'user'), ($viewer['role'] == 'admin' ? $viewer['admin_id'] : $viewer['user_id']), false);
          $report = Report::newInstance()->findByPrimaryKey($report['pk_i_id']);
          osc_run_hook('report_status_change', $report, $report['s_status']);
        }

        $commentRow = Report::newInstance()->findCommentByPrimaryKey($commentId);
        osc_report_mark_seen(Report::newInstance()->findByPrimaryKey($report['pk_i_id']), $viewer['role']);
        osc_run_hook('add_report_comment', $report, $commentRow);
        osc_run_hook('after_add_report_comment', $report, $commentRow);

        // Customer reply goes to admins, admin reply to the party it was written to (smart: see osc_report_notify_allowed)
        if(osc_reports_notify_comments()) {
          if($viewer['role'] == 'admin') {
            $contact = osc_report_party_contact($report, $party);
            osc_run_hook('hook_email_report_new_comment_user', $report, $commentRow, (int)$contact['user_id']);
          } else {
            osc_run_hook('hook_email_report_new_comment', $report, $commentRow);
          }
        }

        if($is_ajax) {
          $this->ajaxResponse($report, osc_report_front_comments(Report::newInstance()->getComments($report['pk_i_id'], (int)Params::getParam('after')), $report));
        }

        osc_add_flash_ok_message(_m('Your reply has been added.'));
        $redirect_url = osc_apply_filter('report_comment_post_redirect_url', osc_report_view_url($report['pk_i_id']), $report, $commentRow);
        $this->redirectTo($redirect_url);
        break;

      // Admin on front (logged in or with admin access key) changes status, e.g. resolves or reopens the report
      case 'status_post':
        osc_csrf_check();

        $report = $this->loadReportForViewer();
        $viewer = osc_report_viewer($report);
        $status = osc_report_status_code(Params::getParam('status'));
        $is_ajax = (Params::getParam('ajax') != '');

        if($viewer['role'] != 'admin' || !in_array($status, array_keys(osc_report_statuses_enabled($report['s_status'])), true)) {
          if($is_ajax) {
            $this->ajaxResponse($report, array(), _m('You cannot change the status of this report.'));
          }
          osc_add_flash_warning_message(_m('You cannot change the status of this report.'));
          $this->redirectTo(osc_report_view_url($report['pk_i_id']));
        }

        if($status != $report['s_status'] && Report::newInstance()->updateStatus($report['pk_i_id'], $status, 'admin', $viewer['admin_id'])) {
          $report = Report::newInstance()->findByPrimaryKey($report['pk_i_id']);
          if(osc_report_status_is_closed($status)) {
            if(osc_reports_notify_reporter_resolved()) {
              osc_run_hook('hook_email_report_reporter_resolved', $report);
            }
            if(osc_reports_notify_owner_resolved()) {
              osc_run_hook('hook_email_report_owner_resolved', $report);
            }
          }
          osc_run_hook('report_status_change', $report, $status);
        }

        if($is_ajax) {
          $this->ajaxResponse($report, array());
        }

        osc_add_flash_ok_message(_m('The report status has been updated'));
        $this->redirectTo(osc_report_view_url($report['pk_i_id']));
        break;

      case 'user':
        $user = User::newInstance()->findByPrimaryKey(Params::getParam('id'));

        if(!$user || $user['b_enabled'] == 0 || $user['b_active'] == 0) {
          osc_add_flash_error_message(_m('User you are trying to report does not exist.'));
          $this->redirectTo(osc_base_url());
        }

        if((int)$user['pk_i_id'] === (int)osc_logged_user_id()) {
          osc_add_flash_error_message(_m('You cannot report yourself.'));
          $this->redirectTo(osc_user_public_profile_url($user['pk_i_id']));
        }

        $duplicate = false;
        if(!osc_reports_allow_multiple()) {
          $duplicate = Report::newInstance()->findExistingReport(osc_logged_user_id(), 'user', null, $user['pk_i_id'], null);
        }
        if($duplicate) {
          $this->redirectTo(osc_report_duplicate_redirect_url('user', $duplicate, osc_user_public_profile_url($user['pk_i_id'])));
        }

        $this->_exportVariableToView('user', $user);
        $this->_exportVariableToView('report_type', 'user');
        $this->_exportVariableToView('report_target_id', $user['pk_i_id']);
        $this->_exportVariableToView('report_mode', 'form');

        osc_run_hook('before_report_form', 'user', $user['pk_i_id']);
        $this->doView('report.php');
        break;

      case 'item':
      default:
        $item = $this->loadItemForReport(Params::getParam('id'));

        View::newInstance()->_exportVariableToView('item', $item);

        if((int)$item['fk_i_user_id'] === (int)osc_logged_user_id() || (osc_logged_user_email() != '' && isset($item['s_contact_email']) && strcasecmp((string)$item['s_contact_email'], (string)osc_logged_user_email()) === 0)) {
          osc_add_flash_error_message(_m('You cannot report your own listing.'));
          $this->redirectTo(osc_item_url());
        }

        $duplicate = false;
        if(!osc_reports_allow_multiple()) {
          $duplicate = Report::newInstance()->findExistingReport(osc_logged_user_id(), 'item', $item['pk_i_id'], null, null);
        }
        if($duplicate) {
          $this->redirectTo(osc_report_duplicate_redirect_url('item', $duplicate, osc_item_url()));
        }

        $this->_exportVariableToView('report_type', 'item');
        $this->_exportVariableToView('report_target_id', $item['pk_i_id']);
        $this->_exportVariableToView('report_mode', 'form');

        osc_run_hook('before_report_form', 'item', $item['pk_i_id']);
        $this->doView('report.php');
        break;
    }
  }

  // Load report for conversation actions, or leave when current visitor (admin, reporter, reported user, access key) has no access
  private function loadReportForViewer() {
    $report = Report::newInstance()->findByPrimaryKey((int)Params::getParam('id'));

    if(!is_array($report) || !osc_report_user_can_view($report)) {
      if(Params::getParam('ajax') != '' || $this->action == 'comments') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(array('ok' => false, 'error' => _m('You can only view reports related to you.')));
        exit;
      }

      if(!osc_is_web_user_logged_in() && !osc_is_admin_user_logged_in()) {
        osc_add_flash_warning_message(_m('Log in, or open the report from the link in your email.'));
        $this->redirectTo(osc_user_login_url());
      }

      osc_add_flash_warning_message(_m('You can only view reports related to you.'));
      $this->redirectTo(osc_base_url());
    }

    return $report;
  }

  // Print conversation state as JSON (comments as plain data) and end the request
  private function ajaxResponse($report, $comments, $error = '') {
    $items = array();
    foreach($comments as $comment) {
      $items[] = osc_report_comment_data($comment, $report);
    }

    $out = array(
      'ok' => ($error == ''),
      'error' => $error,
      'status' => osc_report_viewer_status($report),
      'status_label' => osc_report_status_label(osc_report_viewer_status($report)),
      'open' => ((int)$report['b_open'] === 1),
      'can_comment' => osc_report_user_can_comment($report),
      'comments' => $items
    );

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(osc_apply_filter('report_ajax_response', $out, $report));
    exit;
  }

  // Load listing for report, or redirect with flash if it is missing or not public
  private function loadItemForReport($id) {
    $item = osc_get_item_row($id);
    if(!$item) {
      osc_add_flash_error_message(_m('Listing you are trying to report does not exist.'));
      $this->redirectTo(osc_base_url());
    }

    if($item['b_enabled'] == 0 || $item['b_active'] == 0) {
      if($item['b_enabled'] == 0) {
        osc_add_flash_warning_message(_m('This listing is not enabled and cannot be reported.'));
      } else {
        osc_add_flash_warning_message(_m('This listing is not active and cannot be reported.'));
      }
      $this->redirectTo(osc_item_url_from_item($item));
    }

    return $item;
  }

  // Redirect to the listing when this request is a listing report and the listing exists
  private function itemReportRedirectUrl($item = null) {
    if($this->action != 'item' && $this->action != '' && $this->action != 'mark' && !($this->action == 'report_post' && Params::getParam('type') == 'item')) {
      return osc_base_url();
    }

    if(!is_array($item) || !isset($item['pk_i_id'])) {
      $item = osc_get_item_row((int)Params::getParam('id'));
    }

    if(is_array($item) && isset($item['pk_i_id'])) {
      return osc_item_url_from_item($item);
    }

    return osc_base_url();
  }

  //hopefully generic...
  function doView($file) {
    osc_run_hook("before_html");
    osc_current_web_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_html");
  }
}

/* file end: ./report.php */

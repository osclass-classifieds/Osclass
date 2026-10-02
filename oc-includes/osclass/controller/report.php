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
    if(!osc_reports_enabled()) {
      osc_add_flash_warning_message(_m('Sorry, reports are disabled.'));
      $this->redirectTo($this->itemReportRedirectUrl());
    }

    if(!osc_ensure_reports_tables()) {
      osc_add_flash_error_message(_m('Reports are temporarily unavailable.'));
      $this->redirectTo($this->itemReportRedirectUrl());
    }

    if(!osc_is_web_user_logged_in()) {
      // Admin session alone can open report view on front
      if(osc_is_admin_user_logged_in() && ($this->action == 'view' || $this->action == 'comment_post')) {
        // continue
      } else {
        $id = (int)Params::getParam('id');
        $redirect_url = '';

        if($this->action == 'view' || $this->action == 'comment_post') {
          osc_add_flash_warning_message(_m('You must be logged in to view this report.'));
          $this->redirectTo(osc_base_url());
        } else if($this->action == 'item' || ($this->action == 'report_post' && Params::getParam('type') == 'item')) {
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

        $reportId = Report::newInstance()->createReport($data);

        if(!$reportId) {
          osc_add_flash_error_message(_m('There was a problem submitting your report, please try again later.'));
          $this->redirectTo($redirect_url);
        }

        if(isset($data['fk_i_item_id']) && (int)$data['fk_i_item_id'] > 0) {
          osc_increase_item_stat('reports', (int)$data['fk_i_item_id']);
        }

        // Store attachment renamed to {report_id}_{random 8 chars}.{ext}
        if($attachment_ext !== false && $attachment_tmp !== false) {
          $upload_dir = osc_uploads_path() . 'report/';

          if(!file_exists($upload_dir)) {
            @mkdir($upload_dir, 0755, true);
            @file_put_contents($upload_dir . 'index.php', '<?php // Silence is golden');
          }

          $file_name = $reportId . '_' . strtolower(osc_genRandomPassword(8)) . '.' . $attachment_ext;

          if(@move_uploaded_file($attachment_tmp, $upload_dir . $file_name)) {
            Report::newInstance()->update(array('s_file' => $file_name), array('pk_i_id' => $reportId));
          }
        }

        $report = Report::newInstance()->findByPrimaryKey($reportId);

        osc_run_hook('after_report_add', $report);
        osc_run_hook('posted_report', $report);

        // Notifications
        if(osc_reports_notify_admin()) {
          osc_run_hook('hook_email_report_admin', $report);
        }

        if(osc_reports_notify_reporter_created()) {
          osc_run_hook('hook_email_report_reporter_created', $report);
        }

        if(osc_reports_notify_owner_created()) {
          osc_run_hook('hook_email_report_owner_created', $report);
        }

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
        $report = Report::newInstance()->findByPrimaryKey(Params::getParam('id'));
        $userId = (int)osc_logged_user_id();

        if(!osc_report_user_can_view($report, $userId)) {
          osc_add_flash_warning_message(_m('You can only view reports related to you.'));
          $this->redirectTo(osc_base_url());
        }

        $this->_exportVariableToView('report', $report);
        $this->_exportVariableToView('report_comments', osc_report_front_comments(Report::newInstance()->getComments($report['pk_i_id']), $report));
        $this->_exportVariableToView('report_mode', 'view');

        osc_run_hook('before_report_view', $report);
        $this->doView('report.php');
        break;

      case 'comment_post':
        osc_csrf_check();

        if(!osc_reports_feedback_enabled()) {
          osc_add_flash_warning_message(_m('Sorry, replies to reports are disabled.'));
          $this->redirectTo(osc_base_url());
        }

        $report = Report::newInstance()->findByPrimaryKey(Params::getParam('id'));
        $userId = (int)osc_logged_user_id();

        if(!osc_report_user_can_view($report, $userId)) {
          osc_add_flash_warning_message(_m('You can only view reports related to you.'));
          $this->redirectTo(osc_base_url());
        }

        osc_run_hook('report_comment_post', $report);

        if(!osc_report_user_can_comment($report, $userId)) {
          if(is_array($report) && (int)$report['b_open'] === 0) {
            osc_add_flash_warning_message(_m('This report has been closed, replies are not possible anymore.'));
          } else {
            osc_add_flash_warning_message(_m('You can reply only when feedback was requested on this report.'));
          }
          $this->redirectTo(osc_report_view_url($report['pk_i_id']));
        }

        $comment = trim(strip_tags((string)Params::getParam('comment')));

        if($comment == '' || osc_strlen($comment) < 3) {
          osc_add_flash_error_message(_m('Please enter your reply.'));
          $this->redirectTo(osc_report_view_url($report['pk_i_id']));
        }

        if(osc_strlen($comment) > 2000) {
          $comment = osc_substr($comment, 0, 2000);
        }

        osc_run_hook('before_add_report_comment', $report, $comment);
        $comment = osc_apply_filter('report_comment_insert_data', $comment, $report);

        $commentId = Report::newInstance()->addComment($report['pk_i_id'], $comment, osc_logged_user_id());

        if(!$commentId) {
          osc_add_flash_error_message(_m('There was a problem submitting your reply, please try again later.'));
          $this->redirectTo(osc_report_view_url($report['pk_i_id']));
        }

        Report::newInstance()->updateStatus($report['pk_i_id'], 'in_review', 'user', osc_logged_user_id());
        $report = Report::newInstance()->findByPrimaryKey($report['pk_i_id']);
        osc_run_hook('report_status_change', $report, $report['s_status']);

        $commentRow = Report::newInstance()->findCommentByPrimaryKey($commentId);
        osc_run_hook('add_report_comment', $report, $commentRow);
        osc_run_hook('after_add_report_comment', $report, $commentRow);

        // Notify admin only - reporter must not receive conversation replies
        if(osc_reports_notify_comments()) {
          osc_run_hook('hook_email_report_new_comment', $report, $commentRow);
        }

        osc_add_flash_ok_message(_m('Your reply has been added.'));
        $redirect_url = osc_apply_filter('report_comment_post_redirect_url', osc_report_view_url($report['pk_i_id']), $report, $commentRow);
        $this->redirectTo($redirect_url);
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

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


class CAdminUsers extends AdminSecBaseModel {
  //specific for this class
  private $userManager;

  function __construct() {
    parent::__construct();

    //specific things for this class
    $this->userManager = User::newInstance();
  }

  //Business Layer...
  function doModel() {
    parent::doModel();

    //specific things for this class
    switch($this->action) {
      case('create'):     // calling create view
        $aRegions = array();
        $aCities = array();

        $aCountries = Country::newInstance()->listAll();

        if(isset($aCountries[0]['pk_c_code'])) {
          $aRegions = Region::newInstance()->findByCountry($aCountries[0]['pk_c_code']);
        }

        if(isset($aRegions[0]['pk_i_id'])) {
          $aCities = City::newInstance()->findByRegion($aRegions[0]['pk_i_id']);
        }

        $this->_exportVariableToView('user', null);
        $this->_exportVariableToView('countries', $aCountries);
        $this->_exportVariableToView('regions', $aRegions);
        $this->_exportVariableToView('cities', $aCities);
        $this->_exportVariableToView('locales', OSCLocale::newInstance()->listAllEnabled());

        $this->doView("users/frm.php");
        break;

      case('create_post'):  // creating the user...
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        $userActions = new UserActions(true);
        $success = $userActions->add();

        switch($success) {
          case 1:
            osc_add_flash_ok_message(_m("The user has been created. We've sent an activation e-mail"), 'admin');
            break;

          case 2:
            osc_add_flash_ok_message(_m('The user has been created successfully'), 'admin');
            break;

          default:
            osc_add_flash_error_message($success, 'admin');
            break;
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        break;

      case('edit'):       // calling the edit view
        $aUser = $this->userManager->findByPrimaryKey(Params::getParam("id"));
        $aCountries = Country::newInstance()->listAll();
        $aRegions = array();

        if($aUser['fk_c_country_code'] != '') {
          $aRegions = Region::newInstance()->findByCountry($aUser['fk_c_country_code']);
        } else if(count($aCountries) > 0) {
          $aRegions = Region::newInstance()->findByCountry($aCountries[0]['pk_c_code']);
        }

        $aCities = array();

        if($aUser['fk_i_region_id'] != '') {
          $aCities = City::newInstance()->findByRegion($aUser['fk_i_region_id']);
        } else if(count($aRegions) > 0) {
          $aCities = City::newInstance()->findByRegion($aRegions[0]['pk_i_id']);
        }

        $csrf_token = osc_csrf_token_url();

        if($aUser['b_active'] && $aUser['b_enabled']) {
          $publicProfileUrl = trim((string)osc_user_public_profile_url($aUser['pk_i_id'], $aUser));
          if($publicProfileUrl != '' && $publicProfileUrl != '#') {
            $actions[] = '<a class="btn float-left" href="' . $publicProfileUrl . '">' . __('Public profile') . '</a>';
          }
          $actions[] = '<a class="btn float-left" href="' . osc_admin_base_url(true) . '?page=users&action=login&amp;id=' . $aUser['pk_i_id'] . '&amp;' . $csrf_token . '" target="_blank">' . sprintf(__('Log in as %s'), osc_highlight($aUser['s_name'], 20)) . '</a>';
        }

        $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=items&user='.$aUser['s_username'].'&userId='.$aUser['pk_i_id'].'">'.__('View items') .'</a>';
        if(osc_alerts_enabled()) {
          $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=users&action=alerts&alertUserId='.$aUser['pk_i_id'].'">'.__('View alerts') .'</a>';
        }


        if($aUser['b_active']) {
          $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=users&action=deactivate&id[]='.$aUser['pk_i_id'].'&'.$csrf_token.'&value=INACTIVE">'.__('Deactivate') .'</a>';
          } else {
          $actions[] = '<a class="btn btn-red float-left" href="'.osc_admin_base_url(true).'?page=users&action=activate&id[]='.$aUser['pk_i_id'].'&'.$csrf_token.'&value=ACTIVE">'.__('Activate') .'</a>';
        }

        if($aUser['b_enabled']) {
          $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=users&action=disable&id[]='.$aUser['pk_i_id'].'&'.$csrf_token.'&value=DISABLE">'.__('Block') .'</a>';
          } else {
          $actions[] = '<a class="btn btn-red float-left" href="'.osc_admin_base_url(true).'?page=users&action=enable&id[]='.$aUser['pk_i_id'].'&'.$csrf_token.'&value=ENABLE">'.__('Unblock') .'</a>';
        }

        //$actions[]  = '<a class="btn btn-red float-left" onclick="return delete_dialog(\'' . $aUser['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=users&action=delete&amp;id[]=' . $aUser['pk_i_id'] . '">' . __('Delete') . '</a>';

        $aLocale = $aUser['locale'];
        if(is_array($aLocale) && count($aLocale) > 0) {
          foreach($aLocale as $locale => $aInfo) {
          $aUser['locale'][$locale]['s_info'] = osc_apply_filter('admin_user_profile_info', $aInfo['s_info'], $aUser['pk_i_id'], $aInfo['fk_c_locale_code']);
          }
        }

        $this->_exportVariableToView("actions", $actions);
        $this->_exportVariableToView("user", $aUser);
        $this->_exportVariableToView("countries", $aCountries);
        $this->_exportVariableToView("regions", $aRegions);
        $this->_exportVariableToView("cities", $aCities);
        $this->_exportVariableToView("locales", OSCLocale::newInstance()->listAllEnabled());
        $this->doView("users/frm.php");
        break;

      case('edit_post'):    // edit post
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        $userActions = new UserActions(true);
        $success = $userActions->edit(Params::getParam("id"));
        if($success==1) {
          osc_add_flash_ok_message(_m('The user has been updated'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id='.Params::getParam('id'));

        } else if($success==2) {
          osc_add_flash_ok_message(_m('The user has been updated and activated'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id='.Params::getParam('id'));

        } else {
          osc_add_flash_error_message($success);
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id='.Params::getParam('id'));
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        break;

      case('message_user_post'):
        osc_csrf_check();

        $userId = (int)Params::getParam('id');
        $aUser = $this->userManager->findByPrimaryKey($userId);

        if(!isset($aUser['pk_i_id']) || (int)$aUser['pk_i_id'] <= 0) {
          osc_add_flash_error_message(_m("User doesn't exist"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        $subject = trim((string)Params::getParam('message_subject'));
        $bodyRaw = trim((string)Params::getParam('message_body'));

        if($subject == '' || $bodyRaw == '') {
          osc_add_flash_error_message(_m('Email title and body are required'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id=' . $userId . '&open_message=1');
        }

        $words = array();
        $words[] = array(
          '{USER_NAME}',
          '{USER_EMAIL}',
          '{ADMIN_NAME}',
          '{ADMIN_EMAIL}'
        );

        $words[] = array(
          trim((string)$aUser['s_name']) != '' ? (string)$aUser['s_name'] : (string)$aUser['s_email'],
          (string)$aUser['s_email'],
          osc_logged_admin_name(),
          osc_logged_admin_email()
        );

        $subjectPrepared = osc_mailBeauty($subject, $words);
        $bodyPrepared = osc_mailBeauty($bodyRaw, $words);
        $bodyHtml = nl2br($bodyPrepared);

        $attachmentTmpPath = null;
        $attachmentName = '';
        $attachmentUploaded = false;
        $attachment = Params::getFiles('attachment');

        if(!empty($attachment) && isset($attachment['error']) && (int)$attachment['error'] == UPLOAD_ERR_OK) {
          $tmpName = $attachment['tmp_name'];
          $resourceName = basename((string)$attachment['name']);
          $resourceType = (isset($attachment['type']) ? $attachment['type'] : '');

          if(function_exists('mime_content_type')) {
            $resourceType = mime_content_type($tmpName);
          }

          if(function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $output = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
            $output = explode('; ', $output);

            if(is_array($output)) {
              $output = $output[0];
            }

            $resourceType = $output;
          }

          if(!in_array($resourceType, osc_allowed_mime_types())) {
            osc_add_flash_warning_message(_m('Attachment had incorrect extension or mime type and has not been attached to message'), 'admin');
          } else if(!is_writable(osc_uploads_path())) {
            osc_add_flash_warning_message(_m('Uploads folder is not writable, attachment has not been attached to message'), 'admin');
          } else {
            $attachmentTmpPath = osc_uploads_path() . 'tmp_admin_message_' . time() . '_' . mt_rand(1000, 9999) . '_' . $resourceName;

            if(!@move_uploaded_file($tmpName, $attachmentTmpPath)) {
              $attachmentTmpPath = null;
              osc_add_flash_warning_message(_m('Attachment could not be moved to uploads folder and has not been attached to message'), 'admin');
            } else {
              $attachmentUploaded = true;
              $attachmentName = $resourceName;
            }
          }
        }

        $from = trim((string)osc_mailserver_mail_from()) != '' ? (string)osc_mailserver_mail_from() : (string)osc_contact_email();
        $fromName = trim((string)osc_mailserver_name_from()) != '' ? (string)osc_mailserver_name_from() : (string)osc_page_title();

        $emailParams = array(
          'from' => $from,
          'from_name' => $fromName,
          'to' => (string)$aUser['s_email'],
          'to_name' => (trim((string)$aUser['s_name']) != '' ? (string)$aUser['s_name'] : (string)$aUser['s_email']),
          'reply_to' => osc_logged_admin_email(),
          'subject' => $subjectPrepared,
          'body' => $bodyHtml,
          'alt_body' => $bodyPrepared
        );

        if($attachmentUploaded === true && $attachmentTmpPath !== null) {
          $emailParams['attachment'] = array(
            'path' => $attachmentTmpPath,
            'name' => $attachmentName
          );
        }

        $sent = osc_sendMail($emailParams, 'admin_message_user');

        if($attachmentTmpPath !== null) {
          @unlink($attachmentTmpPath);
        }

        Log::newInstance()->insertLog(
          'user',
          'message',
          $userId,
          (string)$aUser['s_email'],
          'admin',
          osc_logged_admin_id(),
          $subjectPrepared,
          array(
            'result' => ($sent ? 'sent' : 'failed'),
            'attachment' => ($attachmentUploaded ? $attachmentName : ''),
            'recipient_name' => (string)$aUser['s_name'],
            'title' => $subjectPrepared,
            'body' => $bodyPrepared
          )
        );

        if($sent) {
          osc_add_flash_ok_message(_m('Message has been sent to user'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id=' . $userId);
        } else {
          osc_add_flash_error_message(_m('Unable to send message to user'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id=' . $userId . '&open_message=1');
        }
        break;

      case('resend_activation'):
        //activate
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        $iUpdated = 0;
        $userId = Params::getParam('id');

        if(!is_array($userId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        $userActions = new UserActions(true);
        foreach($userId as $id) {
          $iUpdated   += $userActions->resend_activation($id);
        }

        if($iUpdated==0) {
          osc_add_flash_error_message(_m('No users have been selected'), 'admin');
        } else {
          osc_add_flash_ok_message(sprintf(_mn('Activation email sent to one user', 'Activation email sent to %s users', $iUpdated), $iUpdated), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        break;

      case('activate'):     //activate
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        $iUpdated = 0;
        $userId = Params::getParam('id');
        if(!is_array($userId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        $userActions = new UserActions(true);
        foreach($userId as $id) {
          $iUpdated   += $userActions->activate($id);
        }

        if($iUpdated == 0) {
          $msg = _m('No users have been activated');
        } else {
          $msg = sprintf(_mn('One user has been activated', '%s users have been activated', $iUpdated), $iUpdated);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(Params::getServerParam('HTTP_REFERER', false, false));
        break;

      case('deactivate'):   //deactivate
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        $iUpdated = 0;
        $userId = Params::getParam('id');

        if(!is_array($userId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        $userActions = new UserActions(true);
        foreach($userId as $id) {
          $iUpdated += $userActions->deactivate($id);
        }

        if($iUpdated == 0) {
          $msg = _m('No users have been deactivated');
        } else {
          $msg = sprintf(_mn('One user has been deactivated', '%s users have been deactivated', $iUpdated), $iUpdated);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(Params::getServerParam('HTTP_REFERER', false, false));
        break;

      case('enable'):
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        $iUpdated = 0;
        $userId = Params::getParam('id');
        if(!is_array($userId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        $userActions = new UserActions(true);
        foreach($userId as $id) {
          $iUpdated += $userActions->enable($id);
        }

        if($iUpdated == 0) {
          $msg = _m('No users have been enabled');
        } else {
          $msg = sprintf(_mn('One user has been unblocked', '%s users have been unblocked', $iUpdated), $iUpdated);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(Params::getServerParam('HTTP_REFERER', false, false));
        break;

      case('disable'):
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        $iUpdated = 0;
        $userId = Params::getParam('id');
        if(!is_array($userId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        $userActions = new UserActions(true);
        foreach($userId as $id) {
          $iUpdated   += $userActions->disable($id);
        }

        if($iUpdated == 0) {
          $msg = _m('No users have been disabled');
        } else {
          $msg = sprintf(_mn('One user has been blocked', '%s users have been blocked', $iUpdated), $iUpdated);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(Params::getServerParam('HTTP_REFERER', false, false));
        break;

      case('enable_items'):
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        require_once LIB_PATH . 'osclass/ItemActions.php';

        $iUpdated = 0;
        $userId = Params::getParam('id');

        if(!is_array($userId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        $itemActions = new ItemActions(true);
        foreach($userId as $id) {
          $items = Item::newInstance()->findByUserID($id);

          if(is_array($items) && count($items) > 0) {
            foreach($items as $item) {
              if($item['b_enabled'] == 0) {
                $iUpdated += $itemActions->enable($item['pk_i_id']);
              }
            }
          }
        }

        if($iUpdated == 0) {
          $msg = _m('No users listings have been unblocked');
        } else {
          $msg = sprintf(_mn('One user listing has been unblocked', '%s user listings have been unblocked', $iUpdated), $iUpdated);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(Params::getServerParam('HTTP_REFERER', false, false));
        break;

      case('disable_items'):
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';
        require_once LIB_PATH . 'osclass/ItemActions.php';

        $iUpdated = 0;
        $userId = Params::getParam('id');

        if(!is_array($userId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        $itemActions = new ItemActions(true);
        foreach($userId as $id) {
          $items = Item::newInstance()->findByUserID($id);

          if(is_array($items) && count($items) > 0) {
            foreach($items as $item) {
              if($item['b_enabled'] == 1) {
                $iUpdated += $itemActions->disable($item['pk_i_id']);
              }
            }
          }
        }

        if($iUpdated == 0) {
          $msg = _m('No users listings have been blocked');
        } else {
          $msg = sprintf(_mn('One user listing has been blocked', '%s user listings have been blocked', $iUpdated), $iUpdated);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(Params::getServerParam('HTTP_REFERER', false, false));
        break;

      case('delete'):     //delete
        osc_csrf_check();
        $iDeleted = 0;
        $userId = Params::getParam('id');

        if(!is_array($userId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        }

        foreach($userId as $id) {
          $user = $this->userManager->findByPrimaryKey($id);
          Log::newInstance()->insertLog('user', 'delete', $id, $user['s_email'], 'admin', osc_logged_admin_id());
          if($this->userManager->deleteUser($id)) {
            $iDeleted++;
          }
        }

        if($iDeleted == 0) {
          $msg = _m('No users have been deleted');
        } else {
          $msg = sprintf(_mn('One user has been deleted', '%s users have been deleted', $iDeleted), $iDeleted);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        break;

      case('delete_alerts'):     //delete
        $iDeleted = 0;
        $alertId = Params::getParam('alert_id');
        if(!is_array($alertId)) {
          osc_add_flash_error_message(_m("Alert id isn't in the correct format"), 'admin');
          if(Params::getParam('user_id')=='') {
            $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=alerts');
          } else {
            $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id='.Params::getParam('user_id'));
          }
        }

        $mAlerts = new Alerts();
        foreach($alertId as $id) {
          Log::newInstance()->insertLog('user', 'delete_alerts', $id, $id, 'admin', osc_logged_admin_id());
          $iDeleted += $mAlerts->delete(array('pk_i_id' => $id));
        }

        if($iDeleted == 0) {
          $msg = _m('No alerts have been deleted');
        } else {
          $msg = sprintf(_mn('One alert has been deleted', '%s alerts have been deleted', $iDeleted), $iDeleted);
        }

        osc_add_flash_ok_message($msg, 'admin');
        if(Params::getParam('user_id')=='') {
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=alerts');
        } else {
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id='.Params::getParam('user_id'));
        }
        break;

      case('status_alerts'):     // bulk alert update
        // $status = Params::getParam('status');
        $iUpdated = 0;
        $alertId = Params::getParam('alert_id');
        $alert_action = Params::getParam('alert_action');     // activate, deactivate, delete
        $alert_action_text = '';


        if(!is_array($alertId)) {
          osc_add_flash_error_message(_m("Alert id isn't in the correct format"), 'admin');
          if(Params::getParam('user_id') == '') {
            $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=alerts');
          } else {
            $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id='.Params::getParam('user_id'));
          }
        }

        $mAlerts = new Alerts();
        foreach($alertId as $id) {
          if($alert_action == 'activate') {
            $alert_action_text = __('activated');
            $iUpdated += $mAlerts->activate($id);

          } else if($alert_action == 'deactivate') {
            $alert_action_text = __('deactivated');
            $iUpdated += $mAlerts->deactivate($id);

          } else if($alert_action == 'delete') {
            $alert_action_text = __('deleted');
            Log::newInstance()->insertLog('user', 'delete_alerts', $id, $id, 'admin', osc_logged_admin_id());
            $iUpdated += $mAlerts->delete(array('pk_i_id' => $id));

          } else if($alert_action == 'renew') {
            $alert = $mAlerts->findByPrimaryKey($id);
            if(isset($alert['pk_i_id'])) {
              $months = ((int)$alert['fk_i_user_id'] > 0 ? (int)osc_alerts_expiration_months_user() : (int)osc_alerts_expiration_months_guest());
              if($months <= 0) {
                $months = 3;
              }

              $new_expire = date('Y-m-d H:i:s', strtotime('+' . $months . ' month'));
              $iUpdated += $mAlerts->update(array('dt_expire_date' => $new_expire), array('pk_i_id' => $id));
              $alert_action_text = __('renewed');
            }

          } else if($alert_action == 'expire') {
            $new_expire = date('Y-m-d H:i:s', strtotime('-1 minute'));
            $iUpdated += $mAlerts->update(array('dt_expire_date' => $new_expire), array('pk_i_id' => $id));
            $alert_action_text = __('expired');
          }
        }

        if($iUpdated == 0) {
          $msg = sprintf(_m('No alerts have been %s'), $alert_action_text);
        } else {
          $msg = sprintf(_m('%s alerts have been %s', $iUpdated), $iUpdated, $alert_action_text);
        }

        osc_add_flash_ok_message($msg, 'admin');

        if(Params::getParam('user_id') == '') {
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=alerts');
        } else {
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=edit&id='.Params::getParam('user_id'));
        }
        break;

      case('settings'):     // calling the users settings view
        $this->doView('users/settings.php');
        break;

      case('settings_post'):  // updating users
        osc_csrf_check();
        $iUpdated = 0;
        $enabledUserValidation = Params::getParam('enabled_user_validation');
        $enabledUserValidation = (($enabledUserValidation != '') ? true : false);
        $enableTinyMCE = Params::getParam('enabled_tinymce_users');
        $enableTinyMCE = (($enableTinyMCE != '') ? true : false);
        $adminToolbarFront = Params::getParam('admin_toolbar_front');
        $adminToolbarFront = (($adminToolbarFront != '') ? true : false);
        $enableProfileImg = Params::getParam('enable_profile_img');
        $enableProfileImg = (($enableProfileImg != '') ? true : false);
        $profilePictureLibrary = Params::getParam('profile_picture_library');
        $enabledUserRegistration = Params::getParam('enabled_user_registration');
        $enabledUserRegistration = (($enabledUserRegistration != '') ? true : false);
        $enabledUsers = Params::getParam('enabled_users');
        $enabledUsers = (($enabledUsers != '') ? true : false);
        $notifyNewUser = Params::getParam('notify_new_user');
        $notifyNewUser = (($notifyNewUser != '') ? true : false);
        $dimProfileImg = Params::getParam('dimProfileImg');
        $userPublicProfileEnabled = Params::getParam('user_public_profile_enabled');
        $userPublicProfileMinItems = (int)Params::getParam('user_public_profile_min_items');
        $userPublicProfileMinItems = ($userPublicProfileMinItems > 0 ? $userPublicProfileMinItems : 0);
        $usernameGenerator = Params::getParam('username_generator');
        $usernameBlacklistTmp = explode(",", Params::getParam('username_blacklist'));
        $alertsEnabled = Params::getParam('alerts_enabled');
        $alertsEnabled = (($alertsEnabled != '') ? true : false);
        $alertsExpireMonthsUser = max(0, (int)Params::getParam('alerts_expiration_months_user'));
        $alertsExpireMonthsGuest = max(0, (int)Params::getParam('alerts_expiration_months_guest'));
        $alertsAllowNonExpiring = Params::getParam('alerts_allow_non_expiring');
        $alertsAllowNonExpiring = (($alertsAllowNonExpiring != '') ? true : false);
        $alertsAllowUserExpirationChange = Params::getParam('alerts_allow_user_expiration_change');
        $alertsAllowUserExpirationChange = (($alertsAllowUserExpirationChange != '') ? 1 : 0);
        $alertsDefaultNonExpiring = Params::getParam('alerts_default_non_expiring');
        $alertsDefaultNonExpiring = (($alertsDefaultNonExpiring != '') ? true : false);
        $alertsUnsubInactiveMonths = max(0, (int)Params::getParam('alerts_unsub_inactive_months'));

        foreach($usernameBlacklistTmp as $k => $v) {
          $usernameBlacklistTmp[$k] = strtolower(trim($v));
        }

        $usernameBlacklist = implode(',', $usernameBlacklistTmp);

        $iUpdated += osc_set_preference('enabled_user_validation', $enabledUserValidation);
        $iUpdated += osc_set_preference('enabled_user_registration', $enabledUserRegistration);
        $iUpdated += osc_set_preference('profile_picture_library', $profilePictureLibrary);
        $iUpdated += osc_set_preference('enabled_users', $enabledUsers);
        $iUpdated += osc_set_preference('notify_new_user', $notifyNewUser);
        $iUpdated += osc_set_preference('username_generator', $usernameGenerator);
        $iUpdated += osc_set_preference('username_blacklist', $usernameBlacklist);
        $iUpdated += osc_set_preference('alerts_enabled', $alertsEnabled, 'osclass', 'BOOLEAN');
        $iUpdated += osc_set_preference('alerts_expiration_months_user', $alertsExpireMonthsUser, 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('alerts_expiration_months_guest', $alertsExpireMonthsGuest, 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('alerts_allow_non_expiring', $alertsAllowNonExpiring, 'osclass', 'BOOLEAN');
        $iUpdated += osc_set_preference('alerts_allow_user_expiration_change', $alertsAllowUserExpirationChange, 'osclass', 'BOOLEAN');
        $iUpdated += osc_set_preference('alerts_default_non_expiring', $alertsDefaultNonExpiring, 'osclass', 'BOOLEAN');
        $iUpdated += osc_set_preference('alerts_unsub_inactive_months', $alertsUnsubInactiveMonths, 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('user_public_profile_enabled', $userPublicProfileEnabled);
        $iUpdated += osc_set_preference('user_public_profile_min_items', $userPublicProfileMinItems);
        $iUpdated += osc_set_preference('enabled_tinymce_users', $enableTinyMCE);
        $iUpdated += osc_set_preference('admin_toolbar_front', $adminToolbarFront);
        $iUpdated += osc_set_preference('enable_profile_img', $enableProfileImg);
        $iUpdated += osc_set_preference('dimProfileImg', $dimProfileImg);

        if($iUpdated > 0) {
          osc_add_flash_ok_message(_m("User settings have been updated"), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=settings');
        break;

      case('alerts'):        // manage alerts view
        if(!osc_alerts_enabled()) {
          osc_add_flash_warning_message(_m('Alerts are disabled'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=settings');
        }

        require_once osc_lib_path()."osclass/classes/datatables/AlertsDataTable.php";

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
          Params::setParam('sort', 'create_date');
        }

        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'desc');
        }

        $page = (int)Params::getParam('iPage');
        if($page==0) { $page = 1; }
        Params::setParam('iPage', $page);

        $params = Params::getParamsAsArray();

        $alertsDataTable = new AlertsDataTable();
        $alertsDataTable->table($params);
        $aData = $alertsDataTable->getData();

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
        $this->_exportVariableToView('aRawRows', $alertsDataTable->rawRows());

        $this->doView("users/alerts.php");
        break;

      case('ban'):       // manage ban rules view
        if(Params::getParam("action") != "") {
          osc_run_hook("ban_rules_bulk_".Params::getParam("action"), Params::getParam('id'));
        }

        require_once osc_lib_path()."osclass/classes/datatables/BanRulesDataTable.php";

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
          Params::setParam('sort', 'cdate');
        }

        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'desc');
        }

        $page = (int)Params::getParam('iPage');
        if($page==0) { $page = 1; }
        Params::setParam('iPage', $page);

        $params = Params::getParamsAsArray();

        $banRulesDataTable = new BanRulesDataTable();
        $banRulesDataTable->table($params);
        $aData = $banRulesDataTable->getData();

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
        $this->_exportVariableToView('aRawRows', $banRulesDataTable->rawRows());

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array('value' => 'delete_ban_rule', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected ban rules?'), strtolower(__('Delete'))), 'label' => __('Delete'))
        );

        $bulk_options = osc_apply_filter("ban_rule_bulk_filter", $bulk_options);
        $this->_exportVariableToView('bulk_options', $bulk_options);

        //calling the view...
        $this->doView('users/ban.php');
        break;

      case('edit_ban_rule'):
        $this->_exportVariableToView('rule', BanRule::newInstance()->findByPrimaryKey(Params::getParam('id')));
        $this->doView('users/ban_frm.php');
        break;

      case('edit_ban_rule_post'):
        osc_csrf_check();

        if(Params::getParam('s_ip') == '' && Params::getParam('s_email') == '') {
          osc_add_flash_warning_message(_m("Both rules can not be empty"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=ban');
        }

        BanRule::newInstance()->update(array(
          's_name' => Params::getParam('s_name'),
          's_ip' => Params::getParam('s_ip'),
          's_email' => strtolower(Params::getParam('s_email')),
          'dt_expire_date' => Params::getParam('dt_expire_date'),
          'dt_date' => date('Y-m-d H:i:s')
        ), array('pk_i_id' => Params::getParam('id')));

        osc_add_flash_ok_message(_m('Rule updated correctly'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=ban');
        break;

      case('create_ban_rule'):
        $this->_exportVariableToView('rule', null);
        $this->doView('users/ban_frm.php');
        break;

      case('create_ban_rule_post'):
        osc_csrf_check();
        if(Params::getParam('s_ip') == '' && Params::getParam('s_email') == '') {
          osc_add_flash_warning_message(_m("Both rules can not be empty"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=ban');
        }

        BanRule::newInstance()->insert(array(
          's_name' => Params::getParam('s_name'),
          's_ip' => Params::getParam('s_ip'),
          's_email' => strtolower(Params::getParam('s_email')),
          'dt_expire_date' => Params::getParam('dt_expire_date'),
          'dt_date' => date('Y-m-d H:i:s')
        ));

        osc_add_flash_ok_message(_m('Rule saved correctly'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=ban');
        break;

      case('delete_ban_rule'):     //delete ban rules
        osc_csrf_check();
        $iDeleted = 0;
        $ruleId = Params::getParam('id');

        if(!is_array($ruleId)) {
          osc_add_flash_error_message(_m("User id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=ban');
        }

        $ruleMgr = BanRule::newInstance();
        foreach($ruleId as $id) {
          if($ruleMgr->deleteByPrimaryKey($id)) {
            $iDeleted++;
          }
        }

        if($iDeleted == 0) {
          $msg = _m('No rules have been deleted');
        } else {
          $msg = sprintf(_mn('One ban rule has been deleted', '%s ban rules have been deleted', $iDeleted), $iDeleted);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=users&action=ban');
        break;

      case('login'):      // login as admin
        osc_csrf_check();
        $userId = Params::getParam('id');
        $user = User::newInstance()->findByPrimaryKey($userId);

        if(!$user) {
          osc_add_flash_error_message(_m("The user doesn't exist"));
          $this->redirectTo(osc_admin_base_url(true) . '?page=users');
        } elseif($user['b_enabled'] == 0 || $user['b_active'] == 0) {
          osc_add_flash_warning_message(_m('The user is blocked or not activated'));
        }

        Session::newInstance()->_set('userId', $user['pk_i_id']);
        Session::newInstance()->_set('userName', $user['s_name']);
        Session::newInstance()->_set('userEmail', $user['s_email']);
        Session::newInstance()->_set('userPhone', $user['s_phone_mobile'] ? $user['s_phone_mobile'] : $user['s_phone_land']);

        osc_run_hook('after_login', $user, osc_user_dashboard_url());
        osc_add_flash_ok_message(sprintf(_m('You have successfully logged in as %s'), '<strong>' . $user['s_name'] . '</strong>'));
        $this->redirectTo(osc_apply_filter('correct_login_url_redirect', osc_user_dashboard_url()));

      default:        // manage users view
        if(Params::getParam("action") != "") {
          osc_run_hook("user_bulk_".Params::getParam("action"), Params::getParam('id'));
        }

        require_once osc_lib_path()."osclass/classes/datatables/UsersDataTable.php";

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
          Params::setParam('sort', 'update_date');
        }
        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'desc');
        }

        $page = (int)Params::getParam('iPage');
        if($page==0) { $page = 1; }
        Params::setParam('iPage', $page);

        $params = Params::getParamsAsArray();

        $usersDataTable = new UsersDataTable();
        $usersDataTable->table($params);
        $aData = $usersDataTable->getData();

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
        $this->_exportVariableToView('withFilters', $usersDataTable->withFilters());
        $this->_exportVariableToView('aRawRows', $usersDataTable->rawRows());

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array('value' => 'activate', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected users?'), strtolower(__('Activate'))), 'label' => __('Activate')),
          array('value' => 'deactivate', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected users?'), strtolower(__('Deactivate'))), 'label' => __('Deactivate')),
          array('value' => 'enable', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected users?'), strtolower(__('Unblock'))), 'label' => __('Unblock')),
          array('value' => 'disable', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected users?'), strtolower(__('Block'))), 'label' => __('Block')),
          array('value' => 'delete', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected users?'), strtolower(__('Delete'))), 'label' => __('Delete'))
        );

        if(osc_user_validation_enabled()) {
          $bulk_options[] = array('value' => 'resend_activation', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected users?'), strtolower(__('Resend the activation to'))), 'label' => __('Resend activation'));
        }

        $bulk_options = osc_apply_filter("user_bulk_filter", $bulk_options);
        $this->_exportVariableToView('bulk_options', $bulk_options);

        //calling the view...
        $this->doView('users/index.php');
        break;

    }
  }

  //hopefully generic...
  function doView($file)
  {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }
}

/* file end: ./oc-admin/users.php */

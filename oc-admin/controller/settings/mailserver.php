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


class CAdminSettingsMailserver extends AdminSecBaseModel
{
  //Business Layer...
  function doModel()
  {
    switch($this->action) {
      case('mailserver'):
        // calling the mailserver view
        $this->doView('settings/mailserver.php');
      break;
      case('mailserver_post'):
        if( defined('DEMO') ) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=mailserver');
        }

        osc_csrf_check();
        // updating mailserver
        $iUpdated       = 0;
        $mailserverAuth   = Params::getParam('mailserver_auth');
        $mailserverAuth   = ($mailserverAuth != '' ? true : false);
        $mailserverPop    = Params::getParam('mailserver_pop');
        $mailserverPop    = ($mailserverPop != '' ? true : false);
        $mailserverType   = Params::getParam('mailserver_type');
        $mailserverHost   = Params::getParam('mailserver_host');
        $mailserverPort   = Params::getParam('mailserver_port');
        $mailserverUsername = Params::getParam('mailserver_username');
        $mailserverPassword = Params::getParam('mailserver_password', false, false);
        $mailserverSsl    = Params::getParam('mailserver_ssl');
        $mailserverMailFrom = Params::getParam('mailserver_mail_from');
        $mailserverNameFrom = Params::getParam('mailserver_name_from');

        if( !in_array($mailserverType, array('custom', 'gmail')) ) {
          osc_add_flash_error_message( _m('Mail server type is incorrect'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=mailserver');
        }

        $iUpdated += osc_set_preference('mailserver_auth', $mailserverAuth);
        $iUpdated += osc_set_preference('mailserver_pop', $mailserverPop);
        $iUpdated += osc_set_preference('mailserver_type', $mailserverType);
        $iUpdated += osc_set_preference('mailserver_host', $mailserverHost);
        $iUpdated += osc_set_preference('mailserver_port', $mailserverPort);
        $iUpdated += osc_set_preference('mailserver_username', $mailserverUsername);
        $iUpdated += osc_set_preference('mailserver_password', $mailserverPassword);
        $iUpdated += osc_set_preference('mailserver_ssl', $mailserverSsl);
        $iUpdated += osc_set_preference('mailserver_mail_from', $mailserverMailFrom);
        $iUpdated += osc_set_preference('mailserver_name_from', $mailserverNameFrom);

        if($iUpdated > 0) {
          osc_add_flash_ok_message( _m('Mail server configuration has changed'), 'admin');
        }
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=mailserver');
      break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/mailserver.php
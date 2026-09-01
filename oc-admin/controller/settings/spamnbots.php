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


class CAdminSettingsSpamnBots extends AdminSecBaseModel {
  function __construct() {
    parent::__construct();
  }

  //Business Layer...
  function doModel() {
    switch($this->action) {
      case('spamNbots'):
        // calling the spam and bots view
        $akismet_key  = osc_akismet_key();
        $akismet_status = 3;
        if( $akismet_key != '' ) {
          require_once( osc_lib_path() . 'Akismet.class.php' );
          $akismet_obj  = new Akismet(osc_base_url(), $akismet_key);
          $akismet_status = 2;
          if( $akismet_obj->isKeyValid() ) {
            $akismet_status = 1;
          }
        }

        View::newInstance()->_exportVariableToView('akismet_status', $akismet_status);
        $this->doView('settings/spamNbots.php');
        break;
        
      case('akismet_post'):
        // updating spam and bots option
        osc_csrf_check();
        $updated  = 0;
        $akismetKey = Params::getParam('akismetKey');
        $akismetKey = trim($akismetKey);

        $updated = osc_set_preference('akismetKey', $akismetKey);

        if( $akismetKey == '' ) {
          osc_add_flash_info_message(_m('Your Akismet key has been cleared'), 'admin');
        } else {
          osc_add_flash_ok_message(_m('Your Akismet key has been updated'), 'admin');
        }
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=spamNbots');
        break;
      
      case('recaptcha_post'):
        // updating spam and bots option
        osc_csrf_check();
        $iUpdated = 0;
        $recaptchaEnabled = Params::getParam('recaptchaEnabled');
        $recaptchaEnabled = ($recaptchaEnabled != '' ? true : false);
        $recaptchaPrivKey = Params::getParam('recaptchaPrivKey');
        $recaptchaPrivKey = trim($recaptchaPrivKey);
        $recaptchaPubKey  = Params::getParam('recaptchaPubKey');
        $recaptchaPubKey  = trim($recaptchaPubKey);
        $recaptchaVersion = Params::getParam('recaptchaVersion');
        $recaptchaVersion = trim($recaptchaVersion);

        $iUpdated += osc_set_preference('recaptchaEnabled', $recaptchaEnabled);
        $iUpdated += osc_set_preference('recaptchaPrivKey', $recaptchaPrivKey);
        $iUpdated += osc_set_preference('recaptchaPubKey', $recaptchaPubKey);
        $iUpdated += osc_set_preference('recaptcha_version', $recaptchaVersion);

        osc_add_flash_ok_message( _m('ReCaptcha settings have been updated') ,'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=spamNbots');
        break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/spamnbots.php
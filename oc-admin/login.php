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


class CAdminLogin extends AdminBaseModel
{
  function __construct() {
    parent::__construct();
  }

  //Business Layer...
  function doModel() {
    switch( $this->action ) {
      case('login_post'):   //post execution for the login
        osc_csrf_check();
        osc_run_hook('before_login_admin');
        $url_redirect = osc_get_http_referer();
        $page_redirect = '';
        $password = Params::getParam('password', false, false);

        $is_demo = false;
        if((defined('DEMO_PLUGINS') && DEMO_PLUGINS === true) || (defined('DEMO_THEMES') && DEMO_THEMES === true) || (defined('DEMO') && DEMO === true)) {
          $is_demo = true;
        }
        
        if(preg_match('|[\?&]page=([^&]+)|', $url_redirect . '&', $match)) {
          $page_redirect = $match[1];
        }

        if($page_redirect == '' || $page_redirect == 'login') {
          $url_redirect = osc_admin_base_url();
        }

        if( Params::getParam('user') == '' ) {
          osc_add_flash_error_message( _m('The username field is empty'), 'admin');
          $this->redirectTo( osc_admin_base_url(true)."?page=login" );
        }

        if( Params::getParam('password', false, false) == '' ) {
          osc_add_flash_error_message( _m('The password field is empty'), 'admin');
          $this->redirectTo( osc_admin_base_url(true)."?page=login" );
        }

        // fields are not empty
        $admin = Admin::newInstance()->findByUsername( Params::getParam('user') );

        if(isset($admin['pk_i_id']) && $admin['pk_i_id'] > 0) {
          $fail_count = (@$admin['i_login_fails'] > 0 ? $admin['i_login_fails'] : 0);
          $last_fail_date = (@$admin['dt_login_fail_date'] <> '' ? $admin['dt_login_fail_date'] : date('Y-m-d H:i:s'));
          $diff_minutes = round(abs(strtotime(date('Y-m-d H:i:s')) - strtotime($last_fail_date))/60, 2);
          $has_failed = false;
          $limit = 0;


          if($fail_count == 5 && $diff_minutes < 5) {
            $has_failed = true;
            $limit = 5;
          } else if($fail_count == 10 && $diff_minutes < 30) {
            $has_failed = true;
            $limit = 30;
          } else if($fail_count == 15 && $diff_minutes < 60) {
            $has_failed = true;
            $limit = 60;
          } else if($fail_count == 20 && $diff_minutes < 360) {
            $has_failed = true;
            $limit = 360;
          } else if($fail_count >= 24) {
            $admin['i_login_fails'] = 19;
            Admin::newInstance()->updateLoginFailed($admin['pk_i_id'], 19, $last_fail_date);   // reset to 19 so next login is after 6 hours
          }

          if($has_failed && isset($admin['i_login_fails'])) {
            osc_add_flash_error_message( sprintf(_m('Sorry, you have entered incorrect password more than %s times. You will be able to login again in %s minutes'), $fail_count, ceil($limit - $diff_minutes)), 'admin');
            $this->redirectTo( osc_admin_base_url(true)."?page=login" );
          }
        }


        if( !$admin ) {
          osc_add_flash_error_message( sprintf(_m('Sorry, incorrect username. <a href="%s">Have you lost your password?</a>'), osc_admin_base_url(true) . '?page=login&amp;action=recover' ), 'admin');
          $this->redirectTo( osc_admin_base_url(true)."?page=login" );
        }
        
        if($is_demo === true && $admin['s_username'] === 'demo') {
          // demo admin login without need to check password
        } else if(!osc_verify_password($password, $admin['s_password'])) {
          Admin::newInstance()->updateLoginFailed($admin['pk_i_id'], $admin['i_login_fails'] + 1);
          osc_add_flash_error_message( sprintf(_m('Sorry, incorrect password. <a href="%s">Have you lost your password?</a>'), osc_admin_base_url(true) . '?page=login&amp;action=recover' ), 'admin');
          $this->redirectTo( osc_admin_base_url(true)."?page=login" );
        } else {
          if (@$admin['s_password']!='') {
            if (preg_match('|\$2y\$([0-9]{2})\$|', $admin['s_password'], $cost)) {
              if ($cost[1] != BCRYPT_COST) {
                Admin::newInstance()->update(array( 's_password' => osc_hash_password($password)), array( 'pk_i_id' => $admin['pk_i_id'] ) );
              }
            } else {
              Admin::newInstance()->update(array( 's_password' => osc_hash_password($password)), array( 'pk_i_id' => $admin['pk_i_id'] ) );
            }
          }
        }

        if( Params::getParam('remember') ) {
          // disabled, otherwise you could not keep login from different devices
          // currently it only updates secret if it is blank
          if($admin['s_secret'] == '') {
            require_once osc_lib_path() . 'osclass/helpers/hSecurity.php';
            $secret = osc_genRandomPassword();
            Admin::newInstance()->update(array('s_secret' => $secret), array('pk_i_id' => $admin['pk_i_id']));
            $admin['s_secret'] = $secret;
          }
          
          // Cookie::newInstance()->set_expires(osc_time_cookie());
          Cookie::newInstance()->push('oc_adminId', $admin['pk_i_id']);
          Cookie::newInstance()->push('oc_adminSecret', $admin['s_secret']);
          Cookie::newInstance()->push('oc_adminLocale', Params::getParam('locale'));
          Cookie::newInstance()->set();
        }

        // we are logged in... let's go!
        if(isset($admin['i_login_fails'])) {                               // just to make sure after upgrade, admin can login
          Admin::newInstance()->updateLoginFailed($admin['pk_i_id'], 0);   // reset fail login counter
        }

        Session::newInstance()->_set('adminId', $admin['pk_i_id']);
        Session::newInstance()->_set('adminUserName', $admin['s_username']);
        Session::newInstance()->_set('adminName', $admin['s_name']);
        Session::newInstance()->_set('adminEmail', $admin['s_email']);
        Session::newInstance()->_set('adminLocale', Params::getParam('locale'));

        osc_run_hook('login_admin', $admin);

        $this->redirectTo( $url_redirect );
        break;

      case('recover'):    // form to recover the password (in this case we have the form in /gui/)
        $this->doView('gui/recover.php');
        break;

      case('recover_post'):
        if( defined('DEMO') ) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo( osc_admin_base_url() );
        }
        osc_csrf_check();

        // post execution to recover the password
        $admin = Admin::newInstance()->findByEmail( Params::getParam('email') );
        if(!isset($admin['pk_i_id'])) {
          $admin = Admin::newInstance()->findByUsername(Params::getParam('email'));
        }
        
        if( isset($admin['pk_i_id']) ) {
          if(osc_recaptcha_enabled() && osc_recaptcha_private_key() != '') {
            if( !osc_check_recaptcha() ) {
              osc_add_flash_error_message( _m('Recaptcha validation has failed'), 'admin');
              $this->redirectTo( osc_admin_base_url(true).'?page=login&action=recover' );
              return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
            }
          }

          require_once osc_lib_path() . 'osclass/helpers/hSecurity.php';
          $newPassword = osc_genRandomPassword(40);

          Admin::newInstance()->update(
            array('s_secret' => $newPassword),
            array('pk_i_id' => $admin['pk_i_id'])
          );
          $password_url = osc_forgot_admin_password_confirm_url($admin['pk_i_id'], $newPassword);

          osc_run_hook('hook_email_user_forgot_password', $admin, $password_url);
        }

        osc_add_flash_ok_message( _m('A new password has been sent to your e-mail'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=login');
        break;

      case('forgot'):     // form to recover the password (in this case we have the form in /gui/)
        $admin = Admin::newInstance()->findByIdSecret(Params::getParam('adminId'), Params::getParam('code'));
        if( !$admin ) {
          osc_add_flash_error_message( _m('Sorry, the link is not valid'), 'admin');
          $this->redirectTo( osc_admin_base_url() );
        }

        $this->doView( 'gui/forgot_password.php' );
        break;

      case('forgot_post'):
        osc_csrf_check();
        $admin = Admin::newInstance()->findByIdSecret(Params::getParam('adminId'), Params::getParam('code'));
        if( !$admin ) {
          osc_add_flash_error_message( _m('Sorry, the link is not valid'), 'admin');
          $this->redirectTo( osc_admin_base_url() );
        }

        if( Params::getParam('new_password', false, false) == Params::getParam('new_password2', false, false) ) {
          Admin::newInstance()->update(
            array('s_secret' => osc_genRandomPassword()
              , 's_password' => osc_hash_password(Params::getParam('new_password', false, false))
            ), array('pk_i_id' => $admin['pk_i_id'])
          );
          osc_add_flash_ok_message( _m('The password has been changed'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=login');
          
        } else {
          osc_add_flash_error_message( _m("Error, the passwords don't match"), 'admin');
          $this->redirectTo(osc_forgot_admin_password_confirm_url(Params::getParam('adminId'), Params::getParam('code')));
        }
        break;

      default:
        //osc_run_hook( 'init_admin' );
        Session::newInstance()->_setReferer(osc_get_http_referer());
        $this->doView( 'gui/login.php' );

        break;

    }
  }

  //in this case, this function is prepared for the "recover your password" form
  function doView($file) {
    $login_admin_title = osc_apply_filter('login_admin_title', 'Osclass');
    $login_admin_url   = osc_apply_filter('login_admin_url', 'https://osclass-classifieds.com');
    $login_admin_image = osc_apply_filter('login_admin_image', osc_admin_base_url() . 'images/osclass-logo.gif');

    View::newInstance()->_exportVariableToView('login_admin_title', $login_admin_title);
    View::newInstance()->_exportVariableToView('login_admin_url', $login_admin_url);
    View::newInstance()->_exportVariableToView('login_admin_image', $login_admin_image);

    osc_run_hook("before_admin_html");
    require osc_admin_base_path() . $file;
    osc_run_hook("after_admin_html");

  }
}

/* file end: ./oc-admin/login.php */
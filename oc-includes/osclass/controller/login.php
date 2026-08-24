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
 * Class CWebLogin
 */
class CWebLogin extends BaseModel {
  public function __construct() {
    parent::__construct();
    
    if(!osc_users_enabled()) {
      osc_add_flash_error_message(_m('Users not enabled'));
      $this->redirectTo(osc_base_url());
    }
    
    osc_run_hook('init_login');
  }

  //Business Layer...
  public function doModel() {
    switch($this->action) {
      case('login_post'):   // post execution for the login
        if(!osc_users_enabled()) {
          osc_add_flash_error_message(_m('Users are not enabled'));
          $this->redirectTo(osc_base_url());
        }
        
        osc_csrf_check();
        osc_run_hook('before_validating_login');

        $is_demo = false;
        if((defined('DEMO_PLUGINS') && DEMO_PLUGINS === true) || (defined('DEMO_THEMES') && DEMO_THEMES === true) || (defined('DEMO') && DEMO === true)) {
          $is_demo = true;
        }
        
        // e-mail or/and password is/are empty or incorrect
        $wrongCredentials = false;
        $email = trim(Params::getParam('email'));
        $password = Params::getParam('password', false, false);
        
        if(osc_recaptcha_enabled() && osc_recaptcha_private_key() != '' && !osc_is_admin_user_logged_in() && !osc_check_recaptcha()) {
          osc_add_flash_error_message(_m('The reCAPTCHA was not entered correctly'));
          $this->redirectTo(osc_user_login_url());
        }
    
        if($email == '') {
          osc_add_flash_error_message(_m('Please provide an email address'));
          $wrongCredentials = true;
        }
        
        if($password == '') {
          osc_add_flash_error_message(_m('Empty passwords are not allowed. Please provide a password'));
          $wrongCredentials = true;
        }
        
        if($wrongCredentials) {
          $this->redirectTo(osc_user_login_url());
        }

        if(osc_validate_email($email)) {
          $user = User::newInstance()->findByEmail($email);
        }
        
        if(empty($user)) {
          $user = User::newInstance()->findByUsername($email);
        }
        
        if(empty($user)) {
          osc_add_flash_error_message(_m("The user doesn't exist"));
          $this->redirectTo(osc_user_login_url());
        }

        if(isset($user['pk_i_id']) && $user['pk_i_id'] > 0) {
          $fail_count = $user['i_login_fails'];
          $last_fail_date = $user['dt_login_fail_date'];
          
          if($last_fail_date !== NULL) {
            $diff_minutes = round(abs(strtotime(date('Y-m-d H:i:s')) - strtotime($last_fail_date))/60, 2);
          } else {
            $diff_minutes = 999;
          }
          
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
            $user['i_login_fails'] = 19;
            User::newInstance()->updateLoginFailed($user['pk_i_id'], 19, $last_fail_date);   // reset to 19 so next login is after 6 hours
          }

          if($has_failed) {
            osc_add_flash_error_message(sprintf(_m('Sorry, you have entered incorrect password more than %s times. You will be able to login again in %s minutes'), $fail_count, ceil($limit - $diff_minutes)));
            $this->redirectTo(osc_user_login_url());
          }
        }
        
        if($is_demo === true && substr($user['s_username'], 0, 4) === 'demo') {
          // demo user login without need to check password
          
        } else if(!osc_verify_password($password, (isset($user['s_password']) ? $user['s_password']: ''))) {
          User::newInstance()->updateLoginFailed($user['pk_i_id'], $user['i_login_fails'] + 1);
          osc_add_flash_error_message(_m('The password is incorrect'));
          $this->redirectTo(osc_user_login_url());

        } else {
          if(@$user['s_password'] != '') {
            if(preg_match('|\$2y\$([0-9]{2})\$|', $user['s_password'], $cost)) {
              if($cost[1] != BCRYPT_COST) {
                User::newInstance()->update(array('s_password' => osc_hash_password($password)), array('pk_i_id' => $user['pk_i_id']));
              }
            } else {
              User::newInstance()->update(array('s_password' => osc_hash_password($password)), array('pk_i_id' => $user['pk_i_id']));
            }
          }
        }

        User::newInstance()->updateLoginFailed($user['pk_i_id'], 0);   // reset fail login counter

        // e-mail or/and IP is/are banned
        $banned = osc_is_banned($email); // int 0: not banned or unknown, 1: email is banned, 2: IP is banned, 3: both email & IP are banned
        if($banned & 1) {
          osc_add_flash_error_message(_m('Your current email is not allowed'));
        }
        
        if($banned & 2) {
          osc_add_flash_error_message(_m('Your current IP is not allowed'));
        }
        
        if($banned !== 0) {
          $this->redirectTo(osc_user_login_url());
        }

        osc_run_hook('before_login');

        $url_redirect = osc_get_http_referer();
        if(osc_rewrite_enabled() && $url_redirect!='') {
          // if comes from oc-admin/
          if(strpos($url_redirect, OC_ADMIN_FOLDER)!==false) {
            $url_redirect = osc_user_dashboard_url();
          } else {
            $request_uri = urldecode(preg_replace('@^' . osc_base_url() . '@', '' , $url_redirect));
            $tmp_ar = explode('?' , $request_uri);
            $request_uri = $tmp_ar[0];
            $rules = Rewrite::newInstance()->listRules();
            
            foreach($rules as $match => $uri) {
              if(preg_match('#'.$match.'#', $request_uri, $m)) {
                $request_uri = preg_replace('#'.$match.'#', $uri, $request_uri);
                if(preg_match('|([&?]{1})page=([^&]*)|', '&'.$request_uri.'&', $match)) {
                  $page_redirect = $match[2];
                  if($page_redirect=='' || $page_redirect === 'login') {
                    $url_redirect = osc_user_dashboard_url();
                  }
                }
                
                break;
              }
            }
          }
        }

        require_once LIB_PATH . 'osclass/UserActions.php';
        $uActions = new UserActions(false);
        $logged = $uActions->bootstrap_login($user['pk_i_id']);

        if($logged==0) {
          osc_add_flash_error_message(_m("The user doesn't exist"));
          
        } else if($logged==1) {
          // if((time()-strtotime($user['dt_access_date']))>1200) { // EACH 20 MINUTES
            // osc_add_flash_error_message(sprintf(_m('The user has not been validated yet. Would you like to re-send your <a href="%s">activation?</a>'), osc_user_resend_activation_link($user['pk_i_id'], $user['s_email'])));
          // } else {
            // osc_add_flash_error_message(_m('The user has not been validated yet'));
          // }

          osc_add_flash_error_message(sprintf(_m('The user has not been validated yet. <br/>Would you like to <a href="%s">re-send validation link</a>?'), osc_user_resend_activation_link($user['pk_i_id'], $user['s_email'])));

        } else if($logged==2) {
          osc_add_flash_error_message(_m('The user has been suspended'));
          
        } else if($logged==3) {
          if(Params::getParam('remember') == 1) {
            // disabled, otherwise you could not keep login from different devices
            // currently it only updates secret if it is blank
            if($user['s_secret'] == '') {
              require_once osc_lib_path() . 'osclass/helpers/hSecurity.php';
              $secret = osc_genRandomPassword();
              User::newInstance()->update(array('s_secret' => $secret), array('pk_i_id' => $user['pk_i_id']));
              $user['s_secret'] = $secret;
            }
            
            // Cookie::newInstance()->set_expires(osc_time_cookie());
            Cookie::newInstance()->push('oc_userId', $user['pk_i_id']);
            Cookie::newInstance()->push('oc_userSecret', $user['s_secret']);
            Cookie::newInstance()->set();
          }

          if($url_redirect=='') {
            $url_redirect = osc_user_dashboard_url();
          }

          osc_run_hook('after_login' , $user, $url_redirect);

          $this->redirectTo(osc_apply_filter('correct_login_url_redirect', $url_redirect));

        } else {
          osc_add_flash_error_message(_m('This should never happen'));
        }

        if(!$user['b_enabled']) {
          $this->redirectTo(osc_user_login_url());
        }

        $this->redirectTo(osc_user_login_url());
        break;
        
      case('resend'):
        if(!osc_users_enabled()) {
          osc_add_flash_error_message(_m('Users are not enabled'));
          $this->redirectTo(osc_base_url());
        }
        
        if(!osc_user_validation_enabled()) {
          osc_add_flash_error_message(_m('User validation is not required'));
          $this->redirectTo(osc_base_url());
        }
        
        $limit = 5;  // validation email can be re-sent once XY minutes
        $id = osc_esc_html(Params::getParam('id'));
        $email = trim(osc_esc_html(Params::getParam('email')));
        
        if((int)$id <= 0 || $email == '') {
          osc_add_flash_error_message(_m('The link is not valid. It looks like the ID or email may be incorrect.'));
          $this->redirectTo(osc_user_login_url());
        }
        
        $user = osc_get_user_row($id);
        
        if($user === false || !isset($user['pk_i_id']) || $email != $user['s_email']) {
          osc_add_flash_error_message(_m('The link is not valid. It looks like the ID or email may be incorrect.'));
          $this->redirectTo(osc_user_login_url());
        }

        if($user['b_enabled'] == 0) {
          osc_add_flash_error_message(_m('This account has been blocked by administrator.'));
          $this->redirectTo(osc_user_login_url());
        }
        
        if($user['b_active'] == 1) {
          osc_add_flash_error_message(_m('This account has already been validated.'));
          $this->redirectTo(osc_user_login_url());
        }

        // All good, ready to resend
        if((time() - strtotime($user['dt_access_date'])) > ($limit * 60)) {
          osc_run_hook('hook_email_user_validation', $user, $user);
          
          User::newInstance()->update(array('dt_access_date' => date('Y-m-d H:i:s')), array('pk_i_id' => $user['pk_i_id']));
          osc_add_flash_ok_message(sprintf(_m('A validation link has been re-sent to your email address %s.'), $user['s_email']));

        } else {
          $mins_to_next = max(1, ceil(($limit * 60 - (time() - strtotime($user['dt_access_date']))) / 60));
          osc_add_flash_warning_message(sprintf(_m('We\'ve recently sent you a validation email. Please check your inbox and spam folder. You can request a new one in %d minute(s) if you don\'t receive it.'), $mins_to_next));
        }
        
        $this->redirectTo(osc_user_login_url());
        break;
        
      case('recover'):    //form to recover the password (in this case we have the form in /gui/)
        $this->doView('user-recover.php');
        break;
        
      case('recover_post'):   //post execution to recover the password
        osc_csrf_check();
        require_once LIB_PATH . 'osclass/UserActions.php';

        // e-mail is incorrect
        if(!osc_validate_email(Params::getParam('s_email'))) {
          osc_add_flash_error_message(_m('Invalid email address'));
          $this->redirectTo(osc_recover_user_password_url());
        }

        $userActions = new UserActions(false);
        $success = $userActions->recover_password();

        switch ($success) {
          case(0): // recover ok
            osc_add_flash_ok_message(_m('We have sent you an email with the instructions to reset your password'));
            $this->redirectTo(osc_base_url());
            break;
            
          case(1): // e-mail does not exist
            osc_add_flash_error_message(_m('We were not able to identify you given the information provided'));
            $this->redirectTo(osc_recover_user_password_url());
            break;
            
          case(2): // recaptcha wrong
            osc_add_flash_error_message(_m('Recaptcha validation has failed'));
            $this->redirectTo(osc_recover_user_password_url());
            break;
        }
        
        break;
        
      case('forgot'):     //form to recover the password (in this case we have the form in /gui/)
        $user = User::newInstance()->findByIdPasswordSecret(Params::getParam('userId'), Params::getParam('code'));
        if($user) {
          $this->doView('user-forgot_password.php');
        } else {
          osc_add_flash_error_message(_m('Sorry, the link is not valid'));
          $this->redirectTo(osc_base_url());
        }
        
        break;
        
      case('forgot_post'):
        osc_csrf_check();
        if((Params::getParam('new_password', false, false) == '') || (Params::getParam('new_password2', false, false) == '')) {
          osc_add_flash_warning_message(_m('Password cannot be blank'));
          $this->redirectTo(osc_forgot_user_password_confirm_url(Params::getParam('userId'), Params::getParam('code')));
        }

        $user = User::newInstance()->findByIdPasswordSecret(Params::getParam('userId'), Params::getParam('code'));
        
        if(isset($user['b_enabled']) && $user['b_enabled'] == 1) {
          if(Params::getParam('new_password', false, false)==Params::getParam('new_password2', false, false)) {
            User::newInstance()->update(
              array(
                's_pass_code' => osc_genRandomPassword(50)
                ,'s_pass_date' => date('Y-m-d H:i:s', 0)
                ,'s_pass_ip' => osc_get_ip() //Params::getServerParam('REMOTE_ADDR')
                ,'s_password' => osc_hash_password(Params::getParam('new_password', false, false))
             ), 
              array('pk_i_id' => $user['pk_i_id'])
           );
            osc_add_flash_ok_message(_m('The password has been changed'));
            $this->redirectTo(osc_user_login_url());
          } else {
            osc_add_flash_error_message(_m("Error, the password don't match"));
            $this->redirectTo(osc_forgot_user_password_confirm_url(Params::getParam('userId'), Params::getParam('code')));
          }
        } else {
          osc_add_flash_error_message(_m('Sorry, the link is not valid'));
        }
        
        $this->redirectTo(osc_base_url());
        break;
        
      default:        //login
        Session::newInstance()->_setReferer(osc_get_http_referer());
        
        if(osc_logged_user_id() > 0) {
          $this->redirectTo(osc_user_dashboard_url());
        }
        
        $this->doView('user-login.php');
    }
  }

  //hopefully generic...

  /**
   * @param $file
   *
   * @return mixed|void
   */
  public function doView($file) {
    osc_run_hook('before_html');
    osc_current_web_theme_path($file);
    osc_run_hook('after_html');
  }
}

/* file end: ./login.php */
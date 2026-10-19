<?php
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
* Helper Security
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/

use OpensslCryptor\Cryptor;

if(!defined('BCRYPT_COST')) {
  define('BCRYPT_COST', 15);   // could be reduced i.e. to 8 to speed-up login time
}


/**
 * Creates a random password.
 * @param int password $length. Default to 8.
 * @return string
 */
function osc_genRandomPassword($length = 8) {
  $dict = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));

  // cryptographically secure, values are used as secrets and tokens
  $pass = '';
  for($i = 0; $i < $length; $i ++) {
    $pass .= $dict[random_int(0, count($dict) - 1)];
  }

  return $pass;
}

/**
 * Create a CSRF token to be placed in a form
 *
 * @since 3.1
 * @return string
 */
function osc_csrf_token_form() {
  // list($name, $token) = osc_csrfguard_generate_token();
  // return "<input type='hidden' name='CSRFName' value='".$name."' />
  // <input type='hidden' name='CSRFToken' value='".$token."' />";

  $token = osc_csrfguard_generate_token();
  return "<input type='hidden' name='octoken' value='".$token."' />";
}

/**
 * Create a CSRF token to be placed in a url
 *
 * @since 3.1
 * @return string
 */
function osc_csrf_token_url() {
  // list($name, $token) = osc_csrfguard_generate_token();
  // return 'CSRFName=' . $name . '&CSRFToken=' . $token;

  // update 420
  $token = osc_csrfguard_generate_token();
  return 'octoken=' . $token;
}

/**
 * Check if CSRF token is valid, die in other case
 *
 * @since 3.1
 */
function osc_csrf_check($enabled = true) {
  // update 420
  if(!$enabled) {
    return true;
  }

  $error  = false;
  $str_error  = '';

  if(Params::getParam('octoken') == '') {
    $str_error = _m('Probable invalid request.') ;
    $error = true;
  } else {
    $token = Params::getParam('octoken');
    if(!osc_csrfguard_validate_token($token)) {
      $str_error = _m('Invalid CSRF/Security token.');
      $error = true;
    }
  }


  if(defined('IS_AJAX') && $error && IS_AJAX === true) {
    echo json_encode(array('error' => 1, 'msg' => $str_error));
    exit;
  }

  // check ajax request
  if($error) {
    if(OC_ADMIN) {
      osc_add_flash_error_message($str_error, 'admin');
      error_log($str_error);

    } else {
      osc_add_flash_error_message($str_error);
    }

    $url = osc_get_http_referer();
    // drop session referer
    Session::newInstance()->_dropReferer();
    if($url!='') {
      osc_redirect_to($url);
    }

    if(OC_ADMIN) {
      osc_redirect_to( osc_admin_base_url(true) );
    } else {
      osc_redirect_to( osc_base_url(true) );
    }
  }
}

/**
 * Check if an email and/or IP are banned
 *
 * @param string $email
 * @param string $ip
 * @since 3.1
 * @return int 0: not banned, 1: email is banned, 2: IP is banned
 */
function osc_is_banned($email = '', $ip = null) {
  if($ip == null) {
    $ip = osc_get_ip();
  }

  //$rules = BanRule::newInstance()->listAll();

  if(osc_is_ip_banned($ip)) {
    return 2;
  } else if(osc_is_email_banned($email)) {
    return 1;
  }

  return 0;
}

/**
 * Check if IP is banned
 *
 * @param string $ip
 * @param string $rules (optional, to savetime and resources)
 * @since 3.1
 * @return boolean
 */
function osc_is_ip_banned($ip, $rules = null) {
  $ip = trim((string)$ip);

  if($ip == '') {
    return false;
  }

  if($rules === null) {
    //$rules = BanRule::newInstance()->listAll();
    $rules = BanRule::newInstance()->getIpRules();
  }

  $ip_blocks = explode('.' , $ip);

  if(count($ip_blocks) == 4) {
    foreach($rules as $rule) {
      if($rule['s_ip'] != '') {
        $blocks = explode( '.' , $rule['s_ip']);

        if(count($blocks)==4) {
          $matched = true;

          for($k=0;$k<4;$k++) {
            if(preg_match('|([0-9]+)-([0-9]+)|', $blocks[$k], $match)) {
              if($ip_blocks[$k]<$match[1] || $ip_blocks[$k]>$match[2]) {
                $matched = false;
                break;
              }
            } else if($blocks[$k] !== '*' && $blocks[$k] != $ip_blocks[$k]) {
              $matched = false;
              break;
            }
          }

          if($matched) {
            BanRule::newInstance()->increaseHit($rule['pk_i_id']);
            return true;
          }
        }
      }
    }
  }

  return false;
}

/**
 * Check if email is banned
 *
 * @param string $email
 * @param string $rules (optional, to savetime and resources)
 * @since 3.1
 * @return boolean
 */
function osc_is_email_banned($email, $rules = null) {
  $email = strtolower(trim((string)$email));

  if($email == '') {
    return false;
  }

  if($rules === null) {
    //$rules = BanRule::newInstance()->listAll();
    $rules = BanRule::newInstance()->getEmailRules();
  }

  if(is_array($rules) && count($rules) > 0) {  // update 450 - whole function
    foreach($rules as $rule) {
      $rule_email = str_replace(array('*', '|'), array('.*', "\\"), str_replace('.', "\.", strtolower(trim($rule['s_email']))));
      $rlist = array_filter(array_map('trim', explode(',', $rule_email)));

      if(is_array($rlist) && count($rlist) > 0) {
        foreach($rlist as $ritem) {
          if($ritem != '') {
            if(isset($ritem[0]) && $ritem[0] === '!') {
              $ritem = '|^((?'.$ritem.').*)$|';
            } else {
              $ritem = '|^'.$ritem.'$|';
            }

            if(preg_match($ritem, $email)) {
              //echo sprintf('Email %s banned based on rule %s', $email, $ritem) . PHP_EOL;
              BanRule::newInstance()->increaseHit($rule['pk_i_id']);
              return true;
            }
          }
        }
      }
    }
  }

  return false;
}

/**
 * Check if username is blacklisted
 *
 * @param string $username
 * @since 3.1
 * @return boolean
 */
function osc_is_username_blacklisted($username) {
  // Avoid numbers only usernames, this will collide with future users leaving the username field empty
  if(preg_replace('|(\d+)|', '', $username) == '') {
    return true;
  }

  $blacklist = explode(',', osc_username_blacklist());

  if(is_array($blacklist) && count($blacklist) > 0) {
    foreach($blacklist as $bl) {
      if(stripos($username, $bl) !== false) {
        return true;
      }
    }
  }

  return false;
}


/**
 * Verify an user's password
 *
 * @param $password plain-text
 * @param $hash
 *
 * @return bool
 * @throws \Exception
 * @hash  bcrypt/sha1
 * @since 3.3
 */
function osc_verify_password($password , $hash) {
  return password_verify($password, $hash)?true:(sha1($password)==$hash);
}


/**
 * Hash a password in available method (bcrypt/sha1)
 *
 * @param $password plain-text
 *
 * @return string hashed password
 * @throws \Exception
 * @since 3.3
 */
function osc_hash_password($password) {
  $options = array('cost' => BCRYPT_COST);
  return password_hash($password, PASSWORD_BCRYPT, $options);
}


/**
 * Validate a new password, returns error message or empty string when valid.
 * Plugins add own rules via filter user_password_error.
 *
 * @param string $password plain-text
 * @param array|null $user user row (null on registration)
 * @param string $context register, edit, change, reset, admin
 *
 * @return string
 * @since 8.4.1
 */
function osc_validate_password($password, $user = null, $context = '') {
  $error = '';
  $min_length = osc_password_min_length();

  if(mb_strlen((string)$password, 'UTF-8') < $min_length) {
    $error = sprintf(_m('The password must be at least %d characters long'), $min_length);
  }

  return (string)osc_apply_filter('user_password_error', $error, $password, $user, $context);
}


/**
 * Called on user login and password change: new session id (prevents session fixation) and user secret stored in session,
 * so a password change on another device ends this session.
 *
 * @param array $user user row
 *
 * @since 8.4.1
 */
function osc_set_user_session_secret($user) {
  if(session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
    session_regenerate_id(true);
  }

  Session::newInstance()->_set('userSecret', hash('sha256', (string)(isset($user['s_secret']) ? $user['s_secret'] : '')));
}


/**
 * Rotate user secret after password change: other devices (sessions and remember-me cookies) are logged out, current device stays logged in.
 *
 * @param int $user_id
 * @param string $context change, reset, admin
 *
 * @return string new secret
 * @since 8.4.1
 */
function osc_rotate_user_secret($user_id, $context = '') {
  $secret = osc_genRandomPassword(32);
  User::newInstance()->update(array('s_secret' => $secret), array('pk_i_id' => (int)$user_id));

  // keep current device logged in
  if(!OC_ADMIN && osc_logged_user_id() == $user_id) {
    osc_set_user_session_secret(array('s_secret' => $secret));

    if(Cookie::newInstance()->get_value('oc_userSecret') != '') {
      Cookie::newInstance()->push('oc_userId', $user_id);
      Cookie::newInstance()->push('oc_userSecret', $secret);
      Cookie::newInstance()->set();
    }
  }

  osc_run_hook('user_password_changed', $user_id, $context);
  return $secret;
}


/**
 * @param $alert
 *
 * @return string
 */
function osc_encrypt_alert( $alert ) {
  $string = osc_genRandomPassword(32) . $alert;
  osc_set_alert_private_key(); // renew private key and
  osc_set_alert_public_key();  // public key
  $key = hash( 'sha256' , osc_get_alert_private_key(), true);

  if(function_exists('openssl_digest') && function_exists('openssl_encrypt') && function_exists('openssl_decrypt') && in_array('aes-256-ctr', openssl_get_cipher_methods(true)) && in_array('sha256', openssl_get_md_methods(true))) {
    return Cryptor::Encrypt($string, $key, 0);
  }

  // COMPATIBILITY
  while(strlen($string) % 32 != 0) {
    $string .= "\0";
  }

  $cipher = new phpseclib\Crypt\Rijndael();
  $cipher->disablePadding();
  $cipher->setBlockLength(256);
  $cipher->setKey($key);
  $cipher->setIV($key);
  return $cipher->encrypt($string);
}


/**
* @param $string
*
* @return string
* @throws \Exception
*/
function osc_decrypt_alert($string) {
  $key = hash('sha256', osc_get_alert_private_key(), true);

  if(function_exists('openssl_digest') && function_exists('openssl_encrypt') && function_exists('openssl_decrypt') && in_array('aes-256-ctr', openssl_get_cipher_methods(true)) && in_array('sha256', openssl_get_md_methods(true))) {
    return trim(substr(Cryptor::Decrypt($string, $key, 0), 32));
  }

  // COMPATIBILITY
  $cipher = new phpseclib\Crypt\Rijndael();
  $cipher->disablePadding();
  $cipher->setBlockLength(256);
  $cipher->setKey($key);
  $cipher->setIV($key);
  return trim(substr($cipher->decrypt($string), 32));
}


function osc_set_alert_public_key() {
  if(!View::newInstance()->_exists('alert_public_key')) {
    Session::newInstance()->_set('alert_public_key', osc_random_string(32) );
  }
}


/**
 * @return string
 */
function osc_get_alert_public_key() {
  return Session::newInstance()->_get('alert_public_key');
}

function osc_set_alert_private_key() {
  if(!View::newInstance()->_exists('alert_private_key')) {
    Session::newInstance()->_set('alert_private_key', osc_random_string(32) );
  }
}


/**
 * @return string
 */
function osc_get_alert_private_key() {
  return Session::newInstance()->_get('alert_private_key');
}


/**
 * @param $length
 *
 * @return bool|string
 */
function osc_random_string( $length ) {
  $buffer = '';
  $buffer_valid = false;

  if(function_exists('openssl_random_pseudo_bytes')) {
    $buffer = openssl_random_pseudo_bytes($length);
    if($buffer) {
      $buffer_valid = true;
    }
  }

  if(!$buffer_valid && is_readable('/dev/urandom')) {
    $f  = fopen( '/dev/urandom' , 'rb' );
    $read = strlen($buffer);
    while($read < $length) {
      $buffer .= fread($f, $length - $read);
      $read = strlen($buffer);
    }
    fclose($f);
    if($read >= $length) {
      $buffer_valid = true;
    }
  }

  if(!$buffer_valid || strlen($buffer) < $length) {
    $bl = strlen($buffer);
    for($i = 0; $i < $length; $i++) {
      if($i < $bl) {
      $buffer[ $i ] ^= chr( mt_rand( 0 , 255 ) );
      } else {
      $buffer .= chr(mt_rand(0, 255));
      }
    }
  }

  if(!$buffer_valid) {
    $buffer = osc_genRandomPassword(2*$length);
  }
  return substr(str_replace('+', '.', base64_encode($buffer)), 0, $length);
}

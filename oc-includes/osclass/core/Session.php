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


class Session {
  private $session;
  private static $instance;

  /**
   * @return \Session
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    
    return self::$instance;
  }

  // Start session and get values from cookie
  public function session_start() {
    $currentCookieParams = session_get_cookie_params();
    
    if(defined('COOKIE_DOMAIN') && COOKIE_DOMAIN != '') {
      $currentCookieParams['domain'] = '.' . trim(COOKIE_DOMAIN, '. ');   // in config, define domain without leading dot
      
    } else if(function_exists('osc_subdomain_enabled') && osc_subdomain_enabled()) {
      $currentCookieParams['domain'] = '.' . trim(osc_get_parent_domain(), './ ');
    }
    
    if($currentCookieParams['domain'] != '' && strpos($currentCookieParams['domain'], 'http') !== false) {
      // $currentCookieParams['domain'] = preg_replace('#^https?://#', '', $currentCookieParams['domain']);
      $currentCookieParams['domain'] = parse_url($currentCookieParams['domain'], PHP_URL_HOST) ?? $currentCookieParams['domain'];
    }
    
    // Check if SSL enabled
    if((!empty($_SERVER['HTTPS']) && in_array(strtolower($_SERVER['HTTPS']), ['on','1'])) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'])==='https')) {
      $currentCookieParams['secure'] = true;
    }

    if($currentCookieParams['path'] == '') {
      $currentCookieParams['path'] = trim(REL_WEB_URL, '/ ');
      $currentCookieParams['path'] = ($currentCookieParams['path'] === '' ? '/' : $currentCookieParams['path']);
    }
    
    // Not sufficient to set here only, update php.ini!
    // ini_set('session.cookie_lifetime', 94608000);
    // ini_set('session.gc_maxlifetime', 94608000);
    if($currentCookieParams['lifetime'] <= 0) {
      $currentCookieParams['lifetime'] = 86400 * 365 * 3;  // 3 years in seconds, 94608000
    }
    
    // This require PHP 7.3 so let's not use it
    if(PHP_VERSION_ID >= 70300 && (isset($currentCookieParams['samesite']) || $currentCookieParams['samesite'] == '')) {
      $currentCookieParams['samesite'] = 'Lax';
    }

    $currentCookieParams['httponly'] = true;
    
    // Update params
    session_set_cookie_params($currentCookieParams);

    if(!isset($_SESSION) || session_status() !== PHP_SESSION_ACTIVE) {
      session_name('oc_session');      // osclass 
      
      if(!$this->_session_start()) {
        $ses_id = str_replace('.', '', uniqid('', true));
        
        session_id($ses_id);
        session_start();
        session_regenerate_id();
      }
    }

    $this->session = $_SESSION;
    
    if($this->_get('messages') == '') {
      $this->_set('messages', array());
    }
    
    if($this->_get('keepForm') == '') {
      $this->_set('keepForm', array());
    }
    
    if($this->_get('form') == '') {
      $this->_set('form', array());
    }
  }

  /**
   * @return bool
   */
  public function _session_start() {
    $sn = session_name();   // oc_session, name is constant
    
    // Try to identify session ID stored in cookies or get request, ie 3b1efxy4e6947e110859e2b4608db159
    if(isset($_COOKIE[$sn])) {
      $sessid = $_COOKIE[$sn];
      
    } elseif(isset($_GET[$sn])) {
      $sessid = $_GET[$sn];
      
    } else {
      return session_start();
    }

    if(!preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $sessid)) {
      return false;
    }
    
    return session_start();
  }

  public function session_destroy() {
    session_destroy();
    $_SESSION = [];
    
    // Delete session cookie with name "oc_session"
    // if(ini_get('session.use_cookies')) {
      // $params = session_get_cookie_params();
      // setcookie(session_name(), '', time() - 3600, $params['path'] ?? '/');
      // unset($_COOKIE[session_name()]);
    // }
  }

  /**
   * @param $key
   * @param $value
   */
  public function _set($key, $value = '') {
    $_SESSION[$key] = $value;
    $this->session[$key] = $value;
  }

  /**
   * @param $key
   *
   * @return mixed
   */
  public function _get($key) {
    if(isset($this->session[$key])) {
      return $this->session[$key];
    }

    return '';
  }

  /**
   * @param $key
   */
  public function _drop($key) {
    unset($_SESSION[$key], $this->session[$key]);
  }
  
  // Define referer into session
  public function _setReferer($value) {
    // Store previous referer
    Cookie::newInstance()->_setRefererHistory($value);

    // Store current referer
    $_SESSION['oc_http_referer'] = $value;
    $this->session['oc_http_referer'] = $value;
    
    // Set state
    $_SESSION['oc_http_referer_state'] = 0;
    $this->session['oc_http_referer_state'] = 0;
  }

  // Get referer
  public function _getReferer() {
    if(isset($this->session['oc_http_referer'])) {
      return $this->session['oc_http_referer'];
    }

    if(isset($_SESSION['oc_http_referer'])) {
      return $_SESSION['oc_http_referer'];
    }
    
    return '';
  }
  
  public function _dropReferer() {
    unset($_SESSION['oc_http_referer'], $this->session['oc_http_referer'], $_SESSION['oc_http_referer_state'], $this->session['oc_http_referer_state']);
  }
  
  public function _view() {
    print_r($this->session);
  }

  /**
   * @param $key
   * @param $value
   * @param $type
   */
  public function _setMessage($key, $value, $type) {
    $messages = $this->_get('messages');
    $messages = (!is_array($messages) ? array() : $messages);

    if($value !== false && $value !== '') {
      $messages[$key][] = array(
        'msg' => str_replace(PHP_EOL, '<br />', $value), 
        'raw' => $value,
        'type' => $type,
        'key' => osc_sanitizeString(osc_apply_filter('message_key', $key . '-' . $type . '-' . osc_highlight($value, 32)))
      );
      
      $this->_set('messages', $messages);
    }
  }

  /**
   * @param $key
   *
   * @return string|array
   */
  public function _getMessage($key) {
    $messages = $this->_get('messages');

    if(isset($messages[$key])) {
      return $messages[$key];
    }

    return '';
  }

  /**
   * @param $key
   */
  public function _dropMessage($key) {
    $messages = $this->_get('messages');
    unset($messages[$key]);
    
    $this->_set('messages', $messages);
  }

  /**
   * @param $key
   */
  public function _keepForm($key) {
    $aKeep = $this->_get('keepForm');
    $aKeep[$key] = 1;
    $this->_set('keepForm', $aKeep);
  }

  /**
   * @param string $key
   */
  public function _dropKeepForm($key = '') {
    $aKeep = $this->_get('keepForm');
    if($key!='') {
      unset($aKeep[$key]);
      $this->_set('keepForm', $aKeep);
      
    } else {
      $this->_set('keepForm', array());
    }
  }

  /**
   * @param $key
   * @param $value
   */
  public function _setForm($key, $value) {
    $form = $this->_get('form');
    $form[$key] = $value;
    $this->_set('form', $form);
  }

  /**
   * @param string $key
   *
   * @return string|array
   */
  public function _getForm($key = '') {
    $form = $this->_get('form');
    
    if($key!='') {
      if(isset($form[$key])) {
        return $form[$key];
      }

      return '';
    }

    return $form;
  }

  /**
   * @return string|array
   */
  public function _getKeepForm() {
    return $this->_get('keepForm');
  }

  public function _viewMessage() {
    print_r($this->session['messages']);
  }

  public function _viewForm() {
    print_r($_SESSION['form']);
  }

  public function _viewKeep() {
    print_r($_SESSION['keepForm']);
  }

  public function _clearVariables() {
    $form = $this->_get('form');
    $aKeep = $this->_get('keepForm');
    
    if(is_array($form)) {
      foreach ($form as $key => $value) {
        if(!isset($aKeep[$key])) {
          unset($_SESSION[ 'form' ][ $key ], $this->session[ 'form' ][ $key ]);
        }
      }
    }

    if(isset($this->session['oc_http_referer_state'])) {
      $this->session['oc_http_referer_state']++;
      $_SESSION['oc_http_referer_state']++;
      
      if((int) $this->session['oc_http_referer_state'] >= 2) {
        $this->_dropReferer();
        // maybe drop preReferer too?
      }
    }
  }
}

/* file end: ./oc-includes/osclass/core/Session.php */
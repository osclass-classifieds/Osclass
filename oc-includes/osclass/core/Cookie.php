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


class Cookie {
  public $name;
  public $val;
  public $expires;
  public $path = '';
  public $domain = '';
  public $secure = false;
  public $httponly = true;
  
  private static $instance;

  /**
   * @return \Cookie
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    
    return self::$instance;
  }

  public function __construct() {
    $this->val = array();
    $hostname = parse_url(osc_base_url(), PHP_URL_HOST) ?? 'localhost';

    // Set cookies path - sanitize it
    $this->path = trim(REL_WEB_URL, '/ ');
    $this->path = ($this->path === '' ? '/' : $this->path);
    
    // Set cookies domain
    if(defined('COOKIE_DOMAIN') && trim(COOKIE_DOMAIN, './ ') != '') {
      $this->domain = trim(COOKIE_DOMAIN, './ ');   // in config, define domain without leading dot, ie website.com

    } else if(osc_subdomain_enabled()) {
      $this->domain = trim(osc_get_parent_domain(), './ ');
    }

    if($this->domain != '' && strpos($this->domain, 'http') !== false) {
      $this->domain = parse_url($this->domain, PHP_URL_HOST) ?? $this->domain;
    }
    
    if(osc_is_ssl()) {
      $this->secure = true;
    }
    
    // $http_url = osc_is_ssl() ? "https://" : "http://";
    // $web_path = ($domain == '' ? WEB_PATH : $http_url . $domain);
    // $this->name = md5($web_path);

    // Construct osclass cookies name
    $name_ = (osc_subdomain_enabled() ? trim(osc_get_parent_domain(), './ ') : ($hostname ?? $this->domain));
    $this->name = 'oc_master_' . str_replace('.', '_', $name_);


    // Set expires
    $this->expires = time() + (86400 * 365 * 3); // 3 years by default


    // Set cookie id - use as non-logged user id
    if(!isset($_COOKIE['oc_master_id']) || $_COOKIE['oc_master_id'] == '') {
      $this->_setcookie('oc_master_id', bin2hex(random_bytes(6)));
    }
    
    // Set cookie timestamp
    if(!isset($_COOKIE['oc_master_created']) || $_COOKIE['oc_master_created'] == '') {
      $this->_setcookie('oc_master_created', date('Y-m-d_H-i-s'));
    }

    // Get values from Osclass entry and set cookies
    if(isset($_COOKIE[$this->name]) && $_COOKIE[$this->name] != '') {
      $cookies_arr = $this->decodeCookieArray($_COOKIE[$this->name]);
      
      if(count($cookies_arr) > 0) {
        foreach($cookies_arr as $ckey => $cval) {
          $this->_setcookie($ckey, $cval);    // run setcookie & update $this->val
        }
      }
    }
  }

  
  // Set cookie in uniform way
  public function _setcookie($key, $value = '', $httponly = NULL) {
    // Set if cookie can be read by javascript or no ($httponly = true means javascript cannot read cookie value)
    
    if($httponly !== NULL) {
      $httponly = ($httponly === true ? true : false);
      
    } else {
      $httponly = $this->httponly;
      
      // Osclass cookies & explicitely defined as secure cookies are always httponly. Ie: oc_userId, eps_secret_secure
      if(strpos($key, 'oc_') === 0 || stripos($key, '_secure') !== false) {
        $httponly = true;
        
      } else if(stripos($key, '_unsecure') !== false || stripos($key, '_unsec') !== false || stripos($key, '_js') !== false || strpos($key, 'ocx_') === 0 || strpos($key, 'ocjs_') === 0 || strpos($key, 'oc_js_') === 0 || strpos($key, 'js_') === 0) {
      // } else if (preg_match('/(_unsecure|_unsec|_js|^ocx_|^ocjs_|^js_)/i', $key)) {
        $httponly = false;
      }
    }


    // Encode explicit cookies (osclass one is encoded)
    $key_clean = $this->sanitizeCookieName($key);
    $value_clean = $this->encodeCookieValue($value);


    // Create cookie
    if((string)$key_clean != '' && (string)$value_clean != '') {
      // Set cookies those are not going to be stored in oc_master
      if(!in_array($key, array($this->name, 'oc_master_id', 'oc_master_created', 'oc_http_referer_history'))) {
        $this->val[$key_clean] = $value;    // Do not encode value stored in class
      }
      
      if(PHP_VERSION_ID >= 70300) {
        setcookie($key_clean, $value_clean, [
          'expires' => $this->expires,
          'path' => $this->path,
          'domain' => $this->domain,
          'secure' => $this->secure,
          'httponly' => $httponly,
          'samesite' => 'Lax'
        ]);
        
      } else {
        setcookie($key_clean, $value_clean, $this->expires, $this->path, $this->domain, $this->secure, $httponly);
      }
      
      return true;
      
    // Drop cookie
    } else {
      if(PHP_VERSION_ID >= 70300) {
        setcookie($key_clean, '', [
          'expires' => time() - 3600,
          'path' => $this->path,
          'domain' => $this->domain,
          'secure' => $this->secure,
          'httponly' => $httponly,
          'samesite' => 'Lax'
        ]);
        
      } else {
        setcookie($key_clean, '', time() - 3600, $this->path, $this->domain, $this->secure, $httponly);
      }
      
      unset($this->val[$key_clean], $_COOKIE[$key_clean]);
      return false;
    }
  }
  

  // Define cookie value into array
  public function push($key, $value = '', $httponly = NULL) {
    $this->_setcookie($key, $value, $httponly);
  }
 

  // Update osclass cookie entry
  public function set() {
    $cookie_val = $this->encodeCookieArray($this->val);
    $this->_setcookie($this->name, $cookie_val);
  }


  // Get cookies value
  public function get_value($key) {
    $key_clean = $this->sanitizeCookieName($key);

    if(isset($this->val[$key_clean])) {
      return $this->val[$key_clean];
    }
    
    if(isset($COOKIE[$key_clean])) {
      return $COOKIE[$key_clean];
    }
    
    return '';
  }


  // New functions
  public function _get($key) {
    return $this->get_value($key);
  }
  
  public function _set($key, $value = '', $httponly = NULL) {
    $this->push($key, $value, $httponly);
    $this->set();
  }

  public function _drop($key) {
    $this->pop($key);
    $this->set();
  }

  public function _dropAll() {
    $this->clear();
    $this->set();
  }

  public function _count() {
    return $this->num_vals();
  }
  

  // Remove one cookie
  public function pop($key) {
    $this->_setcookie($key, '');
  }


  // Remove all cookies
  public function clear() {
    $this->val = array();
  }


  // Count cookies
  public function num_vals() {
    return count($this->val);
  }


  // It's not supported to set custom expires for cookies from 831
  public function set_expires($tm) {
    // $this->expires = time() + $tm;
    return false;
  }



  // Encode cookies
  // Stores cookies as one value in format key1=value1|key2=value2|key3=value3
  public function encodeCookieArray($data = array()) {
    $pairs = array();
 
    if(is_array($data) && count($data) > 0) {
      // ksort($data);

      foreach($data as $key => $value) {
        if($key !== '' && $value !== '') {
          $pairs[] = $this->encodeCookieValue($key) . '=' . $this->encodeCookieValue($value);   // Use rawurlencode to avoid conflicts with = and |
        }
      }
    }

    return implode('|', $pairs);
  }


  // Decode cookies
  public function decodeCookieArray($cookie = '', $encoded = true) {
    if($encoded) {
      $cookie = $this->decodeCookieValue($cookie);
    }
    
    $result = array();
    $pairs = explode('|', $cookie);

    if(count($pairs) > 0) {
      foreach($pairs as $pair) {
        $parts = explode('=', $pair, 2);
        
        if(count($parts) === 2) {
          $result[$this->decodeCookieValue($parts[0])] = $this->decodeCookieValue($parts[1]);
        } else {
          $result[$this->decodeCookieValue($parts[0])] = '';
        }
      }
    }

    // ksort($result);
    return $result;
  }
  
  
  // Encode cookie value
  public function encodeCookieValue($value = '') {
    return rawurlencode((string)$value);
  }


  // Decode cookie value
  public function decodeCookieValue($value = '') {
    return rawurldecode((string)$value);
  }


  // Sanitize cookie name (key)
  public function sanitizeCookieName($name) {
    $name = preg_replace('/[^a-zA-Z0-9\-_]/', '', $name);

    return $name;
  }


  // Save user urls history
  public function _setRefererHistory($value = null) {
    if(DISABLE_URL_HISTORY === true) {
      $this->_drop('oc_http_referer_history');
      return array();
    }
    
    $ref_hist = (array)$this->_getRefererHistory();
    $http_ref = Params::getServerParam('HTTP_REFERER', false, false);
    $http_ref_path = parse_url($http_ref, PHP_URL_PATH);
    
    // Check if URL is OK
    if(filter_var($http_ref, FILTER_VALIDATE_URL) === false) {
      return false;
    }

    // Validate referer url
    if(Params::existServerParam('HTTP_REFERER')) {
      if($this->urlValidForHist($http_ref) !== false) {
        array_unshift($ref_hist, $http_ref);
        $ref_hist = array_slice(array_filter(array_unique($ref_hist)), 0, 5);
      }
    }

    // Now do current value/url
    $value = ($value === null ? osc_get_current_url() : $value);

    if($this->urlValidForHist($value) !== false) {
      array_unshift($ref_hist, $value);                                  // Add latest page at first array index position
      $ref_hist = array_slice(array_filter(array_unique($ref_hist)), 0, 5);     // Keep last XY urls
    }
    
    $this->_set('oc_http_referer_history', $this->encodeUrlArray($ref_hist));
  }


  // Get last XY referers history
  public function _getRefererHistory() {
    if(DISABLE_URL_HISTORY === true) {
      $this->_drop('oc_http_referer_history');
      return array();
    }
    
    $ref_hist = $this->get_value('oc_http_referer_history');
    $ref_hist = $ref_hist != '' ? $this->decodeUrlArray($ref_hist) : array();
    
    return (array)$ref_hist;
  }
  

  // From referer history, get last valid (that does not match to login/registration/redirect page
  public function _getTrueReferer() {
    $hist = (array)$this->_getRefererHistory();
    $ref = '';

    if(is_array($hist) && count($hist) > 0) {
      foreach($hist as $h) {
        // Validate url
        if($this->urlValidForHist($h) == false) {
          continue;
        }
        
        $current = osc_get_current_url();
        
        if(OC_ADMIN === true || stripos(osc_get_current_url(), OC_ADMIN_FOLDER)) {
          $is_backoffice = true;
        } else {
          $is_backoffice = false;
        }
        
      
        // For front - get front url, for oc-admin - get oc-admin url
        if($is_backoffice === true && stripos($h, osc_admin_base_url()) === false || $is_backoffice === false && stripos($h, osc_admin_base_url()) !== false) {
          continue;
        }

        // Check if it's standard page
        if(in_array($h, array(osc_search_url(), osc_contact_url(), osc_item_post_url(), osc_user_dashboard_url(), osc_user_items_url(), osc_user_profile_url()))) {
          return $h;
        }
        
        return $h;
      }
    }

    return false;
  }
  
  
  // Encode url array
  public function encodeUrlArray($data = array()) {
    $url_arr = array();

    if(is_array($data) && count($data) > 0) {
      foreach($data as $url) {
        if($url !== '') {
          $url_arr[] = rawurlencode($url);   // Use rawurlencode to avoid conflicts with |
        }
      }
    }

    return implode('|', $url_arr);
  }


  // Decode url array
  public function decodeUrlArray($cookie = '') {
    $result = array();
    $url_arr = explode('|', $cookie);

    if(count($url_arr) > 0) {
      foreach($url_arr as $url) {
        $result[] = rawurldecode($url);
      }
    }

    return $result;
  }


  // Check if URL is valid to be stored into history for redirects
  public function urlValidForHist($url = '') {
    $url = trim((string)$url);
    $uri = parse_url($url, PHP_URL_PATH);
    
    // Check if it does not refer 3rd party url
    if($url == '' || strlen($url) <= 5 || stripos($url, osc_base_url()) === false) {
      return false;
    }

    // Invalid url
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
      return false;
    }
    
    // File url
    if(preg_match('/\.[a-zA-Z0-9]{3,4}(\?.*)?$/', $uri)) {
      return false;
    }
    
    // Check if it's wrong page.
    // if(in_array($url, array(osc_base_url(), osc_base_url(true), osc_base_url(true, true), osc_base_url(false, true), osc_admin_base_url(), osc_admin_base_url(true), osc_admin_base_url(true) . '&page=login', osc_user_logout_url(), osc_user_login_url(), osc_register_account_url()))) {
    if(in_array($url, array(osc_admin_base_url(true) . '&page=login', osc_user_logout_url(), osc_user_login_url(), osc_register_account_url(), osc_recover_user_password_url(), osc_change_user_password_url(), osc_change_user_email_url()))) {
      return false;
    }
    
    // Check if it contains blocked word
    $blocked_words = array(
      'login','logout','register','recover','forgot','activate',
      osc_get_preference('rewrite_user_login'),osc_get_preference('rewrite_user_logout'),osc_get_preference('rewrite_user_register'),osc_get_preference('rewrite_user_forgot'),osc_get_preference('rewrite_user_recover'),osc_get_preference('rewrite_user_change_password'),osc_get_preference('rewrite_user_change_email'),osc_get_preference('rewrite_user_change_username'),osc_get_preference('rewrite_user_change_email_confirm'),osc_get_preference('rewrite_user_activate'),
      '/images/','/img/','/css/','/js/','/minify/','/themes/','/plugins/','/oc-content/','/uploads/'
    );
    
    $blocked_words = array_filter(array_unique(array_map('trim', $blocked_words)));
    
    foreach($blocked_words as $bword) {
      if(stripos($uri, $bword) !== false) {
        return false;
      }
    }

    return $url;
  }
}

/* file end: ./oc-includes/osclass/core/Cookie.php */
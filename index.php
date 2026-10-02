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


define('ABS_PATH', str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/'));

if(PHP_SAPI === 'cli') {
  define('CLI', true);
}

require_once ABS_PATH . 'oc-load.php';

if(CLI) {
  $cli_params = getopt('p:t:');
  Params::setParam('page', $cli_params['p']);
  Params::setParam('cron-type', $cli_params['t']);
  
  if(Params::getParam('page') == 'upgrade') {
    require_once(osc_lib_path() . 'osclass/upgrade-funcs.php');
    exit(1);
    
  } else if(!in_array(Params::getParam('page'), array('cron')) && !in_array(Params::getParam('cron-type'), array('minutely','hourly','daily','weekly','monthly','yearly'))) {
    exit(1);
  }
}

if(Params::getParam('page') == 'cron' && !defined('__FROM_CRON__')) {
  define('__FROM_CRON__', true);
}

if(file_exists(ABS_PATH . '.maintenance')) {
  if(!osc_is_admin_user_logged_in()) {
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 900');
    
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath().'maintenance.php')) {
      osc_current_web_theme_path('maintenance.php');
      die();
    } else {
      require_once LIB_PATH . 'osclass/helpers/hErrors.php';

      $title = __('Maintenance');
      $message = sprintf(__('We are sorry for any inconvenience. %s is undergoing maintenance.') . '.', osc_page_title() );
      osc_die($title, $message);
    }
  } else {
    define('__OSC_MAINTENANCE__', true);
  }
}


if(!osc_users_enabled() && osc_is_web_user_logged_in()) {
  Session::newInstance()->_drop('userId');
  Session::newInstance()->_drop('userName');
  Session::newInstance()->_drop('userEmail');
  Session::newInstance()->_drop('userPhone');

  Cookie::newInstance()->pop('oc_userId');
  Cookie::newInstance()->pop('oc_userSecret');
  Cookie::newInstance()->set();
}

if(osc_is_web_user_logged_in()) {
  User::newInstance()->lastAccess(osc_logged_user_id(), date('Y-m-d H:i:s'), osc_get_ip(), 60); // update once per 1 minute = 60s
}


// Manage lang param in URL here so no redirect is required
$lang = str_replace('-', '_', (string)Params::getParam('lang'));
$locale = (string)osc_current_user_locale();
$lang_is_long = (preg_match('/^[a-z]{2}_[a-zA-Z]{2}$/i', $lang) === 1);
$lang_is_short = (!$lang_is_long && preg_match('/^[a-z]{2}$/i', $lang) === 1);
$lang_strict = '';
if($lang_is_long) {
  $lang_strict = strtolower(substr($lang, 0, 2)) . '_' . strtoupper(substr($lang, 3, 2));
}

// Long format (en-us) becomes en_us after hyphen replace. Compare to en_US, not the raw slug.
if(osc_rewrite_enabled() && Params::getParam('page') != 'language' && $lang != '') {
  if($lang_is_long && $lang_strict != '' && strcasecmp($locale, $lang_strict) != 0) {
    Session::newInstance()->_set('userLocale', $lang_strict);
    Translation::init();
    osc_run_hook('user_locale_changed', $lang_strict);
  } else if($lang_is_short && strtolower(substr($locale, 0, 2)) != strtolower($lang)) {
    $find_lang = OSCLocale::newInstance()->findByShortCode($lang);

    if($find_lang !== false && isset($find_lang['pk_c_code']) && $find_lang['pk_c_code'] != '') {
      Session::newInstance()->_set('userLocale', $find_lang['pk_c_code']);
      Translation::init();
      osc_run_hook('user_locale_changed', $find_lang['pk_c_code']);
    }
  }
}

// When locale slug is enabled in URL and home page is loaded without it, redirect to page with lang slug
// Only home page is redirected to avoid issues with redirect loops
if(osc_rewrite_enabled() && osc_subdomain_type() != 'language' && osc_locale_to_base_url_enabled() && osc_is_home_page() && osc_get_current_url() == osc_base_url() && Params::getParam('page') != 'language') {
  if(
    osc_locale_to_base_url_type() == 'LONG' && !(preg_match('/\/[a-z]{2}_[a-zA-Z]{2}\//', osc_get_current_url()) || preg_match('/\/[a-z]{2}-[a-zA-Z]{2}\//', osc_get_current_url()))
    || osc_locale_to_base_url_type() == '' && !preg_match('/\/[a-z]{2}\//', osc_get_current_url())
  ) {
    $redirect_url = str_replace(osc_base_url(), osc_base_url(false, true), osc_get_current_url());   // add slug to link
    osc_redirect_to($redirect_url);
  }
}

// Manage subdomain auto-redirects and landing page
if(osc_subdomain_enabled() && Params::getParam('page') != 'cron' && Params::getParam('page') != 'logout') {
  if(osc_subdomain_landing_enabled() || osc_subdomain_redirect_enabled()) {
    // Load main/home class to initiate BaseModel and get subdomain related data
    require_once(osc_lib_path() . 'osclass/controller/main.php');
    $do = new CWebMain();
    
    $block_redirect = false;
    if(Params::getParam('nored') == 1) {
      $block_redirect = true;
    }
    
    if(osc_subdomain_redirect_enabled() && osc_subdomain_type() == 'country') {
      // Try to identify country and save it into cookies
      osc_user_country_from_ip();
      
      // Automatically redirect from top-domain to sub-domain
      if(osc_is_topdomain() && $block_redirect === false) {
        if(Cookie::newInstance()->get_value('ip_data_status') == 'FOUND_EXISTS') {
          $country_url = Cookie::newInstance()->get_value('ip_country_url');
          
          if($country_url != '' && $country_url != osc_subdomain_top_url(false, false) && $country_url != osc_base_url()) {
            osc_redirect_to($country_url);
          }
        }
      }

      // If sub-domain is restricted and user IP does not match, redirect to top-domain
      if(osc_is_subdomain() && $block_redirect === false && !osc_is_admin_user_logged_in()) {
        $subdomain_id = strtolower(osc_subdomain_id());
        $restricted_country_ids = array_filter(explode(',', osc_subdomain_restricted_ids()));

        if($subdomain_id != '' && is_array($restricted_country_ids) && count($restricted_country_ids) > 0) {
          if(osc_subdomain_restricted_ids() == 'all') {
            if(Cookie::newInstance()->get_value('ip_country_code') != $subdomain_id) {
              $country_name = ucwords(trim(Cookie::newInstance()->get_value('ip_data_country_name')));
              $country_name = ($country_name <> '' ? $country_name : __('your country'));
              
              osc_redirect_to(osc_subdomain_top_url(true, true) . '&restricted=1&restrictedFrom=' . urlencode($country_name));
            }
          }
          
          if(in_array($subdomain_id, $restricted_country_ids)) {
            if(Cookie::newInstance()->get_value('ip_country_code') != $subdomain_id) {
              osc_redirect_to(osc_subdomain_top_url(true, true) . '&restricted=2&restrictedDomain=' . urlencode(osc_subdomain_name()));
            }
          }
        }
      }
    } 
    
    // Landing page visible only if redirect failed or is disabled
    if(osc_subdomain_landing_enabled()) {    
      if(osc_is_topdomain() && !osc_is_home_page() && $block_redirect === false) {
        osc_redirect_to(osc_base_url());
      }

      if(osc_subdomain_type() == 'country') {
        if(Params::getParam('restricted') == 1) {
          osc_add_flash_error_message(sprintf(__('Sorry, this site is not available from %s!'), urldecode(Params::getParam('restrictedFrom'))));
          osc_redirect_to(osc_subdomain_top_url(true, true));
          
        } else if (Params::getParam('restricted') == 2) {
          osc_add_flash_error_message(sprintf(__('Sorry, this site is only available to customers from %s!'), urldecode(Params::getParam('restrictedDomain'))));
          osc_redirect_to(osc_subdomain_top_url(true, true));
        }
      }
      
      if(osc_is_topdomain()) {
        if(file_exists(WebThemes::newInstance()->getCurrentThemePath().'subdomain-navigation.php')) {
          osc_current_web_theme_path('subdomain-navigation.php');
          die();
        } else {
          require_once LIB_PATH . 'osclass/helpers/hErrors.php';

          $title = osc_page_title();
          $message = '';
          
          if(Params::getParam('restricted') == 1) {
            $message .= '<div class="sd-err">' . sprintf(__('Sorry, this site is only available to customers from %s!'), urldecode(Params::getParam('restrictedName'))) . '</div>';
          }
          
          $message .= '<div class="m25">' . __('Join our community to buy and sell from each other everyday around the world.') . '</div>';
          $message .= '<div><strong>' . __('Please select preferred site:') . '</strong></div>';
          $message .= osc_subdomain_links(false, false, false, 1000, 0);
          
          osc_die($title, $message);
        }
      }
    }
  }
}

switch(Params::getParam('page')){
  case ('cron'):    // cron system
    if(!defined('__FROM_CRON__')) {
      define('__FROM_CRON__', true);
    }
    require_once(osc_lib_path() . 'osclass/cron.php');
    break;

  case ('user'):    // user pages (with security)
    if(
      Params::getParam('action')=='change_email_confirm' || Params::getParam('action')=='activate_alert'
      || (Params::getParam('action')=='unsub_alert' && !osc_is_web_user_logged_in())
      || Params::getParam('action')=='contact_post'
      || Params::getParam('action')=='pub_profile'
    ) {
      require_once(osc_lib_path() . 'osclass/controller/user-non-secure.php');
      $do = new CWebUserNonSecure();
      $do->doModel();
    } else {
      require_once(osc_lib_path() . 'osclass/controller/user.php');
      $do = new CWebUser();
      $do->doModel();
    }
    break;

  case ('item'):    // item pages
    require_once(osc_lib_path() . 'osclass/controller/item.php');
    $do = new CWebItem();
    $do->doModel();
    break;

  case ('search'):  // search pages
    require_once(osc_lib_path() . 'osclass/controller/search.php');
    $do = new CWebSearch();
    $do->doModel();
    break;

  case ('page'):    // static pages
    require_once(osc_lib_path() . 'osclass/controller/page.php');
    $do = new CWebPage();
    $do->doModel();
    break;

  case ('register'):  // register page
    require_once(osc_lib_path() . 'osclass/controller/register.php');
    $do = new CWebRegister();
    $do->doModel();
    break;

  case ('ajax'):    // ajax
    require_once(osc_lib_path() . 'osclass/controller/ajax.php');
    $do = new CWebAjax();
    $do->doModel();
    break;

  case ('login'):   // login page
    require_once(osc_lib_path() . 'osclass/controller/login.php');
    $do = new CWebLogin();
    $do->doModel();
    break;

  case ('language'):  // set language
    require_once(osc_lib_path() . 'osclass/controller/language.php');
    $do = new CWebLanguage();
    $do->doModel();
    break;

  case ('contact'):   //contact
    require_once(osc_lib_path() . 'osclass/controller/contact.php');
    $do = new CWebContact();
    $do->doModel();
    break;

  case ('report'):   //report
    require_once(osc_lib_path() . 'osclass/controller/report.php');
    $do = new CWebReport();
    $do->doModel();
    break;

  case ('custom'):   //custom
    require_once(osc_lib_path() . 'osclass/controller/custom.php');
    $do = new CWebCustom();
    $do->doModel();
    break;

  default:          // home
    require_once(osc_lib_path() . 'osclass/controller/main.php');
    $do = new CWebMain();
    $do->doModel();
    break;

}


// Built-in auto-cron: at most once per 5 minutes (MINUTELY), never from a cron/self request
if(
  !defined('__FROM_CRON__')
  && Params::getParam('page') != 'cron'
  && Params::getParam('auto_cron') != 1
  && strpos((string)Params::getServerParam('HTTP_USER_AGENT', false, false), 'Osclass (v.') !== 0
  && strpos((string)Params::getServerParam('HTTP_REFERER', false, false), 'Osclass') !== 0
  && osc_auto_cron()
) {
  $cron_minutely = Cron::newInstance()->getCronByType('MINUTELY');
  if(is_array($cron_minutely)) {
    $i_next = (isset($cron_minutely['d_next_exec']) && $cron_minutely['d_next_exec'] !== '' ? (strtotime($cron_minutely['d_next_exec']) ?: 0) : 0);
    if((time() - $i_next) >= 0) {
      // Claim slot first so concurrent page loads cannot stampede
      $d_next = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:00')) + (5 * 60));
      Cron::newInstance()->update(array('d_next_exec' => $d_next), array('e_type' => 'MINUTELY'));
      osc_doRequest(osc_base_url(true) . '?page=cron', array('page' => 'cron', 'auto_cron' => 1), 5, false);
    }
  }
}

/* file end: ./index.php */
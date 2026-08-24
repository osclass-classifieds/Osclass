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
 * Class CWebLanguage
 */
class CWebLanguage extends BaseModel {
  public function __construct() {
    parent::__construct();
    osc_run_hook('init_language');
  }

  // When user change language, it is reflected here
  public function doModel() {
    $changed = false;
    $locale = str_replace('-', '_', Params::getParam('locale'));
    $locale_strict = strtolower(substr($locale, 0, 2)) . '_' . strtoupper(substr($locale, 3, 2));   // os810

    //if(preg_match('/.{2}_.{2}/', $locale)) {
    if(preg_match('/[a-z]{2}_[a-zA-Z]{2}/', $locale) || preg_match('/\/[a-z]{2}-[a-zA-Z]{2}\//', $locale)) {
      Session::newInstance()->_set('userLocale', $locale_strict);
      osc_run_hook('user_locale_changed', $locale_strict);
      $changed = true;
    //} else if(preg_match('/.{2}/', $locale)) {
    } else if(preg_match('/[a-z]{2}/', $locale)) {
      $find_lang = OSCLocale::newInstance()->findByShortCode($locale);
      
      if($find_lang !== false && isset($find_lang['pk_c_code']) && $find_lang['pk_c_code'] != '') {
        Session::newInstance()->_set('userLocale', $find_lang['pk_c_code']);
        osc_run_hook('user_locale_changed', $find_lang['pk_c_code']);
        $changed = true;
      }
    }
    
    $redirect_url = '';
    if(Params::getServerParam('HTTP_REFERER', false, false) != '') {
      $redirect_url = Params::getServerParam('HTTP_REFERER', false, false);
    } else {
      $redirect_url = osc_base_url(true);
    }

    // URL contains language in format .../en/...
    if(preg_match('/\/[a-z]{2}\//', $redirect_url) && $changed) {
      $redirect_url = preg_replace('/\/[a-z]{2}\//', '/' . substr($locale, 0, 2) . '/', $redirect_url);
      
    // URL contains language in format .../en-US/... 
    // This might only support /en-us/ in future!
    } else if(preg_match('/\/[a-z]{2}-[a-zA-Z]{2}\//', $redirect_url) && $changed) {
      $redirect_url = preg_replace('/\/[a-z]{2}-[a-zA-Z]{2}\//', '/' . str_replace('_', '-', $locale) . '/', $redirect_url);

    // URL contains language in format .../en_US/...
    } else if(preg_match('/\/[a-z]{2}_[a-zA-Z]{2}\//', $redirect_url) && $changed) {
      $redirect_url = preg_replace('/\/[a-z]{2}_[a-zA-Z]{2}\//', '/' . $locale . '/', $redirect_url);
    }

    $this->redirectTo($redirect_url);
  }


  /**
   * @param $file
   *
   * @return mixed|void
   */
  public function doView($file) { }
}

/* file end: ./language.php */
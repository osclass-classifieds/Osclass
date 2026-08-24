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
 * Class BaseModel
 */
abstract class BaseModel {
  protected $page;
  protected $action;
  protected $ajax;
  protected $time;

  public function __construct() {
    // this is necessary because if HTTP_HOST doesn't have the PORT the parse_url is null
    $current_host = parse_url(Params::getServerParam('HTTP_HOST'), PHP_URL_HOST);
    if($current_host === null) {
      $current_host = Params::getServerParam('HTTP_HOST');
    }

    if(parse_url(osc_base_url(), PHP_URL_HOST) !== $current_host) {
      // first check if it's http or https
      $url = 'http://';
      
      if(osc_is_ssl()) {
        $url = 'https://';
      }

      // append the domain
      $url .= parse_url(osc_base_url(), PHP_URL_HOST);
      
      // append the port number if it's necessary
      $http_port = parse_url(Params::getServerParam('HTTP_HOST'), PHP_URL_PORT);
      
      if($http_port !== 80) {
        $url .= ':' . parse_url(Params::getServerParam('HTTP_HOST'), PHP_URL_PORT);
      }
      
      // append the request
      $url .= Params::getServerParam('REQUEST_URI', false, false);

      $this->redirectTo($url);
    }

    try {
      $this->subdomain_params($current_host);
    } catch (Exception $e) {
    }
    
    $this->page = Params::getParam('page');
    $this->action = Params::getParam('action');
    $this->ajax = false;
    $this->time = microtime(true);
    
    osc_run_hook('before_init');
    
    WebThemes::newInstance();
    Cookie::newInstance()->_setRefererHistory();

    osc_run_hook('init');
  }

  public function __destruct() {
    if(!$this->ajax && OSC_DEBUG && Params::getParam('ajaxRequest') != 1 && Params::getParam('nolog') != 1) {
      echo '<!-- ' . $this->getTime() . ' seg. -->';
    }
  }

  //to export variables at the business layer

  /**
   * @param $key
   * @param $value
   */
  public function _exportVariableToView($key, $value) {
    View::newInstance()->_exportVariableToView($key, $value);
  }

  //only for debug (deprecated, all inside View.php)

  /**
   * @param null $key
   */
  public function _view($key = null) {
    View::newInstance()->_view($key);
  }

  //Funciones que se tendran que reescribir en la clase que extienda de esta
  abstract protected function doModel();

  /**
   * @param $file
   *
   * @return mixed
   */
  abstract protected function doView($file);

  public function do400() {
    Rewrite::newInstance()->set_location('error');
    header('HTTP/1.1 400 Bad Request');
    osc_current_web_theme_path('404.php');
    exit;
  }

  public function do404() {
    Rewrite::newInstance()->set_location('error');
    header('HTTP/1.1 404 Not Found');
    osc_current_web_theme_path('404.php');
    exit;
  }

  public function do410() {
    Rewrite::newInstance()->set_location('error');
    header('HTTP/1.1 410 Gone');
    osc_current_web_theme_path('404.php');
    exit;
  }

  /**
   * @param  $url
   * @param null $code
   */
  public function redirectTo($url, $code = null) {
    osc_redirect_to($url, $code);
  }

  /**
   * @return mixed
   */
  public function getTime() {
    $timeEnd = microtime(true);
    return $timeEnd - $this->time;
  }

  /**
   * @param $host
   *
   * @throws \Exception
   */
  private function subdomain_params($host) {
    $subdomain_type = osc_subdomain_type();
    $subhost = osc_subdomain_host();
    
    // strpos is used to check if the domain is different, useful when accessing the website by diferent domains
    if($subdomain_type != '' && $subhost != '' && strpos($host, $subhost) !== false && preg_match('|^(www\.)?(.+)\.' . $subhost . '$|i', $host, $match)) {
      $subdomain = $match[2];
      
      if($subdomain != '' && $subdomain !== 'www') {
        if($subdomain_type === 'category') {
          $category = osc_get_category_row_by_slug($subdomain);

          if(isset($category['pk_i_id'])) {
            View::newInstance()->_exportVariableToView('subdomain_id', (int)$category['pk_i_id']);
            View::newInstance()->_exportVariableToView('subdomain_name', $category['s_name']);
            View::newInstance()->_exportVariableToView('subdomain_slug', $category['s_slug']);
            View::newInstance()->_exportVariableToView('subdomain_param', 'sCategory');
            Params::setParam('sCategory', $category['pk_i_id']);

            // block home page and redirect to search for category based subdomains
            if(Params::getParam('page') == '') {
              Params::setParam('page', 'search');
            }
          } else {
            $this->do400();
          }
        } else if($subdomain_type === 'country') {
          $country = Country::newInstance()->findBySlug($subdomain);
          if(isset($country['pk_c_code'])) {
            View::newInstance()->_exportVariableToView('subdomain_id', strtoupper((string)$country['pk_c_code']));
            View::newInstance()->_exportVariableToView('subdomain_name', $country['s_name']);
            View::newInstance()->_exportVariableToView('subdomain_slug', $country['s_slug']);
            View::newInstance()->_exportVariableToView('subdomain_param', 'sCountry');
            Params::setParam('sCountry', $country['pk_c_code']);
          } else {
            $this->do400();
          }
        } else if($subdomain_type === 'region') {
          $region = Region::newInstance()->findBySlug($subdomain);
          if(isset($region['pk_i_id'])) {
            View::newInstance()->_exportVariableToView('subdomain_id', (int)$region['pk_i_id']);
            View::newInstance()->_exportVariableToView('subdomain_name', $region['s_name']);
            View::newInstance()->_exportVariableToView('subdomain_slug', $region['s_slug']);
            View::newInstance()->_exportVariableToView('subdomain_param', 'sRegion');
            Params::setParam('sRegion', $region['pk_i_id']);
          } else {
            $this->do400();
          }
        } else if($subdomain_type === 'city') {
          $city = City::newInstance()->findBySlug($subdomain);
          if(isset($city['pk_i_id'])) {
            View::newInstance()->_exportVariableToView('subdomain_id', (int)$city['pk_i_id']);
            View::newInstance()->_exportVariableToView('subdomain_name', $city['s_name']);
            View::newInstance()->_exportVariableToView('subdomain_slug', $city['s_slug']);
            View::newInstance()->_exportVariableToView('subdomain_param', 'sCity');
            Params::setParam('sCity', $city['pk_i_id']);
          } else {
            $this->do400();
          }
        } else if($subdomain_type === 'user') {
          $user = User::newInstance()->findByUsername($subdomain);
          if(isset($user['pk_i_id'])) {
            View::newInstance()->_exportVariableToView('subdomain_id', (int)$user['pk_i_id']);
            View::newInstance()->_exportVariableToView('subdomain_name', $user['s_name']);
            View::newInstance()->_exportVariableToView('subdomain_slug', $user['s_username']);
            View::newInstance()->_exportVariableToView('subdomain_param', 'sUser');
            Params::setParam('sUser', $user['pk_i_id']);
          } else {
            $this->do400();
          }
        } else if($subdomain_type === 'language') {
          $lang = str_replace('-', '_', $subdomain);
          $locale = osc_current_user_locale();

          //if($lang != '' && (preg_match('/.{2}_.{2}/', $lang) && $locale != $lang || preg_match('/.{2}/', $lang) && substr($locale, 0, 2) != $lang)) {
          if($lang != '' && (preg_match('/.{2}_.{2}/', $lang) || preg_match('/.{2}/', $lang))) {
            $find_lang = false;
            
            if(preg_match('/.{2}_.{2}/', $lang)) {
              $lang = strtolower(substr($lang, 0, 2)) . '_' . strtolower(substr($lang, 3, 2));
              $find_lang = OSCLocale::newInstance()->findByShortCode($lang);
            } else if(preg_match('/.{2}/', $lang)) {
              $find_lang = OSCLocale::newInstance()->findByShortCode($lang);
            }
            
            if($find_lang !== false && isset($find_lang['pk_c_code']) && $find_lang['pk_c_code'] != '') {
              Session::newInstance()->_set('userLocale', $find_lang['pk_c_code']);
              Translation::init();

              View::newInstance()->_exportVariableToView('subdomain_id', osc_subdomain_locale_slug($find_lang['pk_c_code']));
              View::newInstance()->_exportVariableToView('subdomain_name', ($find_lang['s_short_name'] <> '' ? $find_lang['s_short_name'] : $find_lang['s_name']));
              View::newInstance()->_exportVariableToView('subdomain_slug', $find_lang['pk_c_code']);
              View::newInstance()->_exportVariableToView('subdomain_param', 'sLanguage');
              Params::setParam('sLanguage', $find_lang['pk_c_code']);
              
              osc_run_hook('user_locale_changed', $find_lang['pk_c_code']);
              
            } else {
              $this->do400();
            }
          } else {
            $this->do400();
          }
        } else {
          $this->do400();
        }
      }
    }
  }
}

/* file end: ./oc-includes/osclass/core/BaseModel.php */
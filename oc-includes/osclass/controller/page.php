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
 * Class CWebPage
 */
class CWebPage extends BaseModel {
  public $pageManager;

  public function __construct() {
    parent::__construct();

    $this->pageManager = Page::newInstance();
    osc_run_hook('init_page');
  }

  public function doModel() {
    $id = Params::getParam('id');
    $page = false;

    if(is_numeric($id)) {
      $page = $this->pageManager->findByPrimaryKey($id);
    } else {
      $page = $this->pageManager->findByInternalName(Params::getParam('slug'));
    }

    // page not found
    if($page == false) {
      $this->do404();
      return;
    }

    // check if logged user can see this page
    if(osc_check_static_page_user_visibility($page, osc_logged_user()) === false) {
      $this->do404();
      return;
    }

    $kwords = array('{WEB_URL}', '{WEB_TITLE}');
    $rwords = array(osc_base_url(), osc_page_title());

    foreach($page['locale'] as $k => $v) {
      $page['locale'][$k]['s_title'] = str_ireplace($kwords, $rwords, osc_apply_filter('email_description', $v['s_title']));
      $page['locale'][$k]['s_text'] = str_ireplace($kwords, $rwords, osc_apply_filter('email_description', $v['s_text']));
    }

    $prefLocale = osc_current_user_locale();
    if(isset($page['locale'][$prefLocale])) {
      $page['s_title'] = $page['locale'][$prefLocale]['s_title'];
      $page['s_text'] = $page['locale'][$prefLocale]['s_text'];
    } else {
      $data = current($page['locale']);

      if($data !== false && is_array($data)) {
        $page['s_title'] = $data['s_title'];
        $page['s_text'] = $data['s_text'];
      } else {
        $page['s_title'] = '';
        $page['s_text'] = '';
      }

      unset($data);
    }

    // export $page content to View
    $this->_exportVariableToView('page', $page);

    // redirect to the correct url just in case it has changed
    if(osc_rewrite_enabled()) {
      $pageURI = str_replace(osc_base_url(), '', osc_static_page_url());
      $URI = preg_replace('|^' . REL_WEB_URL . '|', '', Params::getServerParam('REQUEST_URI', false, false));
      $URI = str_replace('?' . Params::getServerParam('QUERY_STRING', false, false), '', $URI);

      if(urlencode(strip_tags(strtolower(str_replace('+', '', str_replace(' ', '', urldecode($pageURI)))))) != urlencode(strip_tags(strtolower(str_replace(' ', '', urldecode($URI)))))) {
        $this->redirectTo(osc_base_url() . strtolower($pageURI), 301);
      }
    }

    if(osc_always_generate_canonical_enabled()) {
      $this->_exportVariableToView('canonical', osc_apply_filter('canonical_url_page', osc_static_page_url()));
    }

    // Update 8.0.2 - lang param handler moved to index.php
    // if(Params::getParam('lang') != '') {
    //   Session::newInstance()->_set('userLocale', Params::getParam('lang'));
    // }

    $meta = json_decode(isset($page['s_meta']) ? (string)$page['s_meta'] : '', true);

    // load the right template file
    if(file_exists(osc_themes_path() . osc_theme() . '/page-' . $page['s_internal_name'] . '.php')) {
      $this->doView('page-' . $page['s_internal_name'] . '.php');

    } else if(isset($meta['template']) && file_exists(osc_themes_path() . osc_theme() . '/' . $meta['template'])) {
      $this->doView($meta['template']);

    } else if(isset($meta['template']) && file_exists(osc_plugins_path() . '/' . $meta['template'])) {
      osc_run_hook('before_html');
      require osc_plugins_path() . '/' . $meta['template'];
      Session::newInstance()->_clearVariables();
      osc_run_hook('after_html');

    } else {
      $this->doView('page.php');
    }
  }

  /**
   * @param $file
   *
   * @return mixed|void
   */
  public function doView($file) {
    osc_run_hook('before_html');
    osc_current_web_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook('after_html');
  }
}

/* file end: ./page.php */

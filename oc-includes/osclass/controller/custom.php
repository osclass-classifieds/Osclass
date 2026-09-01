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
 * Class CWebCustom
 */
class CWebCustom extends BaseModel
{
  public function __construct()
  {
    parent::__construct();
    //specific things for this class
    osc_run_hook('init_custom');
  }

  //Business Layer...
  public function doModel()
  {
    $user_menu = false;
    if(Params::existParam('route')) {
      $routes = Rewrite::newInstance()->getRoutes();
      $rid = Params::getParam('route');
      $file = '../';
      
      if(isset($routes[$rid]) && isset($routes[$rid]['file'])) {
        $file = $routes[$rid]['file'];
        $user_menu = $routes[$rid]['user_menu'];
      }
    } else {
      // DEPRECATED: Disclosed path in URL is deprecated, use routes instead
      // This will be REMOVED in 3.4
      $file = Params::getParam('file');
    }

    // valid file?
    if( strpos($file, '../') !== false || strpos($file, '..\\') !==false || stripos($file, '/admin/') !== false ) { //If the file is inside an "admin" folder, it should NOT be opened in frontend
      $this->do404();
      return;
    }

    // check if the file exists
    if(!file_exists(osc_plugins_path() . $file) && !file_exists(osc_themes_path() . osc_theme() . '/plugins/' . $file) && !file_exists(osc_themes_path() . osc_theme() . '/custom/' . $file) && !file_exists(osc_themes_path() . osc_theme() . '/' . $file)) {
      $this->do404();
      return;
    }

    osc_run_hook('custom_controller');

    $this->_exportVariableToView('file', $file);
    
    if($user_menu) {
      if(osc_is_web_user_logged_in()) {
        Params::setParam('in_user_menu', true);
        $this->doView('user-custom.php');
        
      } else {
        $this->redirectTo(osc_user_login_url());
      }
      
    } else {
      $this->doView('custom.php');
    }
  }

  //hopefully generic...

  /**
   * @param $file
   *
   * @return mixed|void
   */
  public function doView( $file )
  {
    osc_run_hook( 'before_html' );
    osc_current_web_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook( 'after_html' );
  }
}

/* file end: ./custom.php */
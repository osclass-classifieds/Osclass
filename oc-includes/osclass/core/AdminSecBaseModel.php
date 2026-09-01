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


class AdminSecBaseModel extends SecBaseModel
{
  public function __construct()
  {
    parent::__construct();

    $admin = Admin::newInstance()->findByPrimaryKey(osc_logged_admin_id());

    $moderator_access = array();
    if(isset($admin['s_moderator_access']) && trim($admin['s_moderator_access']) <> '') {
      $moderator_access = array_filter(explode(',', $admin['s_moderator_access']));
    }

    // check if is moderator and can enter to this page
    if ( 
      $this->isModerator()
      && !in_array( $this->page , osc_apply_filter( 'moderator_access', array('items','comments','media','login','admins','ajax','stats','main','')))
      && !($this->page == 'plugins' && in_array(Params::getParam('file'), $moderator_access))
      && !($this->page == 'plugins' && in_array(Params::getParam('route'), $moderator_access))
    ) {
      osc_add_flash_error_message( _m( "You don't have enough permissions to access this section" ) , 'admin' );

      $url = (@$_SERVER['HTTP_REFERER'] <> '' ? $_SERVER['HTTP_REFERER'] : osc_admin_base_url());
      $this->redirectTo($url);
    }
    
    osc_run_hook( 'init_admin' );

    $config_version = str_replace('.', '', OSCLASS_VERSION);
    $config_version = preg_replace('|-.*|', '', $config_version);

    if( $config_version > osc_get_preference('version')) {
      if( get_class($this) === 'CAdminTools') {
      } else {
        if ( get_class( $this ) !== 'CAdminUpgrade' ) {
        $this->redirectTo( osc_admin_base_url( true ) . '?page=upgrade' );
        }
      }
    }

    // show donation successful
    if( Params::getParam('donation') === 'successful' ) {
      osc_add_flash_ok_message(_m('Thank you very much for your donation'), 'admin');
    }

    // enqueue scripts
    osc_enqueue_script('jquery');
    osc_enqueue_script('jquery-ui-backoffice');
    osc_enqueue_script('admin-osc');
    osc_enqueue_script('admin-ui-osc');
  }

  /**
   * @return bool
   */
  public function isLogged()
  {
    return osc_is_admin_user_logged_in();
  }

  /**
   * @return bool
   */
  public function isModerator()
  {
    return osc_is_moderator();
  }

  public function logout()
  {
    //destroying session
    $locale = Session::newInstance()->_get('oc_adminLocale');
    Session::newInstance()->session_destroy();
    Session::newInstance()->_drop('adminId');
    Session::newInstance()->_drop('adminUserName');
    Session::newInstance()->_drop('adminName');
    Session::newInstance()->_drop('adminEmail');
    Session::newInstance()->_drop('adminLocale');
    Session::newInstance()->session_start();
    Session::newInstance()->_set('oc_adminLocale', $locale);

    Cookie::newInstance()->pop('oc_adminId');
    Cookie::newInstance()->pop('oc_adminSecret');
    Cookie::newInstance()->pop('oc_adminLocale');
    Cookie::newInstance()->set();
  }

  public function showAuthFailPage()
  {
    if( Params::getParam('page') === 'ajax') {
      echo json_encode(array('error' => 1, 'msg' => __('Session timed out')));
      exit;
    } else {
      // Session::newInstance()->session_start(); 
      // Session::newInstance()->_setReferer(osc_base_url() . preg_replace('|^' . REL_WEB_URL . '|', '', Params::getServerParam('REQUEST_URI', false, false)));

      Cookie::newInstance()->_setRefererHistory();
      Session::newInstance()->_setReferer(osc_get_http_referer());


      header( 'Location: ' . osc_admin_base_url( true) . '?page=login' );
      exit;
    }
  }

  //hopefully generic...

  /**
   * @param $file
   */
  public function doView( $file )
  {
    osc_run_hook( 'before_admin_html' );
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook( 'after_admin_html' );
  }
}

/* file end: ./oc-includes/osclass/core/AdminSecBaseModel.php */
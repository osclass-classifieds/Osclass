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
 * Class CWebMain
 */
class CWebMain extends BaseModel {
  public function __construct() {
    parent::__construct();
    osc_run_hook('init_main');
  }

  //Business Layer...
  public function doModel() {
    switch($this->action) {
      case('logout'):     // unset only the required parameters in Session
        osc_run_hook('logout');

        Session::newInstance()->_drop('userId');
        Session::newInstance()->_drop('userName');
        Session::newInstance()->_drop('userEmail');
        Session::newInstance()->_drop('userPhone');

        Cookie::newInstance()->pop('oc_userId');
        Cookie::newInstance()->pop('oc_userSecret');
        Cookie::newInstance()->set();

        $this->redirectTo(osc_base_url());
        break;
        
      default:
        // update 422 - avoid misuse action param for injections
        if(Params::getParam('action') <> '') {
          $this->redirectTo(osc_base_url());
        }
        
        $this->doView('main.php');
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
    Session::newInstance()->_clearVariables();
    osc_run_hook('after_html');
  }
}

/* file end: ./main.php */
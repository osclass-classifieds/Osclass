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


class CAdminUpgrade extends AdminSecBaseModel {
  function __construct() {
    parent::__construct();
  }

  //Business Layer...
  function doModel() {
    parent::doModel();

    //specific things for this class
    switch ($this->action) {
      case 'upgrade-funcs':
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true));
          exit;
        }
        
        require(LIB_PATH.'osclass/upgrade-funcs.php');
        break;
        
      default:
        $this->doView('upgrade/index.php');
    }
  }

  //hopefully generic...
  function doView($file) {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }
}

/* file end: ./oc-admin/upgrade.php */
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


class CAdminMarket extends AdminSecBaseModel {
  function __construct() {
    parent::__construct();
  }

  //Business Layer...
  function doModel() {
    parent::doModel();

    if($this->action == 'themes' || $this->action == 'plugins') { 
      $api_key = osc_get_preference('osclasspoint_api_key', 'osclass');
      $data = osc_file_get_contents_json(osc_market_url('validate_api_key'));

      $api_valid = false;

      if($api_key == '') {
        osc_add_flash_error_message(sprintf(_m('In order to be able to download data from OsclassPoint, you must define your API key in %s section'), '<a href="' . osc_admin_base_url(true) . '?page=settings">' . __('Settings > General > Software updates') . '</a>'), 'admin');

      } else if(isset($data['error']) && $data['error'] <> '') {
        osc_add_flash_error_message(sprintf(_m('API key validation error: %s'), $data['error']), 'admin');

      } else if(!isset($data['success']) || $data['success'] == '') {
        $msg = sprintf(_m('API key "%s" validation failed, invalid response from server'), osc_update_api_key());
        $msg .= ((isset($data['status']) && $data['status'] == 'ERR') ? ' (' . $data['message'] . ')' : '');
        
        osc_add_flash_warning_message($msg, 'admin');

      } else {
        //do nothing, api key is valid
        //osc_add_flash_ok_message($data['success'], 'admin');
        $api_valid = true;
      }

      $this->_exportVariableToView("api_data", $data);
      $this->_exportVariableToView("api_valid", $api_valid);
    }

    switch ($this->action) {
      case('plugins'):
        $this->doView("market/plugins.php");
        break;
      
      case('themes'):
        $this->doView("market/themes.php");
        break;
      
      case('languages'):
        $this->doView("market/languages.php");
        break;
      
      case('languages-themes'):
        $this->doView("market/languages_themes.php");
        break;
      
      case('languages-plugins'):
        $this->doView("market/languages_plugins.php");
        break;
      
      case('locations'):
        $this->doView("market/locations.php");
        break;
      
      case('overview'):
        $this->doView("market/overview.php");
        break;
      
      default:
        $this->doView("market/themes.php");
        break;
    }
  }

  function __call($name, $arguments) {
    // TODO: Implement __call() method.
  }
  
  function doView($file) {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }
}

/* file end: ./oc-admin/market.php */
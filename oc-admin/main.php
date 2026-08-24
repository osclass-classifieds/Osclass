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


class CAdminMain extends AdminSecBaseModel {
  function __construct() {
    parent::__construct();
  }

  //Business Layer...
  function doModel() {
    switch($this->action) {
      case('logout'):   // unset only the required parameters in Session
        osc_run_hook('logout_admin');
        $this->logout();
        $this->redirectTo(osc_admin_base_url(true));
        break;

      case('settings'):
        $this->doView('main/settings.php');
        break;

      case('settings_post'):  // updating widgets
          osc_csrf_check();
          $iUpdated        = 0;

          $params = Params::getParamsAsArray();

          $cols_hidden = array();
          foreach($params as $name => $value) {
            $name = explode('_', $name);

            if(@$name[0] == 'col' && $value == 1) {
            $cols_hidden[] = $name[1];
            }
          }

          $cols_hidden = array_filter(array_unique(array_map('trim', $cols_hidden)));
          $cols_hidden = implode(',', $cols_hidden);


          $widgets_hidden = array();
          foreach($params as $name => $value) {
            $name = explode('_', $name);

            if(@$name[0] == 'widget' && $value == 1) {
            $widgets_hidden[] = $name[1];
            }
          }

          $widgets_hidden = array_filter(array_unique(array_map('trim', $widgets_hidden)));
          $widgets_hidden = implode(',', $widgets_hidden);

          $iUpdated += osc_set_preference('admindash_columns_hidden', $cols_hidden);
          $iUpdated += osc_set_preference('admindash_widgets_hidden', $widgets_hidden);

          foreach($params as $name => $value) {
            if(strpos($name, 'widgetcol_') === 0) {
              $wid = substr($name, 10);
              $col = (int)$value;
              if($col >= 1 && $col <= 3 && $wid != '') {
                osc_set_preference('admindash_widget_column_' . $wid, $col);
              }
            }
          }

          if($iUpdated > 0) {
            osc_add_flash_ok_message(_m("Widget settings have been updated"), 'admin');
          }
          $this->redirectTo(osc_admin_base_url(true) . '?page=main&action=settings');
        break;

      case('notes_post'):
        osc_csrf_check();

        $notesType = trim((string)Params::getParam('notes_type'));
        $notes = (string)Params::getParam('notes');
        $notes = str_replace("\r\n", "\n", $notes);
        $notes = str_replace("\r", "\n", $notes);

        $prefName = '';

        if($notesType == 'general') {
          $prefName = 'notes';

        } else if($notesType == 'my') {
          $adminId = (int)osc_logged_admin_id();

          if($adminId > 0) {
            $prefName = 'notes_' . $adminId;
          }
        }

        if($prefName == '') {
          osc_add_flash_error_message(_m('Unable to save notes'), 'admin');
          $this->redirectTo(osc_admin_base_url(true));
        }

        osc_set_preference($prefName, $notes);
        osc_add_flash_ok_message(_m('Notes have been updated'), 'admin');
        $this->redirectTo(osc_admin_base_url(true));
        break;

      case('widget'):
        if(Params::getParam('file') <> '') {
          $file = osc_esc_html(Params::getParam('file'));

          if(in_array($file, array('api.php','blog.php','product_updates.php','products.php','update.php'))) {
            $this->doView('main/widget/' . $file);
          }
        }
        break;

      default:      //default dashboard page (main page at oc-admin)
        // Check if tables are on InnoDB
        $item_tbl = Item::newInstance()->getTableInfo();
        $user_tbl = User::newInstance()->getTableInfo();
        $engine_check = false;

        if(isset($item_tbl['ENGINE']) && strtoupper($item_tbl['ENGINE']) == 'INNODB' && isset($user_tbl['ENGINE']) && strtoupper($user_tbl['ENGINE']) == 'INNODB') {
          $engine_check = true;
        }

        osc_ensure_reports_tables();
        $dash_charts = osc_admin_dash_stats_load();
        $this->_exportVariableToView('dash_chart_series', $dash_charts['series']);
        $this->_exportVariableToView('dash_chart_mix', $dash_charts['mix']);
        $this->_exportVariableToView('dash_chart_headings', (isset($dash_charts['headings']) ? $dash_charts['headings'] : array()));

        if(function_exists('disk_free_space')) {
          $freedisk = @disk_free_space(osc_uploads_path());
          if($freedisk!==false && $freedisk<52428800) { //52428800 = 50*1024*1024
            osc_add_flash_error_message(_m('You have very few free space left, users will not be able to upload pictures'), 'admin');
          }
        }

        if(!osc_alerts_enabled()) {
          osc_add_flash_info_message(_m('User alerts are currently disabled. You can enable them in Users > Settings.'), 'admin');
        }

        // show messages subscribed
        $status_subscribe = Params::getParam('subscribe_osclass');
        if($status_subscribe != '') {
          switch($status_subscribe) {
            case -1:
              osc_add_flash_error_message(_m('Entered an invalid email'), 'admin');
              break;

            case 0:
              osc_add_flash_warning_message(_m("You're already subscribed"), 'admin');
              break;

            case 1:
              osc_add_flash_ok_message(_m('Subscribed correctly'), 'admin');
              break;

            default:
              osc_add_flash_warning_message(_m("Error subscribing"), 'admin');
              break;
          }
        }

        $this->_exportVariableToView("engine_check", $engine_check);

        //calling the view...
        $this->doView('main/index.php');
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

/* file end: ./oc-admin/main.php */

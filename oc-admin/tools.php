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


class CAdminTools extends AdminSecBaseModel {
  function __construct() {
    parent::__construct();
  }

  //Business Layer...
  function doModel() {
    parent::doModel();

    switch($this->action) {
      case('cleanup'):    // calling info view 
        $this->doView('tools/cleanup.php');
        break;

      case('info'):       // calling info view
        $this->doView('tools/info.php');
        break;

      case('debug'):       // calling info view
        $logs = glob(CONTENT_PATH . '/*.log');
 
        $logs = osc_apply_filter("admin_tools_log_files", $logs);
        $this->_exportVariableToView('log_files', $logs);

        // if(Params::getParam('log_file') == '') {
          // Params::setParam('log_file', 'debug.log');
        // }
        
        $this->doView('tools/debug.php');
        break;

      case('debug_delete'):       // calling info view
        $file = Params::getParam('log_file');
        
        if(pathinfo($file, PATHINFO_EXTENSION) === 'log') {
          if(file_exists(CONTENT_PATH . $file)) {
            osc_add_flash_ok_message(sprintf(_m('Log file "%s" has been removed'), $file), 'admin');
            @unlink(CONTENT_PATH . $file);
            
          } else {
            osc_add_flash_error_message(sprintf(_m('Log file "%s" has not been found'), $file), 'admin');
          }
          
        } else {
          osc_add_flash_error_message(sprintf(_m('Log file "%s" is invalid and cannot be removed'), $file), 'admin');
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=debug');
        break;

      case('logs'):       // calling info view
        require_once osc_lib_path()."osclass/classes/datatables/LogsDataTable.php";

        // set default iDisplayLength
        if(Params::getParam('iDisplayLength') != '') {
          Cookie::newInstance()->push('listing_iDisplayLength', Params::getParam('iDisplayLength'));
          Cookie::newInstance()->set();
          
        } else {
          // set a default value if it's set in the cookie
          if(Cookie::newInstance()->get_value('listing_iDisplayLength') != '') {
            Params::setParam('iDisplayLength', Cookie::newInstance()->get_value('listing_iDisplayLength'));
          } else {
            Params::setParam('iDisplayLength', 25);
          }
        }
        $this->_exportVariableToView('iDisplayLength', Params::getParam('iDisplayLength'));

        // Table header order by related
        if(Params::getParam('sort') == '') {
          Params::setParam('sort', 'date');
        }
        
        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'desc');
        }

        $page = (int)Params::getParam('iPage');
        if($page==0) { $page = 1; };
        Params::setParam('iPage', $page);

        $params = Params::getParamsAsArray();

        $logsDataTable = new LogsDataTable();
        $logsDataTable->table($params);
        $aData = $logsDataTable->getData();

        if(count($aData['aRows']) == 0 && $page!=1) {
          $total = (int)$aData['iTotalDisplayRecords'];
          $maxPage = ceil($total / (int)$aData['iDisplayLength']);

          $url = osc_admin_base_url(true).'?'.Params::getServerParam('QUERY_STRING', false, false);

          if($maxPage==0) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage=1', $url);
            $this->redirectTo($url);
          }

          if($page > 1) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage='.$maxPage, $url);
            $this->redirectTo($url);
          }
        }

        $this->_exportVariableToView('aData', $aData);
        $this->_exportVariableToView('aRawRows', $logsDataTable->rawRows());

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array('value' => 'logs_delete', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected logs?'), strtolower(__('Delete'))), 'label' => __('Delete'))
        );

        $bulk_options = osc_apply_filter("logs_bulk_filter", $bulk_options);
        $this->_exportVariableToView('bulk_options', $bulk_options);
        
        $this->doView("tools/logs.php");
        break;

      case('logs_delete'):       // calling info view
        osc_csrf_check();
        $iDeleted = 0;
        $logId = Params::getParam('id');

        if(!is_array($logId)) {
          osc_add_flash_error_message(_m("Log id isn't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=logs');
        }

        $logsManager = Log::newInstance();
        foreach($logId as $raw_id) {
          $parts = explode('|', urldecode($raw_id));
          
          if(!isset($parts[0]) || !isset($parts[1]) || !isset($parts[2]) || !isset($parts[3])) {
            continue;   // log id is not in correct format
          }
          
          $date = $parts[0];
          $section = $parts[1];
          $action = $parts[2];
          $id = $parts[3];

          if($logsManager->deleteLog($date, $section, $action, $id)) {
            $iDeleted++;
          }
        }

        if($iDeleted == 0) {
          $msg = _m('No logs have been deleted');
        } else {
          $msg = sprintf(_mn('One log has been deleted', '%s logs have been deleted', $iDeleted), $iDeleted);
        }

        osc_add_flash_ok_message($msg, 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=logs');
        break;
        
      case('import'):     // calling import view
        $this->doView('tools/import.php');
        break;

      case('import_post'):  
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=import');
        }
        
        // calling
        osc_csrf_check();
        $sql = Params::getFiles('sql');
        
        if(isset($sql['size']) && $sql['size'] != 0) {
          $content_file = file_get_contents($sql['tmp_name']);

          $conn = DBConnectionClass::newInstance();
          $c_db = $conn->getOsclassDb();
          $comm = new DBCommandClass($c_db);
          
          // Already happens in importSQL from 8.3.1 together with engine, charset and collate force update
          // $content_file = str_replace('/*TABLE_PREFIX*/', DB_TABLE_PREFIX, $content_file);
          // $content_file = str_replace('/*LOCALE_CODE*/', osc_language(), $content_file);

          if($comm->importSQL($content_file)) {
            osc_calculate_location_slug(osc_subdomain_type());
            osc_add_flash_ok_message( _m('Import complete'), 'admin');
            
          } else {
            // echo '<pre>';
            // echo $conn;
            // echo $comm->getConnErrorLevel();
            // print_r($conn);
            // print_r($comm);
            // print_r($c_db);
            // exit;
            
            // $conn->errorReport();

            //osc_add_flash_error_message( _m('There was a problem importing data to the database'), 'admin');
            osc_add_flash_error_message("There was a problem importing SQL file to the database: <br/><pre>" . $comm->getConnErrorLevel() . " - " . $comm->getConnErrorDesc() . '</pre>', 'admin');
          }
          
        } else {
          osc_add_flash_error_message(_m('SQL File could not be uploaded into server temp folder - check your server permissions and file size!'), 'admin');
        }
        
        @unlink($sql['tmp_name']);
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=import');
        break;

      case('category'):
        $this->doView('tools/category.php');
        break;

      case('category_post'):  if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=category');
        }
        
        osc_update_cat_stats();
        osc_add_flash_ok_message(_m("Recount category stats has been successful"), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=category');
        break;

      case('locations'):
        $this->doView('tools/locations.php');
        break;

      case('locations_post'): 
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=locations');
        }

        osc_update_location_stats(true);

        $this->redirectTo( osc_admin_base_url(true) . '?page=tools&action=locations' );
        break;

      case('upgrade'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true));
        }
        
        $this->doView('tools/upgrade.php');
        break;

      case 'version':
        $this->doView('tools/version.php');
        break;

      case('backup'):
        $this->doView('tools/backup.php');
        break;

      case('backup-sql'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
        }
        
        osc_csrf_check();
        //databasse dump...
        if( Params::getParam('bck_dir') != '' ) {
          $path = trim( Params::getParam('bck_dir') );
          if(substr($path, -1, 1) != "/") {
             $path .= '/';
          }
        } else {
          $path = osc_base_path();
        }
        
        $filename = 'Osclass_mysqlbackup.' . date('YmdHis') . '.sql';

        switch ( osc_dbdump($path, $filename) ) {
          case(-1):
            $msg = _m('Path is empty');
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-2):
            $msg = sprintf(_m('Could not connect with the database. Error: %s'), mysql_error());
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-3):
            $msg = _m('There are no tables to back up');
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-4):
            $msg = _m('The folder is not writable');
            osc_add_flash_error_message( $msg, 'admin');
            break;

          default:
            $msg = _m('Backup completed successfully');
            osc_add_flash_ok_message( $msg, 'admin');
            break;
        }
        
        $this->redirectTo( osc_admin_base_url(true) . '?page=tools&action=backup' );
        break;

      case('backup-sql_file'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
        }
        
        //databasse dump...
        $filename = 'Osclass_mysqlbackup.' . date('YmdHis') . '.sql';
        $path = sys_get_temp_dir()."/";

        switch (osc_dbdump($path, $filename)) {
          case(-1):
            $msg = _m('Path is empty');
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-2):
            $msg = sprintf(_m('Could not connect with the database. Error: %s'), mysql_error());
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-3):
            $msg = sprintf(_m('Could not select the database. Error: %s'), mysql_error());
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-4):
            $msg = _m('There are no tables to back up');
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-5):
            $msg = _m('The folder is not writable');
            osc_add_flash_error_message( $msg, 'admin');
            break;

          default:
            $msg = _m('Backup completed successfully');
            osc_add_flash_ok_message( $msg, 'admin');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($filename));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path.$filename));
            flush();
            readfile($path.$filename);
            exit;
            break;
        }
        
        $this->redirectTo( osc_admin_base_url(true) . '?page=tools&action=backup' );
        break;

      case('backup-zip_file'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
        }
        
        $filename = "Osclass_backup." . date('YmdHis') . ".zip";
        $path = sys_get_temp_dir()."/";

        if (osc_zip_folder(osc_base_path(),$path. $filename)) {
          $msg = _m('Archived successfully!');
          osc_add_flash_ok_message( $msg, 'admin');
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename='.basename($filename));
          header('Content-Transfer-Encoding: binary');
          header('Expires: 0');
          header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
          header('Pragma: public');
          header('Content-Length: ' . filesize($path.$filename));
          flush();
          readfile($path.$filename);
          exit;
          
        } else {
          $msg = _m('Error, the zip file was not created in the specified directory');
          osc_add_flash_error_message( $msg, 'admin');
        }
        
        $this->redirectTo( osc_admin_base_url(true) . '?page=tools&action=backup' );
        break;

      case('backup-zip'):   if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
        }
        
        //zip of the code just to back it up
        osc_csrf_check();
        if( Params::getParam('bck_dir') != '' ) {
          $archive_name = trim( Params::getParam('bck_dir') );
          if(substr(trim($archive_name), -1, 1) != "/") {
            $archive_name .= '/';
          }
          $archive_name = Params::getParam('bck_dir') . '/Osclass_backup.' . date('YmdHis') . '.zip';
        } else {
          $archive_name = osc_base_path() . "Osclass_backup." . date('YmdHis') . ".zip";
        }
        
        $archive_folder = osc_base_path();

        if ( osc_zip_folder($archive_folder, $archive_name) ) {
          $msg = _m('Archived successfully!');
          osc_add_flash_ok_message( $msg, 'admin');
        }else{
          $msg = _m('Error, the zip file was not created in the specified directory');
          osc_add_flash_error_message( $msg, 'admin');
        }
        
        $this->redirectTo( osc_admin_base_url(true) . '?page=tools&action=backup' );
        break;

      case('backup_post'):
        $this->doView('tools/backup.php');
        break;

      case('maintenance'): 
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->doView('tools/maintenance.php');
          break;
        }
        
        $mode = Params::getParam('mode');
        if( $mode == 'on' ) {
          osc_csrf_check();
          $maintenance_file = osc_base_path() . '.maintenance';
          $fileHandler = @fopen($maintenance_file, 'w');
          
          if( $fileHandler ) {
            osc_add_flash_ok_message( _m('Maintenance mode is ON'), 'admin');
          } else {
            osc_add_flash_error_message( _m('There was an error creating the .maintenance file, please create it manually at the root folder'), 'admin');
          }
          
          fclose($fileHandler);
          $this->redirectTo( osc_admin_base_url(true) . '?page=tools&action=maintenance' );
          
        } else if( $mode == 'off' ) {
          osc_csrf_check();
          $deleted = @unlink(osc_base_path() . '.maintenance');
          if( $deleted ) {
            osc_add_flash_ok_message( _m('Maintenance mode is OFF'), 'admin');
          } else {
            osc_add_flash_error_message( _m('There was an error removing the .maintenance file, please remove it manually from the root folder'), 'admin');
          }
          
          $this->redirectTo( osc_admin_base_url(true) . '?page=tools&action=maintenance' );
        }
        
        $this->doView('tools/maintenance.php');
        break;

      case('cleanup_post'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=cleanup');
        }

        $type = Params::getParam('type');
        $limit_days = 7;
        $limit_date = date('Y-m-d', strtotime('-' . $limit_days . ' days'));
        $res = true;

        if($type == 'items_inactive') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_item WHERE b_active != 1 AND dt_pub_date <= "%s" LIMIT 50000', DB_TABLE_PREFIX, $limit_date));

          $manager = new ItemActions(true);
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              $manager->delete($d['s_secret'], $d['pk_i_id']);
            }
          } else {
            $res = false;
          }
          
        } else if($type == 'items_blocked_spam') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_item WHERE b_enabled = 0 OR b_spam = 1 LIMIT 50000', DB_TABLE_PREFIX));

          $manager = new ItemActions(true);
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              $manager->delete($d['s_secret'], $d['pk_i_id']);
            }
          } else {
            $res = false;
          }
          
        } else if($type == 'items_expired') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_item WHERE dt_expiration <= "%s" LIMIT 50000', DB_TABLE_PREFIX, $limit_date));

          $manager = new ItemActions(true);
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              $manager->delete($d['s_secret'], $d['pk_i_id']);
            }
          } else {
            $res = false;
          }
          
        } else if($type == 'users_inactive') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_user WHERE b_active != 1 AND dt_reg_date <= "%s" LIMIT 50000', DB_TABLE_PREFIX, $limit_date));

          $manager = User::newInstance();
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              Log::newInstance()->insertLog('user', 'delete', $d['pk_i_id'], $d['s_email'], 'admin', osc_logged_admin_id());
              $manager->deleteUser($d['pk_i_id']);
            }
          } else {
            $res = false;
          }
          
        } else if($type == 'users_blocked') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_user WHERE b_enabled = 0 LIMIT 50000', DB_TABLE_PREFIX));

          $manager = User::newInstance();
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              Log::newInstance()->insertLog('user', 'delete', $d['pk_i_id'], $d['s_email'], 'admin', osc_logged_admin_id());
              $manager->deleteUser($d['pk_i_id']);
            }
          } else {
            $res = false;
          }
          
        } else if($type == 'comments_inactive') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_item_comment WHERE b_active != 1 AND dt_pub_date <= "%s" LIMIT 50000', DB_TABLE_PREFIX, $limit_date));

          $manager = ItemComment::newInstance();
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              $manager->delete(array('pk_i_id' => $d['pk_i_id']));
            }
          } else {
            $res = false;
          }
          
        } else if($type == 'comments_blocked') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_item_comment WHERE b_enabled = 0 LIMIT 50000', DB_TABLE_PREFIX));
          
          $manager = ItemComment::newInstance();
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              $manager->delete(array('pk_i_id' => $d['pk_i_id']));
            }
          } else {
            $res = false;
          }
          
        } else if($type == 'unsubscribed_alerts') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_alerts WHERE b_active = 0 AND coalesce(dt_unsub_date, dt_date) <= "%s" LIMIT 50000', DB_TABLE_PREFIX, $limit_date));

          $manager = Alerts::newInstance();
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              $manager->delete(array('pk_i_id' => $d['pk_i_id']));
            }
          } else {
            $res = false;
          }
          
        } else if($type == 'expired_ban_rules') {
          $data = osc_get_query_results(sprintf('SELECT * FROM %st_ban_rule WHERE dt_expire_date <= "%s" LIMIT 50000', DB_TABLE_PREFIX, $limit_date));

          $manager = BanRule::newInstance();
          if(is_array($data) && !empty($data)) {
            foreach($data as $d) {
              $manager->delete(array('pk_i_id' => $d['pk_i_id']));
            }
          } else {
            $res = false;
          }

        } else if($type == 'old_logs') {
          $limit_months = (osc_logging_months() > 0 ? osc_logging_months() : 24);
          $limit_date_log = date('Y-m-d', strtotime('-' . $limit_months . ' months'));

          // $data = osc_get_query_results(sprintf('SELECT * FROM %st_log WHERE dt_date <= "%s" LIMIT 50000', DB_TABLE_PREFIX, $limit_date_log));
          $res = osc_execute_query(sprintf('DELETE FROM %st_log WHERE date(dt_date) <= "%s"', DB_TABLE_PREFIX, $limit_date_log));
        }

        if($res) {
          osc_add_flash_ok_message( _m("Data cleaned up successfully"), 'admin');
        } else {
          osc_add_flash_error_message( _m("There was problem cleaning data (no data has been found)"), 'admin');
        }

        //$this->doView('tools/cleanup.php');
        //exit;
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=cleanup');
        exit;
        break;

      default:
        $this->doView('tools/info.php');
        break;
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

/* file end: ./oc-admin/tools.php */
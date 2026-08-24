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

  private function get_saved_backup_folder() {
    $path = trim((string)osc_get_preference('tools_backup_folder', 'osclass'));

    if($path == '') {
      $path = osc_base_path();
    }

    return rtrim($path, '/\\') . '/';
  }

  private function get_backup_folder() {
    $path = trim((string)Params::getParam('bck_dir'));

    if($path != '') {
      $path = rtrim($path, '/\\') . '/';
      osc_set_preference('tools_backup_folder', $path, 'osclass', 'STRING');
      return $path;
    }

    return $this->get_saved_backup_folder();
  }

  private function get_backup_filename($type) {
    $timestamp = date('YmdHis');

    if($type == 'db') {
      return 'osclass_db_backup_' . $timestamp . '.sql';
    }

    return 'osclass_file_backup_' . $timestamp . '.zip';
  }

  private function get_backup_redirect_url() {
    return osc_admin_base_url(true) . '?page=tools&action=backup';
  }

  private function is_backup_filename($name) {
    $is_db = preg_match('/^osclass_db_backup_[0-9]{14}\.sql$/', $name);
    $is_file = preg_match('/^osclass_file_backup_[0-9]{14}\.zip$/', $name);

    return ($is_db || $is_file);
  }

  private function get_backup_files($path) {
    $files = array();

    if($path == '' || !is_dir($path)) {
      return $files;
    }

    $patterns = array('osclass_db_backup_*.sql', 'osclass_file_backup_*.zip');

    foreach($patterns as $pattern) {
      $matches = glob($path . $pattern);

      if(!is_array($matches) || count($matches) <= 0) {
        continue;
      }

      foreach($matches as $file) {
        if(!is_file($file)) {
          continue;
        }

        $name = basename($file);
        $type = (strpos($name, 'osclass_db_backup_') === 0 ? 'db' : 'file');

        $files[] = array(
          'name' => $name,
          'path' => $file,
          'type' => $type,
          'size' => (int)@filesize($file),
          'size_label' => $this->get_backup_size_label((int)@filesize($file)),
          'modified' => (int)@filemtime($file)
        );
      }
    }

    usort($files, function($a, $b) {
      if($a['modified'] == $b['modified']) {
        return 0;
      }

      return ($a['modified'] < $b['modified'] ? 1 : -1);
    });

    return $files;
  }

  private function get_backup_size_label($size) {
    $size = (int)$size;

    if($size >= 1048576) {
      return round($size / 1048576, 2) . ' MB';
    }

    return round($size / 1024, 2) . ' KB';
  }

  private function add_stats_recalc_started_message($type) {
    osc_add_flash_info_message(sprintf(_m('%s statistics recalculation started. Progress will continue on this page until finished. Do not close this window.'), $type), 'admin');
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
        if($page==0) { $page = 1; }
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
        $this->_exportVariableToView('withFilters', $logsDataTable->withFilters());

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
          $decoded = json_decode(base64_decode(urldecode($raw_id)), true);

          if(is_array($decoded) && isset($decoded['dt_date']) && isset($decoded['s_section']) && isset($decoded['s_action']) && isset($decoded['fk_i_id'])) {
            if($logsManager->deleteExactLog($decoded)) {
              $iDeleted++;
            }
            continue;
          }

          $parts = explode('|', urldecode($raw_id));
          if(!isset($parts[0]) || !isset($parts[1]) || !isset($parts[2]) || !isset($parts[3])) {
            continue;
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
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=statistics');
        break;

      case('category_post'):  if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=statistics');
        }

        osc_csrf_check();
        osc_set_preference('category_stats_recalc_running', 1, 'osclass', 'BOOLEAN');
        osc_set_preference('category_stats_recalc_done', 0, 'osclass', 'INTEGER');
        osc_set_preference('category_stats_recalc_offset', 0, 'osclass', 'INTEGER');
        osc_set_preference('category_stats_recalc_total', (int)osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_category', DB_TABLE_PREFIX)), 'osclass', 'INTEGER');
        $this->add_stats_recalc_started_message(__('Category'));
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=statistics');
        break;

      case('locations'):
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=statistics');
        break;

      case('locations_post'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=statistics');
        }

        osc_update_location_stats(true);
        $workToDo = LocationsTmp::newInstance()->count();

        if($workToDo > 0) {
          $this->add_stats_recalc_started_message(__('Location'));
        } else {
          osc_set_preference('location_stats_last_recalc', time(), 'osclass', 'INTEGER');
          osc_add_flash_ok_message(_m("Location statistics are already up to date"), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=statistics');
        break;

      case('user_stats_post'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=statistics');
        }

        osc_csrf_check();
        osc_set_preference('user_stats_recalc_last_id', 0, 'osclass', 'INTEGER');
        osc_set_preference('user_stats_recalc_running', 1, 'osclass', 'BOOLEAN');
        osc_set_preference('user_stats_recalc_done', 0, 'osclass', 'INTEGER');
        osc_set_preference('user_stats_recalc_total', (int)osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_user WHERE b_active = 1', DB_TABLE_PREFIX)), 'osclass', 'INTEGER');
        $this->add_stats_recalc_started_message(__('User'));
        $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=statistics');
        break;

      case('statistics'):
        $this->doView('tools/statistics.php');
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
        $backup_folder = $this->get_backup_folder();
        Params::setParam('bck_dir', $backup_folder);
        $this->_exportVariableToView('backup_folder', $backup_folder);
        $this->_exportVariableToView('backup_files', $this->get_backup_files($backup_folder));
        $this->doView('tools/backup.php');
        break;

      case('backup-download'):
        $file = trim((string)Params::getParam('file'));
        $file = basename($file);

        if(!$this->is_backup_filename($file)) {
          osc_add_flash_error_message(_m('Invalid backup file name'), 'admin');
          $this->redirectTo($this->get_backup_redirect_url());
        }

        $path = $this->get_saved_backup_folder();
        $full_path = $path . $file;

        if(!file_exists($full_path) || !is_file($full_path)) {
          osc_add_flash_error_message(_m('Backup file does not exist'), 'admin');
          $this->redirectTo($this->get_backup_redirect_url());
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($full_path));
        flush();
        readfile($full_path);
        exit;
        break;

      case('backup-delete'):
        osc_csrf_check();
        $file = trim((string)Params::getParam('file'));
        $file = basename($file);

        if(!$this->is_backup_filename($file)) {
          osc_add_flash_error_message(_m('Invalid backup file name'), 'admin');
          $this->redirectTo($this->get_backup_redirect_url());
        }

        $path = $this->get_saved_backup_folder();
        $full_path = $path . $file;

        if(!file_exists($full_path) || !is_file($full_path)) {
          osc_add_flash_error_message(_m('Backup file does not exist'), 'admin');
          $this->redirectTo($this->get_backup_redirect_url());
        }

        if(@unlink($full_path)) {
          osc_add_flash_ok_message(_m('Backup file has been deleted'), 'admin');
        } else {
          osc_add_flash_error_message(_m('Backup file could not be deleted'), 'admin');
        }

        $this->redirectTo($this->get_backup_redirect_url());
        break;

      case('backup-sql'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
        }

        osc_csrf_check();
        $path = $this->get_backup_folder();
        $filename = $this->get_backup_filename('db');

        switch(osc_dbdump($path, $filename) ) {
          case(-1):
            $msg = _m('Path is empty');
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-2):
            $dbError = function_exists('mysqli_connect_error') ? mysqli_connect_error() : '';
            $msg = sprintf(_m('Could not connect with the database. Error: %s'), $dbError);
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

        $this->redirectTo($this->get_backup_redirect_url());
        break;

      case('backup-sql_file'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
        }

        $this->get_backup_folder();
        $filename = $this->get_backup_filename('db');
        $path = sys_get_temp_dir()."/";

        switch(osc_dbdump($path, $filename)) {
          case(-1):
            $msg = _m('Path is empty');
            osc_add_flash_error_message( $msg, 'admin');
            break;

          case(-2):
            $dbError = function_exists('mysqli_connect_error') ? mysqli_connect_error() : '';
            $msg = sprintf(_m('Could not connect with the database. Error: %s'), $dbError);
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
            @unlink($path.$filename);
            exit;
            break;
        }

        $this->redirectTo($this->get_backup_redirect_url());
        break;

      case('backup-zip_file'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
        }

        $this->get_backup_folder();
        $filename = $this->get_backup_filename('file');
        $path = sys_get_temp_dir()."/";

        if(osc_zip_folder(osc_base_path(),$path. $filename)) {
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
          @unlink($path.$filename);
          exit;

        } else {
          $msg = _m('Error, the zip file was not created in the specified directory');
          osc_add_flash_error_message( $msg, 'admin');
        }

        $this->redirectTo($this->get_backup_redirect_url());
        break;

      case('backup-zip'):   if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action cannot be done because it is a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
        }

        osc_csrf_check();
        $path = $this->get_backup_folder();
        $archive_name = $path . $this->get_backup_filename('file');

        $archive_folder = osc_base_path();

        if(osc_zip_folder($archive_folder, $archive_name) ) {
          $msg = _m('Archived successfully!');
          osc_add_flash_ok_message( $msg, 'admin');
        }else{
          $msg = _m('Error, the zip file was not created in the specified directory');
          osc_add_flash_error_message( $msg, 'admin');
        }

        $this->redirectTo($this->get_backup_redirect_url());
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
        if($mode == 'on' ) {
          osc_csrf_check();
          $maintenance_file = osc_base_path() . '.maintenance';
          $fileHandler = @fopen($maintenance_file, 'w');

          if($fileHandler ) {
            osc_add_flash_ok_message( _m('Maintenance mode is ON'), 'admin');
          } else {
            osc_add_flash_error_message( _m('There was an error creating the .maintenance file, please create it manually at the root folder'), 'admin');
          }

          fclose($fileHandler);
          $this->redirectTo( osc_admin_base_url(true) . '?page=tools&action=maintenance' );

        } else if($mode == 'off' ) {
          osc_csrf_check();
          $deleted = @unlink(osc_base_path() . '.maintenance');
          if($deleted ) {
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
        $thresholdDays = (int)osc_cleanup_threshold_days();

        if($type != 'old_logs' && $type != 'item_stats' && $thresholdDays <= 0) {
          osc_add_flash_error_message(_m("Cleanup threshold is set to 0 days. Increase threshold in General Settings to enable cleanup."), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=cleanup');
          exit;
        }

        $deleted = osc_cleanup_data_by_type($type, $thresholdDays);

        if($deleted === false) {
          osc_add_flash_error_message(_m("Unknown cleanup type"), 'admin');
        } else if((int)$deleted > 0) {
          osc_add_flash_ok_message(sprintf(_m("Data cleaned up successfully (%s records removed)"), (int)$deleted), 'admin');
        } else {
          osc_add_flash_error_message( _m("There was problem cleaning data (no data has been found)"), 'admin');
        }

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

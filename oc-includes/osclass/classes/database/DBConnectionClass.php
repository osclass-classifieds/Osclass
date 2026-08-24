<?php
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
 * Database connection object
 *
 * @package Osclass
 * @subpackage Database
 * @since 2.3
 */
class DBConnectionClass {
  /**
   * DBConnectionClass should be instanced one, so it's DBConnectionClass object is set
   *
   * @access private
   * @since 2.3
   * @var DBConnectionClass
   */
  private static $instance;

  /**
   * Host name or IP address where it is located the database
   *
   * @access private
   * @since 2.3
   * @var string
   */
  private $dbHost;
  private $is_install;
  /**
   * Database name where it's installed Osclass
   *
   * @access private
   * @since 2.3
   * @var string
   */
  private $dbName;
  /**
   * Database user
   *
   * @access private
   * @since 2.3
   * @var string
   */
  private $dbUser;
  /**
   * Database user password
   *
   * @access private
   * @since 2.3
   * @var string
   */
  private $dbPassword;

  /**
   * Database connection object to Osclass database
   *
   * @access private
   * @since 2.3
   * @var mysqli
   */
  private $db;

  /**
   * Database error number
   *
   * @access private
   * @since 2.3
   * @var int
   */
  private $errorLevel = 0;
  /**
   * Database error description
   *
   * @access private
   * @since 2.3
   * @var string
   */
  private $errorDesc = '';
  /**
   * Database connection error number
   *
   * @access private
   * @since 2.3
   * @var int
   */
  private $connErrorLevel = 0;
  /**
   * Database connection error description
   *
   * @access private
   * @since 2.3
   * @var string
   */
  private $connErrorDesc = '';


  /** A list of incompatible SQL modes.
   *
   * @since @TODO <-----
   * @access protected
   * @var array
   */
  protected $incompatible_modes = array('NO_ZERO_DATE', 'ONLY_FULL_GROUP_BY', 'STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES', 'TRADITIONAL');

  /**
   * It creates a new DBConnection object class or if it has been created before, it
   * returns the previous object
   *
   * @access public
   * @since 2.3
   * @param string $server Host name where it's located the mysql server
   * @param string $user MySQL user name
   * @param string $password MySQL password
   * @param string $database Default database to be used when performing queries
   * @return DBConnectionClass
   */
  public static function newInstance($server = '', $user = '', $password = '', $database = '', $is_install = false) {
    $server = ($server == '') ? osc_db_host() : $server;
    $user = ($user == '') ? osc_db_user() : $user;
    $password = ($password == '') ? osc_db_password() : $password;
    $database = ($database == '') ? osc_db_name() : $database;

    if(!self::$instance instanceof self) {
      self::$instance = new self ($server, $user, $password, $database, $is_install);
    }
    
    return self::$instance;
  }

  /**
   * Initializate database connection
   *
   * @param string $server Host name where it's located the mysql server
   * @param string $user MySQL user name
   * @param string $password MySQL password
   * @param string $database Default database to be used when performing queries
   */
  public function __construct($server, $user, $password, $database, $is_install = false) {
    $this->dbHost = $server;
    $this->dbName = $database;
    $this->dbUser = $user;
    $this->dbPassword = $password;
    $this->is_install = $is_install;

    $this->connectToOsclassDb();
  }

  /**
   * Connection destructor and print debug
   */
  public function __destruct() {
    $is_admin_logged = false;  // update 420 - probably osclass installation in progress
    
    if(!defined('OSC_INSTALLING')) {
      if(function_exists('osc_is_admin_user_logged_in') && class_exists('Admin') && class_exists('Session')) {
        $is_admin_logged = osc_is_admin_user_logged_in();
      }
    }
    
    $printFrontend = OSC_DEBUG_DB ? $is_admin_logged : false;
    $this->releaseOsclassDb();
    $this->debug($printFrontend);
  }

  /**
   * Set error num error and error description
   *
   * @access private
   * @since 2.3
   */
  public function errorReport() {
    if(OSC_DEBUG) {
      $this->errorLevel = $this->db->errno;
      $this->errorDesc = $this->db->error;
    } else {
      $this->errorLevel = @$this->db->errno;
      $this->errorDesc = @$this->db->error;
    }
  }

  /**
   * Set connection error num error and connection error description
   *
   * @access private
   * @since 2.3
   */
  public function errorConnection() {
    if(OSC_DEBUG) {
      $this->connErrorLevel = $this->db->connect_errno;
      $this->connErrorDesc = $this->db->connect_error;
    } else {
      $this->connErrorLevel = @$this->db->connect_errno;
      $this->connErrorDesc = @$this->db->connect_error;
    }
  }

  /**
   * Return the mysqli connection error number
   *
   * @access public
   * @since  2.3
   * @return int
   */
  public function getErrorConnectionLevel() {
    return $this->connErrorLevel;
  }

  /**
   * Return the mysqli connection error description
   *
   * @access public
   * @since 2.3
   * @return string
   */
  public function getErrorConnectionDesc() {
    return $this->connErrorDesc;
  }

  /**
   * Return the mysqli error number
   *
   * @access public
   * @since  2.3
   * @return int
   */
  public function getErrorLevel() {
    return $this->errorLevel;
  }

  /**
   * Return the mysqli error description
   *
   * @access public
   * @since 2.3
   * @return string
   */
  public function getErrorDesc() {
    return $this->errorDesc;
  }

  /**
   * Connect to Osclass database
   *
   * @access public
   * @since 2.3
   * @return boolean It returns true if the connection has been successful or false if not
   */
  public function connectToOsclassDb() {
    $conn = $this->_connectToDb($this->dbHost, $this->dbUser, $this->dbPassword, $this->db);

    if ($conn == false) {
      $this->errorConnection();
      $this->releaseOsclassDb();

      if(!$this->is_install) {
        require_once LIB_PATH . 'osclass/helpers/hErrors.php';
        $title = 'Osclass Error';
        $message = 'Osclass database server is not available. <a href="https://forums.osclasspoint.com/">Need more help?</a>';
        
        if(OSC_DEBUG && $this->getErrorConnectionLevel() != 0) {
          $message .= '<br/>' . $this->getErrorConnectionLevel() . ' - ' . $this->getErrorConnectionDesc();
        }
        
        osc_die($title, $message);
      } else {
        return false;
      }
    }

    // $this->_setCharset('utf8', $this->db);
    $this->_setCharset('utf8mb4', $this->db);       // Support for emoji

    if($this->dbName == '') {
      return true;
    }

    $selectDb = $this->selectOsclassDb();
    
    if ($selectDb == false) {
      $this->errorReport();
      $this->releaseOsclassDb();

      if(!$this->is_install) {
        require_once LIB_PATH . 'osclass/helpers/hErrors.php';
        $title = 'Osclass Error';
        $message = 'Osclass database is not available. <a href="https://forums.osclasspoint.com">Need more help?</a>';
        osc_die($title, $message);
      } else {
        return false;
      }
    }

    return true;
  }

  /**
   * Select Osclass database in $db var
   *
   * @access private
   * @since 2.3
   * @return boolean It returns true if the database has been selected sucessfully or false if not
   */
  public function selectOsclassDb() {
    return $this->_selectDb($this->dbName, $this->db);
  }

  /**
   * It reconnects to Osclass database. First, it releases the database link connection and it connects again
   *
   * @access private
   * @since 2.3
   */
  public function reconnectOsclassDb() {
    $this->releaseOsclassDb();
    $this->connectToOsclassDb();
  }

  /**
   * Release the Osclass database connection
   *
   * @access private
   * @since 2.3
   * @return boolean
   */
  public function releaseOsclassDb() {
    $release = $this->_releaseDb($this->db);

    if(!$release) {
      $this->errorReport();
    }

    return $release;
  }

  /**
   * It returns the osclass database link connection
   *
   * @access public
   * @since 2.3
   */
  public function getOsclassDb() {
    return $this->_getDb($this->db);
  }

  /**
   * Connect to the database passed per parameter
   *
   * @param string $host Database host
   * @param string $user Database user
   * @param string $password Database user password
   * @param mysqli $connId Database connector link
   * @return boolean It returns true if the connection
   */
  public function _connectToDb($host, $user, $password, &$connId) {
    mysqli_report(MYSQLI_REPORT_OFF);  // default from PHP 8.1 is MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT

    try {
      $connId = $this->db = @new mysqli($host, $user, $password);
    } catch (mysqli_sql_exception $e) {
      $this->errorLevel = $this->connErrorLevel = $e->getCode();
      $this->errorDesc = $this->connErrorDesc = $e->getMessage();

      return false;
    }
    
    if ($this->db->connect_errno) {
      return false;
    }
    
    $this->set_sql_mode(array(), $this->db);
    return true;
  }

  /**
   *
   *
   * @param array $modes
   * @param     $connId
   */
  public function set_sql_mode($modes = array(), &$connId = NULL) {
    if (empty($modes)) {
      $res = mysqli_query($connId, 'SELECT @@SESSION.sql_mode');

      if (empty($res)) {
        return;
      }

      $modes_array = mysqli_fetch_array($res);
      if (empty($modes_array[0])) {
        return;
      }
      $modes_str = $modes_array[0];


      if (empty($modes_str)) {
        return;
      }

      $modes = explode(',', $modes_str);
    }

    $modes = array_change_key_case($modes, CASE_UPPER);
    $incompatible_modes = $this->incompatible_modes;
    foreach ($modes as $i => $mode) {
      if (in_array($mode, $incompatible_modes)) {
        unset($modes[ $i ]);
      }
    }

    $modes_str = implode(',', $modes);
    mysqli_query($connId, "SET SESSION sql_mode='$modes_str'");
  }

  /**
   * At the end of the execution it prints the database debug if it's necessary
   *
   * @since  2.3
   * @access private
   *
   * @param bool $printFrontend
   *
   * @return bool
   */
  public function debug($printFrontend = true) {
    $log = LogDatabase::newInstance();

    if(OSC_DEBUG_DB_EXPLAIN) {
      $log->writeExplainMessages();
    }

    if(!OSC_DEBUG_DB) {
      return false;
    }

    // if((defined('IS_AJAX') || Params::getParam('ajaxRequest') == 1 || Params::getParam('nolog') == 1) && !OSC_DEBUG_DB_LOG && !OSC_DEBUG_DB_AJAX_PRINT) {
    if(
      !OSC_DEBUG_DB_LOG 
      && !OSC_DEBUG_DB_AJAX_PRINT
      && (
        defined('IS_AJAX') 
        || (class_exists('Params') && Params::getParam('ajaxRequest') == 1 || isset($_GET['ajaxRequest']) && $_GET['ajaxRequest'] == 1 || isset($_POST['ajaxRequest']) && $_POST['ajaxRequest'] ==1)
        || (class_exists('Params') && Params::getParam('nolog') == 1 || isset($_GET['nolog']) && $_GET['nolog'] == 1 || isset($_POST['nolog']) && $_POST['nolog'] ==1)
      )
     ) {
      return false;
    }

    if(OSC_DEBUG_DB_LOG) {
      $log->writeMessages();
    } else if($printFrontend || (defined('IS_AJAX') && OSC_DEBUG_DB_AJAX_PRINT)) {
      $log->printMessages();
    } else {
      return false;
    }

    unset($log);
    return true;
  }

  /**
   * It selects the database of a connector database link
   *
   * @since 2.3
   * @access private
   * @param string $dbName Database name. If you leave blank this field, it will
   * select the database set in the init method
   * @param mysqli $connId Database connector link
   * @return boolean It returns true if the database has been selected or false if not
   */
  public function _selectDb($dbName, &$connId) {
    if ($connId->connect_errno) {
      return false;
    }

    if(OSC_DEBUG) {
      return $connId->select_db($dbName);
    }

    return @$connId->select_db($dbName);
  }

  /**
   * Set charset of the database passed per parameter
   *
   * @since 2.3
   * @access private
   * @param string $charset The charset to be set
   * @param mysqli $connId Database link connector
   */
  public function _setCharset($charset, &$connId) {
    if(OSC_DEBUG) {
      $connId->set_charset($charset);
    }

    @$connId->set_charset($charset);
  }

  /**
   * Release the database connection passed per parameter
   *
   * @since 2.3
   * @access private
   * @param mysqli $connId Database connection to be released
   * @return boolean It returns true if the database connection is released and false
   * if the database connection couldn't be closed
   */
  public function _releaseDb(&$connId) {
    // can be false or 0 (as integer) !!
    if(!$connId) {
      return true;
    }

    // update osclass 440
    if($connId->connect_errno) {
      // This would return error: $connId->connect_error;
      return true;
    }
    
    // update osclass 801
    if($connId->errno) {
      return true;
    } 
    
    return @$connId->close();
  }

  /**
   * It returns database link connection
   *
   * @param mysqli $connId Database connector link
   * @return mixed mysqli link connector if it's correct, or false if the dabase connection
   * hasn't been done.
   */
  public function _getDb(&$connId) {
    if($connId != false) {
      return $connId;
    }

    return false;
  }
}

/* file end: ./oc-includes/osclass/classes/database/DBConnectionClass.php */
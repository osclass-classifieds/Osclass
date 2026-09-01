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


define('DB_FUNC_NOW', 'NOW()');
define('DB_CONST_TRUE', 'TRUE');
define('DB_CONST_FALSE', 'FALSE');
define('DB_CONST_NULL', 'NULL');
define('DB_CUSTOM_COND', 'DB_CUSTOM_COND');
/**
 * DAO base model
 * 
 * @package Osclass
 * @subpackage Model
 * @since 2.3
 */
class DAO {
  public $dao;
  public $tableName;
  public $tablePrefix;
  public $primaryKey;
  public $fields;

  /**
   * Init connection of the database and create DBCommandClass object
   */
  public function __construct() {
    $conn = DBConnectionClass::newInstance();
    $data = $conn->getOsclassDb();
    $this->dao = new DBCommandClass($data);
    $this->tablePrefix = DB_TABLE_PREFIX;
  }
  
  /**
   * Reinitialize connection to the database once the object is unserialized
   */
  public function __wakeup() {
    $conn = DBConnectionClass::newInstance();
    $data = $conn->getOsclassDb();
    $this->dao = new DBCommandClass($data);
  }

  /**
   * Get the result match of the primary key passed by parameter
   * 
   * @access public
   * @since unknown
   * @param string $value
   * @return mixed If the result has been found, it return the array row. If not, it returns false
   */
  public function findByPrimaryKey($value) {
    $this->dao->select($this->fields);
    $this->dao->from($this->getTableName());
    $this->dao->where($this->getPrimaryKey(), $value);
    $result = $this->dao->get();

    if($result === false) {
      return false;
    }

    if($result->numRows() !== 1) {
      return false;
    }

    return $result->row();
  }

  /**
   * Update row by primary key
   * 
   * @access public
   * @since unknown
   * @param array $values Array with keys (database field) and values
   * @param string $key Primary key to be updated
   * @return mixed It return the number of affected rows if the update has been 
   * correct or false if nothing has been modified
   */
  public function updateByPrimaryKey($values, $key) {
    $cond = array(
      $this->getPrimaryKey() => $key
    );

    return $this->update($values, $cond);
  }

  /**
   * Delete the result match from the primary key passed by parameter
   * 
   * @access public
   * @since unknown
   * @param string $value
   * @return mixed It return the number of affected rows if the delete has been 
   * correct or false if nothing has been modified
   */
  public function deleteByPrimaryKey($value) {
    $cond = array(
      $this->getPrimaryKey() => $value
    );

    return $this->delete($cond);
  }

  /**
   * Get all the rows from the table $tableName
   * 
   * @access public
   * @since unknown
   * @return array 
   */
  public function listAll() {
    $this->dao->select($this->getFields());
    $this->dao->from($this->getTableName());
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Basic insert
   * 
   * @access public
   * @since unknown
   * @param array $values
   * @return boolean 
   */
  public function insert($values) {
    if(!$this->checkFieldKeys(array_keys($values))) {
      return false;
    }

    $this->dao->from($this->getTableName());
    $this->dao->set($values);
    
    return $this->dao->insert();
  }

  /**
   * Basic update. It returns false if the keys from $values or $where doesn't
   * match with the fields defined in the construct
   * 
   * @access public
   * @since unknown
   *
   * @param string|array $values Array with keys (database field) and values
   * @param array $where
   *
   * @return mixed It returns the number of affected rows if the update has been 
   * correct or false if an error happended
   */
  public function update($values, $where) {
    if(!is_array($values)) {
      $values = array($values);
    }
    
    if(!is_array($where)) {
      $where = array($where);
    }
    
    if(!$this->checkFieldKeys(array_keys($values))) {
      return false;
    }

    if(!$this->checkFieldKeys(array_keys($where))) {
      return false;
    }

    $this->dao->from($this->getTableName());
    $this->dao->set($values);
    $this->dao->where($where);
    
    return $this->dao->update();
  }

  /**
   * Basic delete. It returns false if the keys from $where doesn't
   * match with the fields defined in the construct
   * 
   * @access public
   * @since unknown
   * @param array $where
   * @return mixed It returns the number of affected rows if the delete has been 
   * correct or false if an error happended
   */
  public function delete($where) {
    if(!$this->checkFieldKeys(array_keys($where))) {
      return false;
    }

    $this->dao->from($this->getTableName());
    $this->dao->where($where);
    
    return $this->dao->delete();
  }

  /**
   * Set table name, adding the DB_TABLE_PREFIX at the beginning
   * 
   * @access private
   * @since unknown
   * @param string $table 
   */
  public function setTableName($table) {
    $this->tableName = $this->tablePrefix . $table;
  }

  /**
   * Get table name
   * 
   * @access public
   * @since unknown
   * @return string 
   */
  public function getTableName() {
    return $this->tableName;
  }

  /**
   * Set primary key string
   * 
   * @access private
   * @since unknown
   * @param string $key 
   */
  public function setPrimaryKey($key) {
    $this->primaryKey = $key;
  }

  /**
   * Get primary key string
   * 
   * @access public
   * @since unknown
   * @return string 
   */
  public function getPrimaryKey() {
    return $this->primaryKey;
  }

  /**
   * Set fields array
   * 
   * @access private
   * @since 2.3
   * @param array $fields 
   */
  public function setFields($fields) {
    $this->fields = $fields;
  }

  /**
   * Get fields array
   * 
   * @access public
   * @since 2.3
   * @return array 
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * Check if the keys of the array exist in the $fields array
   * 
   * @access private
   * @since 2.3
   * @param array $aKey
   * @return boolean 
   */
  public function checkFieldKeys($aKey) {
    foreach($aKey as $key) {
      if(!in_array($key, $this->getFields())) {
        return false;
      }
    }

    return true;
  }

  /**
   * Get table prefix
   * 
   * @access public
   * @since 2.3
   * @return string 
   */
  public function getTablePrefix() {
    return $this->tablePrefix;
  }

  /**
   * Returns the last error code for the most recent mysqli function call
   * 
   * @access public
   * @since 2.3
   * @return int 
   */
  public function getErrorLevel() {
    return $this->dao->getErrorLevel();
  }

  /**
   * Returns a string description of the last error for the most recent MySQLi function call
   * 
   * @access public
   * @since 2.3
   * @return string 
   */
  public function getErrorDesc() {
    return $this->dao->getErrorDesc();
  }

  /**
   * Returns the number of rows in the table represented by this object.
   * 
   * @access public
   * @since unknown
   * @return int
   */
  public function count() {
    $this->dao->select('COUNT(*) as count');
    $this->dao->from($this->getTableName());
    $result = $this->dao->get();

    if($result == false) {
      return 0;
    }

    if($result->numRows() == 0) {
      return 0;
    }

    $row = $result->row();
    return $row['count'];
  }


  /**
   * Returns information about database table
   * 
   * @access public
   * @since unknown
   * @return array
   */
  public function getTableInfo($table = '') {
    $table = ($table == '' ? $this->getTableName() : $table);
    
    $this->dao->select();
    $this->dao->from('INFORMATION_SCHEMA.TABLES');
    $this->dao->where('table_name', $table);
    
    $result = $this->dao->get();

    if($result) {
      $data = $result->row();
      return $data;
    }

    return array();
  }
  
  
  /**
   * Returns information about database table columns 
   * 
   * @access public
   * @since unknown
   * @return array
   */
  public function getTableColumnsInfo($table = '') {
    $table = ($table == '' ? $this->getTableName() : $table);
    
    $this->dao->select();
    $this->dao->from('INFORMATION_SCHEMA.COLUMNS');
    $this->dao->where('table_name', $table);
    
    $result = $this->dao->get();

    if($result) {
      $data = $result->result();
      return $data;
    }

    return array();
  }
}

/* file end: ./oc-includes/osclass/classes/database/DAO.php */
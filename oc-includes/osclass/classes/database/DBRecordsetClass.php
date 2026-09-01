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
 * Database recordset object
 * 
 * @package Osclass
 * @subpackage Database
 * @since 2.3
 */
class DBRecordsetClass
{
  /**
   * Database connection object to Osclass database
   * 
   * @access public
   * @since 2.3
   * @var mysqli 
   */
  public $connId;
  /**
   * Database result object
   * 
   * @access public
   * @since 2.3
   * @var MySQLi_Result 
   */
  public $resultId;
  /**
   * Result array
   * 
   * @access private
   * @since 2.3
   * @var array
   */
  public $resultArray;
  /**
   * Result object
   * 
   * @access private
   * @since 2.3
   * @var object
   */
  public $resultObject;
  /**
   * Current row
   * 
   * @access private
   * @since 2.3
   * @var int
   */
  protected $currentRow;
  /**
   * Number of rows
   * 
   * @access public
   * @since 2.3
   * @var int
   */
  public $numRows;

  /**
   * Initialize Recordset Class
   * 
   * @param mysqli $connId
   * @param MySQLi_Result $resultId 
   */
  public function __construct($connId = null, $resultId = null)
  {
    $this->connId     = $connId;
    $this->resultId   = $resultId;
    $this->resultArray  = array();
    $this->resultObject = array();
    $this->currentRow   = 0;
    $this->numRows    = 0;
  }

  /**
   * Get the results of MySQLi_Result object
   * 
   * @access public
   * @since 2.3
   * @param string $type 
   * @return mixed It can be an array or an object 
   */
  public function result($type = 'array')
  {
    if( $type === 'array') {
      return $this->resultArray();
    }

    return $this->resultObject();
  }

  /**
   * Get the results of MySQLi_Result object in array format
   * 
   * @access public
   * @since 2.3
   * @return array 
   */
  public function resultArray()
  {
    if( count($this->resultArray) > 0 ) {
      return $this->resultArray;
    }

    $this->_dataSeek();
    while($row = $this->_fetchArray()) {
      $this->resultArray[] = $row;
    }

    return $this->resultArray;
  }

  /**
   * Get the results of MySQLi_Result object in object format
   * 
   * @access public
   * @since 2.3
   * @return object|countable
   */
  public function resultObject()
  {
    if( count($this->resultObject) > 0 ) {
      return $this->resultObject;
    }

    $this->_dataSeek();
    while( $row = $this->_fetchObject() ) {
      $this->resultObject[] = $row;
    }

    return $this->resultObject;
  }

  /**
   * Adjust resultId pointer to the selected row
   * 
   * @access private
   * @since 2.3
   * @param int $offset Must be between zero and the total number of rows minus one
   * @return bool true on success or false on failure
   */
  public function _dataSeek($offset = 0)
  {
    return $this->resultId->data_seek($offset);
  }

  /**
   * Returns the current row of a result set as an object
   * 
   * @access private
   * @since 2.3
   * @return object 
   */
  public function _fetchObject()
  {
    return $this->resultId->fetch_object();
  }

  /**
   * Returns the current row of a result set as an array
   * 
   * @access private
   * @since 2.3
   * @return array 
   */
  public function _fetchArray()
  {
    return $this->resultId->fetch_assoc();
  }

  /**
   * Get a result row as an array or object
   *
   * @param int $n
   * @param string $type
   * @return mixed 
   */
  public function row($n = 0, $type = 'array')
  {
    if( !is_numeric($n) ) {
      $n = 0;
    }

    if( $type === 'array' ) {
      return $this->rowArray($n);
    }

    return $this->rowObject($n);
  }

  /**
   * Get a result row as an object
   * 
   * @access public
   * @since 2.3
   * @param int $n
   * @return object 
   */
  public function rowObject($n = 0)
  {
    $result = $this->resultObject();

    if( count($result) == 0) {
      return $result;
    }

    if( $n != $this->currentRow && isset($result[$n]) ) {
      $this->currentRow = $n;
    }

    return $result[$this->currentRow];
  }

  /**
   * Get a result row as an array
   * 
   * @access public
   * @since 2.3
   * @param int $n
   * @return array
   */
  public function rowArray($n = 0)
  {
    $result = $this->resultArray();

    if( count($result) == 0) {
      return $result;
    }

    if( $n != $this->currentRow && isset($result[$n]) ) {
      $this->currentRow = $n;
    }

    return $result[$this->currentRow];
  }

  /**
   * Get the first row as an array or object
   * 
   * @access public
   * @since 2.3
   * @param string $type
   * @return mixed 
   */
  public function firstRow($type = 'array')
  {
    $result = $this->result($type);

    if( count($result) == 0 ) {
      return $result;
    }

    return $result[0];
  }

  /**
   * Get the last row as an array or object
   * 
   * @access public
   * @since 2.3
   * @param string $type
   * @return mixed 
   */
  public function lastRow($type = 'array')
  {
    $result = $this->result($type);

    if( count($result) == 0 ) {
      return $result;
    }

    return $result[count($result) - 1];
  }

  /**
   * Get next row as an array or object
   * 
   * @access public
   * @since 2.3
   * @param string $type
   * @return mixed 
   */
  public function nextRow($type = 'array')
  {
    $result = $this->result($type);

    if( count($result) == 0 ) {
      return $result;
    }

    if( isset($result[$this->currentRow + 1]) ) {
      $this->currentRow++;
    }

    return $result[$this->currentRow];
  }

  /**
   * Get previous row as an array or object
   * 
   * @access public
   * @since 2.3
   * @param string $type
   * @return mixed 
   */
  public function previousRow($type = 'array')
  {
    $result = $this->result($type);

    if( count($result) == 0 ) {
      return $result;
    }

    if( isset($result[$this->currentRow - 1]) ) {
      $this->currentRow--;
    }

    return $result[$this->currentRow];
  }

  /**
   * Get number of rows
   * 
   * @access public
   * @since 2.3
   * @return int 
   * updated 440 - condition
   */
  public function numRows() {
    if(!is_bool($this->resultId)) {
      return $this->resultId->num_rows;
    }
    
    return 0;
  }

  /**
   * Get the number of fields in a result
   * 
   * @access public
   * @since 2.3
   * @return int 
   */
  public function numFields()
  {
    return $this->resultId->field_count;
  }

  /**
   * Get the name of the fields in an array
   * 
   * @access public
   * @since 2.3
   * @return array 
   */
  public function listFields()
  {
    $fieldNames = array();
    while( $field = $this->resultId->fetch_field() ) {
      $fieldNames[] = $field->name;
    }

    return $fieldNames;
  }
}

/* file end: ./oc-includes/osclass/classes/database/DBRecordsetClass.php */
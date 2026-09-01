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
 * DataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
abstract class DataTable
{
  protected $aColumns;
  protected $aRows;
  protected $rawRows;

  protected $limit;
  protected $start;
  protected $iPage;
  protected $total;
  protected $totalFiltered;

  public function __construct()
  {
    $this->aColumns = array();
    $this->aRows = array();
    $this->rawRows = array();
  }

  /**
   * FUNCTIONS THAT SHOULD BE REDECLARED IN SUB-CLASSES
   *
   * @param null $results
   */
  public function setResults($results = null) {
    if(is_array($results)) {
      $this->start = 0;
      $this->limit = count($results);
      $this->total = count($results);
      $this->totalFiltered = count($results);

      if(count($results)>0) {
        foreach($results as $r) {
          $row = array();
          if(is_array($r)) {
            foreach($r as $k => $v) {
              $row[$k] = $v;
            }
          }
          $this->addRow($row);
        }
        if(is_array($results[0])) {
          foreach($results[0] as $k => $v) {
            $this->addColumn($k, $k);
          }
        }
      }
    }
  }


  /**
   * COMMON FUNCTIONS . DO NOT MODIFY THEM
   */


  /**
   * Add a colum
   *
   * @param $id
   * @param $text
   * @param int  $priority
   */
  public function addColumn($id, $text, $priority = 5)
  {
    $this->removeColumn($id);
    $this->aColumns[$priority][$id] = $text;
  }

  /**
   * @param $id
   */
  public function removeColumn($id)
  {
    for($priority=1;$priority<=10;$priority++) {
      unset($this->aColumns[$priority][$id]);
    }
  }

  /**
   * @param $aRow
   */
  protected function addRow($aRow)
  {
    $this->aRows[] = $aRow;
  }

  /**
   * @return array
   */
  public function sortedColumns()
  {
    $columns_ordered = array();
    for($priority=1;$priority<=10;$priority++) {
      if(isset($this->aColumns[$priority]) && is_array($this->aColumns[$priority])) {
        foreach($this->aColumns[$priority] as $k => $v) {
          $columns_ordered[$k] = $v;
        }
      }
    }
    return $columns_ordered;
  }

  /**
   * @return array
   */
  public function sortedRows()
  {
    $rows = array();
    $aRows = (array) $this->aRows;
    $columns = (array) $this->sortedColumns();
    if(count($aRows)===0) {
      return $rows;
    }
    foreach($aRows as $row) {
      $aux_row = array();
      foreach($columns as $k => $v) {
        if(isset($row[$k])) {
          $aux_row[$k] = $row[$k];
        } else {
          $aux_row[$k] = '';
        }
      }
      $rows[] = $aux_row;
    }
    return $rows;
  }

  /**
   * @return array
   */
  public function getData()
  {
    return array(
        'aColumns'        => $this->sortedColumns()
        ,'aRows'        => $this->sortedRows()
        ,'iDisplayLength'     => $this->limit
        ,'iTotalDisplayRecords' => $this->total
        ,'iTotalRecords'    => $this->totalFiltered
        ,'iPage'        => $this->iPage
    );
  }

  /**
   * @return array
   */
  public function rawRows()
  {
    return $this->rawRows;
  }
}
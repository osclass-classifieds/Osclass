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
abstract class DataTable {
  protected $aColumns;
  protected $aSortColumns;
  protected $aSourceColumns;
  protected $aRows;
  protected $rawRows;

  protected $limit;
  protected $start;
  protected $iPage;
  protected $total;
  protected $totalFiltered;
  protected $defaultSortKey;
  protected $defaultSortDirection;

  public function __construct() {
    $this->aColumns = array();
    $this->aSortColumns = array();
    $this->aSourceColumns = array();
    $this->aRows = array();
    $this->rawRows = array();
    $this->defaultSortKey = '';
    $this->defaultSortDirection = 'desc';
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
  public function addColumn($id, $text, $priority = 5) {
    $this->removeColumn($id);
    $this->aColumns[$priority][$id] = $text;
  }

  /**
   * Register sortable column
   *
   * @param string $id
   * @param string $dbColumn
   * @param mixed  $coalesce
   */
  public function addSortColumn($id, $dbColumn, $coalesce = false) {
    $this->aSortColumns[$id] = $this->coalesceSortColumn($dbColumn, $coalesce);
  }

  /**
   * Remove sortable column
   *
   * @param string $id
   */
  public function removeSortColumn($id) {
    unset($this->aSortColumns[$id]);
  }

  /**
   * Clear sortable columns
   */
  public function clearSortColumns() {
    $this->aSortColumns = array();
  }

  /**
   * Get sortable columns
   *
   * @return array
   */
  public function getSortColumns() {
    return $this->aSortColumns;
  }

  /**
   * Register source metadata for output headers
   *
   * @param string $id
   * @param string $sourceColumn
   */
  public function addSourceColumn($id, $sourceColumn) {
    $this->aSourceColumns[$id] = $sourceColumn;
  }

  /**
   * Clear source metadata
   */
  public function clearSourceColumns() {
    $this->aSourceColumns = array();
  }

  /**
   * Get source columns
   *
   * @return array
   */
  public function getSourceColumns() {
    return $this->aSourceColumns;
  }

  /**
   * Set default sort for datatable
   *
   * @param string $sortKey
   * @param string $direction
   */
  public function setDefaultSort($sortKey, $direction = 'desc') {
    $this->defaultSortKey = trim((string)$sortKey);
    $this->defaultSortDirection = $this->normalizeSortDirection($direction, 'desc');
  }

  /**
   * Get default sort key
   *
   * @return string
   */
  public function getDefaultSortKey() {
    return $this->defaultSortKey;
  }

  /**
   * Get default sort direction
   *
   * @return string
   */
  public function getDefaultSortDirection() {
    return $this->defaultSortDirection;
  }

  /**
   * Get source metadata ordered by visible columns
   *
   * @return array
   */
  public function sortedSourceColumns() {
    $ordered = array();
    $columns = $this->sortedColumns();
    $sources = $this->getFilteredSourceColumns();

    foreach($columns as $k => $v) {
      $ordered[$k] = (isset($sources[$k]) ? $sources[$k] : '');
    }

    return $ordered;
  }

  /**
   * Resolve request sort key and direction
   *
   * @param array  $params
   * @param string $defaultKey
   * @param string $defaultDirection
   *
   * @return array
   */
  public function resolveSort($params, $defaultKey = '', $defaultDirection = '') {
    $sortColumns = $this->getFilteredSortColumns();
    $fallbackKey = ($defaultKey !== '' ? $defaultKey : $this->getDefaultSortKey());
    $fallbackDirection = ($defaultDirection !== '' ? $defaultDirection : $this->getDefaultSortDirection());
    $sortKey = (isset($params['sort']) ? $params['sort'] : '');
    $direction = $this->normalizeSortDirection((isset($params['direction']) ? $params['direction'] : ''), $fallbackDirection);

    if(!isset($sortColumns[$sortKey])) {
      $sortKey = $fallbackKey;
    }

    if(!isset($sortColumns[$sortKey])) {
      $sortKey = '';
    }

    $data = array(
      'key' => $sortKey,
      'column' => ($sortKey !== '' ? $sortColumns[$sortKey] : ''),
      'direction' => $direction
    );

    return osc_apply_filter('datatable_sort_resolved_' . $this->getDatatableFilterKey(), osc_apply_filter('datatable_sort_resolved', $data, $this, $params), $this, $params);
  }

  /**
   * Build args for next sort state
   *
   * @param string $sortKey
   * @param string $currentSort
   * @param string $currentDirection
   *
   * @return string
   */
  public function buildSortArgs($sortKey, $currentSort, $currentDirection) {
    $nextDirection = 'asc';
    $currentDirection = $this->normalizeSortDirection($currentDirection, 'desc');

    if($currentSort === $sortKey) {
      $nextDirection = ($currentDirection === 'desc' ? 'asc' : 'desc');
    }

    return '&sort=' . urlencode($sortKey) . '&direction=' . urlencode($nextDirection);
  }

  /**
   * Normalize sort direction
   *
   * @param string $direction
   * @param string $defaultDirection
   *
   * @return string
   */
  public function normalizeSortDirection($direction, $defaultDirection = 'desc') {
    $direction = strtolower(trim((string)$direction));
    $defaultDirection = strtolower(trim((string)$defaultDirection));

    if($defaultDirection !== 'asc' && $defaultDirection !== 'desc') {
      $defaultDirection = 'desc';
    }

    if($direction !== 'asc' && $direction !== 'desc') {
      return $defaultDirection;
    }

    return $direction;
  }

  /**
   * Build coalesced SQL for sort column
   *
   * @param string $dbColumn
   * @param mixed  $coalesce
   *
   * @return string
   */
  protected function coalesceSortColumn($dbColumn, $coalesce = false) {
    if($coalesce === false) {
      return $dbColumn;
    }

    $fallback = null;

    if($coalesce === true) {
      $base = $this->extractSortBaseColumn($dbColumn);

      if(strpos($base, 'i_') === 0) {
        $fallback = 0;
      } else if(strpos($base, 's_') === 0) {
        $fallback = '';
      } else {
        return $dbColumn;
      }
    } else {
      $fallback = $coalesce;
    }

    return 'COALESCE(' . $dbColumn . ', ' . $this->formatSortValue($fallback) . ')';
  }

  /**
   * Extract base column name without table prefix
   *
   * @param string $dbColumn
   *
   * @return string
   */
  protected function extractSortBaseColumn($dbColumn) {
    if(!preg_match('/^[a-zA-Z0-9_\.]+$/', $dbColumn)) {
      return '';
    }

    $parts = explode('.', $dbColumn);
    return strtolower(end($parts));
  }

  /**
   * Format SQL fallback value
   *
   * @param mixed $value
   *
   * @return string
   */
  protected function formatSortValue($value) {
    if(is_int($value) || is_float($value)) {
      return (string)$value;
    }

    if(is_bool($value)) {
      return ($value ? '1' : '0');
    }

    if($value === null) {
      return 'NULL';
    }

    return '\'' . str_replace('\'', '\\\'', (string)$value) . '\'';
  }

  /**
   * Resolve pagination, search and sort params from admin datatable request.
   *
   * @param array $params  Request params (Params::getParamsAsArray() or ajax payload)
   * @param array $options {
   *   @type int    $default_per_page   Default rows per page (25)
   *   @type string $default_sort_key   Fallback sort key
   *   @type string $default_sort_dir   asc|desc
   *   @type bool   $with_sort          Resolve sort and update Params (true)
   *   @type string $page_param         Page param name (iPage)
   * }
   *
   * @return array
   */
  protected function resolveListParams($params, $options = array()) {
    $params = (is_array($params) ? $params : array());
    $defaults = array(
      'default_per_page' => 25,
      'default_sort_key' => '',
      'default_sort_dir' => 'desc',
      'with_sort' => true,
      'page_param' => 'iPage'
    );
    $options = array_merge($defaults, (is_array($options) ? $options : array()));

    $pageParam = (string)$options['page_param'];
    $perPage = (isset($params['iDisplayLength']) && (int)$params['iDisplayLength'] > 0 ? (int)$params['iDisplayLength'] : (int)$options['default_per_page']);
    if($perPage <= 0) {
      $perPage = (int)$options['default_per_page'];
    }

    $iPage = (isset($params[$pageParam]) && (int)$params[$pageParam] > 0 ? (int)$params[$pageParam] : 1);
    Params::setParam($pageParam, $iPage);

    $search = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');
    $sortData = array(
      'key' => '',
      'column' => '',
      'direction' => $this->normalizeSortDirection($options['default_sort_dir'], 'desc')
    );

    if($options['with_sort']) {
      $sortData = $this->resolveSort($params, (string)$options['default_sort_key'], (string)$options['default_sort_dir']);
      Params::setParam('sort', $sortData['key']);
      Params::setParam('direction', $sortData['direction']);
    }

    return array(
      'iPage' => $iPage,
      'start' => (int)(($iPage - 1) * $perPage),
      'limit' => $perPage,
      'search' => $search,
      'with_filters' => ($search != ''),
      'sort' => $sortData
    );
  }

  /**
   * Build admin datatable sort URL base (without sort/direction query args).
   *
   * @return string
   */
  protected function getSortUrlBase() {
    Rewrite::newInstance()->init();
    return preg_replace('|&direction=([^&]*)|', '', preg_replace('|&sort=([^&]*)|', '', osc_base_url() . Rewrite::newInstance()->get_raw_request_uri()));
  }

  /**
   * Filter key based on datatable class name
   *
   * @return string
   */
  protected function getDatatableFilterKey() {
    $class = strtolower(get_class($this));
    $class = preg_replace('/datatable$/', '', $class);
    return preg_replace('/[^a-z0-9_]+/', '_', $class);
  }

  /**
   * Get sort columns with filters applied
   *
   * @return array
   */
  protected function getFilteredSortColumns() {
    $columns = osc_apply_filter('datatable_sort_columns', $this->aSortColumns, $this);
    return osc_apply_filter('datatable_sort_columns_' . $this->getDatatableFilterKey(), $columns, $this);
  }

  /**
   * Get source columns with filters applied
   *
   * @return array
   */
  protected function getFilteredSourceColumns() {
    $columns = osc_apply_filter('datatable_source_columns', $this->aSourceColumns, $this);
    return osc_apply_filter('datatable_source_columns_' . $this->getDatatableFilterKey(), $columns, $this);
  }

  /**
   * @param $id
   */
  public function removeColumn($id) {
    foreach($this->aColumns as $priority => $cols) {
      if(is_array($cols)) {
        unset($this->aColumns[$priority][$id]);
      }
    }
  }

  /**
   * @param $aRow
   */
  protected function addRow($aRow) {
    $this->aRows[] = $aRow;
  }

  /**
   * @return array
   */
  public function sortedColumns() {
    $columns_ordered = array();
    $priorities = array_keys($this->aColumns);
    sort($priorities, SORT_NUMERIC);
    foreach($priorities as $priority) {
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
  public function sortedRows() {
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

  protected function buildRowActions($options = array(), $options_more = array(), $visible_limit = 8, $force_more = array()) {
    $options = (is_array($options) ? $options : array());
    $options_more = (is_array($options_more) ? $options_more : array());
    $force_more = (is_array($force_more) ? $force_more : array());
    $limit = (int)$visible_limit;
    if($limit <= 0) {
      $limit = 8;
    }

    $main = array();
    $more = array();

    foreach($options as $action) {
      if(in_array($action, $force_more, true)) {
        if(!in_array($action, $more, true)) {
          $more[] = $action;
        }
        continue;
      }

      if(count($main) < $limit) {
        $main[] = $action;
      } else {
        $more[] = $action;
      }
    }

    foreach($options_more as $action) {
      if(in_array($action, $force_more, true)) {
        if(!in_array($action, $more, true)) {
          $more[] = $action;
        }
        continue;
      }

      if(count($main) < $limit) {
        $main[] = $action;
      } else {
        $more[] = $action;
      }
    }

    if(count($main) == 0 && count($more) == 0) {
      return '';
    }

    $output = '<ul>' . PHP_EOL;
    foreach($main as $action) {
      $output .= '<li>' . $action . '</li>' . PHP_EOL;
    }

    if(count($more) > 0) {
      $output .= '<li class="show-more">' . PHP_EOL . '<a href="#" class="show-more-trigger">' . __('Show more') . '...</a>' . PHP_EOL . '<ul>' . PHP_EOL;
      foreach($more as $action) {
        $output .= '<li>' . $action . '</li>' . PHP_EOL;
      }
      $output .= '</ul>' . PHP_EOL . '</li>' . PHP_EOL;
    }

    $output .= '</ul>' . PHP_EOL;
    return '<div class="actions">' . $output . '</div>' . PHP_EOL;
  }

  /**
   * @return array
   */
  public function getData() {
    $sortableColumns = array_keys($this->getFilteredSortColumns());

    return array(
        'aColumns'        => $this->sortedColumns()
        ,'aColumnSources'   => $this->sortedSourceColumns()
        ,'aSortableColumns' => $sortableColumns
        ,'aRows'        => $this->sortedRows()
        ,'iDisplayLength'     => $this->limit
        ,'iTotalDisplayRecords' => $this->totalFiltered
        ,'iTotalRecords'    => $this->total
        ,'iPage'        => $this->iPage
    );
  }

  /**
   * @return array
   */
  public function rawRows() {
    return $this->rawRows;
  }
}

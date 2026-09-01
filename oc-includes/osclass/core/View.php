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


class View {
  private $aExported;
  private $aCurrent;
  private static $instance;

  /**
   * @return \View
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function __construct() {
    $this->aExported = array();
  }

  //to export variables at the business layer

  /**
   * @param $key
   * @param $value
   */
  public function _exportVariableToView($key, $value = '') {
    $this->aExported[$key] = $value;
  }

  // New function
  public function _set($key, $value = '') {
    $this->_exportVariableToView($key, $value);
  }
  
  //to get the exported variables for the view

  /**
   * @param $key
   *
   * @return mixed|string|array
   */
  public function _get($key) {
    if($this->_exists($key)) {
      return $this->aExported[$key];
    }

    return '';
  }


  // Return all view values
  public function _all() {
    return $this->aExported;
  }
  
  //only for debug
  /**
   * @param null $key
   */
  public function _view($key = null) {
    if($key) {
      print_r($this->aExported[$key]);
    } else {
      print_r($this->aExported);
    }
  }

  /**
   * @param $key
   *
   * @return bool
   */
  public function _next($key) {
    if(isset($this->aExported[$key]) && is_array($this->aExported[$key])) {
      $this->aCurrent[$key] = current($this->aExported[$key]);
      if($this->aCurrent[$key]) {
        next($this->aExported[$key]);
        return true;
      }
    }
    return false;
  }

  /**
   * @param $key
   *
   * @return string|array
   */
  public function _current($key) {
    if(isset($this->aExported[$key]) && is_array($this->aExported[$key])) {
      if(!isset($this->aCurrent[$key])) {
        $this->aCurrent[$key] = current($this->aExported[$key]);
      }
      return $this->aCurrent[$key];
    }
    return '';
  }

  /**
   * @param $key
   *
   * @return bool|int|null|string
   */
  public function _key($key) {
    if(isset($this->aExported[$key]) && is_array($this->aExported[$key])) {
      $_key = key($this->aExported[$key]) -1;
      if($_key==-1) {
        $_key = count($this->aExported[$key]) -1;
      }
      return $_key;
    }
    return false;
  }

  /**
   * @param $key
   * @param $position
   *
   * @return bool
   */
  public function _seek($key, $position) {
    if(isset($this->aExported[$key]) && is_array($this->aExported[$key])) {
      $this->_reset($key);
      for($k = 0;$k<=$position;$k++) {
        $res = $this->_next($key);
        
        if(!$res) {
          return false;
        }
      }
      return true;
    }
    return false;
  }

  /**
   * @param $key
   *
   * @return array|mixed
   */
  public function _reset($key) {
    if(!array_key_exists($key, $this->aExported)) {
      return array();
    }
    if(!is_array($this->aExported[$key])) {
      return array();
    }
    return reset($this->aExported[$key]);
  }

  /**
   * @param $key
   *
   * @return bool
   */
  public function _exists($key) {
    return (isset($this->aExported[$key]) ? true : false);
  }

  /**
   * @param $key
   *
   * @return int
   */
  public function _count($key) {
    if(isset($this->aExported[$key]) && is_array($this->aExported[$key])) {
      return count($this->aExported[$key]);
    }
    return -1; // @TOFIX @FIXME ?? why ? why not 0 ?
  }

  /**
   * @param $key
   */
  public function _erase($key) {
    unset($this->aExported[$key] , $this->aCurrent[$key]);
  }
}

/* file end: ./oc-includes/osclass/core/View.php */
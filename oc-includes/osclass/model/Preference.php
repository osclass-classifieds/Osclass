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
 *
 */
class Preference extends DAO
{
  /**
   *
   * @var
   */
  private static $instance;
  /**
   * array for save preferences
   * @var array
   */
  private $pref;

  /**
   * @return \Preference|\type
   */
  public static function newInstance() {
    if( !self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   *
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_preference');
    /* $this->set_primary_key($key); // no primary key in preference table */
    $this->setFields( array('s_section', 's_name', 's_value', 'e_type') );
    $this->toArray();
  }

  /**
   * Find a value by its name
   *
   * @access public
   * @since  unknown
   *
   * @param $name
   *
   * @return bool
   */
  public function findValueByName($name)
  {
    $this->dao->select('s_value');
    $this->dao->from($this->getTableName());
    $this->dao->where('s_name', $name);
    $result = $this->dao->get();

    if( $result == false ) {
      return false;
    }

    if( $result->numRows() == 0 ) {
      return false;
    }

    $row = $result->row();
    return $row['s_value'];
  }

  /**
   * Find array preference for a given section
   *
   * @access public
   * @since unknown
   *
   * @param string $name
   *
   * @return array|bool
   */
  public function findBySection($name)
  {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_section', $name);
    $result = $this->dao->get();

    if( $result == false ) {
      return array();
    }

    if( $result->numRows() == 0 ) {
      return false;
    }

    return $result->result();
  }

  /**
   * Modify the structure of table.
   *
   * @access public
   * @since unknown
   */
  public function toArray()
  {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $result = $this->dao->get();

    if( $result == false ) {
      return false;
    }

    if( $result->numRows() == 0 ) {
      return false;
    }

    $aTmpPref = $result->result();
    foreach($aTmpPref as $tmpPref) {
      $this->pref[$tmpPref['s_section']][$tmpPref['s_name']] = $tmpPref['s_value'];
    }

    return true;
  }

  /**
   * Get value, given a preference name and a section name.
   *
   * @access public
   * @since unknown
   * @param string $key
   * @param string $section
   * @return string
   */
  public function get($key, $section = 'osclass' )
  {
    if (isset($this->pref[$section]) && isset($this->pref[$section][$key])) {
      return $this->pref[$section][$key];
    }
    return '';
  }

  /**
   * Get value, given a preference name and a section name.
   *
   * @access public
   * @since unknown
   * @param string $section
   * @return array
   */
  public function getSection($section = 'osclass' )
  {
    if (isset($this->pref[$section]) && is_array($this->pref[$section])) {
      return $this->pref[$section];
    }
    return array();
  }

  /**
   * Set preference value, given a preference name and a section name.
   *
   * @access public
   * @since unknown
   * @param string $key
   * @param string$value
   * @param string $section
   */
  public function set($key, $value, $section = 'osclass' )
  {
    $this->pref[$section][$key] = $value;
  }

  /**
   * Replace preference value, given preference name, preference section and value.
   *
   * @access public
   * @since unknown
   * @param string $key
   * @param string $value
   * @param string $section
   * @param string $type
   * @return boolean
   */
  public function replace($key, $value, $section = 'osclass', $type = 'STRING')
  {
    static $aValidEnumTypes = array('STRING','INTEGER','BOOLEAN');
    $array_replace = array(
      's_name'  => $key,
      's_value'   => $value,
      's_section' => $section,
      'e_type'  => in_array($type, $aValidEnumTypes) ? $type : 'STRING'
    );
    return $this->dao->replace($this->getTableName(), $array_replace);
  }
}

/* file end: ./oc-includes/osclass/model/Preference.php */
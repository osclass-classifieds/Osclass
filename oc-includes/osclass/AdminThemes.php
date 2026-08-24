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
 * Class AdminThemes
 */
class AdminThemes extends Themes
{
  private static $instance;

  /**
   * @return \AdminThemes
   */
  public static function newInstance()
  {
    if (!self::$instance instanceof self) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  public function __construct()
  {
    parent::__construct();
    $this->setCurrentTheme(osc_admin_theme());
  }

  public function setCurrentThemeUrl()
  {
    if ($this->theme_exists) {
      $this->theme_url = osc_admin_base_url() . 'themes/' . $this->theme . '/';
    } else {
      $this->theme_url = osc_admin_base_url() . 'themes/modern/';
      //$this->theme_url = osc_admin_base_url() . 'gui/';
    }
  }

  public function setCurrentThemePath()
  {
    if ($this->theme <> '' && file_exists(osc_admin_base_path() . 'themes/' . $this->theme . '/')) {
      $this->theme_exists = true;
      $this->theme_path   = osc_admin_base_path() . 'themes/' . $this->theme . '/';
    } else {
      $this->theme_exists = false;
      $this->theme_path   = osc_admin_base_path() . 'themes/modern/';
      //$this->theme_path   = osc_admin_base_path() . 'gui/';
    }
  }

  /**
   *
   * @param  $theme
   *
   * @return array|bool 
   */
  public function loadThemeInfo($theme)
  { 
    $path = $this->theme_path . 'index.php';
    if( !file_exists($path) ) {
      return false;
    }

    // NEW CODE FOR THEME INFO
    $s_info = file_get_contents($path);
    $info   = array();
    if( preg_match('|Theme Name:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['name'] = trim($match[1]);
    } else {
      $info['name'] = '';
    }

    if( preg_match('|Parent Theme:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['template'] = trim($match[1]);
    } else {
      $info['template'] = '';
    }

    if( preg_match('|Theme URI:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['theme_uri'] = trim($match[1]);
    } else {
      $info['theme_uri'] = '';
    }

    if( preg_match('|Theme update URI:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['theme_update_uri'] = trim($match[1]);
    } else {
      $info['theme_update_uri'] = '';
    }

    if( preg_match('|Description:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['description'] = trim($match[1]);
    } else {
      $info['description'] = '';
    }

    if( preg_match('|Version:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['version'] = trim($match[1]);
    } else {
      $info['version'] = '';
    }

    if( preg_match('|Author:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['author_name'] = trim($match[1]);
    } else {
      $info['author_name'] = '';
    }

    if( preg_match('|Author URI:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['author_url'] = trim($match[1]);
    } else {
      $info['author_url'] = '';
    }

    if( preg_match('|Product Key:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['product_key'] = trim($match[1]);
    } else {
      $info['product_key'] = '';
    }

    if( preg_match('|Widgets:([^\\r\\t\\n]*)|i', $s_info, $match) ) {
      $info['locations'] = explode( ',' , str_replace( ' ' , '' , $match[1]));
    } else {
      $info['locations'] = array();
    }
    $info['filename'] = $path;
    $info['int_name'] = $theme;

    if($info['name']!='') {
      return $info;
    }

    // OLD CODE INFO
    require_once $path;
    $fxName = $theme . '_theme_info';
    if (!function_exists($fxName)) {
      return false;
    }
    $result       = $fxName();
    $result['int_name'] = $theme;

    return $result;
  }
}

/* file end: ./oc-includes/osclass/AdminThemes.php */
<?php
use MatthiasMullie\Minify;

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
 * Styles enqueue class.
 *
 * @since 3.1.1
 */
class Styles {
  public $styles = array();
  private static $instance;

	/**
	 * @return \Styles
	 */
	public static function newInstance()
  {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function __construct()
  {
    $styles = array();
  }

  /**
   * Add style to be loaded
   *
   * @param $id
   * @param $url
   */
  public function addStyle($id, $url)
  {
    $this->styles[$id] = $url;
  }

  /**
   * Remove style to not be loaded
   *
   * @param $id
   */
  public function removeStyle($id)
  {
    unset($this->styles[$id]);
  }

  /**
   * Get the css styles urls
   */
  public function getStyles()
  {
    return $this->styles;
  }

  /**
   * Print the HTML tags to load the styles
   */
  public function printStyles()
  {
    $compress = osc_css_minify();
    $minifier = new Minify\CSS('');
    $banned_pages = array_filter(array_map('strtolower', array_map('trim', explode(',', osc_css_banned_pages())))); 
    $current_page = strtolower(osc_get_osclass_location() == '' ? 'home' : osc_get_osclass_location());
    $current_page .= (osc_get_osclass_section() <> '' ? '-' . strtolower(osc_get_osclass_section()) : '');

    if(osc_css_merge() && !in_array($current_page, $banned_pages) && (!defined('OC_ADMIN') || OC_ADMIN !== true)) {
      $name = '';
      $content = '';
      $internal = array();
      $banned_words = array_filter(array_map('trim', explode(',', osc_css_banned_words()))); 
      
      // first collect internal names and check if file exists
      foreach($this->styles as $id => $url) {
        if(trim($url) != '') {
          if (strpos($url, '?v=') !== false) {
            $url = substr($url, 0, strpos($url, '?v='));
          }

          $path = str_replace(osc_base_url(), osc_base_path(), $url);
          
          // font and awesome are blocked names, these styles will not be minified
          if (strpos($url, osc_base_url()) !== false && !osc_string_contains_array($url, $banned_words)) {
            $internal[] = $id;
            
            if(is_file($path) && is_readable($path)){
              $modtime = filemtime($path);
            } else {
              $modtime = date('YmdHis');
            }
            
            $name .= $id . '_' . $modtime . ';';
          }
        }
      }
      
      $name = md5($name) . '.css';
      $save_path = osc_uploads_path() . 'minify/';
      $save_url = str_replace(osc_base_path(), osc_base_url(), $save_path);

      // if file does not exists, generate new
      if(!file_exists($save_path . $name)) {
        foreach($this->styles as $id => $url) {
          if(trim($url) != '') {
            if(in_array($id, $internal)) {
              if(strpos($url, '?v=') !== false) {
                $url = substr($url, 0, strpos($url, '?v='));
              }

              // If local file, update oc-content & oc-includes folders if changed
              if(strpos($url, osc_base_url()) !== false) {
                $url = str_replace('/oc-content/', '/' . osc_content_folder() . '/', $url);
                $url = str_replace('/oc-includes/', '/' . osc_includes_folder() . '/', $url);
              }
          
              $path = str_replace(osc_base_url(), osc_base_path(), $url);
              
              if(strpos($url, osc_base_url()) !== false && !osc_string_contains_array($url, $banned_words)) {
                if($compress) {
                  $minifier->add($path);
                } else {
                  $file = file_get_contents($path);
                  $content .= $file;
                }
              }
            }
          }
        }

        if($compress) {
          $minifier->minify($save_path . $name);
        } else {
          file_put_contents($save_path . $name, $content);
        }
      }
      
      foreach($this->styles as $id => $css) {
        if(!in_array($id, $internal)) {
          echo '<link href="' . osc_apply_filter('style_url', $css) . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
        }
      }
      
      echo '<link href="' . osc_apply_filter('style_url', $save_url . $name) . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
    } else {
      foreach($this->styles as $css) {
        echo '<link href="' . osc_apply_filter('style_url', $css) . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
      }
    }
  }
}
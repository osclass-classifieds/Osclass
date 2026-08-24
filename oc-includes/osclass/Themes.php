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
 * Class Themes
 */
abstract class Themes
{
  private static $instance;
  protected $theme;
  protected $theme_url;
  protected $theme_path;
  protected $theme_exists;
  protected $theme_is_child;  // if child theme is active, this variable will contain name of parent theme (theme base name)
  protected $path;
  
  protected $scripts;
  protected $queue;
  protected $styles;

  protected $resolved;
  protected $unresolved;

  public function __construct()
  {
    $this->scripts = array();
    $this->queue   = array();
    $this->styles  = array();
  }

  abstract protected function setCurrentThemeUrl();
  abstract protected function setCurrentThemePath();

  /* PUBLIC */
  /**
   * @param $theme
   */
  public function setCurrentTheme( $theme )
  {
    $this->theme = $theme;
    $this->setCurrentThemePath();
    $this->setCurrentThemeUrl();
  }
  
  public function setCurrentThemeIsChild( $theme )
  {
    $this->theme_is_child = $theme;
  }

  public function getCurrentTheme()
  {
    return $this->theme;
  }
  
  public function getCurrentThemeIsChild()
  {
    return $this->theme_is_child;
  }

  public function getCurrentThemeUrl()
  {
    return $this->theme_url;
  }

  public function getCurrentThemePath()
  {
    return $this->theme_path;
  }

  /**
   * @return string
   */
  public function getCurrentThemeStyles()
  {
    return $this->theme_url . 'css/';
  }

/**
 * @return string
 */
public function getCurrentThemeJs()
  {
    return $this->theme_url . 'js/';
  }

  /**
   * Add style to be loaded
   *
   * @param $id
   * @param $url
   * @deprecated deprecated since version 3.1
   */
  public function addStyle($id, $url)
  {
    $this->styles[$id] = $url;
  }

  /**
   * Remove style to not be loaded
   *
   * @param $id
   * @deprecated deprecated since version 3.1
   */
  public function removeStyle($id)
  {
    unset($this->styles[$id]);
  }

  /**
   * Get the css styles urls
   *
   * @deprecated deprecated since version 3.1
   */
  public function getStyles()
  {
    return Styles::newInstance()->getStyles();
  }

  /**
   * Print the HTML tags to load the styles
   *
   * @deprecated deprecated since version 3.1
   */
  public function printStyles()
  {
    foreach($this->styles as $css) {
      echo '<link href="'.$css.'" rel="stylesheet"="text/css" />' . PHP_EOL;
    }
  }

  /**
   * Add script to queue
   *
   * @param $id
   * @deprecated deprecated since version 3.1
   */
  public function enqueueScript($id)
  {
    $this->queue[$id] = $id;
  }

  /**
   * Remove script to not be loaded
   *
   * @param $id
   * @deprecated deprecated since version 3.1
   */
  public function removeScript($id)
  {
    unset($this->queue[$id]);
  }

  /**
   * Add script to be loaded
   *
   * @param $id
   * @param $url
   * @param $dependencies mixed, it could be an array or a string
   * @deprecated deprecated since version 3.1
   */
  public function registerScript($id, $url, $dependencies = null)
  {
    $this->scripts[$id] = array(
      'key' => $id
      ,'url' => $url
      ,'dependencies' => $dependencies
    );
  }

  /**
   * Remove script to not be loaded
   *
   * @param $id
   * @deprecated deprecated since version 3.1
   */
  public function unregisterScript($id)
  {
    unset($this->scripts[$id]);
  }

  /**
   * Order script before being printed on the HTML
   * @deprecated deprecated since version 3.1
   */
  private function orderScripts()
  {
    $this->resolved = array();
    $this->unresolved = array();
    $this->error = array();
    foreach($this->queue as $queue) {
      if(isset($this->scripts[$queue])) {
        $node = $this->scripts[$queue];
        if($node['dependencies']==null) {
          $this->resolved[$node['key']] = $node['key'];
        } else {
          $this->solveDeps($node);
        }
      } else {
        $this->error[$queue] = $queue;
      }
    }
    if(!empty($this->error)) {
      echo sprintf(__('ERROR: Some scripts could not be loaded (%s)'), implode( ', ' , $this->error));
    }
  }

  /**
   * Get the scripts urls
   * @deprecated deprecated since version 3.1
   */
  public function getScripts()
  {
    $scripts = array();
    $this->orderScripts();
    foreach($this->resolved as $id) {
      if( isset($this->scripts[$id]['url']) ) {
        $scripts[] = $this->scripts[$id]['url'];
      }
    }
    return $scripts;
  }

  /**
   * Print the HTML tags to load the scripts
   *
   * @deprecated deprecated since version 3.1
   */
  public function printScripts()
  {
    foreach($this->getScripts() as $script) {
      echo '<script="text/javascript" src="' . osc_apply_filter('theme_url', $script) . '"></script>' . PHP_EOL;
    }
  }

  /**
   * Algorithm to solve the dependencies of the scripts
   *
   * @param $node
   * @deprecated deprecated since version 3.1
   */
  private function solveDeps($node)
  {
    $error = false;
    if(!isset($this->resolved[$node['key']])) {
      $this->unresolved[$node['key']] = $node['key'];
      if($node['dependencies']!=null) {
        if(is_array($node['dependencies'])) {
          foreach($node['dependencies'] as $dep) {
            if(!in_array($dep, $this->resolved)) {
              if(in_array($dep, $this->unresolved)) {
                $this->error[$dep] = $dep;
                $error = true;
              } else {
                if(isset($this->scripts[$dep])) {
                  $this->solveDeps($this->scripts[$dep]);
                } else {
                  $this->error[$dep] = $dep;
                }
              }
            }
          }
        } else {
          if(!in_array($node['dependencies'], $this->resolved)) {
            if(in_array($node['dependencies'], $this->unresolved)) {
              $this->error[$node['dependencies']] = $node['dependencies'];
              $error = true;
            } else {
              if(isset($this->scripts[$node['dependencies']])) {
                $this->solveDeps($this->scripts[$node['dependencies']]);
              } else {
                $this->error[$node['dependencies']] = $node['dependencies'];
              }
            }
          }
        }
      }
      if(!$error) {
        $this->resolved[$node['key']] = $node['key'];
        unset($this->unresolved[$node['key']]);
      }
    }
  }
}

/* file end: ./oc-includes/osclass/Themes.php */
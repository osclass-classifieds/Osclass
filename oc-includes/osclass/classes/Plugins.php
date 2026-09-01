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
 * Class Plugins
 */
class Plugins {
  private static $hooks;

  public function __construct() {}

  /**
   * @param $hook
   */
  public static function runHook($hook) {
    $args = func_get_args();
    array_shift($args);
    
    if(isset(self::$hooks[$hook])) {
      for($priority = 0; $priority<=10; $priority++) {
        if(isset(self::$hooks[$hook][$priority]) && is_array(self::$hooks[$hook][$priority])) {
          foreach(self::$hooks[$hook][$priority] as $fxName) {
            if(is_callable($fxName)) {
              call_user_func_array($fxName, $args);
            }
          }
        }
      }
    }
  }

  /**
   * @param $hook
   *
   * @return mixed|string
   */
  public static function applyFilter($hook) {
    $args = func_get_args();
    $hook = array_shift($args);
    $content = '';
    if (isset($args[0])) {
      $content = $args[0];
    }

    if (isset(self::$hooks[$hook])) {
      for($priority = 0; $priority<=10; $priority++) {
        if(isset(self::$hooks[$hook][$priority]) && is_array(self::$hooks[$hook][$priority])) {
          foreach(self::$hooks[$hook][$priority] as $fxName) {
            if(is_callable($fxName)) {
              $content = call_user_func_array($fxName, $args);
              $args[0] = $content;
            }
          }
        }
      }
    }
    
    return $content;
  }

  /**
   * @param $plugin
   *
   * @return bool
   */
  public static function isInstalled($plugin) {
    if(in_array($plugin, self::listInstalled())) {
      return true;
    }

    return false;
  }

  /**
   * @param $plugin
   *
   * @return bool
   */
  public static function isEnabled($plugin) {
    if(in_array($plugin, self::listEnabled())) {
      return true;
    }

    return false;
  }

  /**
   * @param bool $sort
   *
   * @return array
   */
  public static function listAll($sort = true) {
    $plugins = array();
    $pluginsPath = osc_plugins_path();
    $dir = opendir($pluginsPath);
    
    while($file = readdir($dir)) {
      if(preg_match('/^[a-zA-Z0-9-_]+$/', $file, $matches)) {
        // This has to change in order to catch any .php file
        $pluginPath = $pluginsPath . "$file/index.php";
        if(file_exists($pluginPath)) {
          $plugins[] = $file . '/index.php';
          
        } else {
          trigger_error(sprintf(__('Plugin %s is missing the index.php file %s'), $file, $pluginPath));
        }
      }
    }
    
    closedir($dir);

    if($sort) {
      $enabled = self::listEnabled();
      $installed = self::listInstalled();
      $extended_list = array();
      
      foreach($plugins as $p) {
        $extended_list[$p] = self::getInfo($p);
      }
      
      //uasort($extended_list, array('self', 'strnatcmpCustom'));  // not supported from PHP 8.2
      uasort($extended_list, self::class . '::strnatcmpCustom');
      
      $plugins = array();
      // Enabled
      foreach($extended_list as $k => $v) {
        if(in_array($k, $enabled)) {
          $plugins[] = $k;
          unset($extended_list[$k]);
        }
      }
      
      // Installed but disabled
      foreach($extended_list as $k => $v) {
        if(in_array($k, $installed)) {
          $plugins[] = $k;
          unset($extended_list[$k]);
        }
      }
      
      // Not installed
      foreach($extended_list as $k => $v) {
        $plugins[] = $k;
      }
    }

    return $plugins;
  }

  /**
   * @param $a
   * @param $b
   *
   * @return int
   */
  public static function strnatcmpCustom($a, $b) {
    return strnatcasecmp($a['plugin_name'], $b['plugin_name']);
  }


  // Load active plugins by including their folder to script
  public static function loadActive() {
    $plugins_list = unserialize(osc_active_plugins());

    if(is_array($plugins_list)) {
      foreach($plugins_list as $plugin_name) {
        $pluginPath = osc_plugins_path() . $plugin_name;
        
        if(file_exists($pluginPath)) {
          // This should include the file and adds the hooks
          include_once $pluginPath;
        }
      }
    }
  }

  // List all plugins those are installed
  public static function listInstalled() {
    $p_array = array();
    $plugins_list = unserialize(osc_installed_plugins());

    if(is_array($plugins_list)) {
      foreach($plugins_list as $plugin_name) {
        $p_array[] = $plugin_name;
      }
    }

    return $p_array;
  }


  // List all plugins those are enabled - activated
  public static function listEnabled() {
    $p_array = array();
    $plugins_list = unserialize(osc_active_plugins());

    if(is_array($plugins_list)) {
      foreach($plugins_list as $plugin_name) {
        $p_array[] = $plugin_name;
      }
    }

    return $p_array;
  }


  // List all plugins from plugins folder, those has index.php file (valid plugins)
  public static function listExisting() {
    $arr = array();
    $plugins_path = osc_plugins_path();
    $dir = opendir($plugins_path);
    
    while($plugin_name = readdir($dir)) {
      if(preg_match('/^[a-zA-Z0-9-_]+$/', $plugin_name, $matches)) {
        $plugin_index_path = $plugins_path . $plugin_name . '/index.php';
        
        if(file_exists($plugin_index_path)) {
          $arr[] = $plugin_name . '/index.php';
        }
      }
    }
    
    closedir($dir);
    
    return $arr;
  }


  // INTEGRITY CHECK
  // Check #1: If plugin is enabled, but not installed and exists, add it to installed list. Otherwise drop it from enabled.
  // Check #2: If plugin is enabled or installed, but does not exists in plugins folder, drop it from both enabled and installed.
  public static function integrityCheck() {
    $enabled = self::listEnabled();
    $installed = self::listInstalled();
    $existing = self::listExisting();
    
    $enabled_change = false;
    $installed_change = false;
    
    $disabled_delta = array();
    $uninstalled_delta = array();
    $installed_delta = array();
    

    // Check #1
    if(is_array($enabled) && count($enabled) > 0) {
      foreach($enabled as $k => $plugin) {
        // Plugin enabled, but not installed
        if(!in_array($plugin, $installed)) {

          if(in_array($plugin, $existing)) {
            $installed_change = true;
            $installed_delta[] = $plugin;
            $installed[] = $plugin;
            
          } else {
            $enabled_change = true;
            $disabled_delta[] = $plugin;
            unset($enabled[$k]);
          }
        }
      }
    }
    
    // Check #2
    // Disabled - maybe we do not want to drop it, in case user just renamed plugin folder for quick debug.
    if(1==2) {
      if(is_array($enabled) && count($enabled) > 0) {
        foreach($enabled as $k => $plugin) {
          // Plugin enabled, but does not exists
          if(!in_array($plugin, $existing)) {
            $enabled_change = true;
            $disabled_delta[] = $plugin;
            unset($enabled[$k]);
          }
        }
      }
      
      if(is_array($installed) && count($installed) > 0) {
        foreach($installed as $k => $plugin) {
          // Plugin installed, but does not exists
          if(!in_array($plugin, $existing)) {
            $installed_change = true;
            $uninstalled_delta[] = $plugin;
            unset($installed[$k]);
          }
        }
      }
    }
    
    // Update database values
    if($enabled_change === true) {
      osc_set_preference('active_plugins', serialize($enabled));
    }
    
    if($installed_change === true) {
      osc_set_preference('installed_plugins', serialize($installed));
    }

    // Add flash message in case of change
    if(!empty($disabled_delta) || !empty($uninstalled_delta) || !empty($installed_delta)) {
      $msg_arr = array(__('Integrity check has fixed several issues.'));
      
      if(!empty($disabled_delta)) {
        $msg_arr[] = sprintf(__('Disabled plugins: %s.'), implode(', ', str_replace('/index.php', '', $disabled_delta)));
      }
      
      if(!empty($uninstalled_delta)) {
        $msg_arr[] = sprintf(__('Uninstalled plugins: %s.'), implode(', ', str_replace('/index.php', '', $uninstalled_delta)));
      }
      
      if(!empty($installed_delta)) {
        $msg_arr[] = sprintf(__('Installed plugins: %s.'), implode(', ', str_replace('/index.php', '', $installed_delta)));
      }

      $msg = implode(' ', $msg_arr);
      osc_add_flash_warning_message($msg, 'admin');
    }

    self::reload();
    return false;
  }
  

  /**
   * @param $uri
   *
   * @return bool|mixed
   */
  public static function findByUpdateURI($uri) {
    $plugins = self::listAll();
    
    foreach($plugins as $p) {
      $info = self::getInfo($p);
      if($info['plugin_update_uri'] == $uri) {
        return $p;
      }
    }
    
    return false;
  }

  /**
   * @param $path
   *
   * @return bool|string
   */
  public static function resource($path) {
    $full_path = osc_plugins_path() . $path;
    return file_exists($full_path) ? $full_path : false;
  }

  /**
   * @param $path
   * @param $function
   */
  public static function register($path, $function) {
    $path = str_replace(osc_plugins_path(), '', $path);
    $tmp = explode(OC_CONTENT_FOLDER . '/plugins/', $path);
    
    if(count($tmp)==2) {
      $path = $tmp[1];
    }
    self::addHook('install_' . $path, $function);
  }

  /**
   * @param $path
   *
   * @return array|bool
   */
  public static function install($path) {
    osc_run_hook('before_plugin_install');

    $plugins_list = unserialize(osc_installed_plugins());

    if(is_array($plugins_list) && in_array($path, $plugins_list)) {
      return array ('error_code' => 'error_installed');
    }

    if(!file_exists(osc_plugins_path() . $path)) {
      return array('error_code' => 'error_file');
    }

    try {
      include_once osc_plugins_path() . $path;
      
      self::runHook('install_' . $path);
    } catch(Exception $e) {
      return array('error_code' => 'custom_error','msg' => $e->getMessage());
    }

    if(!self::activate($path)) {
      return array('error_code' => '');
    }
    
    if(!is_array($plugins_list)) {
      $plugins_list = array();
    }

    $plugins_list[] = $path;
    osc_set_preference('installed_plugins', serialize($plugins_list));

    // Check if something failed
    if (ob_get_length() > 0) {
      return array('error_code' => 'error_output', 'output' => ob_get_clean());
    }

    osc_run_hook('after_plugin_install');

    return true;
  }

  /**
   * @param $path
   *
   * @return bool
   */
  public static function uninstall($path) {
    osc_run_hook('before_plugin_uninstall');

    $plugins_list = unserialize(osc_installed_plugins());

    $path = str_replace(osc_plugins_path(), '', $path);
    
    if(!is_array($plugins_list)) {
      return false;
    }

    include_once osc_plugins_path() . $path;

    self::deactivate($path);
    /*if(!self::deactivate($path)) {
      return false;
    }*/

    self::runHook($path . '_uninstall');

    foreach($plugins_list as $k => $v) {
      if($v == $path) {
        unset($plugins_list[$k]);
      }
    }

    osc_set_preference('installed_plugins', serialize($plugins_list));

    $plugin = self::getInfo($path);
    self::cleanCategoryFromPlugin($plugin['short_name']);

    osc_run_hook('after_plugin_uninstall');

    return true;
  }

  /**
   * @param $path
   *
   * @return bool
   */
  public static function activate($path) {
    osc_run_hook('before_plugin_activate');

    // get list of active plugins
    $plugins_list = unserialize(osc_active_plugins());

    if(is_array($plugins_list) && in_array($path, $plugins_list)) {
      return false;
      
    } else if(!is_array($plugins_list)) {
      $plugins_list = array();
    }

    $plugins_list[] = $path;
    osc_set_preference('active_plugins', serialize($plugins_list));

    self::reload();
    self::runHook($path . '_enable');

    osc_run_hook('after_plugin_activate');

    return true;
  }

  /**
   * @param $path
   *
   * @return bool
   */
  public static function deactivate($path) {
    osc_run_hook('before_plugin_deactivate');

    $plugins_list = unserialize(osc_active_plugins());

    // check if there is some plugin enabled
    if(!is_array($plugins_list)) {
      return false;
    }

    $path = str_replace(osc_plugins_path(), '', $path);

    // remove $path from the active plugins list
    foreach($plugins_list as $k => $v) {
      if($v == $path) {
        unset($plugins_list[$k]);
      }
    }

    self::runHook($path . '_disable');

    // update t_preference field for active plugins
    osc_set_preference('active_plugins', serialize($plugins_list));

    self::reload();

    osc_run_hook('after_plugin_deactivate');

    return true;
  }

  /**
   * @param $name
   * @param $id
   *
   * @return mixed
   */
  public static function isThisCategory($name, $id) {
    return PluginCategory::newInstance()->isThisCategory($name, $id);
  }

  /**
   * @param $plugin
   *
   * @return array
   */
  public static function getInfo($plugin) {

    if($plugin == '' || !file_exists(osc_plugins_path() . $plugin)) {
      return array(
        'plugin_name' => '',
        'plugin_uri' => '',
        'plugin_update_uri' => '',
        'support_uri' => '',
        'description' => '',
        'version' => '',
        'author' => '',
        'author_uri' => '',
        'product_key' => '',
        'short_name' => ''
      );
    }
    
    $s_info = file_get_contents(osc_plugins_path() . $plugin);
    $info = array();
    
    if(preg_match('|Plugin Name:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['plugin_name'] = trim($match[1]);
    } else {
      $info['plugin_name'] = $plugin;
    }

    if(preg_match('|Plugin URI:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['plugin_uri'] = trim($match[1]);
    } else {
      $info['plugin_uri'] = '';
    }

    if(preg_match('|Plugin update URI:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['plugin_update_uri'] = trim($match[1]);
    } else {
      $info['plugin_update_uri'] = '';
    }

    if(preg_match('|Support URI:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['support_uri'] = trim($match[1]);
    } else {
      $info['support_uri'] = '';
    }

    if(preg_match('|Description:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['description'] = trim($match[1]);
    } else {
      $info['description'] = '';
    }

    if(preg_match('|Version:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['version'] = trim($match[1]);
    } else {
      $info['version'] = '';
    }

    if(preg_match('|Author:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['author'] = trim($match[1]);
    } else {
      $info['author'] = '';
    }

    if(preg_match('|Author URI:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['author_uri'] = trim($match[1]);
    } else {
      $info['author_uri'] = '';
    }

    if(preg_match('|Short Name:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['short_name'] = trim($match[1]);
    } else {
      $info['short_name'] = $info['plugin_name'];
    }

    if(preg_match('|Product Key:([^\\r\\t\\n]*)|i', $s_info, $match)) {
      $info['product_key'] = trim($match[1]);
    } else {
      $info['product_key'] = '';
    }

    $info['filename'] = $plugin;

    return $info;
  }

  /**
   * @param $plugin
   *
   * @return bool
   */
  public static function checkUpdate($plugin) {
    $info = self::getInfo($plugin);
    return osc_check_plugin_update($info['plugin_update_uri'], $info['version']);
  }


  /**
   * @param $path
   */
  public static function configureView($path) {
    $plugin = str_replace(osc_plugins_path(), '', $path);
    
    if(stripos($plugin, '.php') === FALSE) {
      $plugins_list = unserialize(osc_active_plugins());
      
      if(is_array($plugins_list)) {
        foreach($plugins_list as $p){
          $data = self::getInfo($p);
          
          if($plugin == $data['plugin_name']) {
            $plugin = $p;
            break;
          }
        }
      }
    }
    
    osc_redirect_to(osc_plugin_configure_url($plugin));
  }

  /**
   * @param $plugin
   */
  public static function cleanCategoryFromPlugin($plugin) {
    $dao_pluginCategory = new PluginCategory();
    $dao_pluginCategory->delete(array('s_plugin_name' => $plugin));
    unset($dao_pluginCategory);
  }

  /**
   * @param $categories
   * @param $plugin
   */
  public static function addToCategoryPlugin($categories, $plugin) {
    $dao_pluginCategory = new PluginCategory();
    $dao_category = new Category();
    
    if(!empty($categories)) {
      foreach($categories as $catId) {
        $result = $dao_pluginCategory->isThisCategory($plugin, $catId);
        
        if($result==0) {
          $fields = array();
          $fields['s_plugin_name'] = $plugin;
          $fields['fk_i_category_id'] = $catId;
          $dao_pluginCategory->insert($fields);

          $subs = $dao_category->findSubcategories($catId);
          
          if(is_array($subs) && count($subs)>0) {
            $cats = array();
            foreach($subs as $sub) {
              $cats[] = $sub['pk_i_id'];
            }
            self::addToCategoryPlugin($cats, $plugin);
          }
        }
      }
    }
    
    unset($dao_pluginCategory, $dao_category);
  }


  // Add a hook
  /**
   * @param   $hook
   * @param   $function
   * @param int $priority
   */
  public static function addHook($hook, $function, $priority = 5) {
    $hook = preg_replace('|/+|', '/', str_replace('\\', '/', $hook));
    $plugin_path = str_replace('\\', '/', osc_plugins_path());
    $hook = str_replace($plugin_path, '', $hook);
    $found_plugin = false;
    
    if(isset(self::$hooks[$hook])) {
      for($_priority = 0;$_priority<=10;$_priority++) {
        if(isset(self::$hooks[$hook][$_priority])) {
          foreach(self::$hooks[$hook][$_priority] as $fxName) {
            if($fxName==$function) {
              $found_plugin = true;
              break;
            }
          }
        }
      }
    }
    
    if(!$found_plugin) { 
      self::$hooks[$hook][$priority][] = $function;
    }
  }

  /**
   * @param $hook
   * @param $function
   */
  public static function removeHook($hook, $function) {
    for($priority = 0;$priority<=10;$priority++) {
      if(isset(self::$hooks[$hook][$priority])) {
        foreach(self::$hooks[$hook][$priority] as $k => $v) {
          if($v==$function) {
            unset(self::$hooks[$hook][$priority][$k]);
          }
        }
      }
    }
  }

  public static function getActive() {
    return self::$hooks;
  }

  public static function reload() {
    osc_reset_preferences();
    self::init();
  }

  public static function init() {
    self::loadActive();
  }
}

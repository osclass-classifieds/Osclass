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
 * AdminMenu class
 *
 * @since 3.0
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class AdminMenu {
  private static $instance;
  private $aMenu;

  public function __construct() {
    $this->aMenu = array();
  }

  /**
   * @return \AdminMenu
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    
    return self::$instance;
  }

  /**
   *  Initialize menu representation.
   */
  public function init() {
    $this->add_menu(__('Dashboard'), osc_admin_base_url(), 'dash', 'moderator');

    $this->add_menu(__('Listings'), osc_admin_base_url(true).'?page=items', 'items', 'moderator');
    $this->add_submenu('items', __('Manage listings'), osc_admin_base_url(true).'?page=items', 'items_manage', 'moderator');
    $this->add_submenu('items', __('Manage comments'), osc_admin_base_url(true).'?page=comments', 'items_comments', 'moderator');
    $this->add_submenu('items', __('Manage media'), osc_admin_base_url(true).'?page=media', 'items_media', 'moderator');
    $this->add_submenu('items', __('Reported listings'), osc_admin_base_url(true).'?page=items&action=items_reported', 'items_reported', 'moderator');
    $this->add_submenu('items', __('Custom fields'), osc_admin_base_url(true).'?page=cfields', 'items_cfields', 'administrator');
    $this->add_submenu('items', __('Settings'), osc_admin_base_url(true).'?page=items&action=settings', 'items_settings', 'administrator');

    $is_moderator = false;
    if(osc_logged_admin_id() > 0) {
      $admin = Admin::newInstance()->findByPrimaryKey(osc_logged_admin_id());
      
      if(isset($admin['pk_i_id']) && $admin['pk_i_id'] > 0 && isset($admin['b_moderator']) && $admin['b_moderator'] == 1) {
        $is_moderator = true;
      }
    }

    if(!$is_moderator) {
      $this->add_menu(__('Users'), osc_admin_base_url(true) .'?page=users', 'users', 'administrator');
    } else {
      $this->add_menu(__('Users'), osc_admin_base_url(true) .'?page=admins&action=edit', 'users', 'moderator');
    }
    
    $this->add_submenu('users', __('Users'), osc_admin_base_url(true) .'?page=users', 'users_manage', 'administrator');
    $this->add_submenu('users', __('Ban rules'), osc_admin_base_url(true) .'?page=users&action=ban', 'users_ban', 'administrator');
    $this->add_submenu('users', __('Alerts'), osc_admin_base_url(true) .'?page=users&action=alerts', 'users_alerts', 'administrator');
    $this->add_submenu('users', __('Settings'), osc_admin_base_url(true) .'?page=users&action=settings', 'users_settings', 'administrator');

    $this->add_menu(__('Statistics'), osc_admin_base_url(true) .'?page=stats&action=items', 'stats', 'moderator');
    $this->add_submenu('stats', __('Listings'), osc_admin_base_url(true) .'?page=stats&action=items', 'stats_items', 'moderator');
    $this->add_submenu('stats', __('Reports'), osc_admin_base_url(true) .'?page=stats&action=reports', 'stats_reports', 'moderator');
    $this->add_submenu('stats', __('Users'), osc_admin_base_url(true) .'?page=stats&action=users', 'stats_users', 'moderator');
    $this->add_submenu('stats', __('Comments'), osc_admin_base_url(true) .'?page=stats&action=comments', 'stats_comments', 'moderator');

    $this->add_menu(__('Appearance'), osc_admin_base_url(true) .'?page=appearance', 'appearance', 'administrator');
    $this->add_submenu('appearance', __('Manage themes'), osc_admin_base_url(true) .'?page=appearance', 'appearance_manage', 'administrator');
    $this->add_submenu('appearance', __('Manage widgets'), osc_admin_base_url(true) .'?page=appearance&action=widgets', 'appearance_widgets', 'administrator');
    $this->add_submenu('appearance', __('Customization'), osc_admin_base_url(true) .'?page=appearance&action=customization', 'appearance_customization', 'administrator');

    $this->add_menu(__('Plugins'), osc_admin_base_url(true) .'?page=plugins', 'plugins', 'administrator');
    $this->add_submenu('plugins', __('Manage plugins'), osc_admin_base_url(true) .'?page=plugins', 'plugins_manage', 'administrator');

    $this->add_menu(__('Market'), osc_admin_base_url(true) .'?page=market&action=themes', 'market', 'administrator');
    $this->add_submenu('market', __('Themes'), osc_admin_base_url(true) .'?page=market&action=themes', 'market_themes', 'administrator');
    $this->add_submenu('market', __('Plugins'), osc_admin_base_url(true) .'?page=market&action=plugins', 'market_plugins', 'administrator');
    $this->add_submenu('market', __('Languages'), osc_admin_base_url(true) .'?page=market&action=languages', 'market_languages', 'administrator');
    $this->add_submenu('market', __('Locations'), osc_admin_base_url(true) .'?page=market&action=locations', 'market_locations', 'administrator');

    $this->add_menu(__('International'), osc_admin_base_url(true) .'?page=translations', 'international', 'administrator');
    $this->add_submenu('international', __('Translations'), osc_admin_base_url(true) .'?page=translations', 'international_translations', 'administrator');
    $this->add_submenu('international', __('Languages'), osc_admin_base_url(true) .'?page=languages', 'international_languages', 'administrator');
    $this->add_submenu('international', __('Locations'), osc_admin_base_url(true) .'?page=locations', 'international_locations', 'administrator');
    $this->add_submenu('international', __('Currencies'), osc_admin_base_url(true) .'?page=currencies', 'international_currencies', 'administrator');

    $this->add_menu(__('Pages'), osc_admin_base_url(true) .'?page=pages', 'pages', 'administrator');

    $this->add_menu(__('Settings'), osc_admin_base_url(true) .'?page=settings', 'settings', 'administrator');
    $this->add_submenu('settings', __('General'), osc_admin_base_url(true) .'?page=settings', 'settings_general', 'administrator');
    $this->add_submenu('settings', __('Categories'), osc_admin_base_url(true) .'?page=categories', 'settings_categories', 'administrator');
    // $this->add_submenu('settings', __('Item'), osc_admin_base_url(true) .'?page=items&action=settings', 'settings_items', 'administrator');
    // $this->add_submenu('settings', __('Comment'), osc_admin_base_url(true) .'?page=settings&action=comments', 'settings_comments', 'administrator');
    // $this->add_submenu('settings', __('User'), osc_admin_base_url(true) .'?page=users&action=settings', 'settings_users', 'administrator');
    $this->add_submenu('settings', __('Email templates'), osc_admin_base_url(true) .'?page=emails', 'settings_emails_manage', 'administrator');
    $this->add_submenu('settings', __('Mail server'), osc_admin_base_url(true) .'?page=settings&action=mailserver', 'settings_mailserver', 'administrator');
    $this->add_submenu('settings', __('Comments'), osc_admin_base_url(true) .'?page=settings&action=comments', 'settings_comments', 'administrator');
    $this->add_submenu('settings', __('Media'), osc_admin_base_url(true) .'?page=settings&action=media', 'settings_media', 'administrator');
    $this->add_submenu('settings', __('Spam and bots'), osc_admin_base_url(true) .'?page=settings&action=spamNbots', 'settings_spambots', 'administrator');

    //$this->add_submenu('settings', __('Locations'), osc_admin_base_url(true) .'?page=settings&action=locations', 'settings_locations', 'administrator');
    //$this->add_submenu('settings', __('Languages'), osc_admin_base_url(true) .'?page=languages', 'settings_language', 'administrator');
    //$this->add_submenu('settings', __('Currencies'), osc_admin_base_url(true) .'?page=settings&action=currencies', 'settings_currencies', 'administrator');

    $this->add_submenu('settings', __('Breadcrumbs'), osc_admin_base_url(true) .'?page=settings&action=breadcrumbs', 'settings_breadcrumbs', 'administrator');
    $this->add_submenu('settings', __('Permalinks'), osc_admin_base_url(true) .'?page=settings&action=permalinks', 'settings_permalinks', 'administrator');
    $this->add_submenu('settings', __('Latest searches'), osc_admin_base_url(true) .'?page=settings&action=latestsearches', 'settings_searches', 'administrator');
    $this->add_submenu('settings', __('Optimization'), osc_admin_base_url(true) .'?page=settings&action=optimization', 'settings_optimization', 'administrator');
    $this->add_submenu('settings', __('Subdomains & Advanced'), osc_admin_base_url(true) .'?page=settings&action=advanced', 'settings_advanced', 'administrator');

    $this->add_menu(__('Tools'), osc_admin_base_url(true) .'?page=tools', 'tools', 'administrator');
    $this->add_submenu('tools', __('Configuration info'), osc_admin_base_url(true) .'?page=tools&action=info', 'tools_info', 'administrator');
    $this->add_submenu('tools', __('Data clean up'), osc_admin_base_url(true) .'?page=tools&action=cleanup', 'tools_cleanup', 'administrator');
    $this->add_submenu('tools', __('Action logs'), osc_admin_base_url(true) .'?page=tools&action=logs', 'tools_logs', 'administrator');
    $this->add_submenu('tools', __('Debug/Error log'), osc_admin_base_url(true) .'?page=tools&action=debug', 'tools_debug', 'administrator');
    $this->add_submenu('tools', __('Import SQL data'), osc_admin_base_url(true) .'?page=tools&action=import', 'tools_import', 'administrator');
    $this->add_submenu('tools', __('Backup data'), osc_admin_base_url(true) .'?page=tools&action=backup', 'tools_backup', 'administrator');
    $this->add_submenu('tools', __('Upgrade Osclass'), osc_admin_base_url(true) .'?page=tools&action=upgrade', 'tools_upgrade', 'administrator');
    $this->add_submenu('tools', __('Location stats'), osc_admin_base_url(true) .'?page=tools&action=locations', 'tools_location', 'administrator');
    $this->add_submenu('tools', __('Category stats'), osc_admin_base_url(true) .'?page=tools&action=category', 'tools_category', 'administrator');
    $this->add_submenu('tools', __('Maintenance mode'), osc_admin_base_url(true) .'?page=tools&action=maintenance', 'tools_maintenance', 'administrator');
    $this->add_submenu('tools', __('Changelog'), osc_admin_base_url(true) .'?page=tools&action=version', 'tools_version', 'administrator');
    
    $this->add_menu(__('Admins'), osc_admin_base_url(true) .'?page=admins', 'admins', 'moderator');
    $this->add_submenu('admins', __('Admins'), osc_admin_base_url(true) .'?page=admins', 'admins_manage', 'admins');
    $this->add_submenu('admins', __('Your Profile'), osc_admin_base_url(true) .'?page=admins&action=edit', 'admins_profile', 'moderator');


    if($is_moderator) {
      $moderator_access = array();
      if(isset($admin['s_moderator_access']) && trim($admin['s_moderator_access']) <> '') {
        $moderator_access = array_filter(explode(',', $admin['s_moderator_access']));
      }
      
      if(is_array($moderator_access) && !empty($moderator_access) && count($moderator_access) > 0) {
        $def_file_url = osc_admin_base_url() . '?page=plugins&action=renderplugin&file=';
        $def_route_url = osc_admin_base_url() . '?page=plugins&action=renderplugin&route=';

        $routes = Rewrite::newInstance()->getRoutes();
      
        $c = 0;
        foreach($moderator_access as $m) {
          // is file
          $is_ok = true;
          if(isset(pathinfo($m)['extension']) && pathinfo($m)['extension'] == 'php') {
            $menu_url = $def_file_url . $m;
            $part = str_replace('/admin', '', $m);
            $part = array_filter(explode('/', str_replace('.php', '', $part)));
            
            $name = ucwords(str_replace('_', ' ', implode(' > ', $part)));
            
          // is route
          } else {
            if(isset($routes[$m]) && isset($routes[$m]['title']) && $routes[$m]['title'] <> '') {
              $plg = @explode('/', @$routes[$m]['file'])[0];
              $plg = ucwords(str_replace('_', ' ', str_replace('-', ' ', $plg)));
              
              $name = $routes[$m]['title'];
              
              if($plg <> '') {
                $name = $plg . ' > ' . $name;
              }
            } else {
              $is_ok = false;   // it's not good, we've not found route
              $part = str_replace('_', ' ', $m);
              $part = str_replace('-', ' ', $part);

              $name = ucwords($part);
            }
            $menu_url = $def_route_url . $m;
          }
          
          if($c == 0) {
            $this->add_menu(__('Plugins'), '#', 'modplugins', 'moderator');
          }
          
          if($is_ok) {   // remove unfound routes
            $this->add_submenu('modplugins', $name, $menu_url, 'modplugins_c' . $c, 'moderator');
          }
          
          $c++;
        }
      }
    }

    osc_run_hook('admin_menu_init');
  }

  /**
   * Add menu entry
   *
   * @param $menu_title
   * @param $url
   * @param $menu_id
   * @param $icon_url   (unused)
   * @param $capability (unused)
   * @param $position   (unused)
   */
  public function add_menu($menu_title, $url, $menu_id, $capability = null ,$icon_url = null, $position = null) {
    $array = array(
      $menu_title,
      $url,
      $menu_id,
      $capability,
      $icon_url,
      $position
   );
    
    $this->aMenu[$menu_id] = $array;
  }

  /**
   * Remove menu and submenus under menu with id $id_menu
   *
   * @param $menu_id
   */
  public function remove_menu($menu_id) {
    unset($this->aMenu[$menu_id]);
  }

  /**
   * Add submenu under menu id $menu_id
   *
   * @param    $menu_id
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param    $capability
   * @param null $icon_url
   */
  public function add_submenu($menu_id , $submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $array = array(
      $submenu_title,
      $url,
      $submenu_id,
      $menu_id,
      $capability ,
      $icon_url
   );
    
    $this->aMenu[$menu_id]['sub'][$submenu_id] = $array;
  }

  /**
   * Remove submenu with id $id_submenu under menu id $id_menu
   *
   * @param $menu_id
   * @param $submenu_id
   */
  public function remove_submenu($menu_id, $submenu_id) {
    unset($this->aMenu[$menu_id]['sub'][$submenu_id]);
  }

  /**
   * Add submenu under menu id $menu_id
   *
   * @param $menu_id
   * @param $submenu_title
   * @param    $submenu_id
   * @param $capability
   *
   * @since 3.1
   */
  public function add_submenu_divider($menu_id, $submenu_title, $submenu_id, $capability = null) {
    $array                           = array(
      $submenu_title,
      'divider_' . $submenu_id,
      $menu_id,
      $capability
   );
    $this->aMenu[$menu_id]['sub']['divider_' . $submenu_id] = $array;
  }

  /**
   * Remove submenu with id $id_submenu under menu id $id_menu
   *
   * @param $menu_id
   * @param $submenu_id
   *
   * @since 3.1
   */
  public function remove_submenu_divider($menu_id, $submenu_id) {
    unset($this->aMenu[$menu_id]['sub']['divider_' . $submenu_id]);
  }

  /**
   * Return menu as array
   *
   * @return array
   */
  public function get_array_menu() {
    return $this->aMenu;
  }

  // common functions

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_items($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('items', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_categories($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('categories', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_pages($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('pages', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_appearance($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('appearance', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_plugins($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('plugins', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_settings($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('settings', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_tools($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('tools', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_users($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('users', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /**
   * @param    $submenu_title
   * @param    $url
   * @param    $submenu_id
   * @param null $capability
   * @param null $icon_url
   */
  public function add_menu_stats($submenu_title , $url , $submenu_id , $capability = null , $icon_url = null) {
    $this->add_submenu('stats', $submenu_title, $url, $submenu_id, $capability, $icon_url);
  }

  /*
   * Empty the menu
   */
  public function clear_menu() {
    $this->aMenu = array();
  }
}
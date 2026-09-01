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
 * AdminToolbar class
 *
 * @since 3.0
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class AdminToolbar {
  private static $instance;
  private $nodes = array();

  public function __construct() {}

  /**
   * @return \AdminToolbar
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function init(){}

  /**
   * Add toolbar menus and add menus running hook add_admin_toolbar_menus
   */
  public function add_menus($is_front = false) {
    // User related, aligned right.
    if(!$is_front) {
      osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_menu', 0);
    }
    
    osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_demo', 0);
    osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_comments', 0);
    osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_spam', 0);

    osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_update_core', 0);

    osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_update_themes', 0);
    osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_update_plugins', 0);
    osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_update_languages', 0);


    if(!$is_front) {
      osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_logout', 9);
    } else {
      osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_logout2', 9);
    }
    
    osc_run_hook('add_admin_toolbar_menus');
  }

  /**
   * Add a node to the menu.
   *
   * @todo implement parent nodes
   *
   * @param $array
   */
  public function add_menu($array) {
    if(isset($array['id'])) {
      $this->nodes[$array['id']] = (object) $array;
    }
  }

  /**
   * Add a submenu to the menu.
   *
   * @param array $args - The arguments for each subitem.
   * - id     - string  - The ID of the mainitem.
   * - parentid   - string  - The ID of the parent item.
   * - title    - string  - The title of the node.
   * - href     - string  - The link for the item. Optional.
   * - meta     - array   - Meta data including the following keys: html, class, onclick, target, title, tabindex.
   * - target   - string  - _blank
   */
  function add_submenu($array) {
    if(isset($array['parentid']) && isset($array['id'])) {
      $this->nodes[$array['parentid']]->submenu[$array['id']] = (object)$array;
    }
  }

  /**
   * Remove entry with id $id
   *
   * @param string $id
   */
  public function remove_menu($id) {
    unset($this->nodes[$id]);
  }

  /**
   * Remove entry with id $id
   *
   * @param string $parentid
   * @param string $id
   */
  function remove_submenu($parentid, $id) {
    if(isset($this->nodes[$parentid]) && isset($this->nodes[$parentid]->submenu[$id])) {
      unset($this->nodes[$parentid]->submenu[$id]);
    }
  }

  /**
   * Render admin toolbar
   *
   * <div>
   *   <a></a>
   * </div>
   */
  public function render($is_front = false) {
    if (count($this->nodes) > 0) {
      
      $scheme_class = '';
      if($is_front && osc_get_preference('admin_color_scheme') <> '') {
        $scheme_class = ' scheme-' . osc_get_preference('admin_color_scheme');
      }
      
      echo '<div id="header' . ($is_front ? '-admin' : '') . '" class="navbar' . $scheme_class . '"><div class="header-wrapper">';
      osc_run_hook('render_admintoolbar_pre');

      foreach($this->nodes as $value) {
        $meta = "";
        if (isset($value->meta)) {
          foreach($value->meta as $k => $v)
            $meta .= $k.'="'.$v.'" ';
        }
        echo '<div id="osc_toolbar_'.$value->id.'" ><a '.$meta.' href="'.$value->href.'" ' . ((isset($value->target)) ? 'target="' . $value->target . '"' : '') . '><span>'.$value->title.'</span></a>';

        if (isset($value->submenu) && is_array($value->submenu)) {
          echo '<nav class="osc_admin_submenu" id="osc_toolbar_sub_'.$value->id.'"><ul>';
          foreach($value->submenu as $subvalue) {
            if (isset($subvalue->subid)) {
              $submeta = "";
              if (isset($subvalue->meta)) {
                foreach($subvalue->meta as $sk => $sv)
                  $submeta .= $sk.'="'.$sv.'" ';
              }
              echo '<li><a '.$submeta.' href="'.$subvalue->href.'" ' . ((isset($subvalue->target)) ? 'target="' . $subvalue->target . '"' : '') . '>'.$subvalue->title.'</a><li>';
            }
          }
          echo '</ul></nav>';
        }
        echo '</div>';
      }

      $notif_count = 0;
      echo '<ul class="osc_mobile_list" style="display:none;">';
      foreach($this->nodes as $value) {
        if(!in_array($value->id, array('home','comments','spam','logout'))) {
          echo '<li id="osc_mt_'.$value->id.'" ><a href="'.$value->href.'" ' . ((isset($value->target)) ? 'target="' . $value->target . '"' : '') . '><span>'.$value->title.'</span></a></li>';
          $notif_count++;
        }
      }
      echo '</ul>';
      
      echo '<div id="osc_toolbar_mobilemenu" ' . ($notif_count > 0 ? '' : 'is-empty') . ' style="display:none;"><a class="" href="#"><i class="fa fa-exclamation-circle"></i></a></div>';

      
      osc_run_hook('render_admintoolbar');
      echo '<div style="clear: both;"></div></div></div>';  // end of header-wrapper
      
      osc_run_hook('render_admintoolbar_after');
    }
  }
}
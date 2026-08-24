<?php
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


define('ABS_PATH', str_replace('//', '/', str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . '/'));
define('OC_ADMIN', true);

require_once ABS_PATH . 'oc-load.php';

if(file_exists(ABS_PATH . '.maintenance')) {
  if(!defined('__OSC_MAINTENANCE__')) {
    define('__OSC_MAINTENANCE__', true);
  }
}

// Identify jquery version
$jquery_version = (trim(osc_get_preference('jquery_version')) == '' ? '1' : trim(osc_get_preference('jquery_version'))); 

if(!defined('JQUERY_VERSION')) {
  define('JQUERY_VERSION', $jquery_version);   // can be '1' or '3'
}

// check if script use proper theme
if(!in_array(osc_get_preference('admin_theme'), array('modern', 'omega'))) {
  // upgrade from Evolution branch
  if(osc_get_preference('osclass_evo_installed') != '') {
    osc_changeVersionTo('400');
    osc_delete_preference('osclass_evo_installed');
  }
  
  osc_set_preference('admin_theme', 'omega');
  osc_redirect_to(osc_admin_base_url());
}


// register admin scripts
osc_register_script('admin-osc', osc_current_admin_theme_js_url('osc.js?v=' . date('Ymdhis')), 'jquery');
osc_register_script('admin-ui-osc', osc_current_admin_theme_js_url('ui-osc.js?v=' . date('Ymdhis')), 'jquery');
osc_register_script('admin-location', osc_current_admin_theme_js_url('location.js?v=' . date('Ymdhis')), 'jquery');


// enqueue scripts
osc_enqueue_script('jquery');
//osc_enqueue_script('jquery-ui');
osc_enqueue_script('jquery-ui-backoffice');
osc_enqueue_script('admin-osc');
osc_enqueue_script('admin-ui-osc');
osc_enqueue_script('tabber');

osc_add_hook('admin_footer', array('FieldForm', 'i18n_datePicker') );

// enqueue css styles
if(JQUERY_VERSION == '3') {
  osc_enqueue_style('jquery-ui', osc_assets_url('js/jquery3/jquery-ui-backoffice/jquery-ui.min.css'));
} else {
  osc_enqueue_style('jquery-ui', osc_assets_url('css/jquery-ui/jquery-ui.css'));
}

osc_enqueue_style('admin-css', osc_current_admin_theme_styles_url('main.css'));


// PLUGINS DEMO SETUP
if(defined('DEMO_PLUGINS') && DEMO_PLUGINS === true) {
  if(strpos(osc_logged_admin_username(), 'demo') !== false) {   // admin user name is demo, demo1, mydemo etc.
    if(!(
      (Params::getParam('page') == 'ajax' && in_array(Params::getParam('action'), array('runhook','userajax','location','cities','regions')))
      || (Params::getParam('page') == 'plugins' && Params::getParam('action') != 'add')
      || Params::getParam('action') == 'logout'
    )) {
      osc_add_flash_warning_message( __('Plugins demo site mode enabled, you cannot access this section. You can access only oc-admin > Plugins section. Action buttons may be restricted.'), 'admin');
      header('Location: '.osc_admin_base_url(true) . '?page=plugins');
      exit;
    }
  }
}

// THEMES DEMO SETUP
if(defined('DEMO_THEMES') && DEMO_THEMES === true) {
  if(strpos(osc_logged_admin_username(), 'demo') !== false) {   // admin user name is demo, demo1, mydemo etc.
    if(!(
      Params::getParam('page') == 'appearance' && trim(Params::getParam('theme_action')) == '' && trim(Params::getParam('action_specific')) == '' && !in_array(Params::getParam('action'), array('add','activate','delete','add_widget','edit_widget','delete_widget','customization'))
      || Params::getParam('action') == 'logout'
    )) {
      osc_add_flash_warning_message( __('Themes demo site mode enabled, you cannot access this section. You can access only oc-admin > Appearance section. Action buttons may be restricted.'), 'admin');
      header('Location: '.osc_admin_base_url(true) . '?page=appearance');
      exit;
    }
  }
}


// Drop search term on plugin page when leaving plugins section
if(Params::getParam('page') != 'plugins') {
  osc_drop_session('pluginSearchTerm');
}


switch(Params::getParam('page')) {
  case('items'):
    require_once(osc_admin_base_path() . 'items.php');
    $do = new CAdminItems();
    $do->doModel();
    break;
    
  case('comments'):
    require_once(osc_admin_base_path() . 'comments.php');
    $do = new CAdminItemComments();
    $do->doModel();
    break;
    
  case('media'):
    require_once(osc_admin_base_path() . 'media.php');
    $do = new CAdminMedia();
    $do->doModel();
    break;
    
  case ('login'):
    require_once(osc_admin_base_path() . 'login.php');
    $do = new CAdminLogin();
    $do->doModel();
    break;
    
  case('categories'):
    require_once(osc_admin_base_path() . 'categories.php');
    $do = new CAdminCategories();
    $do->doModel();
    break;
    
  case('emails'):
    require_once(osc_admin_base_path() . 'emails.php');
    $do = new CAdminEmails();
    $do->doModel();
    break;
    
  case('pages'):
    require_once(osc_admin_base_path() . 'pages.php');
    $do = new CAdminPages();
    $do->doModel();
    break;
    
  case('settings'):
    require_once(osc_admin_base_path() . 'settings.php');
    $do = new CAdminSettings();
    $do->doModel();
    break;
    
  case('plugins'):
    require_once(osc_admin_base_path() . 'plugins.php');
    $do = new CAdminPlugins();
    $do->doModel();
    break;
    
  case('languages'):
    require_once(osc_admin_base_path() . 'languages.php');
    $do = new CAdminLanguages();
    $do->doModel();
    break;

  case('locations'):
    require_once(osc_admin_base_path() . 'locations.php');
    $do = new CAdminLocations();
    $do->doModel();
    break;
    
  case('translations'):
    require_once(osc_admin_base_path() . 'translations.php');
    $do = new CAdminTranslations();
    $do->doModel();
    break;
    
  case('currencies'):
    require_once(osc_admin_base_path() . 'currencies.php');
    $do = new CAdminCurrencies();
    $do->doModel();
    break;
    
  case('admins'):
    require_once(osc_admin_base_path() . 'admins.php');
    $do = new CAdminAdmins();
    $do->doModel();
    break;
    
  case('users'):
    require_once(osc_admin_base_path() . 'users.php');
    $do = new CAdminUsers();
    $do->doModel();
    break;
    
  case('ajax'):
    header('Access-Control-Allow-Origin: *');
    require_once(osc_admin_base_path() . 'ajax/ajax.php');
    $do = new CAdminAjax();
    $do->doModel();
    break;
    
  case('appearance'):
    require_once(osc_admin_base_path() . 'appearance.php');
    $do = new CAdminAppearance();
    $do->doModel();
    break;
    
  case('tools'):
    require_once(osc_admin_base_path() . 'tools.php');
    $do = new CAdminTools();
    $do->doModel();
    break;
    
  case('stats'):
    require_once(osc_admin_base_path() . 'stats.php');
    $do = new CAdminStats();
    $do->doModel();
    break;
    
  case('cfields'):
    require_once(osc_admin_base_path() . 'custom_fields.php');
    $do = new CAdminCFields();
    $do->doModel();
    break;
    
  case('upgrade'):
    require_once(osc_admin_base_path() . 'upgrade.php');
    $do = new CAdminUpgrade();
    $do->doModel();
    break;
    
  case('market'):
    require_once(osc_admin_base_path() . 'market.php');
    $do = new CAdminMarket();
    $do->doModel();
    break;
    
  default:  //login of oc-admin
    header('Access-Control-Allow-Origin: *');
    require_once(osc_admin_base_path() . 'main.php');
    $do = new CAdminMain();
    $do->doModel();
}

/* file end:./oc-admin/index.php */
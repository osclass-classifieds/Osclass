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


// Define absolute path
if(!defined('ABS_PATH')) {
  define('ABS_PATH', str_replace('\\', '/', dirname(__FILE__) . '/'));
}


// Initialize installation window if configuration file is not found
if(!file_exists(ABS_PATH . 'config.php')) {
  define('OC_INCLUDES_FOLDER', 'oc-includes');
  define('OC_CONTENT_FOLDER', 'oc-content');
  define('OC_ADMIN_FOLDER', 'oc-admin');
  define('LIB_PATH', ABS_PATH . OC_INCLUDES_FOLDER . '/');
  define('CONTENT_PATH', ABS_PATH . OC_CONTENT_FOLDER . '/');
  define('THEMES_PATH', CONTENT_PATH . 'themes/');
  define('PLUGINS_PATH', CONTENT_PATH . 'plugins/');
  define('TRANSLATIONS_PATH', CONTENT_PATH . 'languages/');

  require_once LIB_PATH . 'osclass/helpers/hErrors.php';
  
  $title = 'Welcome to Osclass Installation';
  $message  = 'Thanks for downloading Osclass, the best classifieds script. Before getting started, we need some information on the database. You will need to know the following items before proceeding.</p>';
  $message .= '<ul class="install-req">';
  $message .= '<li>Database name</li>';
  $message .= '<li>Database username</li>';
  $message .= '<li>Database password</li>';
  $message .= '<li>Database host (port)</li>';
  $message .= '<li>Table prefix</li>';
  $message .= '</ul>';
  $message .= '<p>If you\'ve already installed Osclass, your <b>config.php</b> file is missing and needs to be created. You may create config file also from <b>config-sample.php</b>. <a target="_blank" href="https://forums.osclasspoint.com/">Need more help?</a></p>';
  $message .= '<p class="margin25t"><a class="btn btn-primary" href="' . osc_get_absolute_url() . 'oc-includes/osclass/install.php">Install</a>';

  osc_die($title, $message);
}


// Load configuration file
require_once ABS_PATH . 'config.php';

if(!defined('OC_INCLUDES_FOLDER') || OC_INCLUDES_FOLDER == '') {
  require_once ABS_PATH . 'oc-includes/osclass/default-constants.php';
} else {
  require_once ABS_PATH . OC_INCLUDES_FOLDER . '/osclass/default-constants.php';
}

// Sets PHP error handling
if(OSC_DEBUG) {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);                  // update 420, replaced error_reporting(E_ALL | E_STRICT);

  if (OSC_DEBUG_LOG) {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', CONTENT_PATH . 'debug.log');
  }
} else {
  error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING);
}


// Include Composer's autoloader
require_once LIB_PATH . 'vendor/autoload.php';


// Core libraries
require_once LIB_PATH . 'osclass/db.php';
require_once LIB_PATH . 'osclass/Logger/LogDatabase.php';
require_once LIB_PATH . 'osclass/classes/database/DBConnectionClass.php';
require_once LIB_PATH . 'osclass/classes/database/DBCommandClass.php';
require_once LIB_PATH . 'osclass/classes/database/DBRecordsetClass.php';
require_once LIB_PATH . 'osclass/classes/database/DAO.php';
require_once LIB_PATH . 'osclass/helpers/hDatabaseInfo.php';
require_once LIB_PATH . 'osclass/model/Preference.php';
require_once LIB_PATH . 'osclass/helpers/hPreference.php';

// check if Osclass is installed
if(!getBoolPreference('osclass_installed')) {
  require_once LIB_PATH . 'osclass/helpers/hErrors.php';

  $title = 'Osclass Error';
  $message = 'Osclass isn\'t installed. <a href="https://forums.osclasspoint.com/">Need more help?</a></p>';
  $message .= '<p><a class="btn btn-primary" href="' . osc_get_absolute_url() . 'oc-includes/osclass/install.php">Install</a>';

  osc_die($title, $message);
}

// Load all classes
require_once LIB_PATH . 'osclass/helpers/hDefines.php';
require_once LIB_PATH . 'osclass/helpers/hLocale.php';
require_once LIB_PATH . 'osclass/helpers/hMessages.php';
require_once LIB_PATH . 'osclass/helpers/hUsers.php';
require_once LIB_PATH . 'osclass/helpers/hItems.php';
require_once LIB_PATH . 'osclass/helpers/hSearch.php';
require_once LIB_PATH . 'osclass/helpers/hUtils.php';
require_once LIB_PATH . 'osclass/helpers/hCurrency.php';
require_once LIB_PATH . 'osclass/helpers/hCategories.php';
require_once LIB_PATH . 'osclass/helpers/hTranslations.php';
require_once LIB_PATH . 'osclass/helpers/hSecurity.php';
require_once LIB_PATH . 'osclass/helpers/hSanitize.php';
require_once LIB_PATH . 'osclass/helpers/hValidate.php';
require_once LIB_PATH . 'osclass/helpers/hPage.php';
require_once LIB_PATH . 'osclass/helpers/hPagination.php';
require_once LIB_PATH . 'osclass/helpers/hPremium.php';
require_once LIB_PATH . 'osclass/helpers/hTheme.php';
require_once LIB_PATH . 'osclass/helpers/hLocation.php';
require_once LIB_PATH . 'osclass/core/Params.php';
require_once LIB_PATH . 'osclass/core/Cookie.php';
require_once LIB_PATH . 'osclass/core/Session.php';
require_once LIB_PATH . 'osclass/core/View.php';
require_once LIB_PATH . 'osclass/core/BaseModel.php';
require_once LIB_PATH . 'osclass/core/AdminBaseModel.php';
require_once LIB_PATH . 'osclass/core/SecBaseModel.php';
require_once LIB_PATH . 'osclass/core/WebSecBaseModel.php';
require_once LIB_PATH . 'osclass/core/AdminSecBaseModel.php';
require_once LIB_PATH . 'osclass/core/Translation.php';
require_once LIB_PATH . 'osclass/Themes.php';
require_once LIB_PATH . 'osclass/AdminThemes.php';
require_once LIB_PATH . 'osclass/WebThemes.php';
require_once LIB_PATH . 'osclass/utils.php';
require_once LIB_PATH . 'osclass/formatting.php';
require_once LIB_PATH . 'osclass/locales.php';
require_once LIB_PATH . 'osclass/classes/Plugins.php';
require_once LIB_PATH . 'osclass/helpers/hPlugins.php';
require_once LIB_PATH . 'osclass/ItemActions.php';
require_once LIB_PATH . 'osclass/emails.php';
require_once LIB_PATH . 'osclass/model/Admin.php';
require_once LIB_PATH . 'osclass/model/Alerts.php';
require_once LIB_PATH . 'osclass/model/AlertsStats.php';
require_once LIB_PATH . 'osclass/model/Cron.php';
require_once LIB_PATH . 'osclass/model/Category.php';
require_once LIB_PATH . 'osclass/model/CategoryStats.php';
require_once LIB_PATH . 'osclass/model/City.php';
require_once LIB_PATH . 'osclass/model/CityArea.php';
require_once LIB_PATH . 'osclass/model/Country.php';
require_once LIB_PATH . 'osclass/model/Currency.php';
require_once LIB_PATH . 'osclass/model/OSCLocale.php';
require_once LIB_PATH . 'osclass/model/Item.php';
require_once LIB_PATH . 'osclass/model/ItemComment.php';
require_once LIB_PATH . 'osclass/model/ItemResource.php';
require_once LIB_PATH . 'osclass/model/ItemStats.php';
require_once LIB_PATH . 'osclass/model/Page.php';
require_once LIB_PATH . 'osclass/model/PluginCategory.php';
require_once LIB_PATH . 'osclass/model/Region.php';
require_once LIB_PATH . 'osclass/model/User.php';
require_once LIB_PATH . 'osclass/model/UserEmailTmp.php';
require_once LIB_PATH . 'osclass/model/ItemLocation.php';
require_once LIB_PATH . 'osclass/model/Widget.php';
require_once LIB_PATH . 'osclass/model/Search.php';
require_once LIB_PATH . 'osclass/model/LatestSearches.php';
require_once LIB_PATH . 'osclass/model/Field.php';
require_once LIB_PATH . 'osclass/model/Log.php';
require_once LIB_PATH . 'osclass/model/CountryStats.php';
require_once LIB_PATH . 'osclass/model/RegionStats.php';
require_once LIB_PATH . 'osclass/model/CityStats.php';
require_once LIB_PATH . 'osclass/model/BanRule.php';
require_once LIB_PATH . 'osclass/model/LocationsTmp.php';
require_once LIB_PATH . 'osclass/classes/Cache.php';
require_once LIB_PATH . 'osclass/classes/ImageProcessing.php';
require_once LIB_PATH . 'osclass/classes/RSSFeed.php';
require_once LIB_PATH . 'osclass/classes/Sitemap.php';
require_once LIB_PATH . 'osclass/classes/Pagination.php';
require_once LIB_PATH . 'osclass/classes/Rewrite.php';
require_once LIB_PATH . 'osclass/classes/Stats.php';
require_once LIB_PATH . 'osclass/classes/AdminMenu.php';
require_once LIB_PATH . 'osclass/classes/datatables/DataTable.php';
require_once LIB_PATH . 'osclass/classes/AdminToolbar.php';
require_once LIB_PATH . 'osclass/classes/Breadcrumb.php';
require_once LIB_PATH . 'osclass/classes/EmailVariables.php';
require_once LIB_PATH . 'osclass/alerts.php';
require_once LIB_PATH . 'osclass/classes/Dependencies.php';
require_once LIB_PATH . 'osclass/classes/Scripts.php';
require_once LIB_PATH . 'osclass/classes/Styles.php';
require_once LIB_PATH . 'osclass/frm/Form.form.class.php';
require_once LIB_PATH . 'osclass/frm/Page.form.class.php';
require_once LIB_PATH . 'osclass/frm/Category.form.class.php';
require_once LIB_PATH . 'osclass/frm/Item.form.class.php';
require_once LIB_PATH . 'osclass/frm/Contact.form.class.php';
require_once LIB_PATH . 'osclass/frm/Comment.form.class.php';
require_once LIB_PATH . 'osclass/frm/User.form.class.php';
require_once LIB_PATH . 'osclass/frm/Language.form.class.php';
require_once LIB_PATH . 'osclass/frm/SendFriend.form.class.php';
require_once LIB_PATH . 'osclass/frm/Alert.form.class.php';
require_once LIB_PATH . 'osclass/frm/Field.form.class.php';
require_once LIB_PATH . 'osclass/frm/Admin.form.class.php';
require_once LIB_PATH . 'osclass/frm/ManageItems.form.class.php';
require_once LIB_PATH . 'osclass/frm/BanRule.form.class.php';
require_once LIB_PATH . 'osclass/functions.php';
require_once LIB_PATH . 'osclass/structured-data.php';
require_once LIB_PATH . 'osclass/helpers/hAdminMenu.php';
require_once LIB_PATH . 'osclass/core/iObject_Cache.php';
require_once LIB_PATH . 'osclass/core/Object_Cache_Factory.php';
require_once LIB_PATH . 'osclass/helpers/hCache.php';
require_once LIB_PATH . 'osclass/compatibility.php';


// Define website crypt key that is randomized at installation
if(!defined('OSC_CRYPT_KEY')) {
  define('OSC_CRYPT_KEY', osc_get_preference('crypt_key'));
}


// Initialize cache and set load flag
osc_cache_init();
define('__OSC_LOADED__', true);


// Initialize Cookie for user & admin login, then Params and Session
Cookie::newInstance();
Params::init();
Session::newInstance()->session_start();

if(osc_timezone() != '') {
  date_default_timezone_set(osc_timezone());
}


// Show maintenance page when maintenance mode is enabled
function osc_show_maintenance(){
  if(defined('__OSC_MAINTENANCE__') && Params::getParam('ajaxRequest') != 1 && Params::getParam('nolog') != 1 && Params::getParam('page') != 'cron' && Params::getParam('page') != 'ajax') { 
  ?>
  <div id="maintenance" class="flashmessage flashmessage-warning">
    <?php _e("The website is currently undergoing maintenance"); ?>
  </div>
  <?php 
  }
}

osc_add_hook('header', 'osc_show_maintenance');


// Add generator details into website head
function osc_meta_generator(){
  if(osc_get_preference('hide_generator') != 1) {
    $text = 'Osclass ' . OSCLASS_VERSION;
    echo '<meta name="generator" content="' . osc_esc_html(osc_apply_filter('meta_generator', $text)) . '" />' . PHP_EOL;
  }
}

osc_add_hook('header', 'osc_meta_generator');


// Show subdomain restriction message if enabled
function osc_show_subdomain_restricted(){
  if(Params::getParam('ajaxRequest') != 1 && Params::getParam('nolog') != 1 && Params::getParam('page') != 'cron' && Params::getParam('page') != 'ajax') {
    if(osc_subdomain_enabled() && osc_subdomain_redirect_enabled() && osc_subdomain_type() == 'country' && osc_is_subdomain() && osc_is_admin_user_logged_in()) { 
      $subdomain_id = strtolower(osc_subdomain_id());

      $restricted_country_ids = array_filter(explode(',', osc_subdomain_restricted_ids()));
      if(osc_subdomain_restricted_ids() == 'all' || !empty($restricted_country_ids) && in_array($subdomain_id, $restricted_country_ids)) {
        ?>
        <div id="maintenance" class="flashmessage flashmessage-warning">
          <?php _e("This subdomain is restricted based on user IP"); ?>
        </div>
        <?php 
      }
    }
  }
}

osc_add_hook('header', 'osc_show_subdomain_restricted');
osc_add_hook('header', 'osc_load_styles', 9);
osc_add_hook('header', 'osc_load_scripts', 10);


// Register javascript libraries and jQuery
$jquery_version = (trim(osc_get_preference('jquery_version')) == '' ? '1' : trim(osc_get_preference('jquery_version'))); 

if(!defined('JQUERY_VERSION')) {
  define('JQUERY_VERSION', $jquery_version);   // can be '1' or '3'
}

if(JQUERY_VERSION == '3') {
  osc_register_script('jquery', osc_assets_url('js/jquery3/jquery.min.js'));
  osc_register_script('jquery-uniform', osc_assets_url('js/jquery3/jquery.uniform.standalone.js'), array('jquery'));
  osc_register_script('jquery-migrate', osc_assets_url('js/jquery3/jquery-migrate.min.js'), array('jquery'));
  osc_register_script('jquery-ui', osc_assets_url('js/jquery3/jquery-ui/jquery-ui.min.js'), array('jquery'));
  osc_register_script('jquery-ui-backoffice', osc_assets_url('js/jquery3/jquery-ui-backoffice/jquery-ui.min.js'), array('jquery'));
  osc_register_script('jquery-validate', osc_assets_url('js/jquery3/jquery.validate.min.js'), array('jquery'));
  osc_register_script('fancybox', osc_assets_url('js/jquery3/jquery.fancybox.min.js'), array('jquery'));
} else {
  osc_register_script('jquery', osc_assets_url('js/jquery.min.js'));
  osc_register_script('jquery-migrate', osc_assets_url('js/jquery-migrate.min.js'), array('jquery'));
  osc_register_script('jquery-ui', osc_assets_url('js/jquery-ui.min.js'), array('jquery'));
  osc_register_script('jquery-ui-backoffice', osc_assets_url('js/jquery-ui.min.js'), array('jquery'));
  osc_register_script('jquery-validate', osc_assets_url('js/jquery.validate.min.js'), array('jquery'));
  osc_register_script('fancybox', osc_assets_url('js/fancybox/jquery.fancybox.pack.js'), array ('jquery'));
}

osc_register_script('jquery-json', osc_assets_url('js/jquery.json.js'), 'jquery');
osc_register_script('jquery-treeview', osc_assets_url('js/jquery.treeview.js'), 'jquery');
osc_register_script('jquery-nested', osc_assets_url('js/jquery.ui.nestedSortable.js'), array('jquery', OC_ADMIN ? 'jquery-ui-backoffice' : 'jquery-ui'));
osc_register_script('jquery-rotate', osc_assets_url('js/jquery.rotate.min.js'), 'jquery');
osc_register_script('tabber', osc_assets_url('js/tabber-minimized.js'), 'jquery');
osc_register_script('tiny_mce', osc_assets_url('js/tinymce/tinymce.min.js'));
osc_register_script('colorpicker', osc_assets_url('js/colorpicker/js/colorpicker.js'));
osc_register_script('php-date', osc_assets_url('js/date.js'));
osc_register_script('jquery-fineuploader', osc_assets_url('js/fineuploader/jquery.fineuploader.min.js'), 'jquery');
osc_register_script('cropper', osc_assets_url('js/cropper/cropper.min.js'), 'jquery');
osc_register_script('uppy', osc_assets_url('js/uppy.min.js'));
osc_register_script('jquery-touchpunch', osc_assets_url('js/jquery.ui.touch-punch.min.js'), array('jquery', OC_ADMIN ? 'jquery-ui-backoffice' : 'jquery-ui'));


// Manage lang param in URL here so no redirect is required
$lang = str_replace('-', '_', Params::getParam('lang'));
$locale = osc_current_user_locale();


// Initialize Plugins, Translations and csrf form guard
Plugins::init();
Translation::init();
osc_csrfguard_start();


// Run backoffice
if(OC_ADMIN) {
  // init admin menu
  AdminMenu::newInstance()->init();
  $functions_path = AdminThemes::newInstance()->getCurrentThemePath() . 'functions.php';
  
  if(file_exists($functions_path)) {
    require_once $functions_path;
  }
  
} else {
  $theme_manager = WebThemes::newInstance();
  $theme_path = $theme_manager->getCurrentThemePath();
  $theme = $theme_manager->getCurrentTheme();
  $theme_parent = $theme_manager->getCurrentThemeIsChild();
  $theme_parent_path = str_replace('/' . $theme . '/', '/' . $theme_parent . '/', $theme_path);
  
  
  // In case there are routes, we need this file to be loaded now
  if($theme_parent != '') {
    if($theme_parent_path != $theme_path && file_exists($theme_parent_path . 'functions.php')) {
      require_once $theme_parent_path . 'functions.php';      // Parent theme
    }
    
    if(file_exists($theme_path . 'functions_child.php')) {
      require_once $theme_path . 'functions_child.php';       // Child theme
    }
    
  } else {
    if(file_exists($theme_path . 'functions.php')) {
      require_once $theme_path . 'functions.php';
    }
  }

  
  Rewrite::newInstance()->init();
}

/* file end: ./oc-load.php */
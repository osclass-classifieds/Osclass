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
 

// INFORM ADMIN ABOUT MINIMUM PHP VERSION NOT MET
function omg_warn_php_version() {
  // Defined in oc-includes/osclass/install.php
  $php_min = (defined('PHP_MIN') ? PHP_MIN : '7.2');
  $php_max = (defined('PHP_MAX') ? PHP_MAX : '');

  
  if($php_min != '' && !version_compare(PHP_VERSION, $php_min . '.0', '>=')) {
  ?>
    <div id="flashmessage" class="flashmessage flashmessage-error">
      <?php echo sprintf(__('Your server does not meet minimum PHP version requirements. Your version is %s, minimum required version is %s! Please update your PHP version in order to avoid errors.'), PHP_VERSION, $php_min . '.0'); ?>
    </div>
  <?php 
  }


  if($php_max != '' && !version_compare(PHP_VERSION, $php_max . '.99', '<=')) {
  ?>
    <div id="flashmessage" class="flashmessage flashmessage-error">
      <?php echo sprintf(__('PHP %s is latest version supported by Osclass. You may talk with your hosting to downgrade your PHP version.'), $php_max); ?>
    </div>
  <?php 
  }
}

osc_add_hook('admin_dashboard_top', 'omg_warn_php_version');


// INFORM ADMIN ABOUT ENABLED MAINTENANCE MODE
function omg_warn_maintenance() {
  if(defined('__OSC_MAINTENANCE__') && __OSC_MAINTENANCE__ === true) {
  ?>
    <div id="flashmessage" class="flashmessage flashmessage-warning">
      <?php _e('Your website has mainentance mode enabled, customers will not be able to access you website.'); ?>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=tools&amp;action=maintenance"><?php _e('Manage maintanance mode'); ?></a>.</div>
    </div>
  <?php 
  }
}

osc_add_hook('admin_dashboard_top', 'omg_warn_maintenance');


// Get list of color schemes
function omg_color_schemes() {
  return array(
    array('id' => 'default', 'name' => __('Default'), 'colors' => array('#1d2327', '#2c3338', '#2271b1', '#72aee6'), 'chart' => '#0073aa'),
    array('id' => 'light', 'name' => __('Light'), 'colors' => array('#e5e5e5', '#999', '#d64e07', '#04a4cc'), 'chart' => '#04a4cc'),
    array('id' => 'modern', 'name' => __('Modern'), 'colors' => array('#1e1e1e', '#3858e9', '#33f078'), 'chart' => '#3858e9'),
    array('id' => 'blue', 'name' => __('Blue'), 'colors' => array('#096484', '#4796b3', '#52accc', '#74B6CE'), 'chart' => '#096484'),
    array('id' => 'coffee', 'name' => __('Coffee'), 'colors' => array('#46403c', '#59524c', '#c7a589', '#9ea476'), 'chart' => '#59524c'),
    array('id' => 'ectoplasm', 'name' => __('Ectoplasm'), 'colors' => array('#413256', '#523f6d', '#a3b745', '#d46f15'), 'chart' => '#d46f15'),
    array('id' => 'midnight', 'name' => __('Midnight'), 'colors' => array('#25282b', '#363b3f', '#69a8bb', '#e14d43'), 'chart' => '#e14d43'),
    array('id' => 'ocean', 'name' => __('Ocean'), 'colors' => array('#627c83', '#738e96', '#9ebaa0', '#aa9d88'), 'chart' => '#627c83'),
    array('id' => 'sunrise', 'name' => __('Sunrise'), 'colors' => array('#b43c38', '#cf4944', '#dd823b', '#ccaf0b'), 'chart' => '#b43c38')
  );  
}

function omg_current_color_scheme() {
  $color_schemes = omg_color_schemes();
  $current_scheme = (osc_get_preference('admin_color_scheme') <> '' ? osc_get_preference('admin_color_scheme') : 'default');

  foreach($color_schemes as $scheme) {
    if($scheme['id'] == $current_scheme) {
      return $scheme;
    }
  }
  
  return array();  
}


function omg_current_color_scheme_chart() {
  $current_scheme = omg_current_color_scheme();
  
  if(isset($current_scheme['chart']) && $current_scheme['chart'] != '') {
    return $current_scheme['chart'];
  }
  
  return '#0073aa';
}


// Replace login/recover/forgot in oc-admin to theme folder
function omg_replace_gui() {
  $params = Params::getParamsAsArray();
  $params['page'] = (isset($params['page']) ? $params['page'] : '');

  if($params['page'] == 'login' && !isset($params['action'])) {
    ob_clean();
    require_once osc_admin_base_path() . 'themes/omega/gui/login.php';
    exit;

  } else if($params['page'] == 'login' && @$params['action'] == 'recover') {
    ob_clean();
    require_once osc_admin_base_path() . 'themes/omega/gui/recover.php';
    exit;

  } else if($params['page'] == 'login' && @$params['action'] == 'forgot') {
    ob_clean();
    require_once osc_admin_base_path() . 'themes/omega/gui/forgot_password.php';
    exit;

  }
}

osc_add_hook('init_admin', 'omg_replace_gui', 9);

// clean optimized css & js when you switch theme
osc_add_hook('theme_activate', 'osc_clean_optimization_files', 9);


function admin_compact_mode_class($args){
  $compactMode = osc_get_preference('compact_mode','omega_admin_theme');
  if($compactMode == true){
    $args[] = 'compact';
  }
  
  return $args;
}

osc_add_filter('admin_body_class', 'admin_compact_mode_class');


function admin_color_scheme_class($args){
  $scheme = osc_get_preference('admin_color_scheme');
  
  if($scheme != '' && $scheme != 'default') {
    $args[] = 'scheme-' . $scheme;
  }
  
  return $args;
}

osc_add_filter('admin_body_class', 'admin_color_scheme_class');



function modern_compactmode_actions(){
  $compactMode = osc_get_preference('compact_mode','omega_admin_theme');
  $modeStatus  = array('compact_mode'=>true);
  if($compactMode == true){
    $modeStatus['compact_mode'] = false;
  }
  osc_set_preference('compact_mode', $modeStatus['compact_mode'], 'omega_admin_theme');
  echo json_encode($modeStatus);
}

osc_add_hook('ajax_admin_compactmode','modern_compactmode_actions');


// favicons
function admin_header_favicons() {
  $favicons   = array();
  $favicons[] = array(
    'rel'   => 'shortcut icon',
    'sizes' => '',
    'href'  => osc_current_admin_theme_url('images/favicon-48.png')
  );
  $favicons[] = array(
    'rel'   => 'apple-touch-icon-precomposed',
    'sizes' => '144x144',
    'href'  => osc_current_admin_theme_url('images/favicon-144.png')
  );
  $favicons[] = array(
    'rel'   => 'apple-touch-icon-precomposed',
    'sizes' => '114x114',
    'href'  => osc_current_admin_theme_url('images/favicon-114.png')
  );
  $favicons[] = array(
    'rel'   => 'apple-touch-icon-precomposed',
    'sizes' => '72x72',
    'href'  => osc_current_admin_theme_url('images/favicon-72.png')
  );
  $favicons[] = array(
    'rel'   => 'apple-touch-icon-precomposed',
    'sizes' => '',
    'href'  => osc_current_admin_theme_url('images/favicon-57.png')
  );

  $favicons = osc_apply_filter('admin_favicons', $favicons);

  foreach($favicons as $f) { 
    ?>
    <link <?php if($f['rel'] !== '') { ?>rel="<?php echo $f['rel']; ?>" <?php } if($f['sizes'] !== '') { ?>sizes="<?php echo $f['sizes']; ?>" <?php } ?>href="<?php echo $f['href']; ?>">
    <?php 
   }
}
osc_add_hook('admin_header', 'admin_header_favicons');


function admin_header_menu_icon() {
  echo '<div id="osc_toolbar_menu" style="display:none;"><a href="#" class="open-admin-menu"><span class="one"></span><span class="two"></span><span class="three"></span></a></div>';
}

osc_add_hook('render_admintoolbar_pre', 'admin_header_menu_icon');


// scripts
function admin_theme_js() {
  osc_load_scripts();
}

osc_add_hook('admin_header', 'admin_theme_js', 9);

// css
function admin_theme_css() {
  osc_enqueue_style('new', osc_current_admin_theme_url('css/new.css?v=' . date('YmdHis')));
  osc_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
  osc_load_styles();
}

osc_add_hook('admin_header', 'admin_theme_css', 9);


function admin_login_css() {
  echo '<link href="' . osc_current_admin_theme_url('css/new.css?v=' . date('YmdHis')) . '" rel="stylesheet" type="text/css" />';
  echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />';
  echo '<script type="text/javascript" src="' . osc_current_admin_theme_url('js/login.js?v=' . date('YmdHis')) . '"></script>';
}

osc_add_hook('admin_login_header', 'admin_login_css', 1);


function printLocaleTabs($locales = null) {
  if($locales==null) { $locales = osc_get_locales(); }
  $num_locales = count($locales);
  if($num_locales>1) {
  echo '<div id="language-tab" class="ui-osc-tabs ui-tabs ui-widget ui-widget-content ui-corner-all"><ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">';
    foreach($locales as $locale) {
      echo '<li class="ui-state-default ui-corner-top"><a href="#'.$locale['pk_c_code'].'">'.$locale['s_name'].'</a></li>';
    }
  echo '</ul></div>';
  };
}

function printLocaleTitle($locales = null, $item = null) {
  if($locales==null) { $locales = osc_get_locales(); }
  if($item==null) { $item = osc_item(); }
  $num_locales = count($locales);
  foreach($locales as $locale) {
    echo '<div class="input-has-placeholder input-title-wide"><label for="title">' . __('Title') . ' *</label>';
    $title = (isset($item) && isset($item['locale'][$locale['pk_c_code']]) && isset($item['locale'][$locale['pk_c_code']]['s_title'])) ? $item['locale'][$locale['pk_c_code']]['s_title'] : '';
    if( Session::newInstance()->_getForm('title') != "" ) {
      $title_ = Session::newInstance()->_getForm('title');
      if( $title_[$locale['pk_c_code']] != "" ){
        $title = $title_[$locale['pk_c_code']];
      }
    }
    
    $title = osc_apply_filter('admin_item_title', $title, $item, $locale);

    $name = 'title'. '[' . $locale['pk_c_code'] . ']';
    echo '<input id="' . $name . '" type="text" name="' . $name . '" value="' . osc_esc_html(htmlentities($title, ENT_COMPAT, "UTF-8")) . '"  />';
    echo '</div>';
  }
}

function printLocaleTitlePage($locales = null,$page = null) {
  if($locales==null) { $locales = osc_get_locales(); }
  $aFieldsDescription = Session::newInstance()->_getForm("aFieldsDescription");
  $num_locales = count($locales);
  echo '<label for="title">' . __('Title') . ' *</label>';

  foreach($locales as $locale) {
    $title = '';
    if(isset($page['locale'][$locale['pk_c_code']])) {
      $title = $page['locale'][$locale['pk_c_code']]['s_title'];
    }
    if( isset($aFieldsDescription[$locale['pk_c_code']]) && isset($aFieldsDescription[$locale['pk_c_code']]['s_title']) &&$aFieldsDescription[$locale['pk_c_code']]['s_title'] != '' ) {
      $title = $aFieldsDescription[$locale['pk_c_code']]['s_title'];
    }
    $name = $locale['pk_c_code'] . '#s_title';

    $title = osc_apply_filter('admin_page_title', $title, $page, $locale);

    echo '<div class="input-has-placeholder input-title-wide">';
    echo '<input id="' . $name . '" type="text" name="' . $name . '" value="' . osc_esc_html(htmlentities($title, ENT_COMPAT, "UTF-8")) . '"  />';
    echo '</div>';
  }
}

function printLocaleDescription($locales = null, $item = null) {
  if($locales==null) { $locales = osc_get_locales(); }
  if($item==null) { $item = osc_item(); }
  $num_locales = count($locales);
  foreach($locales as $locale) {
    $name = 'description'. '[' . $locale['pk_c_code'] . ']';

    echo '<div><label for="description">' . __('Description') . ' *</label>';
    $description = (isset($item) && isset($item['locale'][$locale['pk_c_code']]) && isset($item['locale'][$locale['pk_c_code']]['s_description'])) ? $item['locale'][$locale['pk_c_code']]['s_description'] : '';

    if( Session::newInstance()->_getForm('description') != "" ) {
      $description_ = Session::newInstance()->_getForm('description');
      if( $description_[$locale['pk_c_code']] != "" ){
        $description = $description_[$locale['pk_c_code']];
      }
    }

    $description = osc_apply_filter('admin_item_description', $description, $item, $locale);

    echo '<textarea id="' . $name . '" name="' . $name . '" rows="10">' . $description . '</textarea></div>';
  }
}

function printLocaleDescriptionPage($locales = null, $page = null) {
  if($locales==null) { $locales = osc_get_locales(); }
  $aFieldsDescription = Session::newInstance()->_getForm("aFieldsDescription");
  $num_locales = count($locales);

  foreach($locales as $locale) {
    $description = '';
    if(isset($page['locale'][$locale['pk_c_code']])) {
      $description = $page['locale'][$locale['pk_c_code']]['s_text'];
    }
    if( isset($aFieldsDescription[$locale['pk_c_code']]) && isset($aFieldsDescription[$locale['pk_c_code']]['s_text']) &&$aFieldsDescription[$locale['pk_c_code']]['s_text'] != '' ) {
      $description = $aFieldsDescription[$locale['pk_c_code']]['s_text'];
    }

    $description = osc_apply_filter('admin_page_description', $description, $page, $locale);

    $name = $locale['pk_c_code'] . '#s_text';
    echo '<div><label for="description">' . __('Description') . ' *</label>';
    echo '<textarea id="' . $name . '" name="' . $name . '" rows="10">' . $description . '</textarea></div>';
  }
}


function check_version_admin_footer() {
  if( (time() - osc_last_version_check()) > (24 * 3600) ) {
    ?>
    <script type="text/javascript">
      $(document).ready(function() {
        $.getJSON(
          '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=check_version',
          {},
          function(data){}
        );
      });
    </script>
    <?php
  }
}
osc_add_hook('admin_footer', 'check_version_admin_footer');


function check_languages_admin_footer() {
  ?>
  <script type="text/javascript">
    $(document).ready(function() {
      $.getJSON(
        '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=check_languages',
        {},
        function(data){}
      );
    });
  </script>
<?php
}


function check_themes_admin_footer() {
  ?>
  <script type="text/javascript">
    $(document).ready(function() {
      $.getJSON(
        '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=check_themes',
        {},
        function(data){}
      );
    });
  </script>
<?php
}


function check_plugins_admin_footer() {
  ?>
  <script type="text/javascript">
    $(document).ready(function() {
      $.getJSON(
        '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=check_plugins',
        {},
        function(data){}
      );
    });
  </script>
<?php
}


function backtheme_check_compatibility_branch() {
  $osclass_version = (int)str_replace('.', '', OSCLASS_VERSION);
  $osclass_author = (!defined('OSCLASS_AUTHOR') ? 'NONE' : strtoupper(OSCLASS_AUTHOR));
  
  if($osclass_version >= 420 && $osclass_author <> 'OSCLASSPOINT') {
    osc_add_flash_error_message('Theme is not compatible with your osclass version or branch! You cannot use this theme as it would generate errors on your installation. Download and install supported osclass version: <a href="https://osclass-classifieds.com/download">https://osclass-classifieds.com/download</a>', 'admin');
  }
} 

osc_add_hook('admin_header', 'backtheme_check_compatibility_branch', 1);



function phpinfo2array() {
  $entitiesToUtf8 = function($input) {
  // http://php.net/manual/en/function.html-entity-decode.php#104617
    return preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input);
  };
  $plainText = function($input) use ($entitiesToUtf8) {
    return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
  };
    $titlePlainText = function($input) use ($plainText) {
    return '# '.$plainText($input);
  };
   
  ob_start();
  phpinfo(-1);
   
  $phpinfo = array('phpinfo' => array());

  // Strip everything after the <h1>Configuration</h1> tag (other h1's)
  if (!preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
    return array();
  }
   
  $input = $matches[1];
  $matches = array();

  if(preg_match_all(
    '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|'.
    '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
    $input,
    $matches,
    PREG_SET_ORDER
  )) {
    foreach ($matches as $match) {
      $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
      if (strlen($match[1])) {
        $phpinfo[$match[1]] = array();
      } elseif (isset($match[3])) {
        $keys1 = array_keys($phpinfo);
        $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array($fn($match[3]), $fn($match[4])) : $fn($match[3]);
      } else {
        $keys1 = array_keys($phpinfo);
        $phpinfo[end($keys1)][] = $fn($match[2]);
      }
    }
  }
   
  return $phpinfo;
}

/* end of file */
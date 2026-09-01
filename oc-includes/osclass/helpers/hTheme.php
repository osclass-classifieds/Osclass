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


/**
 * Gets urls for current theme administrations options
 *
 * @param string $file must be a relative path, from ABS_PATH
 * @return string
 */
function osc_admin_render_theme_url($file = '') {
  return osc_admin_base_url(true).'?page=appearance&action=render&file=' . $file;
}


/**
 * Render the specified file
 *
 * @param string $file must be a relative path, from PLUGINS_PATH
 */
function osc_render_file($file = '') {
  if($file=='') {
    $file = __get('file');
  }
  // Clean $file to prevent hacking of some
  osc_sanitize_url($file);
  $file = str_replace(array("..\\" , '../') , '' , str_replace( '://' , '' , preg_replace( '|http([s]*)|' , '' , $file)));
  
  if(file_exists(osc_themes_path() . osc_theme() . '/plugins/' . $file)) {
    include osc_themes_path() . osc_theme() . '/plugins/' . $file;

  } else if(file_exists(osc_themes_path() . osc_theme() . '/custom/' . $file)) {
    include osc_themes_path() . osc_theme() . '/custom/' . $file;
    
  } else if(file_exists(osc_themes_path() . osc_theme() . '/' . $file)) {
    include osc_themes_path() . osc_theme() . '/' . $file;
    
  } else if(file_exists(osc_plugins_path() . $file)) {
    include osc_plugins_path() . $file;
  }
}


/**
 * Gets urls for render custom files in front-end
 *
 * @param string $file must be a relative path, from PLUGINS_PATH
 * @return string
 */
function osc_render_file_url($file = '') {
  osc_sanitize_url($file);
  $file = str_replace(array("..\\", '../'), '', str_replace('://' , '', preg_replace('|http([s]*)|', '' , $file)));
  return osc_base_url(true).'?page=custom&file=' . $file;
}


/**
 * Returns theme's information
 *
 * @param $theme
 *
 * @return array
 */
function osc_theme_get_info($theme) {
  return WebThemes::newInstance()->loadThemeInfo($theme);
}


/**
 * Re-send the flash messages of the given section. Usefull for custom theme/plugins files.
 *
 * @param string $section
 */
function osc_resend_flash_messages($section = 'pubMessages' ) {
  $messages = Session::newInstance()->_getMessage($section);
  if (is_array($messages)) {

    foreach ($messages as $message) {
  
      $message = Session::newInstance()->_getMessage($section);
      if(isset($message['msg'])) {
        if( isset($message[ 'type' ]) && $message[ 'type' ] === 'info' ) {
          osc_add_flash_info_message($message['msg'], $section);
        } else if( isset($message[ 'type' ]) && $message[ 'type' ] === 'ok' ) {
          osc_add_flash_ok_message($message['msg'], $section);
        } else {
          osc_add_flash_error_message($message['msg'], $section);
        }
      }
    }
  }
}

/**
 * Enqueue script
 *
 * @param string $id
 */
function osc_enqueue_script($id) {
  Scripts::newInstance()->enqueuScript($id);
}

/**
 * Remove script from the queue, so it will not be loaded
 *
 * @param string $id
 */
function osc_remove_script($id) {
  Scripts::newInstance()->removeScript($id);
}

/**
 * Add script to be loaded
 *
 * @param $id       string Id to identify the script
 * @param $url      string url of the .js file
 * @param $dependencies mixed, could be an array or a string
 */
function osc_register_script($id, $url, $dependencies = null, $attributes = null) {
  Scripts::newInstance()->registerScript($id, $url, $dependencies, $attributes);
}

/**
 * Remove script from the queue, so it will not be loaded
 *
 * @param string $id
 */
function osc_unregister_script($id) {
  Scripts::newInstance()->unregisterScript($id);
}

/**
 * Print the HTML tags to make the script load
 */
function osc_load_scripts() {
  Scripts::newInstance()->printScripts();
  if( OC_ADMIN ) {
    osc_run_hook('admin_scripts_loaded');
  } else {
    osc_run_hook('scripts_loaded');
  }
}

/**
 * Add style to be loaded
 *
 * @param $id  string Id to identify the style
 * @param $url string Url of the .css file
 */
function osc_enqueue_style($id, $url) {
  Styles::newInstance()->addStyle($id, $url);
}

/**
 * Remove style from the queue, so it will not be loaded
 *
 * @param $id
 */
function osc_remove_style($id) {
  Styles::newInstance()->removeStyle($id);
}

/**
 * Print the HTML tags to make the style load
 */
function osc_load_styles() {
  Styles::newInstance()->printStyles();
}


/**
 * @param    $id
 * @param    $name
 * @param    $options
 * @param string $class
 */
function osc_print_bulk_actions( $id , $name , $options , $class = '' ) {
  echo '<select id="'.$id.'" name="'.$name.'" '.($class!=''?'class="'.$class.'"':'').'>';
  foreach($options as $o) {
    $opt = '';
    $label = '';
    foreach($o as $k => $v) {
      if( $k !== 'label') {
        $opt .= $k.'="'.$v.'" ';
      } else {
        $label = $v;
      }
    }
    echo '<option '.$opt.'>'.$label.'</option>';
  }
  echo '</select>';
}


/* file end: ./oc-includes/osclass/hTheme.php */
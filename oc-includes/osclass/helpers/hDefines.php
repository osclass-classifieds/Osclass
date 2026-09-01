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
* Helper Defines
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/

/**
 * List all known RTL languages/locales
 *
 * @param boolean $long if return long format (5 char) or short format (2 char)
 * @return array()
 */
function osc_rtl_lang_codes($long = true) {
  $langs_ = array('ar_LB','ar_DZ','ar_BH','ar_EG','ar_IQ','ar_JO','ar_KW','ar_LY','ar_MA','ar_OM','ar_SA','ar_SY','fa_IR','ar_TN','ar_AE','ar_YE','ar_TD','ar_CO','ar_DJ','ar_ER','ar_MR','ar_SD');

  if(!$long) {
    $langs = array_unique(array_map(function($v) { return substr($v, 0, 2); }, $langs_));
  } else {
    $langs = $langs_;
  }

  return osc_apply_filter('rtl_lang_codes', $langs);
}

/**
 * Gets the root url for your installation
 *
 * @param boolean $with_index true if index.php in the url is needed
 * @return string
 */
function osc_base_url($with_index = false, $with_lang_code = false, $lang_code = '') {
  $path = WEB_PATH;
  
  if(osc_locale_to_base_url_enabled() && osc_subdomain_type() != 'language' && $with_lang_code === true) {
    $path .= osc_base_url_locale_slug($lang_code) . '/';          // os810
  }
  
  // add the index.php if it's true
  if($with_index) {
    $path .= 'index.php';
  }

  return osc_apply_filter('base_url', $path, $with_index);
}


/**
 * @param array $params
 *
 * @return string
 * @throws \Exception
 */
function osc_subdomain_base_url($params = array()) {
  $fields = array();
  $fields['category'] = 'sCategory';
  $fields['country'] = 'sCountry';
  $fields['region'] = 'sRegion';
  $fields['city'] = 'sCity';
  $fields['user'] = 'sUser';
  $fields['language'] = 'sLanguage';
  
  if(isset($fields[osc_subdomain_type()])) {
    $field = $fields[osc_subdomain_type()];
    
    if(isset($params[$field]) && !is_array($params[$field]) && $params[$field] != '' && strpos($params[$field], ',') === false) {
      return osc_search_url(array($fields[osc_subdomain_type()] => $params[$field]));
    }
  }
  
  return osc_base_url(false, true);
}


/**
 * Return top URL for subdomains
 *
 * @return string
 */
function osc_subdomain_top_url($with_index = false, $stop_redirect_param = NULL) {
  if($stop_redirect_param === NULL) {
    $stop_redirect_param = osc_subdomain_redirect_enabled();
  }
  
  $http_url = osc_is_ssl() ? "https://" : "http://";
  $path = $http_url . osc_subdomain_host();
  $path .= REL_WEB_URL;
  
  if(substr($path, -1) !== '/') {
    $path .= '/';
  }

  if($with_index || $stop_redirect_param) {
    $path .= 'index.php';
  }

  if($stop_redirect_param) {
    $path .= '?nored=1';
  }
  
  return osc_apply_filter('subdomain_top_url', $path, $with_index, $stop_redirect_param);
}


/**
 * Gets the root url of oc-admin for your installation
 *
 * @param boolean $with_index true if index.php in the url is needed
 * @return string
 */
function osc_admin_base_url($with_index = false) {
  $path = osc_base_url() . OC_ADMIN_FOLDER . '/';

  // add the index.php if it's true
  if($with_index) {
    $path .= 'index.php';
  }

  return osc_apply_filter('admin_base_url', $path, $with_index);
}

/**
* Gets the root path for your installation
*
* @return string
*/
function osc_base_path() {
  return ABS_PATH;
}

/**
* Gets the root path of oc-admin
*
* @return string
*/
function osc_admin_base_path() {
  return(osc_base_path() . OC_ADMIN_FOLDER . '/');
}

/**
* Gets the librarieas path
*
* @return string
*/
function osc_lib_path() {
  return LIB_PATH;
}


/**
* Gets the librarieas path / alternative name
*
* @return string
*/
function osc_includes_path() {
  return osc_lib_path();
}


/**
* Gets the content path
*
* @return string
*/
function osc_content_path() {
  return CONTENT_PATH;
}


/**
* Gets the themes path
*
* @return string
*/
function osc_themes_path() {
  return THEMES_PATH;
}

/**
* Gets the plugins path
*
* @return string
*/
function osc_plugins_path() {
  return PLUGINS_PATH;
}

/**
* Gets the translations path
*
* @return string
*/
function osc_translations_path() {
  return TRANSLATIONS_PATH;
}

/**
* Gets the uploads path
*
* @return string
*/
function osc_uploads_path() {
  return UPLOADS_PATH;
}

/**
* Gets the uploads url
*
* @return string
*/
function osc_uploads_url() {
  return UPLOADS_WEB_PATH;
}

/**
* Gets the librarieas url
*
* @return string
*/
function osc_lib_url() {
  return LIB_WEB_PATH;
}

/**
* Gets the librarieas url - alternative name
*
* @return string
*/
function osc_includes_url() {
  return osc_lib_url();
}

/**
* Gets the content url
*
* @return string
*/
function osc_content_url() {
  return CONTENT_WEB_PATH;
}

/**
* Gets the themes url
*
* @return string
*/
function osc_themes_url() {
  return THEMES_WEB_PATH;
}

/**
* Gets the plugins url
*
* @return string
*/
function osc_plugins_url() {
  return PLUGINS_WEB_PATH;
}

/**
* Gets the translations url
*
* @return string
*/
function osc_translations_url() {
  return TRANSLATIONS_WEB_PATH;
}

/**
* Gets the oc-admin folder name
*
* @return string
*/
function osc_admin_folder() {
  return OC_ADMIN_FOLDER;
}

/**
* Gets the oc-includes folder name
*
* @return string
*/
function osc_includes_folder() {
  return OC_INCLUDES_FOLDER;
}

/**
* Gets the oc-content folder name
*
* @return string
*/
function osc_content_folder() {
  return OC_CONTENT_FOLDER;
}


/**
* Gets the current oc-admin theme
*
* @return string
*/
function osc_current_admin_theme() {
  return AdminThemes::newInstance()->getCurrentTheme();
}

/**
 * Gets the complete url of a given admin's file
 *
 * @param string $file the admin's file
 * @return string
 */
function osc_current_admin_theme_url($file = '') {
  return AdminThemes::newInstance()->getCurrentThemeUrl() . $file;
}


/**
 * Gets the complete path of a given admin's file
 *
 * @param string $file the admin's file
 * @return string
 */
function osc_current_admin_theme_path($file = '') {
  require AdminThemes::newInstance()->getCurrentThemePath() . $file;
}

/**
 * Gets the complete url of a given style's file
 *
 * @param string $file the style's file
 * @return string
 */
function osc_current_admin_theme_styles_url($file = '') {
  return AdminThemes::newInstance()->getCurrentThemeStyles() . $file;
}

/**
 * Gets the complete url of a given js's file
 *
 * @param string $file the js's file
 * @return string
 */
function osc_current_admin_theme_js_url($file = '') {
  return AdminThemes::newInstance()->getCurrentThemeJs() . $file;
}

/**
 * Gets the current theme for the public website
 *
 * @return string
 */
function osc_current_web_theme() {
  return WebThemes::newInstance()->getCurrentTheme();
}


/**
 * Gets the base theme name in case theme is child theme
 * Is blank in case theme is not child
 *
 * @return string
 */
function osc_current_web_theme_is_child() {
  return WebThemes::newInstance()->getCurrentThemeIsChild();
}



/**
 * Gets the complete url of a given file using the theme url as a root
 *
 * @param string $file the given file
 * @return string
 */
function osc_current_web_theme_url($file = '') {
  $theme = WebThemes::newInstance()->getCurrentTheme();
  $file_fix = explode('?', $file)[0];  // remove version from file, i.e. style.css?v=21311
  $theme_split = explode('_', $theme);
  $theme_is_child = osc_current_web_theme_is_child();

  // HANDLE CHILD THEME IN BEST POSSIBLE WAY (v450)
  if($theme_is_child != '') {
    if(isset($theme_split[1]) && $theme_split[1] == 'child') {
      $theme_child = $theme;
      $theme = $theme_split[0];
    } else {
      $theme_child = $theme . '_child';
      $theme = $theme;
    }

    
    WebThemes::newInstance()->setCurrentTheme($theme_child);

    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file_fix)){
      return WebThemes::newInstance()->getCurrentThemeUrl() . $file;
    }
    
    WebThemes::newInstance()->setCurrentTheme($theme);

    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file_fix)) {
      return WebThemes::newInstance()->getCurrentThemeUrl() . $file;
    }
  }
  
  return WebThemes::newInstance()->getCurrentThemeUrl() . $file;
}

/**
 * Gets the complete path of a given file using the theme path as a root
 *
 * @param string $file
 * @return string
 */
function osc_current_web_theme_path($file = '') {
  $theme = WebThemes::newInstance()->getCurrentTheme();
  $theme_split = explode('_', $theme);
  $theme_is_child = osc_current_web_theme_is_child();

  // HANDLE CHILD THEME IN BEST POSSIBLE WAY (v450)
  if($theme_is_child != '') {
    if(isset($theme_split[1]) && $theme_split[1] == 'child') {
      $theme_child = $theme;
      $theme = $theme_split[0];
    } else {
      $theme_child = $theme . '_child';
      $theme = $theme;
    }

    
    WebThemes::newInstance()->setCurrentTheme($theme_child);

    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)){
      require WebThemes::newInstance()->getCurrentThemePath() . $file;
      return;
    }
    
    WebThemes::newInstance()->setCurrentTheme($theme);
    
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)) {
      require WebThemes::newInstance()->getCurrentThemePath() . $file;
      return;
    }
    
    WebThemes::newInstance()->setGuiTheme();
    
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)) {
      require WebThemes::newInstance()->getCurrentThemePath() . $file;
      return;
    }
  } else {
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)){
      require WebThemes::newInstance()->getCurrentThemePath() . $file;
      return;
    }
    
    WebThemes::newInstance()->setGuiTheme();
    
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)) {
      require WebThemes::newInstance()->getCurrentThemePath() . $file;
      return;
    }
  }
}


/**
 * Gets the complete path of a given file using the theme path as a root
 *
 * @param string $file
 * @return string
 */
function osc_current_web_theme_path_value($file = '') {
  $theme = WebThemes::newInstance()->getCurrentTheme();
  $theme_split = explode('_', $theme);
  $theme_is_child = osc_current_web_theme_is_child();

  // HANDLE CHILD THEME IN BEST POSSIBLE WAY (v450)
  if($theme_is_child != '') {
    if(isset($theme_split[1]) && $theme_split[1] == 'child') {
      $theme_child = $theme;
      $theme = $theme_split[0];
    } else {
      $theme_child = $theme . '_child';
      $theme = $theme;
    }

    
    WebThemes::newInstance()->setCurrentTheme($theme_child);

    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)){
      return WebThemes::newInstance()->getCurrentThemePath() . $file;
    }
    
    WebThemes::newInstance()->setCurrentTheme($theme);
    
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)) {
      return WebThemes::newInstance()->getCurrentThemePath() . $file;
    }
    
    WebThemes::newInstance()->setGuiTheme();
    
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)) {
      return WebThemes::newInstance()->getCurrentThemePath() . $file;
    }
  } else {
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)){
      return WebThemes::newInstance()->getCurrentThemePath() . $file;
    }
    
    WebThemes::newInstance()->setGuiTheme();
    
    if(file_exists(WebThemes::newInstance()->getCurrentThemePath() . $file)) {
      return WebThemes::newInstance()->getCurrentThemePath() . $file;
    }
  }
}

/**
 * Gets the complete path of a given styles file using the theme path as a root
 *
 * @param string $file
 * @return string
 */
function osc_current_web_theme_styles_url($file = '') {
  return WebThemes::newInstance()->getCurrentThemeStyles() . $file;
}

function osc_current_web_theme_css_url($file = '') {
  return osc_current_web_theme_styles_url($file);
}

/**
 * Gets the complete path of a given js file using the theme path as a root
 *
 * @param string $file
 * @return string
 */
function osc_current_web_theme_js_url($file = '') {
  return WebThemes::newInstance()->getCurrentThemeJs() . $file;
}

/**
 * Gets the complete path of a given common asset
 *
 * @since 3.0
 * @param string $file
 * @param string $assets_base_url
 * @return string
 */
function osc_assets_url($file = '', $assets_base_url = null) {
  if(strpos($file, '../') !== false || strpos($file, '..\\') !== false) {
    $file = '';
  }

  if($assets_base_url === null) {
    $url = osc_includes_url() . 'osclass/assets/' . $file;
  } else {
    $url = $assets_base_url . $file;
  }
  
  return osc_apply_filter('assets_url', $url);
}

/////////////////////////////////////
//functions for the public website //
/////////////////////////////////////

/**
 *  Create automatically the contact url
 *
 * @return string
 */
function osc_contact_url() {
  if (osc_rewrite_enabled()) {
    $path = osc_base_url(false, true) . osc_get_preference('rewrite_contact');
  } else {
    $path = osc_base_url(true) . '?page=contact';
  }
  return $path;
}

/**
 * Create automatically the url to post an item in a category
 *
 * @return string
 */
function osc_item_post_url_in_category($category_id = null) {
  if($category_id === null) {
    $category_id = (osc_category_id() > 0 ? osc_category_id() : osc_item_category_id());
  }
  
  if ($category_id > 0) {
    if (osc_rewrite_enabled()) {
      $path = osc_base_url(false, true) . osc_get_preference('rewrite_item_new') . '/' . $category_id;
    } else {
      $path = sprintf(osc_base_url(true) . '?page=item&action=item_add&catId=%d', $category_id);
    }
  } else {
    $path = osc_item_post_url();
  }
  return $path;
}

/**
 *  Create automatically the url to post an item
 *
 * @return string
 */
function osc_item_post_url() {
  if (osc_rewrite_enabled()) {
    $path = osc_base_url(false, true) . osc_get_preference('rewrite_item_new');
  } else {
    $path = osc_base_url(true) . '?page=item&action=item_add';
  }
  return $path;
}


/**
 * Create automatically the url of a category
 *
 * @return string the url
 * @throws \Exception
 */
function osc_search_category_url($category_id = null) {
  $cat_id = (int)($category_id !== null ? $category_id : osc_category_id());
  
  if($cat_id > 0) {
    return osc_search_url(array('sCategory' => $cat_id));
  }
  
  return osc_search_url();
}


/**
 * Create automatically the url of the users' dashboard
 *
 * @return string
 */
function osc_user_dashboard_url() {
  if (osc_rewrite_enabled()) {
    $path = osc_base_url(false, true) . osc_get_preference('rewrite_user_dashboard');
  } else {
    $path = osc_base_url(true) . '?page=user&action=dashboard';
  }
  return $path;
}

/**
 * Create automatically the logout url
 *
 * @return string
 */
function osc_user_logout_url() {
  if (osc_rewrite_enabled()) {
    $path = osc_base_url(false, true) . osc_get_preference('rewrite_user_logout');
  } else {
    $path = osc_base_url(true) . '?page=main&action=logout';
  }
  return $path;
}

/**
 * Create automatically the login url
 *
 * @return string
 */
function osc_user_login_url() {
  if (osc_rewrite_enabled()) {
    $path = osc_base_url(false, true) . osc_get_preference('rewrite_user_login');
  } else {
    $path = osc_base_url(true) . '?page=login';
  }
  return $path;
}

/**
 * Create automatically the url to register an account
 *
 * @return string
 */
function osc_register_account_url() {
  if (osc_rewrite_enabled()) {
    $path = osc_base_url(false, true) . osc_get_preference('rewrite_user_register');
  } else {
    $path = osc_base_url(true) . '?page=register&action=register';
  }
  return $path;
}

/**
 * Create automatically the url to activate an account
 *
 * @param int $id
 * @param string $code
 * @return string
 */
function osc_user_activate_url($id, $code) {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_activate') . '/' . $id . '/' . $code;
  } else {
    return osc_base_url(true) . '?page=register&action=validate&id=' . $id . '&code=' . $code;
  }
}

/**
 * Re-send the activation link
 *
 * @param int $id
 * @param string $email
 * @return string
 */
function osc_user_resend_activation_link($id, $email) {
  return osc_base_url(true) . '?page=login&action=resend&id='.$id.'&email='.$email;
}


/**
 * Create automatically the url of the item's comments page
 *
 * @param mixed  $page
 * @param string $locale
 *
 * @return string
 * @throws \Exception
 */
function osc_item_comments_url($page = 'all', $locale = '') {
  if (osc_rewrite_enabled()) {
    return osc_item_url($locale) . '?comments-page=' . $page;
  } else {
    return osc_item_url($locale) . '&comments-page=' . $page;
  }
}


/**
 * Create automatically the url of the item's comments page
 *
 * @param string $locale
 *
 * @return string
 * @throws \Exception
 */
function osc_comment_url($locale = '') {
  return osc_item_url($locale) . '?comment=' . osc_comment_id();
}


/**
 * Create automatically the url of the item details page
 *
 * @param string $locale
 *
 * @return string
 * @throws \Exception
 */
function osc_item_url($locale = '') {
  return osc_item_url_from_item(osc_item(), $locale);
}


/**
 * Create item url from item data without exported to view.
 *
 * @since 3.3
 *
 * @param array  $item
 * @param string $locale
 *
 * @return string
 * @throws \Exception
 */
function osc_item_url_from_item($item, $locale = '') {
  if(osc_rewrite_enabled()) {
    $uri = osc_get_preference('rewrite_item_url');
    
    // Based on subdomain type, remove some elements from URI as these are already in base url
    if(osc_subdomain_enabled()) {
      switch(osc_subdomain_type()) {
        case 'category':
          $uri = str_replace('{CATEGORIES}', '', $uri);
          $uri = str_replace('{CATEGORY}', '', $uri);
          break;
          
        case 'country':
          if($item['fk_c_country_code'] <> '') {
            $uri = str_replace('{ITEM_COUNTRY_CODE}', '', $uri);
            $uri = str_replace('{ITEM_COUNTRY}', '', $uri);
          }
          break;
          
        case 'region':
          if($item['fk_i_region_id'] > 0) {
            $uri = str_replace('{ITEM_REGION}', '', $uri);
          }
          break;
          
        case 'city':
          if($item['fk_i_city_id'] > 0) {
            $uri = str_replace('{ITEM_CITY}', '', $uri);
          }
          break;
          
        case 'user':
          if($item['fk_i_user_id'] > 0) {
            $uri = str_replace('{ITEM_CONTACT_NAME}', '', $uri);
          }
          break;
      }
    }


    // Replace keywords in URI
    if(preg_match('|{CATEGORIES}|', $uri)) {
      $sanitized_categories = array();
      $cat = Category::newInstance()->hierarchy(isset($item['fk_i_category_id']) ? $item['fk_i_category_id'] : NULL);
      
      for ($i = count($cat); $i > 0; $i--) {
        // For category based subdomains, do not repeat top category slug, if it is already in URL
        if(osc_subdomain_type() == 'category' && $cat[$i - 1]['s_slug'] != osc_subdomain_slug() || osc_subdomain_type() != 'category') {
          $sanitized_categories[] = $cat[$i - 1]['s_slug'];
        }
      }

      $uri = str_replace('{CATEGORIES}', implode('/' , $sanitized_categories), $uri);
    }
    
    if(isset($cat[0]) && (osc_subdomain_type() == 'category' && $cat[0]['s_slug'] != osc_subdomain_slug() || osc_subdomain_type() != 'category')) {
      $category = $cat[0];
      $uri = str_replace('{CATEGORY}', osc_sanitizeString(isset($category['s_slug']) ? $category['s_slug'] : ''), $uri);
    } else {
      $uri = str_replace('{CATEGORY}', '', $uri);
    }

    $uri = str_replace('{ITEM_ID}', osc_sanitizeString(isset($item['pk_i_id']) ? $item['pk_i_id'] : ''), $uri);
    $uri = str_replace('{ITEM_COUNTRY_CODE}', osc_sanitizeString(isset($item['fk_c_country_code']) ? $item['fk_c_country_code'] : ''), $uri);
    $uri = str_replace('{ITEM_COUNTRY}', osc_sanitizeString(isset($item['s_country']) ? $item['s_country'] : ''), $uri);
    $uri = str_replace('{ITEM_REGION}', osc_sanitizeString(isset($item['s_region']) ? $item['s_region'] : ''), $uri);
    $uri = str_replace('{ITEM_CITY}', osc_sanitizeString(isset($item['s_city']) ? $item['s_city'] : ''), $uri);
    $uri = str_replace('{ITEM_CITY_AREA}', osc_sanitizeString(isset($item['s_city_area']) ? $item['s_city_area'] : ''), $uri);
    $uri = str_replace('{ITEM_ZIP}', osc_sanitizeString(isset($item['s_zip']) ? $item['s_zip'] : ''), $uri);
    $uri = str_replace('{ITEM_CONTACT_NAME}', osc_sanitizeString(isset($item['s_contact_name']) ? $item['s_contact_name'] : ''), $uri);
    $uri = str_replace('{ITEM_CONTACT_EMAIL}', osc_sanitizeString(isset($item['s_contact_email']) ? str_replace('@', '-at-', $item['s_contact_email']) : ''), $uri);
    $uri = str_replace('{ITEM_CURRENCY_CODE}', osc_sanitizeString(isset($item['fk_c_currency_code']) ? $item['fk_c_currency_code'] : ''), $uri);
    $uri = str_replace('{ITEM_PUB_DATE}', osc_sanitizeString(isset($item['dt_pub_date']) ? date('Y-m-d', strtotime($item['dt_pub_date'])) : ''), $uri);
    $uri = str_replace('{ITEM_TITLE}', osc_sanitizeString(str_replace(',' , '-' , isset($item['s_title']) ? $item['s_title'] : '')), $uri);
    $uri = str_replace('?', '', $uri);
    $uri = str_replace(array('//','///','////','/////','//////'), '/', $uri);
    $uri = ltrim($uri, '/');


    $base_url = '';
    
    // Identify subdomain url for item where it belongs - if we are not on subdomain url already
    if(osc_subdomain_enabled() && osc_subdomain_type() != 'language' && osc_is_topdomain()) {
      $sub_param = osc_item_to_subdomain_param($item);

      if(!empty($sub_param)) {
        $base_url = osc_subdomain_base_url($sub_param);
        
        if($locale != '' && substr($base_url, -(strlen($locale)+2)) != '/' . $locale . '/') {
          $base_url .= $locale . '/';
        }
      }
    }

    // Subdomain url not found
    if($base_url == '') {
      if($locale != '' && substr($base_url, -(strlen($locale)+2)) != '/' . $locale . '/') {
        $base_url = osc_base_url() . $locale . '/';
      } else {
        $base_url = osc_base_url(false, true);
      }
    }
    
    // Finalize url
    $item_url = $base_url . $uri;
  
  } else {
    $item_url = osc_item_url_ns(isset($item['pk_i_id']) ? $item['pk_i_id'] : null, $locale);
  }
  
  return $item_url;
}


// Item row to subdomain params
function osc_item_to_subdomain_param($item) {
  if($item === false || !is_array($item)) {
    return array();
  }
  
  $sub_param = array();
  
  switch(osc_subdomain_type()) {
    case 'category': 
      $sub_param = isset($item['fk_i_category_id']) ? array('sCategory' => $item['fk_i_category_id']) : array();
      break;
      
    case 'country': 
      $sub_param = isset($item['fk_c_country_code']) ? array('sCountry' => $item['fk_c_country_code']) : array();
      break;
      
    case 'region': 
      $sub_param = isset($item['fk_i_region_id']) ? array('sRegion' => $item['fk_i_region_id']) : array();
      break;
      
    case 'city': 
      $sub_param = isset($item['fk_i_city_id']) ? array('sCity' => $item['fk_i_city_id']) : array();
      break;
      
    case 'user': 
      $sub_param = isset($item['fk_i_user_id']) ? array('sUser' => $item['fk_i_user_id']) : array();
      break;
  }
  
  $sub_param = array_filter($sub_param);
  
  return $sub_param;
}


/**
 * Create automatically the url of the item details page
 *
 * @param string $locale
 * @return string
 * @throws \Exception
*/
function osc_premium_url($locale = '') {
  return osc_item_url_from_item(osc_premium(), $locale);

  /*
  // Replaced with simplified function in same way as osc_item_url is
  if (osc_rewrite_enabled()) {
    $sanitized_categories = array();
    $cat = Category::newInstance()->hierarchy(osc_premium_category_id());
    
    for ($i = count($cat); $i > 0; $i--) {
      $sanitized_categories[] = $cat[$i - 1]['s_slug'];
    }
    
    $url = str_replace(
      array('{ITEM_ID}', '{CATEGORIES}'), 
      array(osc_premium_id(), implode('/' , $sanitized_categories)), 
      str_replace('{ITEM_TITLE}', osc_sanitizeString(str_replace(',', '-', osc_premium_title())), osc_get_preference('rewrite_item_url'))
   );

    if($locale!='') {
      $path = osc_base_url() . $locale . '/' . $url;
    } else {
      $path = osc_base_url() . $url;
    }
  } else {
    $path = osc_item_url_ns(osc_premium_id(), $locale);
  }
  return $path;
  */
}


/**
 * Create the no friendly url of the item using the id of the item
 *
 * @param int $id the primary key of the item
 * @param $locale
 * @return string
 */
function osc_item_url_ns($id, $locale = '') {
  $path = osc_base_url(true) . '?page=item&id=' . $id;
  if($locale!='') {
    $path .= '&lang=' . $locale;
  }

  return $path;
}

/**
 * Create automatically the url to for admin to edit an item
 *
 * @param int $id
 * @return string
 */
function osc_item_admin_edit_url($id) {
  return osc_admin_base_url(true) . '?page=items&action=item_edit&id=' . $id;
}

/**
 * Gets current user alerts' url
 *
 * @return string
 */
function osc_user_alerts_url() {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_alerts');
  } else {
    return osc_base_url(true) . '?page=user&action=alerts';
  }
}


/**
 * Gets current user alert unsubscribe url
 *
 * @param string $id
 * @param string $email
 * @param string $secret
 *
 * @return string
 */
function osc_user_unsubscribe_alert_url($id = '', $email = '', $secret = '') {
  if($secret=='') { $secret = osc_alert_secret(); }
  if($id=='') { $id = osc_alert_id(); }
  if($email=='') { $email = osc_user_email(); }
  return osc_base_url(true) . '?page=user&action=unsub_alert&email='.urlencode($email).'&secret='.$secret.'&id='.$id;
}


/**
 * Gets user alert activate url
 *
 * @param    $id
 * @param string $secret
 * @param string $email
 *
 * @return string
 */
function osc_user_activate_alert_url($id, $secret , $email) {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_activate_alert') . '/' . $id . '/' . $secret . '/' . urlencode($email);
  } else {
    return osc_base_url(true) . '?page=user&action=activate_alert&email=' . urlencode($email) . '&secret=' . $secret .'&id='.$id;
  }

}

/**
 * Gets current user url
 *
 * @return string
 */
function osc_user_profile_url() {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_profile');
  } else {
    return osc_base_url(true) . '?page=user&action=profile';
  }
}


/**
 * Gets current items page from public profile
 *
 * @param int $page
 * @return string
 */
function osc_user_list_items_pub_profile_url($page = '', $itemsPerPage = false) {
  $path = osc_user_public_profile_url();
  
  if($itemsPerPage !== false && $itemsPerPage > 0) {
    if(osc_rewrite_enabled()) {
      $path .= "?itemsPerPage=" . $itemsPerPage;
    } else {
      $path .= "&itemsPerPage=" . $itemsPerPage;
    }
  }
  
  if($page) {
    if($itemsPerPage !== false && $itemsPerPage > 0) {
      $path .= "&iPage=" . $page;
    } else {
      if(osc_rewrite_enabled()) {
        $path .= "?iPage=" . $page;
      } else {
        $path .= "&iPage=" . $page;
      }
    }
  }

  return $path;
}

/**
 * Gets user's profile url
 *
 * @return string
 */
function osc_user_public_profile_url($id = null, $user = false, $mode = 'username', $params = array()) {
  if($id == null) {
    $id = osc_user_id();
  }

  if($id == osc_user_id() && $user === false) {
    $user = osc_user();
  }
  
  if($id > 0 && ($user === false || (is_array($user) && !isset($user['pk_i_id'])))) {
    $user = osc_get_user_row($id);
  }

  if($params != null && is_array($params) && count($params) > 0) {
    unset($params['page']);       // not pagination page
    unset($params['action']);
    unset($params['id']);
    unset($params['itemsPerPage']);
    unset($params['username']);
    osc_prune_array($params);
  }
  
  $params_count = 0;
  if($params != null && is_array($params) && count($params) > 0) {
    $params_count = count($params);
  }
  
  if(isset($params['sPattern']) && $params['sPattern'] != '') {
    $params['sPattern'] = osc_apply_filter('user_public_profile_pattern', $params['sPattern']);
  }
  
  if(osc_user_public_profile_is_enabled($user) === true) {
    if(osc_rewrite_enabled()) {
      if($params != null && is_array($params) && count($params) > 0) {
        foreach($params as $kp => $vp) {
          $params[$kp] = osc_remove_slash($vp);
        }
      }
      
      // if($mode == 'username' && trim((string)$user['s_username']) != '') {
      if(isset($user['s_username']) && trim((string)$user['s_username']) != '') {
        $url = osc_base_url(false, true) . osc_get_preference('rewrite_user_profile') . '/' . $user['s_username'];
        
      } else if ($id > 0) {
        $url = osc_base_url(false, true) . osc_get_preference('rewrite_user_profile') . '/' . $id;

      } else {
        $url = '';
      }

      
      if($url != '' && $params != null && is_array($params) && count($params) > 0) {
        foreach($params as $k => $v) {
          switch($k) {
            case 'sCountry':
              $k = osc_get_preference('rewrite_search_country');
              break;
              
            case 'sRegion':
              $k = osc_get_preference('rewrite_search_region');
              break;
              
            case 'sCity':
              $k = osc_get_preference('rewrite_search_city');
              break;
              
            case 'sCityArea':
              $k = osc_get_preference('rewrite_search_city_area');
              break;
              
            case 'sCategory':
              $k = osc_get_preference('rewrite_search_category');
              if(is_array($v)) {
                $v = implode(',', $v);
              }
              break;
              
            case 'sUser':
              $k = osc_get_preference('rewrite_search_user');
              if(is_array($v)) {
                $v = implode(',', $v);
              }
              break;
              
            case 'sPattern':
              $k = osc_get_preference('rewrite_search_pattern');
              break;

            default:
              // No action
              break;
          }
          
          // Add additional parameters to URL
          if(!is_array($v) && $v != '') { 
            $url .= '/' . $k . ',' . urlencode($v); 
          }
        }
      }

    } else {
      // Lang code in base URL cannot be used
      $url = sprintf(osc_base_url(true, false) . '?page=user&action=pub_profile&id=%d', $id);

      if($params !== null && is_array($params) && count($params) > 0) {
        foreach($params as $k => $v) {
          if(is_array($v)) { 
            $v = implode(',', $v); 
          }

          $url .= '&' . $k . '=' . urlencode($v);
        }
      }
    }
    
  } else {
    $url = '';
  }

  $url = str_replace('%2C', ',', $url);
  return osc_apply_filter('user_public_profile_url', $url, $id, $user, $params);
}


/**
 * Gets user items url (old version)
 *
 * @param string $page
 * @param string $typeItem
 * @return string
 */
function osc_user_list_items_url($page = '', $typeItem = '') {
  if (osc_rewrite_enabled()) {

    if($page=='') {
      $typeItem = $typeItem != '' ? '?itemType=' . $typeItem : '';
      return osc_base_url(false, true) . osc_get_preference('rewrite_user_items') . $typeItem;
    } else {
      $typeItem = $typeItem != '' ? '&itemType=' . $typeItem  : '';
      return osc_base_url(false, true) . osc_get_preference('rewrite_user_items') . '?iPage=' . $page . $typeItem;
    }
  } else {
    $typeItem = $typeItem != '' ? '&itemType=' . $typeItem  : '';

    if($page=='') {
      return osc_base_url(true) . '?page=user&action=items' . $typeItem;
    } else {
      return osc_base_url(true) . '?page=user&action=items&iPage=' . $page . $typeItem;
    }
  }
}


/**
 * Gets user items url (new version)
 *
 * @param string $options
 * @return string
 */
function osc_user_items_url($params = null) {
  // if (osc_rewrite_enabled()) {

    // if($page=='') {
      // $typeItem = $typeItem != '' ? '?itemType=' . $typeItem : '';
      // return osc_base_url(false, true) . osc_get_preference('rewrite_user_items') . $typeItem;
    // } else {
      // $typeItem = $typeItem != '' ? '&itemType=' . $typeItem  : '';
      // return osc_base_url(false, true) . osc_get_preference('rewrite_user_items') . '?iPage=' . $page . $typeItem;
    // }
  // } else {
    // $typeItem = $typeItem != '' ? '&itemType=' . $typeItem  : '';

    // if($page=='') {
      // return osc_base_url(true) . '?page=user&action=items' . $typeItem;
    // } else {
      // return osc_base_url(true) . '?page=user&action=items&iPage=' . $page . $typeItem;
    // }
  // }

  if($params != null && is_array($params) && count($params) > 0) {
    unset($params['page']);       // not pagination page
    unset($params['action']);
    unset($params['itemsPerPage']);
    osc_prune_array($params);
  }
  
  $params_count = 0;
  if($params != null && is_array($params) && count($params) > 0) {
    $params_count = count($params);
  }
  
  if(isset($params['sPattern']) && $params['sPattern'] != '') {
    $params['sPattern'] = osc_apply_filter('user_items_pattern', $params['sPattern']);
  }

  // NICE URLS
  if(osc_rewrite_enabled()) {
    if($params != null && is_array($params) && count($params) > 0) {
      foreach($params as $kp => $vp) {
        $params[$kp] = osc_remove_slash($vp);
      }
    }
    
    $url = osc_base_url(false, true) . osc_get_preference('rewrite_user_items');
    
    if($params != null && is_array($params) && count($params) > 0) {
      foreach($params as $k => $v) {
        switch($k) {
          case 'sCountry':
            $k = osc_get_preference('rewrite_search_country');
            break;
            
          case 'sRegion':
            $k = osc_get_preference('rewrite_search_region');
            break;
            
          case 'sCity':
            $k = osc_get_preference('rewrite_search_city');
            break;
            
          case 'sCityArea':
            $k = osc_get_preference('rewrite_search_city_area');
            break;
            
          case 'sCategory':
            $k = osc_get_preference('rewrite_search_category');
            if(is_array($v)) {
              $v = implode(',', $v);
            }
            break;
            
          case 'sUser':
            $k = osc_get_preference('rewrite_search_user');
            if(is_array($v)) {
              $v = implode(',', $v);
            }
            break;
            
          case 'sPattern':
            $k = osc_get_preference('rewrite_search_pattern');
            break;

          default:
            // No action
            break;
        }
        
        // Add additional parameters to URL
        if(!is_array($v) && $v != '') { 
          $url .= '/' . $k . ',' . urlencode($v); 
        }
      }
    }

  } else {
  
    // Lang code in base URL cannot be used
    $url = osc_base_url(true, false) . '?page=user&action=items';

    if($params !== null && is_array($params) && count($params) > 0) {
      foreach($params as $k => $v) {
        if(is_array($v)) { 
          $v = implode(',', $v); 
        }

        $url .= '&' . $k . '=' . urlencode($v);
      }
    }
  }

  $url = str_replace('%2C', ',', $url);
  return osc_apply_filter('user_items_url', $url, $params);
}

/**
 * Gets url to change email
 *
 * @return string
 */
function osc_change_user_email_url() {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_change_email');
  } else {
    return osc_base_url(true) . '?page=user&action=change_email';
  }
}

/**
 * Gets url to change username
 *
 * @return string
 */
function osc_change_user_username_url() {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_change_username');
  } else {
    return osc_base_url(true) . '?page=user&action=change_username';
  }
}

/**
 * Gets confirmation url of change email
 *
 * @param int $userId
 * @param string $code
 * @return string
 */
function osc_change_user_email_confirm_url($userId, $code) {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_change_email_confirm') . '/' . $userId . '/' . $code;
  } else {
    return osc_base_url(true) . '?page=user&action=change_email_confirm&userId=' . $userId . '&code=' . $code;
  }
}

/**
 * Gets url for changing password
 *
 * @return string
 */
function osc_change_user_password_url() {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_change_password');
  } else {
    return osc_base_url(true) . '?page=user&action=change_password';
  }
}

/**
 * Gets url for recovering password
 *
 * @return string
 */
function osc_recover_user_password_url() {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_recover');
  } else {
    return osc_base_url(true) . '?page=login&action=recover';
  }
}

/**
 * Gets url for confirm the forgot password process
 *
 * @param int $userId
 * @param string $code
 * @return string
 */
function osc_forgot_user_password_confirm_url($userId, $code) {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_user_forgot') . '/' . $userId . '/' . $code;
  } else {
    return osc_base_url(true) . '?page=login&action=forgot&userId='.$userId.'&code='.$code;
  }
}

/**
 * Gets url for confirmation admin password recover proces
 *
 * @param int $adminId
 * @param string $code
 * @return string
 */
function osc_forgot_admin_password_confirm_url($adminId, $code) {
  return osc_admin_base_url(true) . '?page=login&action=forgot&adminId='.$adminId.'&code='.$code;
}

/**
 * Gets url for changing website language (for users)
 * Support subdomain URLs as well
 *
 * @param string $locale
 * @return string
 */
function osc_change_language_url($locale) {
  if(osc_subdomain_type() == 'language') {
    $http_url = osc_is_ssl() ? "https://" : "http://";
    return $http_url . osc_subdomain_locale_slug($locale) . '.' . osc_subdomain_host() . REL_WEB_URL;
  } else if(osc_rewrite_enabled()) {
    if(osc_locale_to_base_url_enabled()) {      // os810
      return osc_base_url() . osc_base_url_locale_slug($locale) . '/';
    } else {
      return osc_base_url() . osc_get_preference('rewrite_language') . '/' . $locale;
    }
  } else {
    return osc_base_url(true) . '?page=language&locale=' . $locale;
  }
}

/////////////////////////////////////
//     functions for items     //
/////////////////////////////////////

/**
 * Gets url for editing an item
 *
 * @param string $secret
 * @param string $id
 * @return string
 */
function osc_item_edit_url($secret = '', $id = '') {
  if ($id == '') { $id = osc_item_id(); }
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_item_edit') . '/' . $id . '/' . $secret;
  } else {
    return osc_base_url(true) . '?page=item&action=item_edit&id=' . $id . ($secret != '' ? '&secret=' . $secret : '');
  }
}


/**
 * Gets url for delete an item
 *
 * @param string $secret
 * @param string $id
 * @return string
 */
function osc_item_delete_url($secret = '', $id = '') {
  if ($id == '') { $id = osc_item_id(); }
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_item_delete') . '/' . $id . '/' . $secret;
  } else {
    return osc_base_url(true) . '?page=item&action=item_delete&id=' . $id . ($secret != '' ? '&secret=' . $secret : '');
  }
}


/**
 * Gets url for activate an item
 *
 * @param string $secret
 * @param string $id
 * @return string
 */
function osc_item_activate_url($secret = '', $id = '') {
  if ($id == '') { $id = osc_item_id(); }
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_item_activate') . '/' . $id . '/' . $secret;
  } else {
    return osc_base_url(true) . '?page=item&action=activate&id=' . $id . ($secret != '' ? '&secret=' . $secret : '');
  }
}


/**
 * Gets url for deactivate an item
 *
 * @param string $secret
 * @param string $id
 * @return string
 */
function osc_item_deactivate_url($secret = '', $id = '') {
  if ($id == '') { $id = osc_item_id(); }
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_item_deactivate') . '/' . $id . '/' . $secret;
  } else {
    return osc_base_url(true) . '?page=item&action=deactivate&id=' . $id . ($secret != '' ? '&secret=' . $secret : '');
  }
}


/**
 * Gets url for item renewal
 *
 * @param string $secret
 * @param string $id
 * @return string
 */
function osc_item_renew_url($secret = '', $id = '') {
  if ($id == '') { $id = osc_item_id(); }
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_item_renew') . '/' . $id . '/' . $secret;
  } else {
    return osc_base_url(true) . '?page=item&action=renew&id=' . $id . ($secret != '' ? '&secret=' . $secret : '');
  }
}


/**
 * Gets url for deleting a resource of an item
 *
 * @param int $id of the resource
 * @param int $item
 * @param string $code
 * @param string $secret
 * @return string
 */
function osc_item_resource_delete_url($id, $item, $code, $secret = '') {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_item_resource_delete') . '/' . $id . '/' . $item . '/' . $code . ($secret != '' ? '/' . $secret : '');
  } else {
    return osc_base_url(true) . '?page=item&action=deleteResource&id=' . $id . '&item=' . $item . '&code=' . $code . ($secret != '' ? '&secret=' . $secret : '');
  }
}

/**
 * Gets url of send a friend (current item)
 *
 * @return string
 */
function osc_item_send_friend_url() {
  if (osc_rewrite_enabled()) {
    return osc_base_url(false, true) . osc_get_preference('rewrite_item_send_friend') . '/' . osc_item_id();
  } else {
    return osc_base_url(true) . '?page=item&action=send_friend&id=' . osc_item_id();
  }
}


/**
 * @param $id
 * @param $args
 *
 * @since 3.2
 * @return string
 */
function osc_route_url($id, $args = array()) {
  $routes = Rewrite::newInstance()->getRoutes();
  if(!isset($routes[$id])) { return ''; }
  if (osc_rewrite_enabled()) {
    $uri = $routes[$id]['url'];
    $params_url = '';
    foreach($args as $k => $v) {
      $old_uri = $uri;
      $uri = str_ireplace('{'.$k.'}', $v, $uri);
      if($old_uri==$uri) {
        $params_url .= '&'.$k.'='.$v;
      }
    }
    return osc_base_url(false, true).$uri.(($params_url!='')?'?'.$params_url:'');
  } else {
    $params_url = '';
    foreach($args as $k => $v) {
      $params_url .= '&'.$k.'='.$v;
    }
    return osc_base_url(true) . '?page=custom&route=' . $id . $params_url;
  }
}


/**
 * @param $id
 * @param $args
 *
 * @since 3.2
 * @return string
 */
function osc_route_admin_url($id, $args = array()) {
  $routes = Rewrite::newInstance()->getRoutes();
  if(!isset($routes[$id])) { return ''; }
  $params_url = '';
  foreach($args as $k => $v) {
    $params_url .= '&'.$k.'='.$v;
  }
  return osc_admin_base_url(true) . '?page=plugins&action=renderplugin&route=' . $id . $params_url;
}


/**
 * @param $id
 * @param $args
 *
 * @since 3.2
 * @return string
 */
function osc_route_ajax_url($id, $args = array()) {
  $routes = Rewrite::newInstance()->getRoutes();
  if(!isset($routes[$id])) { return ''; }
  $params_url = '';
  foreach($args as $k => $v) {
    $params_url .= '&'.$k.'='.$v;
  }
  return osc_base_url(true) . '?page=ajax&action=custom&route=' . $id . $params_url;
}


/**
 * @param $id
 * @param $args
 *
 * @since 3.2
 * @return string
 */
function osc_route_admin_ajax_url($id, $args = array()) {
  $routes = Rewrite::newInstance()->getRoutes();
  if(!isset($routes[$id])) { return ''; }
  $params_url = '';
  foreach($args as $k => $v) {
    $params_url .= '&'.$k.'='.$v;
  }
  return osc_admin_base_url(true) . '?page=ajax&action=custom&route=' . $id . $params_url;
}


/**
 * Prints the additional options to the menu
 *
 * @param array $option with options of the form array('name' => 'display name', 'url' => 'url of link')
 *
 * @return void
 */
function osc_add_option_menu($option = null) {
  if($option!=null) {
    echo '<li><a href="' . $option['url'] . '" >' . $option['name'] . '</a></li>';
  }
}

/**
 * Get if user is on homepage
 *
 * @return boolean
 */
function osc_is_home_page() {
  return osc_is_current_page('', '');
}

/**
 * Get if user is on search page
 *
 * @return boolean
 */
function osc_is_search_page() {
  return osc_is_current_page('search', '');
}

/**
 * Get if user is on a static page
 *
 * @return boolean
 */
function osc_is_static_page() {
  return osc_is_current_page('page', '');
}

/**
 * Get if user is on a contact page
 *
 * @return boolean
 */
function osc_is_contact_page() {
  return osc_is_current_page('contact', '');
}

/**
 * Get if user is on ad page
 *
 * @return boolean
 */
function osc_is_ad_page() {
  return osc_is_current_page('item', '');
}

/**
 * Get if user is on publish page
 *
 * @return boolean
 */
function osc_is_publish_page() {
  return osc_is_current_page('item', 'item_add');
}

/**
 * Get if user is on edit page
 *
 * @return boolean
 */
function osc_is_edit_page() {
  return osc_is_current_page('item', 'item_edit');
}

/**
 * Get if user is on a item contact page
 *
 * @return boolean
 */
function osc_is_item_contact_page() {
  return osc_is_current_page('item', 'contact');
}

/**
 * Get if user is on login form
 *
 * @return boolean
 * @deprecated since version 3.5.7 use osc_is_login_page()
 */
function osc_is_login_form() {
  return osc_is_current_page('login', '');
}

/**
 * Get if user is on login page
 *
 * @return boolean
 */
function osc_is_login_page() {
  return osc_is_current_page('login', '');
}

/**
 * Get if user is on register page
 *
 * @return boolean
 */
function osc_is_register_page() {
  return osc_is_current_page('register' , 'register');
}

/**
 * Get if the user is on recover page
 *
 * @return boolean
 */
function osc_is_recover_page() {
  return osc_is_current_page('login', 'recover');
}

/**
 * Get if the user is on forgot page
 *
 * @return boolean
 */
function osc_is_forgot_page() {
  return osc_is_current_page('login', 'forgot');
}


/**
 * Get if the user is on custom page
 *
 * @param null $value
 * @return boolean
 */
function osc_is_custom_page($value = null) {
  if(Rewrite::newInstance()->get_location() === 'custom') {
    if($value==null || Params::getParam('file')==$value || Params::getParam('route')==$value) {
      return true;
    }
  }
  return false;
}

/**
 * Get if the user is on public profile page
 *
 * @return boolean
 */
function osc_is_public_profile() {
  return osc_is_current_page('user', 'pub_profile');
}

/**
 * Get if user is on user dashboard
 *
 * @return boolean
 */
function osc_is_user_dashboard() {
  return osc_is_current_page('user', 'dashboard');
}

/**
 * Get if user is on user profile
 *
 * @return boolean
 */
function osc_is_user_profile() {
  return osc_is_current_page('user', 'profile');
}

/**
 * Get if the user is on user's items page
 *
 * @return boolean
 */
function osc_is_list_items() {
  return osc_is_current_page('user', 'items');
}

/**
 * Get if the user is on user's alerts page
 *
 * @return boolean
 */
function osc_is_list_alerts() {
  return osc_is_current_page('user', 'alerts');
}

/**
 * Get if user is on change email page
 *
 * @return boolean
 */
function osc_is_change_email_page() {
  return osc_is_current_page('user', 'change_email');
}

/**
 * Get if user is on change username page
 *
 * @return boolean
 */
function osc_is_change_username_page() {
  return osc_is_current_page('user', 'change_username');
}

/**
 * Get if user is on change password page
 *
 * @return boolean
 */
function osc_is_change_password_page() {
  return osc_is_current_page('user', 'change_password');
}

/**
 * Get if the user is on page
 *
 * @param string $location of the resource
 * @param string $section
 * @return boolean
 */
function osc_is_current_page($location, $section) {
  return osc_get_osclass_location() === $location && osc_get_osclass_section() === $section;
}  

/**
 * Get if the user is on 404 error page
 *
 * @return boolean
 */
function osc_is_404() {
  return (Rewrite::newInstance()->get_location() === 'error');
}



/**
 * Get location
 *
 * @return string
 */
function osc_get_osclass_location() {
  return Rewrite::newInstance()->get_location();
}

/**
 * Get section
 *
 * @return string
 */
function osc_get_osclass_section() {
  return Rewrite::newInstance()->get_section();
}


/**
 * Check is an admin is a super admin or only a moderator
 *
 * @return boolean
 */
function osc_is_moderator() {
  $admin = Admin::newInstance()->findByPrimaryKey(osc_logged_admin_id());

  return isset($admin[ 'b_moderator' ]) && $admin[ 'b_moderator' ] != 0;
}


/**
 * @return mixed
 */
function osc_get_domain() {
  $result = parse_url(osc_base_url());

  return $result['host'];
}


/**
 * Get top level domain Osclass is running on
 *
 * @return string|void
 */
function osc_get_parent_domain($url = '', $tld = false) {
  if($url == '') {
    $url = osc_base_url();
  }
  
  $url = preg_replace('/^https:\/\/www./', 'https://', $url);
  $url = preg_replace('/^http:\/\/www./', 'http://', $url);
  $url = preg_replace('/^www./', '', $url);
  $url = preg_replace('/\/$/', '', $url);
  
  $pieces = parse_url($url);
  $domain = isset($pieces['host']) ? $pieces['host'] : '';
  
  if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $m)) {
    return ($tld === true) ? substr($m['domain'], ($pos = strpos($m['domain'], '.')) !== false ? $pos + 1 : 0) : $m['domain'];
  }
  
  return preg_replace(array('/^https:\/\/www./', '/^http:\/\/www./', '/^www./', '/\/$/'), '', ($domain <> '' ? $domain : $url));
}


/**
 * @param string $separator
 * @param bool   $echo
 * @param array  $lang
 *
 * @return string|void
 */
function osc_breadcrumb($separator = '&raquo;', $echo = true, $lang = array()) {
  $br = new Breadcrumb($lang);
  $br->init();
  
  $data = $br->render($separator);
  
  if($echo === true) {
    echo $data;
  }
  
  return $data;
}

/**
 * @return mixed|string
 */
function osc_subdomain_id() {
  return View::newInstance()->_get('subdomain_id');
}

/**
 * @return mixed|string
 */
function osc_subdomain_name() {
  return View::newInstance()->_get('subdomain_name');
}


/**
 * @return mixed|string
 */
function osc_subdomain_slug() {
  return View::newInstance()->_get('subdomain_slug');
}

/**
 * @return mixed|string
 */
function osc_subdomain_param() {
  return View::newInstance()->_get('subdomain_param');
}

/**
 * @return bool
 */
function osc_is_subdomain() {
  if(osc_subdomain_slug() != '') {
    return true;
  }
  
  return false;
}


/**
 * @return bool
 */
function osc_is_topdomain() {
  return !osc_is_subdomain();
}


/**
 * Return true if subdomains are enabled
 *
 * @return boolean
 */
function osc_subdomain_enabled() {
  if(osc_subdomain_type() != '') {
    return true;
  }
  
  return false;
}

/**
 * Return name of subdomain type
 *
 * @return boolean
 */
function osc_subdomain_type_name() {
  switch(osc_subdomain_type()) {
    case 'country': return __('Country');
    case 'region': return __('Region');
    case 'city': return __('City');
    case 'category': return __('Category');
    case 'user': return __('User');
  }
  
  return __('None');
}


/**
 * Return true if subdomains are enabled and current domain is top/parent/base domain
 *
 * @return bool
 */
function osc_is_subdomain_base() {
  if(osc_subdomain_enabled()) {
    if(!osc_is_subdomain()) {
      return true;
    }
  }

  return false;
}


/**
 * Create new email template in all locales
 *
 * $internal_name - xyz_my_custom_template
 * $title - {WEB_TITLE} - New info #{KEYWORD0}
 * $text - <p>Hi {CONTACT_NAME}!</p><p>{KEYWORD1}</p><p>{KEYWORD2}</p><p><br/></p><p>Thank you, <br />{WEB_TITLE}</p>
 */
function osc_email_template_create($internal_name, $title, $text) {
  $internal_name = osc_sanitizeString($internal_name);
  
  if($internal_name == '') {
    return false;
  }

  $locales = OSCLocale::newInstance()->listAll();
  $page = Page::newInstance()->findByInternalName($internal_name);
  $email_data = array();

  // Template already exists
  if($page !== false && isset($page['pk_i_id'])) {
    return false;
  }

  foreach($locales as $l) {
    $email_data[$l['pk_c_code']]['s_title'] = $title;
    $email_data[$l['pk_c_code']]['s_text'] = $text;
  }

  Page::newInstance()->insert(array(
    's_internal_name' => $internal_name, 
    'b_indelible' => '1'
  ), $email_data);
}


// Remove email template
function osc_email_template_delete($internal_name) {
  $internal_name = osc_sanitizeString($internal_name);
  
  if($internal_name == '') {
    return false;
  }
  
  $page = Page::newInstance()->findByInternalName($internal_name);

  if($page !== false && isset($page['pk_i_id'])) {
    Page::newInstance()->deleteByPrimaryKey($page['pk_i_id']);
    return true;
  }
  
  return false;
}
  

/* file end: ./oc-includes/osclass/helpers/hDefines.php */